<?php
namespace App\Models;

class WorkshopOps extends Model {
    public function getHourlyRates() {
        $stmt = $this->db->query("SELECT * FROM workshop_hourly_rates ORDER BY amount ASC");
        return $stmt->fetchAll();
    }

    public function getOrdersSummary() {
        $sql = "
            SELECT
                wo.id,
                wo.status,
                wo.priority,
                wo.created_at,
                wo.estimated_delivery_at,
                c.name AS client_name,
                v.plate,
                v.brand,
                v.model,
                COALESCE(t.worked_minutes, 0) AS worked_minutes,
                COALESCE(t.paused_minutes, 0) AS paused_minutes,
                COALESCE(t.billable_minutes, 0) AS billable_minutes,
                COALESCE(t.non_billable_minutes, 0) AS non_billable_minutes,
                COALESCE(t.labor_cost, 0) AS labor_cost,
                COALESCE(s.services_cost, 0) AS services_cost,
                COALESCE(p.parts_cost, 0) AS parts_cost
            FROM work_orders wo
            INNER JOIN clients c ON c.id = wo.client_id
            INNER JOIN vehicles v ON v.id = wo.vehicle_id
            LEFT JOIN (
                SELECT
                    te.work_order_id,
                    SUM(GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0)) AS worked_minutes,
                    SUM(te.paused_minutes) AS paused_minutes,
                    SUM(CASE WHEN te.billable = 1 THEN GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) ELSE 0 END) AS billable_minutes,
                    SUM(CASE WHEN te.billable = 0 THEN GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) ELSE 0 END) AS non_billable_minutes,
                    SUM(CASE WHEN te.billable = 1 THEN ((GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) / 60) * te.hourly_rate) ELSE 0 END) AS labor_cost
                FROM work_order_time_entries te
                GROUP BY te.work_order_id
            ) t ON t.work_order_id = wo.id
            LEFT JOIN (
                SELECT work_order_id, SUM(quantity * price) AS services_cost
                FROM work_order_services
                GROUP BY work_order_id
            ) s ON s.work_order_id = wo.id
            LEFT JOIN (
                SELECT work_order_id, SUM(quantity * price) AS parts_cost
                FROM work_order_parts
                GROUP BY work_order_id
            ) p ON p.work_order_id = wo.id
            ORDER BY wo.created_at DESC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getOrderDetails($workOrderId) {
        $stmt = $this->db->prepare("SELECT wo.*, c.name AS client_name, v.plate, v.brand, v.model, v.year FROM work_orders wo INNER JOIN clients c ON c.id = wo.client_id INNER JOIN vehicles v ON v.id = wo.vehicle_id WHERE wo.id = :id");
        $stmt->execute(['id' => $workOrderId]);
        return $stmt->fetch();
    }

    public function getActiveTimeEntry($workOrderId, $mechanicId = null) {
        $sql = "SELECT * FROM work_order_time_entries WHERE work_order_id = :work_order_id AND status IN ('running','paused')";
        $params = ['work_order_id' => $workOrderId];

        if ($mechanicId !== null) {
            $sql .= " AND mechanic_id = :mechanic_id";
            $params['mechanic_id'] = $mechanicId;
        }

        $sql .= " ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function startWork($workOrderId, $mechanicId, $rateCode, $notes = null) {
        $rateStmt = $this->db->prepare("SELECT * FROM workshop_hourly_rates WHERE code = :code LIMIT 1");
        $rateStmt->execute(['code' => $rateCode]);
        $rate = $rateStmt->fetch();

        if (!$rate) {
            return false;
        }

        $hourlyRate = (float)$rate['amount'];
        if ((int)$rate['billable'] === 1) {
            $userStmt = $this->db->prepare("SELECT hourly_rate FROM users WHERE id = :id LIMIT 1");
            $userStmt->execute(['id' => $mechanicId]);
            $userRate = $userStmt->fetchColumn();
            if ($userRate !== false && $userRate !== null && (float)$userRate > 0) {
                $hourlyRate = (float)$userRate;
            }
        }

        $stmt = $this->db->prepare("INSERT INTO work_order_time_entries (work_order_id, mechanic_id, rate_code, hourly_rate, billable, status, started_at, notes) VALUES (:work_order_id, :mechanic_id, :rate_code, :hourly_rate, :billable, 'running', NOW(), :notes)");
        return $stmt->execute([
            'work_order_id' => $workOrderId,
            'mechanic_id' => $mechanicId,
            'rate_code' => $rate['code'],
            'hourly_rate' => $hourlyRate,
            'billable' => (int)$rate['billable'],
            'notes' => $notes,
        ]);
    }

    public function pauseWork($timeEntryId, $reason, $notes = null) {
        $stmt = $this->db->prepare("UPDATE work_order_time_entries SET status = 'paused', paused_started_at = NOW() WHERE id = :id AND status = 'running'");
        $updated = $stmt->execute(['id' => $timeEntryId]);

        if ($updated) {
            $pauseStmt = $this->db->prepare("INSERT INTO work_order_pause_events (time_entry_id, reason, notes, started_at) VALUES (:time_entry_id, :reason, :notes, NOW())");
            $pauseStmt->execute([
                'time_entry_id' => $timeEntryId,
                'reason' => $reason,
                'notes' => $notes,
            ]);
        }

        return $updated;
    }

    public function resumeWork($timeEntryId) {
        $stmt = $this->db->prepare("SELECT paused_started_at, paused_minutes FROM work_order_time_entries WHERE id = :id AND status = 'paused'");
        $stmt->execute(['id' => $timeEntryId]);
        $entry = $stmt->fetch();

        if (!$entry || empty($entry['paused_started_at'])) {
            return false;
        }

        $minutesStmt = $this->db->prepare("SELECT TIMESTAMPDIFF(MINUTE, :paused_started_at, NOW())");
        $minutesStmt->execute(['paused_started_at' => $entry['paused_started_at']]);
        $extraMinutes = (int)$minutesStmt->fetchColumn();

        $updStmt = $this->db->prepare("UPDATE work_order_time_entries SET status = 'running', paused_minutes = paused_minutes + :extra_minutes, paused_started_at = NULL WHERE id = :id");
        $ok = $updStmt->execute([
            'extra_minutes' => max(0, $extraMinutes),
            'id' => $timeEntryId,
        ]);

        if ($ok) {
            $eventStmt = $this->db->prepare("UPDATE work_order_pause_events SET ended_at = NOW() WHERE time_entry_id = :time_entry_id AND ended_at IS NULL ORDER BY id DESC LIMIT 1");
            $eventStmt->execute(['time_entry_id' => $timeEntryId]);
        }

        return $ok;
    }

    public function finishWork($timeEntryId) {
        $stmt = $this->db->prepare("SELECT status, paused_started_at FROM work_order_time_entries WHERE id = :id");
        $stmt->execute(['id' => $timeEntryId]);
        $entry = $stmt->fetch();

        if (!$entry) {
            return false;
        }

        if ($entry['status'] === 'paused' && !empty($entry['paused_started_at'])) {
            $this->resumeWork($timeEntryId);
        }

        $finishStmt = $this->db->prepare("UPDATE work_order_time_entries SET status = 'finished', ended_at = NOW() WHERE id = :id");
        return $finishStmt->execute(['id' => $timeEntryId]);
    }

    public function getTimeEntriesByOrder($workOrderId) {
        $stmt = $this->db->prepare("SELECT te.*, u.name AS mechanic_name, r.label AS rate_label FROM work_order_time_entries te INNER JOIN users u ON u.id = te.mechanic_id INNER JOIN workshop_hourly_rates r ON r.code = te.rate_code WHERE te.work_order_id = :work_order_id ORDER BY te.id DESC");
        $stmt->execute(['work_order_id' => $workOrderId]);
        return $stmt->fetchAll();
    }

    public function getMetricsByOrder($workOrderId) {
        $sql = "
            SELECT
                COALESCE(SUM(GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0)), 0) AS worked_minutes,
                COALESCE(SUM(te.paused_minutes), 0) AS paused_minutes,
                COALESCE(SUM(CASE WHEN te.billable = 1 THEN GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) ELSE 0 END), 0) AS billable_minutes,
                COALESCE(SUM(CASE WHEN te.billable = 0 THEN GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) ELSE 0 END), 0) AS non_billable_minutes,
                COALESCE(SUM(CASE WHEN te.billable = 1 THEN ((GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) / 60) * te.hourly_rate) ELSE 0 END), 0) AS labor_cost,
                COALESCE((SELECT SUM(quantity * price) FROM work_order_services WHERE work_order_id = :work_order_id2), 0) AS services_cost,
                COALESCE((SELECT SUM(quantity * price) FROM work_order_parts WHERE work_order_id = :work_order_id3), 0) AS parts_cost
            FROM work_order_time_entries te
            WHERE te.work_order_id = :work_order_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'work_order_id' => $workOrderId,
            'work_order_id2' => $workOrderId,
            'work_order_id3' => $workOrderId,
        ]);

        $row = $stmt->fetch();
        if (!$row) {
            $row = [
                'worked_minutes' => 0,
                'paused_minutes' => 0,
                'billable_minutes' => 0,
                'non_billable_minutes' => 0,
                'labor_cost' => 0,
                'services_cost' => 0,
                'parts_cost' => 0,
            ];
        }

        $discount = 0;
        $subtotal = (float)$row['labor_cost'] + (float)$row['services_cost'] + (float)$row['parts_cost'];
        $taxRate = 0.19;
        $tax = max(0, ($subtotal - $discount) * $taxRate);

        $row['discount_amount'] = $discount;
        $row['tax_amount'] = $tax;
        $row['total_amount'] = max(0, $subtotal - $discount + $tax);

        return $row;
    }

    public function saveQualityChecklist($workOrderId, $data) {
        $existingStmt = $this->db->prepare("SELECT id FROM work_order_quality_checks WHERE work_order_id = :work_order_id LIMIT 1");
        $existingStmt->execute(['work_order_id' => $workOrderId]);
        $exists = $existingStmt->fetchColumn();

        $payload = [
            'work_order_id' => $workOrderId,
            'work_done_ok' => !empty($data['work_done_ok']) ? 1 : 0,
            'torque_applied_ok' => !empty($data['torque_applied_ok']) ? 1 : 0,
            'no_leaks_ok' => !empty($data['no_leaks_ok']) ? 1 : 0,
            'no_dashboard_lights_ok' => !empty($data['no_dashboard_lights_ok']) ? 1 : 0,
            'road_test_ok' => !empty($data['road_test_ok']) ? 1 : 0,
            'cleaning_ok' => !empty($data['cleaning_ok']) ? 1 : 0,
            'client_informed_ok' => !empty($data['client_informed_ok']) ? 1 : 0,
            'signed_by_user_id' => $data['signed_by_user_id'] ?? null,
            'signed_name' => $data['signed_name'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($exists) {
            $stmt = $this->db->prepare("UPDATE work_order_quality_checks SET work_done_ok = :work_done_ok, torque_applied_ok = :torque_applied_ok, no_leaks_ok = :no_leaks_ok, no_dashboard_lights_ok = :no_dashboard_lights_ok, road_test_ok = :road_test_ok, cleaning_ok = :cleaning_ok, client_informed_ok = :client_informed_ok, signed_by_user_id = :signed_by_user_id, signed_name = :signed_name, signed_at = NOW(), notes = :notes WHERE work_order_id = :work_order_id");
            return $stmt->execute($payload);
        }

        $stmt = $this->db->prepare("INSERT INTO work_order_quality_checks (work_order_id, work_done_ok, torque_applied_ok, no_leaks_ok, no_dashboard_lights_ok, road_test_ok, cleaning_ok, client_informed_ok, signed_by_user_id, signed_name, signed_at, notes) VALUES (:work_order_id, :work_done_ok, :torque_applied_ok, :no_leaks_ok, :no_dashboard_lights_ok, :road_test_ok, :cleaning_ok, :client_informed_ok, :signed_by_user_id, :signed_name, NOW(), :notes)");
        return $stmt->execute($payload);
    }

    public function getQualityChecklist($workOrderId) {
        $stmt = $this->db->prepare("SELECT * FROM work_order_quality_checks WHERE work_order_id = :work_order_id LIMIT 1");
        $stmt->execute(['work_order_id' => $workOrderId]);
        return $stmt->fetch();
    }

    public function createBillingDocument($workOrderId, $data, $createdBy) {
        $metrics = $this->getMetricsByOrder($workOrderId);

        $stmt = $this->db->prepare("INSERT INTO billing_documents (work_order_id, document_type, document_number, issued_at, payment_method, payment_status, pending_balance, labor_subtotal, services_subtotal, parts_subtotal, supplies_subtotal, tax_amount, discount_amount, total_amount, created_by) VALUES (:work_order_id, :document_type, :document_number, NOW(), :payment_method, :payment_status, :pending_balance, :labor_subtotal, :services_subtotal, :parts_subtotal, :supplies_subtotal, :tax_amount, :discount_amount, :total_amount, :created_by)");

        $total = (float)$metrics['total_amount'];
        $pending = ($data['payment_status'] ?? 'pendiente') === 'pagado' ? 0 : $total;

        return $stmt->execute([
            'work_order_id' => $workOrderId,
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_status' => $data['payment_status'] ?? 'pendiente',
            'pending_balance' => $pending,
            'labor_subtotal' => $metrics['labor_cost'],
            'services_subtotal' => $metrics['services_cost'],
            'parts_subtotal' => $metrics['parts_cost'],
            'supplies_subtotal' => 0,
            'tax_amount' => $metrics['tax_amount'],
            'discount_amount' => $metrics['discount_amount'],
            'total_amount' => $metrics['total_amount'],
            'created_by' => $createdBy,
        ]);
    }

    public function getBillingDocuments($workOrderId) {
        $stmt = $this->db->prepare("SELECT bd.*, u.name AS created_by_name FROM billing_documents bd LEFT JOIN users u ON u.id = bd.created_by WHERE bd.work_order_id = :work_order_id ORDER BY bd.id DESC");
        $stmt->execute(['work_order_id' => $workOrderId]);
        return $stmt->fetchAll();
    }

    // Mechanic productivity reports
    public function getMechanicProductivity($startDate = null, $endDate = null) {
        $startDate = $startDate ?? date('Y-m-01'); // First day of current month
        $endDate = $endDate ?? date('Y-m-d');

        $sql = "
            SELECT 
                u.id AS mechanic_id,
                u.name AS mechanic_name,
                u.hourly_rate,
                COUNT(DISTINCT te.work_order_id) AS orders_worked,
                COUNT(te.id) AS sessions_count,
                SUM(GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0)) AS total_minutes,
                SUM(CASE WHEN te.billable = 1 THEN GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) ELSE 0 END) AS billable_minutes,
                SUM(CASE WHEN te.billable = 0 THEN GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) ELSE 0 END) AS non_billable_minutes,
                SUM(CASE WHEN te.billable = 1 THEN ((GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) / 60) * te.hourly_rate) ELSE 0 END) AS total_billed,
                AVG(GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0)) AS avg_session_minutes
            FROM users u
            INNER JOIN work_order_time_entries te ON te.mechanic_id = u.id
            WHERE u.role = 2
            AND DATE(te.started_at) BETWEEN :start_date AND :end_date
            GROUP BY u.id, u.name, u.hourly_rate
            ORDER BY total_billed DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll();
    }

    public function getMechanicDailyDetail($mechanicId, $date) {
        $sql = "
            SELECT 
                te.*,
                wo.id AS work_order_id,
                c.name AS client_name,
                v.plate,
                v.brand,
                v.model,
                wr.code AS rate_code,
                wr.label AS rate_label
            FROM work_order_time_entries te
            INNER JOIN work_orders wo ON wo.id = te.work_order_id
            INNER JOIN clients c ON c.id = wo.client_id
            INNER JOIN vehicles v ON v.id = wo.vehicle_id
            LEFT JOIN workshop_hourly_rates wr ON wr.code = te.rate_code
            WHERE te.mechanic_id = :mechanic_id
            AND DATE(te.started_at) = :date
            ORDER BY te.started_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mechanic_id' => $mechanicId, 'date' => $date]);
        return $stmt->fetchAll();
    }

    public function getTeamEfficiencyMetrics($startDate = null, $endDate = null) {
        $startDate = $startDate ?? date('Y-m-01');
        $endDate = $endDate ?? date('Y-m-d');

        $sql = "
            SELECT 
                COUNT(DISTINCT te.mechanic_id) AS total_mechanics,
                COUNT(DISTINCT te.work_order_id) AS total_orders,
                SUM(GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0)) AS total_minutes,
                SUM(CASE WHEN te.billable = 1 THEN GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) ELSE 0 END) AS billable_minutes,
                SUM(CASE WHEN te.billable = 1 THEN ((GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0) / 60) * te.hourly_rate) ELSE 0 END) AS total_billed,
                AVG(GREATEST(TIMESTAMPDIFF(MINUTE, te.started_at, COALESCE(te.ended_at, NOW())) - te.paused_minutes, 0)) AS avg_session_minutes,
                COUNT(CASE WHEN qc.id IS NOT NULL AND qc.status = 'aprobado' THEN 1 END) AS approved_quality_checks,
                COUNT(CASE WHEN qc.id IS NOT NULL THEN 1 END) AS total_quality_checks
            FROM work_order_time_entries te
            LEFT JOIN work_order_quality_checks qc ON qc.work_order_id = te.work_order_id
            WHERE DATE(te.started_at) BETWEEN :start_date AND :end_date
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetch();
    }

    // Manager Dashboard - KPIs and financial metrics
    public function getManagerDashboardData($startDate = null, $endDate = null) {
        $startDate = $startDate ?? date('Y-m-01');
        $endDate = $endDate ?? date('Y-m-d');

        // Financial metrics
        $financialSql = "
            SELECT 
                COALESCE(SUM(bd.total_amount), 0) AS total_revenue,
                COALESCE(SUM(bd.parts_subtotal), 0) AS parts_revenue,
                COALESCE(SUM(bd.labor_subtotal), 0) AS labor_revenue,
                COALESCE(SUM(bd.services_subtotal), 0) AS services_revenue,
                COALESCE(SUM(bd.tax_amount), 0) AS tax_collected,
                COUNT(bd.id) AS invoices_issued,
                COALESCE(SUM(CASE WHEN bd.payment_status = 'pagado' THEN bd.total_amount ELSE 0 END), 0) AS collected_amount,
                COALESCE(SUM(CASE WHEN bd.payment_status = 'pendiente' THEN bd.pending_balance ELSE 0 END), 0) AS pending_collection
            FROM billing_documents bd
            WHERE DATE(bd.issued_at) BETWEEN :start_date AND :end_date
        ";
        $stmt = $this->db->prepare($financialSql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $financial = $stmt->fetch();

        // Work order metrics
        $woSql = "
            SELECT 
                COUNT(*) AS total_orders,
                COUNT(CASE WHEN status = 'completada' THEN 1 END) AS completed_orders,
                COUNT(CASE WHEN status = 'en_progreso' THEN 1 END) AS in_progress_orders,
                COUNT(CASE WHEN status = 'recepcion' THEN 1 END) AS pending_orders,
                COUNT(CASE WHEN status = 'calidad' THEN 1 END) AS quality_check_orders,
                AVG(CASE WHEN status = 'completada' THEN TIMESTAMPDIFF(HOUR, created_at, delivered_at) END) AS avg_completion_hours
            FROM work_orders
            WHERE DATE(created_at) BETWEEN :start_date AND :end_date
        ";
        $stmt = $this->db->prepare($woSql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $workOrders = $stmt->fetch();

        // Services breakdown
        $servicesSql = "
            SELECT 
                s.name AS service_name,
                COUNT(wos.id) AS times_sold,
                SUM(wos.quantity * wos.price) AS total_revenue
            FROM work_order_services wos
            JOIN services s ON s.id = wos.service_id
            JOIN work_orders wo ON wo.id = wos.work_order_id
            WHERE DATE(wo.created_at) BETWEEN :start_date AND :end_date
            GROUP BY s.id, s.name
            ORDER BY total_revenue DESC
            LIMIT 10
        ";
        $stmt = $this->db->prepare($servicesSql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $topServices = $stmt->fetchAll();

        // Daily trend for charts
        $trendSql = "
            SELECT 
                DATE(issued_at) AS date,
                COUNT(*) AS invoices,
                SUM(total_amount) AS revenue
            FROM billing_documents
            WHERE DATE(issued_at) BETWEEN :start_date AND :end_date
            GROUP BY DATE(issued_at)
            ORDER BY date
        ";
        $stmt = $this->db->prepare($trendSql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $dailyTrend = $stmt->fetchAll();

        // Parts inventory alerts
        $inventorySql = "
            SELECT 
                COUNT(*) AS low_stock_count,
                SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) AS out_of_stock_count
            FROM parts
            WHERE quantity <= min_stock
        ";
        $stmt = $this->db->query($inventorySql);
        $inventory = $stmt->fetch();

        // Customer metrics
        $customerSql = "
            SELECT 
                COUNT(DISTINCT client_id) AS unique_customers,
                COUNT(DISTINCT CASE WHEN DATE(created_at) BETWEEN :start_date AND :end_date THEN client_id END) AS new_customers_this_period
            FROM work_orders
        ";
        $stmt = $this->db->prepare($customerSql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $customers = $stmt->fetch();

        return [
            'financial' => $financial,
            'work_orders' => $workOrders,
            'top_services' => $topServices,
            'daily_trend' => $dailyTrend,
            'inventory' => $inventory,
            'customers' => $customers,
            'period' => ['start' => $startDate, 'end' => $endDate]
        ];
    }

    public function getPreviousPeriodComparison($startDate, $endDate) {
        // Calculate previous period dates
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $diff = $start->diff($end);
        
        $prevEnd = clone $start;
        $prevEnd->modify('-1 day');
        $prevStart = clone $prevEnd;
        $prevStart->modify('-' . $diff->days . ' days');

        $current = $this->getManagerDashboardData($startDate, $endDate);
        $previous = $this->getManagerDashboardData($prevStart->format('Y-m-d'), $prevEnd->format('Y-m-d'));

        // Calculate growth percentages
        $currentRevenue = (float)$current['financial']['total_revenue'];
        $previousRevenue = (float)$previous['financial']['total_revenue'];
        $revenueGrowth = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        $currentOrders = (int)$current['work_orders']['total_orders'];
        $previousOrders = (int)$previous['work_orders']['total_orders'];
        $orderGrowth = $previousOrders > 0 ? (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0;

        return [
            'revenue_growth' => round($revenueGrowth, 2),
            'order_growth' => round($orderGrowth, 2),
            'previous_revenue' => $previousRevenue,
            'current_revenue' => $currentRevenue
        ];
    }
}
