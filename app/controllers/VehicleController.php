<?php
namespace App\Controllers;

use App\Models\Vehicle;
use App\Models\Client;
use App\Models\WorkOrder;

class VehicleController {
    
    private function checkLogin() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
            exit;
        }
    }

    private function checkAuth() {
        $this->checkLogin();
    }
    
    private function requireManagerRole() {
        $this->checkLogin();
        // Role 1 (Admin) and 3 (Recepcionista) can manage vehicles
        if (getUserRole() != 1 && getUserRole() != 3) {
            die("Acceso denegado. Solo administradores y recepcionistas pueden gestionar vehículos.");
        }
    }

    public function index() {
        $this->checkAuth(); // Solo requiere login, no rol específico
        $vehicleModel = new Vehicle();
        $vehicles = $vehicleModel->getAllWithClients();
        view('vehicles/index', [
            'vehicles' => $vehicles,
            'userRole' => getUserRole()
        ]);
    }

    public function create() {
        $this->requireManagerRole();
        $clientModel = new Client();
        $clients = $clientModel->all();
        view('vehicles/create', ['clients' => $clients]);
    }

    public function store() {
        $this->requireManagerRole();
        $vehicleModel = new Vehicle();
        $vehicleModel->create($_POST);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/vehicles');
    }

    public function edit($id) {
        $this->requireManagerRole();
        $vehicleModel = new Vehicle();
        $vehicle = $vehicleModel->find($id);
        
        if (!$vehicle) {
            die("Vehículo no encontrado.");
        }

        $clientModel = new Client();
        $clients = $clientModel->all();
        view('vehicles/edit', ['vehicle' => $vehicle, 'clients' => $clients]);
    }

    public function update($id) {
        $this->requireManagerRole();
        $vehicleModel = new Vehicle();
        $vehicleModel->update($id, $_POST);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/vehicles');
    }

    public function delete($id) {
        $this->requireManagerRole();
        $vehicleModel = new Vehicle();
        $vehicleModel->delete($id);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/vehicles');
    }

    public function show($id) {
        $this->checkLogin();

        $vehicleModel = new Vehicle();
        $vehicle = $vehicleModel->getWithClient($id);

        if (!$vehicle) {
            die("Vehículo no encontrado.");
        }

        $woModel = new WorkOrder();
        $history = [];
        if (!empty($vehicle['plate'])) {
            $history = $woModel->getVehicleHistoryByPlate($vehicle['plate']);
        }

        view('vehicles/show', [
            'vehicle' => $vehicle,
            'history' => $history,
        ]);
    }
}
