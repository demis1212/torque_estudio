<?php
namespace App\Controllers;

use App\Models\WorkOrder;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Part;

class ApiController {
    
    private function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    private function checkAuth() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';
        
        // Simple token check - in production use JWT
        if (!isset($_SESSION['user_id']) && !str_starts_with($token, 'Bearer ')) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }
    }

    // GET /api/work-orders
    public function workOrders() {
        $this->checkAuth();
        
        $model = new WorkOrder();
        $orders = $model->getAllWithDetails();
        
        // Add links
        foreach ($orders as &$order) {
            $order['links'] = [
                'self' => '/api/work-orders/' . $order['id'],
                'invoice' => '/reports/invoice/' . $order['id']
            ];
        }
        
        $this->jsonResponse([
            'data' => $orders,
            'meta' => [
                'total' => count($orders),
                'timestamp' => date('c')
            ]
        ]);
    }

    // GET /api/work-orders/{id}
    public function workOrder($id) {
        $this->checkAuth();
        
        $model = new WorkOrder();
        $order = $model->getWithDetails($id);
        
        if (!$order) {
            $this->jsonResponse(['error' => 'Not found'], 404);
        }
        
        $order['services'] = $model->getServices($id);
        $order['parts'] = $model->getParts($id);
        $order['assignments'] = $model->getAssignments($id);
        
        $this->jsonResponse(['data' => $order]);
    }

    // GET /api/clients
    public function clients() {
        $this->checkAuth();
        
        $model = new Client();
        $clients = $model->all();
        
        $this->jsonResponse([
            'data' => $clients,
            'meta' => ['total' => count($clients)]
        ]);
    }

    // GET /api/vehicles
    public function vehicles() {
        $this->checkAuth();
        
        $model = new Vehicle();
        $vehicles = $model->getAllWithClients();
        
        $this->jsonResponse([
            'data' => $vehicles,
            'meta' => ['total' => count($vehicles)]
        ]);
    }

    // GET /api/parts
    public function parts() {
        $this->checkAuth();
        
        $model = new Part();
        $parts = $model->getAllWithStockAlert();
        
        $lowStock = array_filter($parts, fn($p) => $p['quantity'] <= $p['min_stock']);
        
        $this->jsonResponse([
            'data' => $parts,
            'meta' => [
                'total' => count($parts),
                'low_stock_count' => count($lowStock),
                'low_stock_items' => array_values($lowStock)
            ]
        ]);
    }

    // GET /api/stats
    public function stats() {
        $this->checkAuth();
        
        $db = \Config\Database::getConnection();
        
        // Get various statistics
        $stats = [
            'work_orders' => [
                'total' => $db->query("SELECT COUNT(*) FROM work_orders")->fetchColumn(),
                'by_status' => $db->query("SELECT status, COUNT(*) as count FROM work_orders GROUP BY status")->fetchAll()
            ],
            'clients' => [
                'total' => $db->query("SELECT COUNT(*) FROM clients")->fetchColumn()
            ],
            'vehicles' => [
                'total' => $db->query("SELECT COUNT(*) FROM vehicles")->fetchColumn()
            ],
            'parts' => [
                'total' => $db->query("SELECT COUNT(*) FROM parts")->fetchColumn(),
                'low_stock' => $db->query("SELECT COUNT(*) FROM parts WHERE quantity <= min_stock")->fetchColumn()
            ],
            'revenue' => [
                'total' => $db->query("SELECT SUM(total_cost) FROM work_orders")->fetchColumn() ?? 0,
                'this_month' => $db->query("SELECT SUM(total_cost) FROM work_orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE())")->fetchColumn() ?? 0
            ]
        ];
        
        $this->jsonResponse(['data' => $stats]);
    }

    // POST /api/work-orders/{id}/status
    public function updateStatus($id) {
        $this->checkAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['status'])) {
            $this->jsonResponse(['error' => 'Status required'], 400);
        }
        
        $model = new WorkOrder();
        $model->updateStatus($id, $data['status']);
        
        // Log activity
        $log = new \App\Models\ActivityLog();
        $log->log('status_change', 'work_order', $id, "API: Estado cambiado a {$data['status']}");
        
        $this->jsonResponse([
            'message' => 'Status updated successfully',
            'data' => ['id' => $id, 'status' => $data['status']]
        ]);
    }

    // GET /api/mechanic/{id}/orders
    public function mechanicOrders($mechanicId) {
        $this->checkAuth();
        
        $model = new WorkOrder();
        $orders = $model->getByMechanic($mechanicId);
        
        $this->jsonResponse([
            'data' => $orders,
            'meta' => [
                'mechanic_id' => $mechanicId,
                'total' => count($orders)
            ]
        ]);
    }
}
