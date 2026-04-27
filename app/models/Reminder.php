<?php
namespace App\Models;

class Reminder extends Model {
    protected $table = 'reminders';

    public function __construct() {
        parent::__construct();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(150) NOT NULL,
            description TEXT NULL,
            reminder_date DATETIME NOT NULL,
            is_completed BOOLEAN DEFAULT FALSE,
            entity_type VARCHAR(50) NULL,
            entity_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, reminder_date),
            INDEX idx_completed (is_completed)
        ) ENGINE=InnoDB";
        
        $this->db->exec($sql);
    }

    public function getUpcoming($userId, $days = 7) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} 
                                   WHERE user_id = :user_id 
                                   AND is_completed = FALSE 
                                   AND reminder_date <= DATE_ADD(NOW(), INTERVAL :days DAY)
                                   AND reminder_date >= NOW()
                                   ORDER BY reminder_date ASC");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUser($userId, $limit = 50) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} 
                                   WHERE user_id = :user_id 
                                   ORDER BY reminder_date DESC 
                                   LIMIT :limit");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} 
            (user_id, title, description, reminder_date, entity_type, entity_id) 
            VALUES (:user_id, :title, :description, :reminder_date, :entity_type, :entity_id)");
        
        return $stmt->execute([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'reminder_date' => $data['reminder_date'],
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null
        ]);
    }

    public function markAsCompleted($id, $userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_completed = TRUE WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function getOverdue($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} 
                                   WHERE user_id = :user_id 
                                   AND is_completed = FALSE 
                                   AND reminder_date < NOW()
                                   ORDER BY reminder_date ASC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
