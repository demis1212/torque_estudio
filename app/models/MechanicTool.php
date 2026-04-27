<?php
namespace App\Models;

class MechanicTool extends Model {
    protected $table = 'mechanic_tools';

    public function __construct() {
        parent::__construct();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            code VARCHAR(50) UNIQUE NULL,
            brand VARCHAR(50) NULL,
            model VARCHAR(50) NULL,
            purchase_date DATE NULL,
            cost DECIMAL(10,2) NULL,
            status ENUM('activa', 'danada', 'perdida', 'en_reparacion') DEFAULT 'activa',
            mechanic_id INT NOT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_mechanic (mechanic_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB";
        
        $this->db->exec($sql);
    }

    public function getByMechanic($mechanicId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE mechanic_id = :mechanic_id ORDER BY name");
        $stmt->execute(['mechanic_id' => $mechanicId]);
        return $stmt->fetchAll();
    }

    public function getAllWithMechanics() {
        $stmt = $this->db->query("SELECT t.*, u.name as mechanic_name FROM {$this->table} t JOIN users u ON t.mechanic_id = u.id ORDER BY u.name, t.name");
        return $stmt->fetchAll();
    }

    public function getStatusCounts($mechanicId) {
        $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM {$this->table} WHERE mechanic_id = :mechanic_id GROUP BY status");
        $stmt->execute(['mechanic_id' => $mechanicId]);
        return $stmt->fetchAll();
    }

    public function getTotalValue($mechanicId) {
        $stmt = $this->db->prepare("SELECT SUM(cost) as total FROM {$this->table} WHERE mechanic_id = :mechanic_id");
        $stmt->execute(['mechanic_id' => $mechanicId]);
        return $stmt->fetchColumn() ?? 0;
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, description, code, brand, model, purchase_date, cost, status, mechanic_id, notes) VALUES (:name, :description, :code, :brand, :model, :purchase_date, :cost, :status, :mechanic_id, :notes)");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'code' => $data['code'] ?? null,
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'purchase_date' => $data['purchase_date'] ?? null,
            'cost' => $data['cost'] ?? null,
            'status' => $data['status'] ?? 'activa',
            'mechanic_id' => $data['mechanic_id'],
            'notes' => $data['notes'] ?? null
        ]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }
}
