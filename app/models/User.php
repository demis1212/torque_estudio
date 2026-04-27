<?php
namespace App\Models;

class User extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function getAllWithRoles() {
        $stmt = $this->db->query("SELECT u.*, r.name as role_name 
                                   FROM {$this->table} u 
                                   JOIN roles r ON u.role_id = r.id 
                                   ORDER BY u.created_at DESC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, email, password, role_id, hourly_rate) VALUES (:name, :email, :password, :role_id, :hourly_rate)");
        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $data['role_id'],
            'hourly_rate' => isset($data['hourly_rate']) && $data['hourly_rate'] !== '' ? $data['hourly_rate'] : null
        ]);
    }

    public function update($id, $data) {
        if (isset($data['password'])) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET name = :name, email = :email, password = :password, role_id = :role_id, hourly_rate = :hourly_rate WHERE id = :id");
            return $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role_id' => $data['role_id'],
                'hourly_rate' => isset($data['hourly_rate']) && $data['hourly_rate'] !== '' ? $data['hourly_rate'] : null
            ]);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET name = :name, email = :email, role_id = :role_id, hourly_rate = :hourly_rate WHERE id = :id");
            return $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $data['role_id'],
                'hourly_rate' => isset($data['hourly_rate']) && $data['hourly_rate'] !== '' ? $data['hourly_rate'] : null
            ]);
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getByRole($roleId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role_id = :role_id ORDER BY name ASC");
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll();
    }
}
