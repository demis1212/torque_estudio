<?php
namespace App\Controllers;

use App\Models\Client;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\Notification;
use App\Models\Part;

class DashboardController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
            exit;
        }

        // Get statistics
        $clientModel = new Client();
        $vehicleModel = new Vehicle();
        $workOrderModel = new WorkOrder();

        $stats = [
            'total_clients' => count($clientModel->all()),
            'total_vehicles' => count($vehicleModel->all()),
            'total_orders' => count($workOrderModel->all()),
            'pending_orders' => count(array_filter($workOrderModel->all(), function($o) {
                return in_array($o['status'], ['recepcion', 'diagnostico', 'reparacion']);
            })),
            'completed_orders' => count(array_filter($workOrderModel->all(), function($o) {
                return $o['status'] === 'terminado';
            }))
        ];

        // Recent orders
        $recentOrders = array_slice($workOrderModel->getAllWithDetails(), 0, 5);

        // Get notifications
        $notificationModel = new Notification();
        $notifications = $notificationModel->getUnreadByUser($_SESSION['user_id']);
        $notificationCount = count($notifications);

        // Get low stock alerts for admin
        $lowStockAlerts = [];
        if (getUserRole() == 1) {
            $partModel = new Part();
            $lowStockAlerts = $partModel->getLowStock();
        }

        view('dashboard', [
            'user_name' => $_SESSION['user_name'],
            'user_role' => getUserRole(),
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'notifications' => $notifications,
            'notification_count' => $notificationCount,
            'low_stock_alerts' => $lowStockAlerts
        ]);
    }
}
