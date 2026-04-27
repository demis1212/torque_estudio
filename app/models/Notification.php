<?php
namespace App\Models;

class Notification extends Model {
    protected $table = 'notifications';

    public function getUnreadByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id AND is_read = FALSE ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAllByUser($userId, $limit = 20) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id AND is_read = FALSE");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, title, message, type, link) VALUES (:user_id, :title, :message, :type, :link)");
        return $stmt->execute([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info',
            'link' => $data['link'] ?? null
        ]);
    }

    public function markAsRead($id, $userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = TRUE WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = TRUE WHERE user_id = :user_id AND is_read = FALSE");
        return $stmt->execute(['user_id' => $userId]);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function deleteOld($days = 30) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)");
        return $stmt->execute(['days' => $days]);
    }
}
