<?php
namespace App\Models;

class Part extends Model {
    protected $table = 'parts';

    public function getAllWithStockAlert() {
        $stmt = $this->db->query("SELECT *, (quantity <= min_stock) as low_stock FROM {$this->table} ORDER BY name");
        return $stmt->fetchAll();
    }

    public function getByCategory($category) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE category = :category ORDER BY name");
        $stmt->execute(['category' => $category]);
        return $stmt->fetchAll();
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM {$this->table} WHERE category IS NOT NULL ORDER BY category");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function search($query) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE code LIKE :query OR name LIKE :query OR description LIKE :query ORDER BY name");
        $search = "%{$query}%";
        $stmt->execute(['query' => $search]);
        return $stmt->fetchAll();
    }

    public function getLowStock() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE quantity <= min_stock ORDER BY quantity ASC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (code, name, description, category, unit_type, quantity, min_stock, cost_price, sale_price, supplier, location) VALUES (:code, :name, :description, :category, :unit_type, :quantity, :min_stock, :cost_price, :sale_price, :supplier, :location)");
        return $stmt->execute([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'unit_type' => $data['unit_type'] ?? 'unidad',
            'quantity' => $data['quantity'] ?? 0,
            'min_stock' => $data['min_stock'] ?? 5,
            'cost_price' => $data['cost_price'] ?? 0,
            'sale_price' => $data['sale_price'] ?? 0,
            'supplier' => $data['supplier'] ?? null,
            'location' => $data['location'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET code = :code, name = :name, description = :description, category = :category, unit_type = :unit_type, quantity = :quantity, min_stock = :min_stock, cost_price = :cost_price, sale_price = :sale_price, supplier = :supplier, location = :location WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'unit_type' => $data['unit_type'] ?? 'unidad',
            'quantity' => $data['quantity'] ?? 0,
            'min_stock' => $data['min_stock'] ?? 5,
            'cost_price' => $data['cost_price'] ?? 0,
            'sale_price' => $data['sale_price'] ?? 0,
            'supplier' => $data['supplier'] ?? null,
            'location' => $data['location'] ?? null
        ]);
    }

    public function updateStock($id, $quantity) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET quantity = quantity + :quantity WHERE id = :id");
        return $stmt->execute(['id' => $id, 'quantity' => $quantity]);
    }

    public function isUsedInWorkOrders($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM work_order_parts WHERE part_id = :part_id");
        $stmt->execute(['part_id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    public function delete($id) {
        // Verificar si la pieza está siendo usada en órdenes de trabajo
        if ($this->isUsedInWorkOrders($id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
