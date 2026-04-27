<?php
namespace App\Models;

class WarehouseTool extends Model {
    protected $table = 'warehouse_tools';

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
            serial_number VARCHAR(100) NULL,
            purchase_date DATE NULL,
            cost DECIMAL(10,2) NULL,
            status ENUM('disponible', 'solicitada', 'prestada', 'en_mantenimiento', 'danada') DEFAULT 'disponible',
            min_stock_alert INT DEFAULT 1,
            location VARCHAR(100) NULL,
            requires_auth BOOLEAN DEFAULT FALSE,
            auth_role_id INT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_code (code)
        ) ENGINE=InnoDB";
        
        $this->db->exec($sql);
    }

    public function getAvailable() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE status = 'disponible' ORDER BY name");
        return $stmt->fetchAll();
    }

    public function getAllWithAuthInfo() {
        $stmt = $this->db->query("SELECT t.*, r.name as auth_role_name FROM {$this->table} t LEFT JOIN roles r ON t.auth_role_id = r.id ORDER BY t.name");
        return $stmt->fetchAll();
    }

    public function getByStatus($status) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = :status ORDER BY name");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    public function getStatusCounts() {
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status");
        return $stmt->fetchAll();
    }

    public function getTotalValue() {
        $stmt = $this->db->query("SELECT SUM(cost) as total FROM {$this->table}");
        return $stmt->fetchColumn() ?? 0;
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, description, code, brand, model, serial_number, purchase_date, cost, location, requires_auth, auth_role_id, notes) VALUES (:name, :description, :code, :brand, :model, :serial_number, :purchase_date, :cost, :location, :requires_auth, :auth_role_id, :notes)");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'code' => $data['code'] ?? null,
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'purchase_date' => $data['purchase_date'] ?? null,
            'cost' => $data['cost'] ?? null,
            'location' => $data['location'] ?? null,
            'requires_auth' => $data['requires_auth'] ?? false,
            'auth_role_id' => $data['auth_role_id'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }
}
