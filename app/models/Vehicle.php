<?php
namespace App\Models;

class Vehicle extends Model {
    protected $table = 'vehicles';

    public function getAllWithClients() {
        $stmt = $this->db->query("SELECT v.*, c.name as client_name, c.phone as client_phone 
                                   FROM {$this->table} v 
                                   JOIN clients c ON v.client_id = c.id 
                                   ORDER BY v.created_at DESC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} 
            (client_id, vin, plate, brand, model, color, year, engine, mileage, notes, transmission) 
            VALUES (:client_id, :vin, :plate, :brand, :model, :color, :year, :engine, :mileage, :notes, :transmission)");
        return $stmt->execute([
            'client_id' => $data['client_id'],
            'vin' => $data['vin'],
            'plate' => $data['plate'] ?? null,
            'brand' => $data['brand'],
            'model' => $data['model'],
            'color' => $data['color'] ?? null,
            'year' => $data['year'],
            'engine' => $data['engine'] ?? null,
            'mileage' => $data['mileage'] ?? null,
            'notes' => $data['notes'] ?? null,
            'transmission' => $data['transmission'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET 
            client_id = :client_id, vin = :vin, plate = :plate, brand = :brand, 
            model = :model, color = :color, year = :year, engine = :engine, mileage = :mileage, notes = :notes, transmission = :transmission 
            WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'client_id' => $data['client_id'],
            'vin' => $data['vin'],
            'plate' => $data['plate'] ?? null,
            'brand' => $data['brand'],
            'model' => $data['model'],
            'color' => $data['color'] ?? null,
            'year' => $data['year'],
            'engine' => $data['engine'] ?? null,
            'mileage' => $data['mileage'] ?? null,
            'notes' => $data['notes'] ?? null,
            'transmission' => $data['transmission'] ?? null
        ]);
    }

    public function getWithClient($id) {
        $stmt = $this->db->prepare("SELECT v.*, c.name AS client_name, c.phone AS client_phone, c.email AS client_email FROM {$this->table} v INNER JOIN clients c ON c.id = v.client_id WHERE v.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getHistoryByPlate($plate) {
        $stmt = $this->db->prepare("SELECT wo.*, c.name AS client_name, v.plate, v.brand, v.model FROM work_orders wo INNER JOIN vehicles v ON v.id = wo.vehicle_id INNER JOIN clients c ON c.id = wo.client_id WHERE v.plate = :plate ORDER BY wo.created_at DESC");
        $stmt->execute(['plate' => $plate]);
        return $stmt->fetchAll();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
