<?php
namespace App\Models;

class WhatsAppReminder extends Model {
    protected $table = 'whatsapp_reminders';

    public function getAllWithDetails() {
        $sql = "SELECT wr.*, c.name as client_name, u.name as created_by_name, wo.id as work_order_number
                FROM {$this->table} wr
                JOIN clients c ON wr.client_id = c.id
                JOIN users u ON wr.created_by = u.id
                LEFT JOIN work_orders wo ON wr.work_order_id = wo.id
                ORDER BY wr.scheduled_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getPending() {
        $sql = "SELECT wr.*, c.name as client_name, c.whatsapp
                FROM {$this->table} wr
                JOIN clients c ON wr.client_id = c.id
                WHERE wr.status = 'programado' AND wr.scheduled_at <= NOW()
                ORDER BY wr.scheduled_at ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getByClient($clientId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE client_id = :client_id ORDER BY scheduled_at DESC");
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} 
            (client_id, work_order_id, vehicle_id, reminder_type, message, scheduled_at, whatsapp_number, created_by) 
            VALUES (:client_id, :work_order_id, :vehicle_id, :reminder_type, :message, :scheduled_at, :whatsapp_number, :created_by)");
        
        return $stmt->execute([
            'client_id' => $data['client_id'],
            'work_order_id' => $data['work_order_id'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'reminder_type' => $data['reminder_type'],
            'message' => $data['message'],
            'scheduled_at' => $data['scheduled_at'],
            'whatsapp_number' => $data['whatsapp_number'],
            'created_by' => $data['created_by']
        ]);
    }

    public function markAsSent($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'enviado', sent_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function markAsFailed($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'fallido' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function cancel($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'cancelado' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getCountPending() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'programado'");
        return $stmt->fetchColumn();
    }

    public function getCountToday() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE DATE(scheduled_at) = CURDATE()");
        return $stmt->fetchColumn();
    }

    // Templates predefinidos para mensajes
    public function getTemplates() {
        return [
            'cita' => [
                'label' => 'Recordatorio de Cita',
                'template' => "Hola {nombre}, le recordamos que tiene una cita programada para el día {fecha} a las {hora} en Torque Studio. Por favor confirme su asistencia."
            ],
            'entrega_ot' => [
                'label' => 'Vehículo Listo',
                'template' => "Hola {nombre}, su vehículo {vehiculo} ya está listo para retiro. Puede pasar a retirarlo en horario de atención. Total a pagar: {total}."
            ],
            'mantenimiento' => [
                'label' => 'Mantenimiento Programado',
                'template' => "Hola {nombre}, es hora del mantenimiento de su vehículo {vehiculo}. Contáctenos para agendar una cita y mantener su auto en óptimas condiciones."
            ],
            'promocion' => [
                'label' => 'Promoción Especial',
                'template' => "Hola {nombre}, en Torque Studio tenemos una promoción especial para ti: {promo}. Válido hasta {fecha}. ¡No te lo pierdas!"
            ],
            'personalizado' => [
                'label' => 'Mensaje Personalizado',
                'template' => ""
            ]
        ];
    }
}
