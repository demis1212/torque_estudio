<?php
namespace App\Controllers;

use App\Models\WorkOrder;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\User;
use App\Models\Part;
use App\Models\ActivityLog;

class WorkOrderController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
    }

    public function index() {
        require_auth();
        $woModel = new WorkOrder();
        $orders = $woModel->getAllWithDetails();
        view('work-orders/index', ['orders' => $orders]);
    }

    public function kanban() {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $orders = $woModel->getAllWithDetails();
        view('work-orders/kanban', ['orders' => $orders]);
    }

    public function create() {
        $this->checkAuth();
        $clientModel = new Client();
        $vehicleModel = new Vehicle();
        $serviceModel = new Service();
        $userModel = new User();
        
        $clients = $clientModel->all();
        $vehicles = $vehicleModel->getAllWithClients();
        $services = $serviceModel->all();
        $mechanics = $userModel->getByRole(2); // role 2 = mechanic
        
        view('work-orders/create', [
            'clients' => $clients,
            'vehicles' => $vehicles,
            'services' => $services,
            'mechanics' => $mechanics
        ]);
    }

    public function store() {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $data = $_POST;
        $data['user_id'] = $_SESSION['user_id'];
        
        $workOrderId = $woModel->create($data);
        
        // Add services if provided
        if (!empty($data['services']) && is_array($data['services'])) {
            $woModel->addServices($workOrderId, $data['services']);
        }
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/work-orders');
    }

    public function show($id) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $order = $woModel->getWithDetails($id);
        
        if (!$order) {
            die("Orden no encontrada.");
        }
        
        $selectedServices = $woModel->getServices($id);
        $selectedParts = $woModel->getParts($id);
        $assignments = $woModel->getAssignments($id);
        
        view('work-orders/show', [
            'order' => $order,
            'selectedServices' => $selectedServices,
            'selectedParts' => $selectedParts,
            'assignments' => $assignments
        ]);
    }

    public function edit($id) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $order = $woModel->getWithDetails($id);
        
        if (!$order) {
            die("Orden no encontrada.");
        }

        $clientModel = new Client();
        $vehicleModel = new Vehicle();
        $serviceModel = new Service();
        $userModel = new User();
        $partModel = new Part();
        
        $clients = $clientModel->all();
        $vehicles = $vehicleModel->getAllWithClients();
        $services = $serviceModel->all();
        $selectedServices = $woModel->getServices($id);
        
        // Get mechanics (role_id = 2)
        $mechanics = $userModel->getAllWithRoles();
        $mechanics = array_filter($mechanics, fn($u) => $u['role_id'] == 2);
        
        // Get parts and assignments
        $parts = $partModel->getAllWithStockAlert();
        $selectedParts = $woModel->getParts($id);
        $assignments = $woModel->getAssignments($id);
        
        view('work-orders/edit', [
            'order' => $order,
            'clients' => $clients,
            'vehicles' => $vehicles,
            'services' => $services,
            'selectedServices' => $selectedServices,
            'mechanics' => $mechanics,
            'parts' => $parts,
            'selectedParts' => $selectedParts,
            'assignments' => $assignments
        ]);
    }

    public function update($id) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $woModel->update($id, $_POST);
        
        // Update services
        if (!empty($_POST['services']) && is_array($_POST['services'])) {
            $woModel->updateServices($id, $_POST['services']);
        }
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/work-orders');
    }

    public function updateStatus($id) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $woModel->updateStatus($id, $_POST['status']);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/work-orders');
    }

    public function delete($id) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $woModel->delete($id);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/work-orders');
    }

    // Assign mechanic to work order
    public function assignMechanic($id) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $woModel->assignMechanic($id, $_POST['mechanic_id'], $_POST['notes'] ?? null);
        
        // Log activity
        $log = new ActivityLog();
        $log->log('assign', 'work_order', $id, "Mecánico asignado a la orden #{$id}");
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/work-orders/edit/' . $id);
    }

    // Remove mechanic from work order
    public function removeMechanic($id, $mechanicId) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $woModel->removeMechanic($id, $mechanicId);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/work-orders/edit/' . $id);
    }

    // Add part to work order
    public function addPart($id) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $partModel = new Part();
        
        $part = $partModel->find($_POST['part_id']);
        if ($part) {
            $result = $woModel->addPart($id, $_POST['part_id'], $_POST['quantity'], $part['sale_price']);
            
            if ($result['success']) {
                // Log activity
                $log = new ActivityLog();
                $log->log('add_part', 'work_order', $id, "Repuesto agregado: {$part['name']} x{$_POST['quantity']}");
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }
        } else {
            $_SESSION['error'] = 'Repuesto no encontrado';
        }
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/work-orders/edit/' . $id);
    }

    // Remove part from work order
    public function removePart($id, $partEntryId) {
        $this->checkAuth();
        $woModel = new WorkOrder();
        $woModel->removePart($id, $partEntryId);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/work-orders/edit/' . $id);
    }
}
