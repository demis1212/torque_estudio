<?php
namespace App\Models;

class PurchaseAlert extends Model {
    protected $table = 'purchase_alerts';

    public function getPending() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE status = 'pendiente' ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getAllWithPartInfo() {
        $stmt = $this->db->query("SELECT pa.*, p.supplier, p.location 
                                   FROM {$this->table} pa 
                                   LEFT JOIN parts p ON pa.part_id = p.id 
                                   ORDER BY pa.created_at DESC");
        return $stmt->fetchAll();
    }

    public function markAsPurchased($id, $notes = null) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'comprado', resolved_at = NOW(), notes = :notes WHERE id = :id");
        return $stmt->execute(['id' => $id, 'notes' => $notes]);
    }

    public function markAsCancelled($id, $notes = null) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'cancelado', resolved_at = NOW(), notes = :notes WHERE id = :id");
        return $stmt->execute(['id' => $id, 'notes' => $notes]);
    }

    public function getCountPending() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'pendiente'");
        return $stmt->fetchColumn();
    }
}
