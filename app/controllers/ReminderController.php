<?php
namespace App\Controllers;

use App\Models\Reminder;

class ReminderController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
    }

    public function index() {
        $this->checkAuth();
        
        $reminderModel = new Reminder();
        $reminders = $reminderModel->getByUser($_SESSION['user_id']);
        $upcoming = $reminderModel->getUpcoming($_SESSION['user_id'], 7);
        $overdue = $reminderModel->getOverdue($_SESSION['user_id']);
        
        view('reminders/index', [
            'reminders' => $reminders,
            'upcoming' => $upcoming,
            'overdue' => $overdue
        ]);
    }

    public function store() {
        $this->checkAuth();
        
        $reminderModel = new Reminder();
        $data = [
            'user_id' => $_SESSION['user_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? null,
            'reminder_date' => $_POST['reminder_date'],
            'entity_type' => $_POST['entity_type'] ?? null,
            'entity_id' => $_POST['entity_id'] ?? null
        ];
        
        $reminderModel->create($data);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/reminders');
    }

    public function complete($id) {
        $this->checkAuth();
        
        $reminderModel = new Reminder();
        $reminderModel->markAsCompleted($id, $_SESSION['user_id']);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/reminders');
    }

    public function delete($id) {
        $this->checkAuth();
        
        $reminderModel = new Reminder();
        $reminderModel->delete($id, $_SESSION['user_id']);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/reminders');
    }
}
