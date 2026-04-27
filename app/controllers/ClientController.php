<?php
namespace App\Controllers;

use App\Models\Client;

class ClientController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
            exit;
        }
        // Role 1 (Admin) and 3 (Recepcionista) can manage clients
        if (getUserRole() != 1 && getUserRole() != 3) {
            die("Acceso denegado.");
        }
    }

    public function index() {
        $this->checkAuth();
        $model = new Client();
        $clients = $model->all();
        view('clients/index', ['clients' => $clients]);
    }

    public function create() {
        $this->checkAuth();
        view('clients/create');
    }

    public function store() {
        $this->checkAuth();
        $model = new Client();
        $model->create($_POST);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/clients');
    }

    public function edit($id) {
        $this->checkAuth();
        $model = new Client();
        $client = $model->find($id);
        
        if (!$client) {
            die("Cliente no encontrado.");
        }

        view('clients/edit', ['client' => $client]);
    }

    public function update($id) {
        $this->checkAuth();
        $model = new Client();
        $model->update($id, $_POST);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/clients');
    }

    public function delete($id) {
        $this->checkAuth();
        $model = new Client();
        $model->delete($id);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/clients');
    }
}
