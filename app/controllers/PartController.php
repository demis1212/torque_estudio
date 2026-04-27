<?php
namespace App\Controllers;

use App\Models\Part;
use App\Models\ActivityLog;
use App\Models\PurchaseAlert;

class PartController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
            exit;
        }
    }

    public function index() {
        $this->checkAuth();
        $partModel = new Part();
        
        $filters = [];
        if (!empty($_GET['category'])) {
            $filters['category'] = $_GET['category'];
            $parts = $partModel->getByCategory($_GET['category']);
        } elseif (!empty($_GET['search'])) {
            $parts = $partModel->search($_GET['search']);
        } else {
            $parts = $partModel->getAllWithStockAlert();
        }
        
        $categories = $partModel->getCategories();
        $lowStock = $partModel->getLowStock();
        
        view('parts/index', [
            'parts' => $parts,
            'categories' => $categories,
            'lowStock' => $lowStock,
            'filters' => $filters
        ]);
    }

    public function create() {
        $this->checkAuth();
        $partModel = new Part();
        $categories = $partModel->getCategories();
        view('parts/create', ['categories' => $categories]);
    }

    public function store() {
        $this->checkAuth();
        $partModel = new Part();
        
        // Check if code already exists
        $existing = $partModel->findBy('code', $_POST['code']);
        if ($existing) {
            $error = "El código ya existe.";
            $categories = $partModel->getCategories();
            view('parts/create', ['error' => $error, 'categories' => $categories]);
            return;
        }
        
        // Manejar nueva categoría
        $data = $_POST;
        if (!empty($_POST['category_new'])) {
            $data['category'] = $_POST['category_new'];
        }
        
        $partModel->create($data);
        
        // Log activity
        $log = new ActivityLog();
        $log->log('create', 'part', null, "Repuesto creado: {$_POST['code']} - {$_POST['name']}");
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/parts');
    }

    public function edit($id) {
        $this->checkAuth();
        $partModel = new Part();
        $part = $partModel->find($id);
        
        if (!$part) {
            die("Repuesto no encontrado.");
        }
        
        $categories = $partModel->getCategories();
        view('parts/edit', ['part' => $part, 'categories' => $categories]);
    }

    public function update($id) {
        $this->checkAuth();
        $partModel = new Part();
        $partModel->update($id, $_POST);
        
        // Log activity
        $log = new ActivityLog();
        $log->log('update', 'part', $id, "Repuesto actualizado: {$_POST['code']}");
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/parts');
    }

    public function delete($id) {
        $this->checkAuth();
        $partModel = new Part();
        $part = $partModel->find($id);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        
        if ($part) {
            // Intentar eliminar la pieza
            if ($partModel->delete($id)) {
                // Log activity
                $log = new ActivityLog();
                $log->log('delete', 'part', $id, "Repuesto eliminado: {$part['code']}");
                
                $_SESSION['success'] = "Repuesto '{$part['code']}' eliminado correctamente";
            } else {
                // La pieza está siendo usada en órdenes de trabajo
                $_SESSION['error'] = "No se puede eliminar '{$part['code']}' porque está siendo usado en órdenes de trabajo existentes";
            }
        } else {
            $_SESSION['error'] = "Repuesto no encontrado";
        }
        
        redirect($basePath . '/parts');
    }

    public function adjustStock($id) {
        $this->checkAuth();
        $partModel = new Part();
        $part = $partModel->find($id);
        
        if (!$part) {
            die("Repuesto no encontrado.");
        }
        
        $adjustment = intval($_POST['adjustment']);
        $reason = $_POST['reason'] ?? 'Ajuste de inventario';
        
        $partModel->updateStock($id, $adjustment);
        
        // Log activity
        $log = new ActivityLog();
        $log->log('adjust_stock', 'part', $id, "Stock ajustado: {$adjustment} unidades. Razón: {$reason}");
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/parts');
    }

    // Purchase alerts
    public function alerts() {
        $this->checkAuth();
        $alertModel = new PurchaseAlert();
        $alerts = $alertModel->getAllWithPartInfo();
        
        view('parts/alerts', ['alerts' => $alerts]);
    }
    
    public function buyAlert($id) {
        $this->checkAuth();
        $alertModel = new PurchaseAlert();
        $alertModel->markAsPurchased($id, 'Compra realizada');
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/parts/alerts');
    }
    
    public function cancelAlert($id) {
        $this->checkAuth();
        $alertModel = new PurchaseAlert();
        $alertModel->markAsCancelled($id, 'Alerta cancelada manualmente');
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/parts/alerts');
    }
}
