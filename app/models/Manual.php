<?php
namespace App\Models;

class Manual extends Model {
    protected $table = 'manuals';

    public function __construct() {
        parent::__construct();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            category VARCHAR(50) NOT NULL,
            brand VARCHAR(50) NULL,
            model VARCHAR(50) NULL,
            year VARCHAR(20) NULL,
            content TEXT NULL,
            file_path VARCHAR(255) NULL,
            file_type VARCHAR(10) NULL,
            views INT DEFAULT 0,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_category (category),
            INDEX idx_brand (brand),
            INDEX idx_title (title)
        ) ENGINE=InnoDB";
        
        $this->db->exec($sql);
    }

    public function getByCategory($category) {
        $stmt = $this->db->prepare("SELECT m.*, u.name as user_name FROM {$this->table} m JOIN users u ON m.user_id = u.id WHERE category = :category ORDER BY title");
        $stmt->execute(['category' => $category]);
        return $stmt->fetchAll();
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM {$this->table} ORDER BY category");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getSortedByBrandModelYear() {
        $stmt = $this->db->query("SELECT m.*, u.name as user_name FROM {$this->table} m JOIN users u ON m.user_id = u.id ORDER BY brand ASC, model ASC, year DESC, title ASC");
        return $stmt->fetchAll();
    }
    
    public function getGroupedByBrand() {
        $stmt = $this->db->query("SELECT m.*, u.name as user_name FROM {$this->table} m JOIN users u ON m.user_id = u.id ORDER BY brand ASC, model ASC, year DESC");
        $manuals = $stmt->fetchAll();
        
        $grouped = [];
        foreach ($manuals as $manual) {
            $brand = $manual['brand'] ?: 'Sin Marca';
            $model = $manual['model'] ?: 'Sin Modelo';
            $year = $manual['year'] ?: 'Sin Año';
            
            if (!isset($grouped[$brand])) {
                $grouped[$brand] = [];
            }
            if (!isset($grouped[$brand][$model])) {
                $grouped[$brand][$model] = [];
            }
            if (!isset($grouped[$brand][$model][$year])) {
                $grouped[$brand][$model][$year] = [];
            }
            $grouped[$brand][$model][$year][] = $manual;
        }
        return $grouped;
    }

    public function search($query) {
        $stmt = $this->db->prepare("SELECT m.*, u.name as user_name FROM {$this->table} m JOIN users u ON m.user_id = u.id WHERE title LIKE :query OR description LIKE :query OR brand LIKE :query OR model LIKE :query ORDER BY title");
        $search = "%{$query}%";
        $stmt->execute(['query' => $search]);
        return $stmt->fetchAll();
    }

    public function incrementViews($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET views = views + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (title, description, category, brand, model, year, content, file_path, file_type, user_id) VALUES (:title, :description, :category, :brand, :model, :year, :content, :file_path, :file_type, :user_id)");
        return $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'year' => $data['year'] ?? null,
            'content' => $data['content'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'file_type' => $data['file_type'] ?? null,
            'user_id' => $data['user_id']
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
