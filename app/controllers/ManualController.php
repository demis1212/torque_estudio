<?php
namespace App\Controllers;

use App\Models\Manual;
use App\Models\ActivityLog;

class ManualController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
    }

    public function index() {
        $this->checkAuth();
        
        $manualModel = new Manual();
        $category = $_GET['category'] ?? null;
        $view = $_GET['view'] ?? 'grouped'; // 'grouped' or 'list'
        
        if ($category) {
            $manuals = $manualModel->getByCategory($category);
            $groupedManuals = null;
        } else {
            if ($view === 'grouped') {
                $groupedManuals = $manualModel->getGroupedByBrand();
                $manuals = null;
            } else {
                $manuals = $manualModel->getSortedByBrandModelYear();
                $groupedManuals = null;
            }
        }
        
        $categories = $manualModel->getCategories();
        
        view('manuals/index', [
            'manuals' => $manuals,
            'grouped_manuals' => $groupedManuals,
            'categories' => $categories,
            'selected_category' => $category,
            'current_view' => $view
        ]);
    }

    public function view($id) {
        $this->checkAuth();
        
        $manualModel = new Manual();
        $manual = $manualModel->find($id);
        
        if (!$manual) {
            die("Manual no encontrado");
        }
        
        // Increment view count
        $manualModel->incrementViews($id);
        
        view('manuals/view', ['manual' => $manual]);
    }

    public function create() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $manualModel = new Manual();
            
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'category' => $_POST['category'],
                'brand' => $_POST['brand'] ?? null,
                'model' => $_POST['model'] ?? null,
                'year' => $_POST['year'] ?? null,
                'content' => $_POST['content'] ?? null,
                'file_path' => null,
                'user_id' => $_SESSION['user_id']
            ];
            
            // Handle file upload
            if (!empty($_FILES['manual_file']['name'])) {
                $uploadDir = 'uploads/manuals/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['manual_file']['name']);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['manual_file']['tmp_name'], $filePath)) {
                    $data['file_path'] = $filePath;
                    $data['file_type'] = pathinfo($fileName, PATHINFO_EXTENSION);
                }
            }
            
            $manualModel->create($data);
            
            // Log activity
            $log = new ActivityLog();
            $log->log('create', 'manual', null, "Manual creado: {$_POST['title']}");
            
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/manuals');
        }
        
        $manualModel = new Manual();
        $categories = $manualModel->getCategories();
        
        view('manuals/create', ['categories' => $categories]);
    }

    public function delete($id) {
        $this->checkAuth();
        
        $manualModel = new Manual();
        $manual = $manualModel->find($id);
        
        if ($manual && $_SESSION['user_role'] == 1) {
            // Delete file if exists
            if ($manual['file_path'] && file_exists($manual['file_path'])) {
                unlink($manual['file_path']);
            }
            
            $manualModel->delete($id);
            
            // Log activity
            $log = new ActivityLog();
            $log->log('delete', 'manual', $id, "Manual eliminado: {$manual['title']}");
        }
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/manuals');
    }

    public function search() {
        $this->checkAuth();
        
        $query = $_GET['q'] ?? '';
        $manualModel = new Manual();
        
        $results = $manualModel->search($query);
        $categories = $manualModel->getCategories();
        
        view('manuals/index', [
            'manuals' => $results,
            'categories' => $categories,
            'search_query' => $query,
            'is_search' => true
        ]);
    }
}
