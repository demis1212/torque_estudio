<?php
namespace App\Models;

class Client extends Model {
    protected $table = 'clients';

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, rut, phone, whatsapp, whatsapp_opt_in, email, address) VALUES (:name, :rut, :phone, :whatsapp, :whatsapp_opt_in, :email, :address)");
        return $stmt->execute([
            'name' => $data['name'],
            'rut' => $data['rut'] ?? null,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'whatsapp_opt_in' => !empty($data['whatsapp_opt_in']) ? 1 : 0,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET name = :name, rut = :rut, phone = :phone, whatsapp = :whatsapp, whatsapp_opt_in = :whatsapp_opt_in, email = :email, address = :address WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'rut' => $data['rut'] ?? null,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'whatsapp_opt_in' => !empty($data['whatsapp_opt_in']) ? 1 : 0,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function search($query) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name LIKE :query OR email LIKE :query OR phone LIKE :query ORDER BY name LIMIT 20");
        $search = "%{$query}%";
        $stmt->execute(['query' => $search]);
        return $stmt->fetchAll();
    }
}
