<?php
namespace App\Controllers;

use App\Models\WorkOrder;
use App\Models\Notification;

class MechanicController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
        // Only mechanics (user_role = 2) and admins (user_role = 1) can access
        if (!in_array(getUserRole(), [1, 2])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/dashboard');
        }
    }

    public function dashboard() {
        $this->checkAuth();
        
        $woModel = new WorkOrder();
        $mechanicId = $_SESSION['user_id'];
        
        // Get orders assigned to this mechanic
        $assignedOrders = $woModel->getByMechanic($mechanicId);
        
        // Separate by status
        $pendingOrders = array_filter($assignedOrders, fn($o) => in_array($o['status'], ['recepcion', 'diagnostico', 'reparacion']));
        $completedOrders = array_filter($assignedOrders, fn($o) => $o['status'] === 'terminado');
        
        // Get notifications
        $notificationModel = new Notification();
        $notifications = $notificationModel->getUnreadByUser($mechanicId);
        
        // Stats
        $stats = [
            'total_assigned' => count($assignedOrders),
            'pending' => count($pendingOrders),
            'completed' => count($completedOrders),
            'in_progress' => count(array_filter($assignedOrders, fn($o) => $o['status'] === 'reparacion'))
        ];
        
        view('mechanics/dashboard', [
            'user_name' => $_SESSION['user_name'],
            'stats' => $stats,
            'pending_orders' => array_slice($pendingOrders, 0, 10),
            'completed_orders' => array_slice($completedOrders, 0, 5),
            'notifications' => $notifications
        ]);
    }

    public function myOrders() {
        $this->checkAuth();
        
        $woModel = new WorkOrder();
        $mechanicId = $_SESSION['user_id'];
        
        $orders = $woModel->getByMechanic($mechanicId);
        
        // Filter by status if provided
        if (!empty($_GET['status'])) {
            $orders = array_filter($orders, fn($o) => $o['status'] === $_GET['status']);
        }
        
        view('mechanics/orders', [
            'user_name' => $_SESSION['user_name'],
            'orders' => $orders,
            'filter_status' => $_GET['status'] ?? null
        ]);
    }

    public function updateOrderStatus($orderId) {
        $this->checkAuth();
        
        $woModel = new WorkOrder();
        $order = $woModel->find($orderId);
        
        if (!$order) {
            die("Orden no encontrada");
        }
        
        // Check if this mechanic is assigned to this order
        $assignments = $woModel->getAssignments($orderId);
        $isAssigned = false;
        foreach ($assignments as $assignment) {
            if ($assignment['mechanic_id'] == $_SESSION['user_id']) {
                $isAssigned = true;
                break;
            }
        }
        
        if (!$isAssigned && getUserRole() != 1) {
            die("No tienes permiso para actualizar esta orden");
        }
        
        $newStatus = $_POST['status'];
        $woModel->updateStatus($orderId, $newStatus);
        
        // Log activity
        $log = new \App\Models\ActivityLog();
        $log->log('status_change', 'work_order', $orderId, "Estado cambiado a: {$newStatus}");
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/mechanic/dashboard');
    }
}
