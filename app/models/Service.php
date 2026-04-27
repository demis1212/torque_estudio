<?php
namespace App\Models;

class Service extends Model {
    protected $table = 'services';

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, description, price) VALUES (:name, :description, :price)");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0.00
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET name = :name, description = :description, price = :price WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0.00
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
