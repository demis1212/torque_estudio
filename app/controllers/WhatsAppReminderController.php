<?php
namespace App\Controllers;

use App\Models\WhatsAppReminder;
use App\Models\Client;

class WhatsAppReminderController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
    }

    public function index() {
        $this->checkAuth();
        
        $waModel = new WhatsAppReminder();
        $clientModel = new Client();
        
        $reminders = $waModel->getAllWithDetails();
        $pendingCount = $waModel->getCountPending();
        $todayCount = $waModel->getCountToday();
        $templates = $waModel->getTemplates();
        $clients = $clientModel->all();
        
        view('whatsapp-reminders/index', [
            'reminders' => $reminders,
            'pendingCount' => $pendingCount,
            'todayCount' => $todayCount,
            'templates' => $templates,
            'clients' => $clients
        ]);
    }

    public function store() {
        $this->checkAuth();
        
        $clientModel = new Client();
        $client = $clientModel->find($_POST['client_id']);
        
        if (!$client) {
            $_SESSION['error'] = 'Cliente no encontrado';
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/whatsapp-reminders');
            return;
        }
        
        $waModel = new WhatsAppReminder();
        $data = [
            'client_id' => $_POST['client_id'],
            'work_order_id' => $_POST['work_order_id'] ?? null,
            'vehicle_id' => $_POST['vehicle_id'] ?? null,
            'reminder_type' => $_POST['reminder_type'],
            'message' => $_POST['message'],
            'scheduled_at' => $_POST['scheduled_at'],
            'whatsapp_number' => $client['whatsapp'] ?? $client['phone'],
            'created_by' => $_SESSION['user_id']
        ];
        
        $waModel->create($data);
        $_SESSION['success'] = 'Recordatorio programado correctamente';
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/whatsapp-reminders');
    }

    public function sendNow($id) {
        $this->checkAuth();
        
        $waModel = new WhatsAppReminder();
        $reminder = $waModel->find($id);
        
        if (!$reminder) {
            $_SESSION['error'] = 'Recordatorio no encontrado';
        } else {
            // Aquí iría la integración real con API de WhatsApp
            // Por ahora simulamos el envío
            $result = $this->simulateWhatsAppSend($reminder);
            
            if ($result['success']) {
                $waModel->markAsSent($id);
                $_SESSION['success'] = 'Mensaje enviado correctamente a ' . $reminder['whatsapp_number'];
            } else {
                $waModel->markAsFailed($id);
                $_SESSION['error'] = 'Error al enviar: ' . $result['message'];
            }
        }
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/whatsapp-reminders');
    }

    public function cancel($id) {
        $this->checkAuth();
        
        $waModel = new WhatsAppReminder();
        $waModel->cancel($id);
        
        $_SESSION['success'] = 'Recordatorio cancelado';
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/whatsapp-reminders');
    }

    // Simulador de envío WhatsApp (reemplazar con API real)
    private function simulateWhatsAppSend($reminder) {
        // Validar número
        $number = preg_replace('/[^0-9]/', '', $reminder['whatsapp_number']);
        if (strlen($number) < 9) {
            return ['success' => false, 'message' => 'Número de WhatsApp inválido'];
        }
        
        // Simular envío exitoso
        // En producción, aquí se integraría con:
        // - WhatsApp Business API
        // - Twilio
        // - Meta Business Suite
        // - etc.
        
        // Guardar log del "envío"
        $logMessage = sprintf(
            "[SIMULACIÓN] WhatsApp enviado a %s | Cliente: %s | Tipo: %s | Mensaje: %s",
            $reminder['whatsapp_number'],
            $reminder['client_id'],
            $reminder['reminder_type'],
            substr($reminder['message'], 0, 50) . '...'
        );
        error_log($logMessage);
        
        return ['success' => true, 'message' => 'Mensaje simulado enviado'];
    }

    // API endpoint para enviar mensajes programados (ejecutar via cron)
    public function processScheduled() {
        // Esta acción puede ser llamada por un cron job
        $waModel = new WhatsAppReminder();
        $pending = $waModel->getPending();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($pending as $reminder) {
            $result = $this->simulateWhatsAppSend($reminder);
            
            if ($result['success']) {
                $waModel->markAsSent($reminder['id']);
                $sent++;
            } else {
                $waModel->markAsFailed($reminder['id']);
                $failed++;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'processed' => count($pending),
            'sent' => $sent,
            'failed' => $failed,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    // Endpoint para configurar recordatorio automático de entrega OT
    public function autoCreateDeliveryReminder($workOrderId, $clientId) {
        $clientModel = new Client();
        $client = $clientModel->find($clientId);
        
        if (!$client || empty($client['whatsapp'])) {
            return false;
        }
        
        $waModel = new WhatsAppReminder();
        $templates = $waModel->getTemplates();
        
        $message = str_replace(
            ['{nombre}', '{vehiculo}'],
            [$client['name'], 'su vehículo'],
            $templates['entrega_ot']['template']
        );
        
        $data = [
            'client_id' => $clientId,
            'work_order_id' => $workOrderId,
            'reminder_type' => 'entrega_ot',
            'message' => $message,
            'scheduled_at' => date('Y-m-d H:i:s'), // Enviar inmediatamente
            'whatsapp_number' => $client['whatsapp'],
            'created_by' => $_SESSION['user_id'] ?? 1
        ];
        
        return $waModel->create($data);
    }
}
