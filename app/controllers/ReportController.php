<?php
namespace App\Controllers;

use App\Models\WorkOrder;
use App\Models\Client;
use App\Models\Part;
use App\Models\WorkshopOps;

class ReportController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
            exit;
        }
    }

    public function search() {
        $this->checkAuth();
        
        $query = $_GET['q'] ?? '';
        $results = [];
        
        if (strlen($query) >= 2) {
            // Search work orders
            $woModel = new WorkOrder();
            $results['work_orders'] = $woModel->search($query);
            
            // Search clients
            $clientModel = new Client();
            $results['clients'] = $clientModel->search($query);
            
            // Search parts
            $partModel = new Part();
            $results['parts'] = $partModel->search($query);
        }
        
        view('reports/search', [
            'query' => $query,
            'results' => $results
        ]);
    }

    public function invoice($workOrderId) {
        $this->checkAuth();
        
        $woModel = new WorkOrder();
        $workOrder = $woModel->getWithDetails($workOrderId);
        
        if (!$workOrder) {
            die("Orden no encontrada.");
        }
        
        $services = $woModel->getServices($workOrderId);
        $parts = $woModel->getParts($workOrderId);
        
        view('reports/invoice', [
            'workOrder' => $workOrder,
            'services' => $services,
            'parts' => $parts
        ]);
    }

    public function dashboardStats() {
        $this->checkAuth();
        
        $stats = [];
        
        // Work orders by status
        $woModel = new WorkOrder();
        $stats['orders_by_status'] = [
            'recepcion' => count($woModel->getByStatus('recepcion')),
            'diagnostico' => count($woModel->getByStatus('diagnostico')),
            'reparacion' => count($woModel->getByStatus('reparacion')),
            'terminado' => count($woModel->getByStatus('terminado'))
        ];
        
        // Revenue this month
        $stats['month_revenue'] = $this->getMonthlyRevenue();
        
        // Low stock items
        $partModel = new Part();
        $stats['low_stock_count'] = count($partModel->getLowStock());
        
        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    private function getMonthlyRevenue() {
        $db = \Config\Database::getConnection();
        $stmt = $db->query("SELECT SUM(total_cost) as total FROM work_orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        return $stmt->fetchColumn() ?? 0;
    }

    // Advanced Reports Dashboard
    public function index() {
        $this->checkAuth();
        
        $db = \Config\Database::getConnection();
        
        // Revenue by month (last 12 months)
        $stmt = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_cost) as total FROM work_orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month");
        $revenueByMonth = $stmt->fetchAll();
        
        // Top services
        $stmt = $db->query("SELECT s.name, SUM(wos.quantity) as total_qty, SUM(wos.quantity * wos.price) as total_revenue FROM work_order_services wos JOIN services s ON wos.service_id = s.id GROUP BY s.id ORDER BY total_revenue DESC LIMIT 10");
        $topServices = $stmt->fetchAll();
        
        // Orders by status
        $stmt = $db->query("SELECT status, COUNT(*) as count FROM work_orders GROUP BY status");
        $ordersByStatus = $stmt->fetchAll();
        
        // Top clients
        $stmt = $db->query("SELECT c.name, COUNT(wo.id) as order_count, SUM(wo.total_cost) as total_spent FROM clients c JOIN work_orders wo ON c.id = wo.client_id GROUP BY c.id ORDER BY total_spent DESC LIMIT 10");
        $topClients = $stmt->fetchAll();
        
        view('reports/index', [
            'revenue_by_month' => $revenueByMonth,
            'top_services' => $topServices,
            'orders_by_status' => $ordersByStatus,
            'top_clients' => $topClients
        ]);
    }

    // Export data as JSON for charts
    public function data() {
        $this->checkAuth();
        
        $type = $_GET['type'] ?? 'revenue';
        $db = \Config\Database::getConnection();
        
        switch ($type) {
            case 'revenue':
                $stmt = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_cost) as total FROM work_orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month");
                $data = $stmt->fetchAll();
                break;
            case 'services':
                $stmt = $db->query("SELECT s.name, SUM(wos.quantity) as qty FROM work_order_services wos JOIN services s ON wos.service_id = s.id GROUP BY s.id ORDER BY qty DESC LIMIT 10");
                $data = $stmt->fetchAll();
                break;
            case 'status':
                $stmt = $db->query("SELECT status, COUNT(*) as count FROM work_orders GROUP BY status");
                $data = $stmt->fetchAll();
                break;
            default:
                $data = [];
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // Activity logs report
    public function activity() {
        $this->checkAuth();
        
        $logModel = new \App\Models\ActivityLog();
        $logs = $logModel->getRecent(100);
        
        view('reports/activity', ['logs' => $logs]);
    }

    // Mechanic productivity report
    public function mechanicProductivity() {
        $this->checkAuth();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $opsModel = new WorkshopOps();
        $productivity = $opsModel->getMechanicProductivity($startDate, $endDate);
        $teamMetrics = $opsModel->getTeamEfficiencyMetrics($startDate, $endDate);
        
        view('reports/mechanic-productivity', [
            'productivity' => $productivity,
            'teamMetrics' => $teamMetrics,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    // Manager Dashboard - Gerencial
    public function managerDashboard() {
        $this->checkAuth();
        
        // Solo admin puede ver dashboard gerencial
        if ($_SESSION['user_role'] != 1) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/dashboard');
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $opsModel = new WorkshopOps();
        $dashboardData = $opsModel->getManagerDashboardData($startDate, $endDate);
        $comparison = $opsModel->getPreviousPeriodComparison($startDate, $endDate);
        
        view('reports/manager-dashboard', [
            'data' => $dashboardData,
            'comparison' => $comparison,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
