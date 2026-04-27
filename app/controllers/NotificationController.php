<?php
namespace App\Controllers;

use App\Models\Notification;

class NotificationController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
    }

    public function index() {
        $this->checkAuth();
        $notificationModel = new Notification();
        $notifications = $notificationModel->getAllByUser($_SESSION['user_id'], 50);
        
        view('notifications/index', ['notifications' => $notifications]);
    }

    public function markAsRead($id) {
        $this->checkAuth();
        $notificationModel = new Notification();
        $notificationModel->markAsRead($id, $_SESSION['user_id']);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/notifications');
    }

    public function markAllAsRead() {
        $this->checkAuth();
        $notificationModel = new Notification();
        $notificationModel->markAllAsRead($_SESSION['user_id']);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/notifications');
    }

    public function delete($id) {
        $this->checkAuth();
        $notificationModel = new Notification();
        $notificationModel->delete($id, $_SESSION['user_id']);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/notifications');
    }
}
