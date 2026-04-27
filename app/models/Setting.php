<?php
namespace App\Models;

class Setting extends Model {
    protected $table = 'settings';

    public function get($key, $default = null) {
        $stmt = $this->db->prepare("SELECT `value` FROM {$this->table} WHERE `key` = :key");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY `group`, `key`");
        return $stmt->fetchAll();
    }

    public function getByGroup($group) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE `group` = :group ORDER BY `key`");
        $stmt->execute(['group' => $group]);
        return $stmt->fetchAll();
    }

    public function set($key, $value, $group = 'general', $description = null) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (`key`, `value`, `group`, description) VALUES (:key, :value, :group, :description) ON DUPLICATE KEY UPDATE `value` = :value, `group` = :group, description = :description");
        return $stmt->execute([
            'key' => $key,
            'value' => $value,
            'group' => $group,
            'description' => $description
        ]);
    }

    public function update($key, $value) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET `value` = :value WHERE `key` = :key");
        return $stmt->execute(['key' => $key, 'value' => $value]);
    }

    public function delete($key) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE `key` = :key");
        return $stmt->execute(['key' => $key]);
    }

    public function getMultiple(array $keys) {
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $this->db->prepare("SELECT `key`, `value` FROM {$this->table} WHERE `key` IN ($placeholders)");
        $stmt->execute($keys);
        $results = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        return $results;
    }
}
