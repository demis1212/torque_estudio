<?php
namespace App\Controllers;

use App\Models\Service;

class ServiceController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
            exit;
        }
        // Only Admin can manage services
        if (getUserRole() != 1) {
            die("Acceso denegado. Solo administradores.");
        }
    }

    public function index() {
        $this->checkAuth();
        $model = new Service();
        $services = $model->all();
        view('services/index', ['services' => $services]);
    }

    public function create() {
        $this->checkAuth();
        view('services/create');
    }

    public function store() {
        $this->checkAuth();
        $model = new Service();
        $model->create($_POST);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/services');
    }

    public function edit($id) {
        $this->checkAuth();
        $model = new Service();
        $service = $model->find($id);
        
        if (!$service) {
            die("Servicio no encontrado.");
        }

        view('services/edit', ['service' => $service]);
    }

    public function update($id) {
        $this->checkAuth();
        $model = new Service();
        $model->update($id, $_POST);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/services');
    }

    public function delete($id) {
        $this->checkAuth();
        $model = new Service();
        $model->delete($id);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/services');
    }
}
