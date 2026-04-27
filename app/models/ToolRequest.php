<?php
namespace App\Models;

class ToolRequest extends Model {
    protected $table = 'tool_requests';

    public function __construct() {
        parent::__construct();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            warehouse_tool_id INT NOT NULL,
            mechanic_id INT NOT NULL,
            request_date DATE NOT NULL,
            return_date DATE NULL,
            status ENUM('pendiente', 'aprobada', 'rechazada', 'entregada', 'devuelta', 'atrasada') DEFAULT 'pendiente',
            requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            delivered_at TIMESTAMP NULL,
            returned_at TIMESTAMP NULL,
            condition_notes TEXT NULL,
            notes TEXT NULL,
            FOREIGN KEY (warehouse_tool_id) REFERENCES warehouse_tools(id) ON DELETE RESTRICT,
            FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_mechanic (mechanic_id),
            INDEX idx_status (status),
            INDEX idx_date (request_date)
        ) ENGINE=InnoDB";
        
        $this->db->exec($sql);
    }

    public function getAllWithDetails() {
        $stmt = $this->db->query("SELECT r.*, t.name as tool_name, t.code as tool_code, t.cost as tool_cost, 
            m.name as mechanic_name, a.name as approved_by_name 
            FROM {$this->table} r 
            JOIN warehouse_tools t ON r.warehouse_tool_id = t.id 
            JOIN users m ON r.mechanic_id = m.id 
            LEFT JOIN users a ON r.approved_by = a.id 
            ORDER BY r.requested_at DESC");
        return $stmt->fetchAll();
    }

    public function getByMechanic($mechanicId) {
        $stmt = $this->db->prepare("SELECT r.*, t.name as tool_name, t.code as tool_code, t.cost as tool_cost,
            m.name as mechanic_name, a.name as approved_by_name 
            FROM {$this->table} r 
            JOIN warehouse_tools t ON r.warehouse_tool_id = t.id 
            JOIN users m ON r.mechanic_id = m.id 
            LEFT JOIN users a ON r.approved_by = a.id 
            WHERE r.mechanic_id = :mechanic_id 
            ORDER BY r.requested_at DESC");
        $stmt->execute(['mechanic_id' => $mechanicId]);
        return $stmt->fetchAll();
    }

    public function getPendingRequests() {
        $stmt = $this->db->query("SELECT r.*, t.name as tool_name, t.code as tool_code, t.requires_auth,
            m.name as mechanic_name 
            FROM {$this->table} r 
            JOIN warehouse_tools t ON r.warehouse_tool_id = t.id 
            JOIN users m ON r.mechanic_id = m.id 
            WHERE r.status = 'pendiente' 
            ORDER BY r.requested_at ASC");
        return $stmt->fetchAll();
    }

    public function getActiveLoans() {
        $stmt = $this->db->query("SELECT r.*, t.name as tool_name, t.code as tool_code, t.cost as tool_cost,
            m.name as mechanic_name 
            FROM {$this->table} r 
            JOIN warehouse_tools t ON r.warehouse_tool_id = t.id 
            JOIN users m ON r.mechanic_id = m.id 
            WHERE r.status IN ('pendiente', 'aprobada', 'entregada') 
            ORDER BY r.request_date ASC");
        return $stmt->fetchAll();
    }

    public function getOverdueLoans() {
        $stmt = $this->db->query("SELECT r.*, t.name as tool_name, t.code as tool_code, t.cost as tool_cost,
            m.name as mechanic_name 
            FROM {$this->table} r 
            JOIN warehouse_tools t ON r.warehouse_tool_id = t.id 
            JOIN users m ON r.mechanic_id = m.id 
            WHERE r.status = 'entregada' AND r.return_date < CURDATE() 
            ORDER BY r.return_date ASC");
        return $stmt->fetchAll();
    }

    public function getStatusCounts() {
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (warehouse_tool_id, mechanic_id, request_date, return_date, notes) VALUES (:warehouse_tool_id, :mechanic_id, :request_date, :return_date, :notes)");
        
        $result = $stmt->execute([
            'warehouse_tool_id' => $data['warehouse_tool_id'],
            'mechanic_id' => $data['mechanic_id'],
            'request_date' => $data['request_date'],
            'return_date' => $data['return_date'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);

        if ($result) {
            // Cambiar estado de la herramienta a 'solicitada'
            $tool = new WarehouseTool();
            $tool->updateStatus($data['warehouse_tool_id'], 'solicitada');
        }

        return $result;
    }

    public function approve($id, $approvedBy) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'aprobada', approved_by = :approved_by, approved_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id, 'approved_by' => $approvedBy]);
    }

    public function reject($id, $approvedBy) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'rechazada', approved_by = :approved_by, approved_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id, 'approved_by' => $approvedBy]);
    }

    public function deliver($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'entregada', delivered_at = NOW() WHERE id = :id");
        if ($stmt->execute(['id' => $id])) {
            // Actualizar estado de la herramienta
            $request = $this->find($id);
            if ($request) {
                $tool = new WarehouseTool();
                $tool->updateStatus($request['warehouse_tool_id'], 'prestada');
            }
            return true;
        }
        return false;
    }

    public function getActiveLoanForTool($toolId) {
        $stmt = $this->db->prepare("SELECT r.*, m.name as mechanic_name 
            FROM {$this->table} r 
            JOIN users m ON r.mechanic_id = m.id 
            WHERE r.warehouse_tool_id = :tool_id AND r.status IN ('aprobada', 'entregada') 
            ORDER BY r.request_date DESC LIMIT 1");
        $stmt->execute(['tool_id' => $toolId]);
        return $stmt->fetch();
    }

    public function returnTool($id, $condition = 'buena', $notes = '') {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'devuelta', returned_at = NOW(), condition_notes = :condition_notes WHERE id = :id");
        $conditionNotes = "Condición: {$condition}" . ($notes ? " - {$notes}" : '');
        if ($stmt->execute(['id' => $id, 'condition_notes' => $conditionNotes])) {
            // Actualizar estado de la herramienta a disponible
            $request = $this->find($id);
            if ($request) {
                $tool = new WarehouseTool();
                $tool->updateStatus($request['warehouse_tool_id'], 'disponible');
            }
            return true;
        }
        return false;
    }

    public function checkOverdue() {
        $stmt = $this->db->query("UPDATE {$this->table} SET status = 'atrasada' WHERE status = 'entregada' AND return_date < CURDATE()");
        return $stmt->rowCount();
    }
}
