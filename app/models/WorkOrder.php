<?php
namespace App\Models;

class WorkOrder extends Model {
    protected $table = 'work_orders';

    public function getAllWithDetails() {
        $stmt = $this->db->query("SELECT wo.*, c.name as client_name, v.plate, v.brand, v.model, u.name as user_name 
                                   FROM {$this->table} wo 
                                   JOIN clients c ON wo.client_id = c.id 
                                   JOIN vehicles v ON wo.vehicle_id = v.id 
                                   JOIN users u ON wo.user_id = u.id 
                                   ORDER BY wo.created_at DESC");
        return $stmt->fetchAll();
    }

    public function getWithDetails($id) {
        $stmt = $this->db->prepare("SELECT wo.*, c.name as client_name, v.plate, v.brand, v.model, u.name as user_name 
                                     FROM {$this->table} wo 
                                     JOIN clients c ON wo.client_id = c.id 
                                     JOIN vehicles v ON wo.vehicle_id = v.id 
                                     JOIN users u ON wo.user_id = u.id 
                                     WHERE wo.id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} 
            (client_id, mechanic_id, vehicle_id, user_id, status, description, problem_reported, diagnosis, priority, estimated_delivery_at, total_cost) 
            VALUES (:client_id, :mechanic_id, :vehicle_id, :user_id, :status, :description, :problem_reported, :diagnosis, :priority, :estimated_delivery_at, :total_cost)");
        $stmt->execute([
            'client_id' => $data['client_id'],
            'mechanic_id' => !empty($data['mechanic_id']) ? $data['mechanic_id'] : null,
            'vehicle_id' => $data['vehicle_id'],
            'user_id' => $data['user_id'],
            'status' => $data['status'] ?? 'recepcion',
            'description' => $data['description'] ?? null,
            'problem_reported' => $data['problem_reported'] ?? ($data['description'] ?? null),
            'diagnosis' => $data['diagnosis'] ?? null,
            'priority' => $data['priority'] ?? 'media',
            'estimated_delivery_at' => !empty($data['estimated_delivery_at']) ? $data['estimated_delivery_at'] : null,
            'total_cost' => $data['total_cost'] ?? 0.00
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET 
            client_id = :client_id, mechanic_id = :mechanic_id, vehicle_id = :vehicle_id, status = :status, 
            description = :description, problem_reported = :problem_reported, diagnosis = :diagnosis, priority = :priority, estimated_delivery_at = :estimated_delivery_at, total_cost = :total_cost 
            WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'client_id' => $data['client_id'],
            'mechanic_id' => !empty($data['mechanic_id']) ? $data['mechanic_id'] : null,
            'vehicle_id' => $data['vehicle_id'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'problem_reported' => $data['problem_reported'] ?? ($data['description'] ?? null),
            'diagnosis' => $data['diagnosis'] ?? null,
            'priority' => $data['priority'] ?? 'media',
            'estimated_delivery_at' => !empty($data['estimated_delivery_at']) ? $data['estimated_delivery_at'] : null,
            'total_cost' => $data['total_cost'] ?? 0.00
        ]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function addServices($workOrderId, $services) {
        $stmt = $this->db->prepare("INSERT INTO work_order_services 
            (work_order_id, service_id, quantity, price) 
            SELECT :work_order_id, id, :quantity, price FROM services WHERE id = :service_id");
        
        foreach ($services as $serviceData) {
            $serviceId = $serviceData['id'];
            $quantity = $serviceData['quantity'] ?? 1;
            $stmt->execute([
                'work_order_id' => $workOrderId,
                'service_id' => $serviceId,
                'quantity' => $quantity
            ]);
        }
        
        $this->recalculateTotal($workOrderId);
    }

    public function updateServices($workOrderId, $services) {
        // Delete existing services
        $stmt = $this->db->prepare("DELETE FROM work_order_services WHERE work_order_id = :work_order_id");
        $stmt->execute(['work_order_id' => $workOrderId]);
        
        // Add new services
        $this->addServices($workOrderId, $services);
    }

    public function getServices($workOrderId) {
        $stmt = $this->db->prepare("SELECT wos.*, s.name as service_name 
                                   FROM work_order_services wos 
                                   JOIN services s ON wos.service_id = s.id 
                                   WHERE wos.work_order_id = :work_order_id");
        $stmt->execute(['work_order_id' => $workOrderId]);
        return $stmt->fetchAll();
    }

    private function recalculateTotal($workOrderId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET total_cost = 
            COALESCE((SELECT SUM(quantity * price) FROM work_order_services WHERE work_order_id = :work_order_id), 0) +
            COALESCE((SELECT SUM(quantity * price) FROM work_order_parts WHERE work_order_id = :work_order_id2), 0)
            WHERE id = :work_order_id3");
        $stmt->execute([
            'work_order_id' => $workOrderId,
            'work_order_id2' => $workOrderId,
            'work_order_id3' => $workOrderId
        ]);
    }

    // Parts management with automatic stock control
    public function addPart($workOrderId, $partId, $quantity, $price) {
        // Check available stock first
        $checkStmt = $this->db->prepare("SELECT quantity, min_stock, name, code FROM parts WHERE id = :part_id");
        $checkStmt->execute(['part_id' => $partId]);
        $part = $checkStmt->fetch();
        
        if (!$part) {
            return ['success' => false, 'message' => 'Repuesto no encontrado'];
        }
        
        if ($part['quantity'] < $quantity) {
            return [
                'success' => false, 
                'message' => "Stock insuficiente para {$part['name']} ({$part['code']}). Disponible: {$part['quantity']}, Solicitado: {$quantity}"
            ];
        }
        
        // Insert work order part
        $stmt = $this->db->prepare("INSERT INTO work_order_parts (work_order_id, part_id, quantity, price) VALUES (:work_order_id, :part_id, :quantity, :price)");
        $result = $stmt->execute([
            'work_order_id' => $workOrderId,
            'part_id' => $partId,
            'quantity' => $quantity,
            'price' => $price
        ]);
        
        // Update part stock
        $newQuantity = $part['quantity'] - $quantity;
        $stockStmt = $this->db->prepare("UPDATE parts SET quantity = :new_qty WHERE id = :part_id");
        $stockStmt->execute(['new_qty' => $newQuantity, 'part_id' => $partId]);
        
        // Check if stock is now low and create purchase alert
        if ($newQuantity <= $part['min_stock']) {
            $this->createLowStockAlert($partId, $part['name'], $part['code'], $newQuantity, $part['min_stock']);
        }
        
        $this->recalculateTotal($workOrderId);
        return ['success' => true, 'message' => 'Repuesto agregado correctamente'];
    }
    
    private function createLowStockAlert($partId, $partName, $partCode, $currentQty, $minStock) {
        // Check if alert already exists
        $checkStmt = $this->db->prepare("SELECT id FROM purchase_alerts WHERE part_id = :part_id AND status = 'pendiente'");
        $checkStmt->execute(['part_id' => $partId]);
        if ($checkStmt->fetch()) {
            return; // Alert already exists
        }
        
        // Create purchase alert
        $alertStmt = $this->db->prepare("INSERT INTO purchase_alerts (part_id, part_name, part_code, current_quantity, min_stock, suggested_quantity, status, created_at) VALUES (:part_id, :part_name, :part_code, :current_qty, :min_stock, :suggested_qty, 'pendiente', NOW())");
        $suggestedQty = max($minStock * 2 - $currentQty, $minStock);
        $alertStmt->execute([
            'part_id' => $partId,
            'part_name' => $partName,
            'part_code' => $partCode,
            'current_qty' => $currentQty,
            'min_stock' => $minStock,
            'suggested_qty' => $suggestedQty
        ]);
    }

    public function removePart($workOrderId, $partEntryId) {
        // Get the part details first to restore stock
        $getStmt = $this->db->prepare("SELECT part_id, quantity FROM work_order_parts WHERE id = :id AND work_order_id = :work_order_id");
        $getStmt->execute(['id' => $partEntryId, 'work_order_id' => $workOrderId]);
        $part = $getStmt->fetch();
        
        if ($part) {
            // Restore stock
            $stockStmt = $this->db->prepare("UPDATE parts SET quantity = quantity + :qty WHERE id = :part_id");
            $stockStmt->execute(['qty' => $part['quantity'], 'part_id' => $part['part_id']]);
            
            // Delete entry
            $stmt = $this->db->prepare("DELETE FROM work_order_parts WHERE id = :id");
            $stmt->execute(['id' => $partEntryId]);
            
            $this->recalculateTotal($workOrderId);
        }
    }

    public function getParts($workOrderId) {
        $stmt = $this->db->prepare("SELECT wop.*, p.code, p.name as part_name 
                                   FROM work_order_parts wop 
                                   JOIN parts p ON wop.part_id = p.id 
                                   WHERE wop.work_order_id = :work_order_id");
        $stmt->execute(['work_order_id' => $workOrderId]);
        return $stmt->fetchAll();
    }

    // Mechanic assignments
    public function assignMechanic($workOrderId, $mechanicId, $notes = null) {
        $stmt = $this->db->prepare("INSERT INTO work_order_assignments (work_order_id, mechanic_id, notes) VALUES (:work_order_id, :mechanic_id, :notes) ON DUPLICATE KEY UPDATE notes = :notes2");
        return $stmt->execute([
            'work_order_id' => $workOrderId,
            'mechanic_id' => $mechanicId,
            'notes' => $notes,
            'notes2' => $notes
        ]);
    }

    public function removeMechanic($workOrderId, $mechanicId) {
        $stmt = $this->db->prepare("DELETE FROM work_order_assignments WHERE work_order_id = :work_order_id AND mechanic_id = :mechanic_id");
        return $stmt->execute(['work_order_id' => $workOrderId, 'mechanic_id' => $mechanicId]);
    }

    public function getAssignments($workOrderId) {
        $stmt = $this->db->prepare("SELECT woa.*, u.name as mechanic_name 
                                   FROM work_order_assignments woa 
                                   JOIN users u ON woa.mechanic_id = u.id 
                                   WHERE woa.work_order_id = :work_order_id");
        $stmt->execute(['work_order_id' => $workOrderId]);
        return $stmt->fetchAll();
    }

    public function getByMechanic($mechanicId) {
        $stmt = $this->db->prepare("SELECT wo.*, c.name as client_name, v.plate, v.brand, v.model 
                                   FROM {$this->table} wo 
                                   JOIN work_order_assignments woa ON wo.id = woa.work_order_id 
                                   JOIN clients c ON wo.client_id = c.id 
                                   JOIN vehicles v ON wo.vehicle_id = v.id 
                                   WHERE woa.mechanic_id = :mechanic_id 
                                   ORDER BY wo.created_at DESC");
        $stmt->execute(['mechanic_id' => $mechanicId]);
        return $stmt->fetchAll();
    }

    // Search functionality
    public function search($query) {
        $stmt = $this->db->prepare("SELECT wo.*, c.name as client_name, v.plate, v.brand, v.model 
                                   FROM {$this->table} wo 
                                   JOIN clients c ON wo.client_id = c.id 
                                   JOIN vehicles v ON wo.vehicle_id = v.id 
                                   WHERE c.name LIKE :query OR v.plate LIKE :query OR wo.description LIKE :query 
                                   ORDER BY wo.created_at DESC");
        $search = "%{$query}%";
        $stmt->execute(['query' => $search]);
        return $stmt->fetchAll();
    }

    public function getByStatus($status) {
        $stmt = $this->db->prepare("SELECT wo.*, c.name as client_name, v.plate, v.brand, v.model 
                                   FROM {$this->table} wo 
                                   JOIN clients c ON wo.client_id = c.id 
                                   JOIN vehicles v ON wo.vehicle_id = v.id 
                                   WHERE wo.status = :status 
                                   ORDER BY wo.created_at DESC");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    public function getVehicleHistoryByPlate($plate) {
        $stmt = $this->db->prepare("SELECT wo.*, c.name AS client_name, v.plate, v.brand, v.model, v.year FROM {$this->table} wo INNER JOIN clients c ON c.id = wo.client_id INNER JOIN vehicles v ON v.id = wo.vehicle_id WHERE v.plate = :plate ORDER BY wo.created_at DESC");
        $stmt->execute(['plate' => $plate]);
        return $stmt->fetchAll();
    }
}
