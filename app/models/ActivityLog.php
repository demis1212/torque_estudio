<?php
namespace App\Models;

class ActivityLog extends Model {
    protected $table = 'activity_logs';

    public function log($action, $entityType, $entityId, $description, $userId = null) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, action, entity_type, entity_id, description, ip_address) VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip_address)");
        return $stmt->execute([
            'user_id' => $userId ?? ($_SESSION['user_id'] ?? null),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    public function getRecent($limit = 50) {
        $stmt = $this->db->prepare("SELECT al.*, u.name as user_name FROM {$this->table} al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByEntity($entityType, $entityId) {
        $stmt = $this->db->prepare("SELECT al.*, u.name as user_name FROM {$this->table} al LEFT JOIN users u ON al.user_id = u.id WHERE al.entity_type = :entity_type AND al.entity_id = :entity_id ORDER BY al.created_at DESC");
        $stmt->execute(['entity_type' => $entityType, 'entity_id' => $entityId]);
        return $stmt->fetchAll();
    }

    public function getByUser($userId, $limit = 50) {
        $stmt = $this->db->prepare("SELECT al.*, u.name as user_name FROM {$this->table} al LEFT JOIN users u ON al.user_id = u.id WHERE al.user_id = :user_id ORDER BY al.created_at DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function clearOld($days = 90) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)");
        return $stmt->execute(['days' => $days]);
    }
}
