<?php
namespace App\Models;

class WorkOrderPartRequest extends Model {
    protected $table = 'work_order_part_requests';

    public function getByWorkOrder($workOrderId) {
        $stmt = $this->db->prepare("SELECT pr.*, p.quantity as stock, p.sale_price as price, p.category,
                req.name as requested_by_name, 
                apr.name as approved_by_name
                FROM {$this->table} pr
                LEFT JOIN parts p ON pr.part_id = p.id
                LEFT JOIN users req ON pr.requested_by = req.id
                LEFT JOIN users apr ON pr.approved_by = apr.id
                WHERE pr.work_order_id = :work_order_id
                ORDER BY pr.created_at DESC");
        $stmt->execute(['work_order_id' => $workOrderId]);
        return $stmt->fetchAll();
    }

    public function getPendingRequests() {
        $stmt = $this->db->query("SELECT pr.*, p.quantity as stock, p.sale_price as price, p.category,
                wo.id as work_order_id, c.name as client_name, v.plate, v.brand, v.model,
                req.name as requested_by_name
                FROM {$this->table} pr
                LEFT JOIN parts p ON pr.part_id = p.id
                JOIN work_orders wo ON pr.work_order_id = wo.id
                JOIN clients c ON wo.client_id = c.id
                JOIN vehicles v ON wo.vehicle_id = v.id
                LEFT JOIN users req ON pr.requested_by = req.id
                WHERE pr.status = 'pendiente'
                ORDER BY pr.created_at ASC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} 
            (work_order_id, part_id, part_name, quantity, status, requested_by, notes)
            VALUES (:work_order_id, :part_id, :part_name, :quantity, 'pendiente', :requested_by, :notes)");
        return $stmt->execute([
            'work_order_id' => $data['work_order_id'],
            'part_id' => !empty($data['part_id']) ? $data['part_id'] : null,
            'part_name' => $data['part_name'],
            'quantity' => $data['quantity'] ?? 1,
            'requested_by' => $data['requested_by'],
            'notes' => $data['notes'] ?? null
        ]);
    }

    public function approve($id, $approvedBy, $partId = null) {
        // Si hay part_id, verificar stock
        if ($partId) {
            $partStmt = $this->db->prepare("SELECT stock FROM parts WHERE id = :id");
            $partStmt->execute(['id' => $partId]);
            $stock = $partStmt->fetchColumn();
            
            $reqStmt = $this->db->prepare("SELECT quantity FROM {$this->table} WHERE id = :id");
            $reqStmt->execute(['id' => $id]);
            $quantity = $reqStmt->fetchColumn();
            
            if ($stock < $quantity) {
                return false; // No hay suficiente stock
            }
            
            // Descontar stock
            $updateStock = $this->db->prepare("UPDATE parts SET stock = stock - :quantity WHERE id = :id");
            $updateStock->execute(['quantity' => $quantity, 'id' => $partId]);
        }
        
        $stmt = $this->db->prepare("UPDATE {$this->table} 
            SET status = 'aprobado', approved_by = :approved_by 
            WHERE id = :id");
        return $stmt->execute(['approved_by' => $approvedBy, 'id' => $id]);
    }

    public function reject($id, $approvedBy) {
        $stmt = $this->db->prepare("UPDATE {$this->table} 
            SET status = 'rechazado', approved_by = :approved_by 
            WHERE id = :id");
        return $stmt->execute(['approved_by' => $approvedBy, 'id' => $id]);
    }

    public function despachar($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} 
            SET status = 'despachado' 
            WHERE id = :id AND status = 'aprobado'");
        return $stmt->execute(['id' => $id]);
    }

    public function getCountPending() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'pendiente'");
        return $stmt->fetchColumn();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
