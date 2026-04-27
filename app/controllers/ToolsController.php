<?php
namespace App\Controllers;

use App\Models\MechanicTool;
use App\Models\WarehouseTool;
use App\Models\ToolRequest;
use App\Models\User;

class ToolsController {
    private $mechanicTool;
    private $warehouseTool;
    private $toolRequest;
    private $user;
    private $basePath;

    public function __construct() {
        $this->mechanicTool = new MechanicTool();
        $this->warehouseTool = new WarehouseTool();
        $this->toolRequest = new ToolRequest();
        $this->user = new User();
        
        $this->basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($this->basePath === '/' || $this->basePath === '\\') {
            $this->basePath = '';
        }
        
        // Verificar préstamos atrasados
        $this->toolRequest->checkOverdue();
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            redirect($this->basePath . '/login');
            exit;
        }
    }

    private function requireAuth() {
        $this->checkAuth();
    }

    private function requireRole($roleId) {
        $this->checkAuth();
        $actualRole = getUserRole();
        if ($actualRole != $roleId) {
            $_SESSION['error'] = "No tienes permiso. Rol requerido: $roleId, Tu rol: $actualRole";
            redirect($this->basePath . '/dashboard');
            exit;
        }
    }

    private function verifyCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('CSRF token validation failed');
        }
    }

    private function render($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die('View not found: ' . $view);
        }
        require $viewFile;
    }

    private function logActivity($action, $title, $description) {
        if (class_exists('App\Models\ActivityLog')) {
            $log = new \App\Models\ActivityLog();
            $log->log($action, 'tool', 0, $description);
        }
    }

    // 🏠 Dashboard de Herramientas
    public function index() {
        $this->requireAuth();
        
        $data = [
            'title' => 'Gestión de Herramientas',
            'mechanic_tools_count' => count($this->mechanicTool->getAllWithMechanics()),
            'warehouse_tools_count' => count($this->warehouseTool->getAllWithAuthInfo()),
            'pending_requests' => count($this->toolRequest->getPendingRequests()),
            'active_loans' => count($this->toolRequest->getActiveLoans()),
            'overdue_loans' => count($this->toolRequest->getOverdueLoans()),
            'total_warehouse_value' => $this->warehouseTool->getTotalValue(),
            'user_role' => $_SESSION['user_role'] ?? 0
        ];

        $this->render('tools/index', $data);
    }

    // 🔧 HERRAMIENTAS DE MECÁNICO
    
    public function mechanicTools() {
        $this->requireAuth();
        
        $mechanicId = $_GET['mechanic_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? 0;
        $userId = $_SESSION['user_id'];
        
        if ($mechanicId && ($userRole == 1 || $userId == $mechanicId)) {
            $tools = $this->mechanicTool->getByMechanic($mechanicId);
            $mechanic = $this->user->find($mechanicId);
            $stats = $this->mechanicTool->getStatusCounts($mechanicId);
            $totalValue = $this->mechanicTool->getTotalValue($mechanicId);
        } elseif ($userRole == 1) {
            $tools = $this->mechanicTool->getAllWithMechanics();
            $mechanic = null;
            $stats = [];
            $totalValue = 0;
        } else {
            $tools = $this->mechanicTool->getByMechanic($userId);
            $mechanic = $this->user->find($userId);
            $stats = $this->mechanicTool->getStatusCounts($userId);
            $totalValue = $this->mechanicTool->getTotalValue($userId);
        }

        $mechanics = $this->user->getByRole(2); // Mecánicos

        $this->render('tools/mechanic-tools', [
            'tools' => $tools,
            'mechanic' => $mechanic,
            'stats' => $stats,
            'total_value' => $totalValue,
            'mechanics' => $mechanics,
            'user_role' => $userRole
        ]);
    }

    public function createMechanicTool() {
        $this->requireRole(1); // Solo admin

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? null,
                'code' => $_POST['code'] ?? null,
                'brand' => $_POST['brand'] ?? null,
                'model' => $_POST['model'] ?? null,
                'purchase_date' => $_POST['purchase_date'] ?? null,
                'cost' => $_POST['cost'] ?? null,
                'mechanic_id' => $_POST['mechanic_id'],
                'notes' => $_POST['notes'] ?? null
            ];

            if ($this->mechanicTool->create($data)) {
                // Registrar actividad
                $this->logActivity('tool_assigned', 'Herramienta asignada', "{$data['name']} asignada a mecánico ID: {$data['mechanic_id']}");
                $_SESSION['success'] = 'Herramienta asignada correctamente';
                header('Location: ' . $this->basePath . '/tools/mechanic');
                exit;
            } else {
                $error = 'Error al asignar herramienta';
            }
        }

        $mechanics = $this->user->getByRole(2);
        $this->render('tools/create-mechanic-tool', ['mechanics' => $mechanics, 'error' => $error ?? null]);
    }

    public function updateMechanicToolStatus($id) {
        $this->requireRole(1);
        $this->verifyCsrf();

        $status = $_POST['status'];
        if ($this->mechanicTool->updateStatus($id, $status)) {
            $this->logActivity('tool_status_updated', 'Estado de herramienta actualizado', "Herramienta ID: $id cambiada a: $status");
            $_SESSION['success'] = 'Estado actualizado';
        }
        
        header('Location: ' . $this->basePath . '/tools/mechanic');
        exit;
    }

    // 🏭 HERRAMIENTAS DE BODEGA
    
    public function warehouseTools() {
        $this->requireAuth();
        
        $status = $_GET['status'] ?? null;
        
        if ($status) {
            $tools = $this->warehouseTool->getByStatus($status);
        } else {
            $tools = $this->warehouseTool->getAllWithAuthInfo();
        }

        $stats = $this->warehouseTool->getStatusCounts();
        $totalValue = $this->warehouseTool->getTotalValue();
        
        // Get pending requests to show "Entregar" only for tools with pending requests
        $pendingRequests = $this->toolRequest->getPendingRequests();
        $pendingToolIds = array_column($pendingRequests, 'warehouse_tool_id');
        
        // Get active loans to show who has each tool
        $activeLoans = $this->toolRequest->getActiveLoans();
        $loanInfo = [];
        foreach ($activeLoans as $loan) {
            $loanInfo[$loan['warehouse_tool_id']] = $loan;
        }

        $this->render('tools/warehouse-tools', [
            'tools' => $tools,
            'stats' => $stats,
            'total_value' => $totalValue,
            'selected_status' => $status,
            'user_role' => $_SESSION['user_role'] ?? 0,
            'pending_tool_ids' => $pendingToolIds,
            'loan_info' => $loanInfo
        ]);
    }

    public function createWarehouseTool() {
        $this->requireRole(1);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? null,
                'code' => $_POST['code'] ?? null,
                'brand' => $_POST['brand'] ?? null,
                'model' => $_POST['model'] ?? null,
                'serial_number' => $_POST['serial_number'] ?? null,
                'purchase_date' => $_POST['purchase_date'] ?? null,
                'cost' => $_POST['cost'] ?? null,
                'location' => $_POST['location'] ?? null,
                'requires_auth' => isset($_POST['requires_auth']),
                'auth_role_id' => $_POST['auth_role_id'] ?? null,
                'notes' => $_POST['notes'] ?? null
            ];

            try {
                if ($this->warehouseTool->create($data)) {
                    $this->logActivity('warehouse_tool_created', 'Herramienta de bodega creada', "{$data['name']} agregada al inventario");
                    $_SESSION['success'] = 'Herramienta de bodega creada correctamente';
                    header('Location: ' . $this->basePath . '/tools/warehouse');
                    exit;
                } else {
                    $error = 'Error al crear herramienta';
                }
            } catch (\PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $error = 'Error: El código "' . $data['code'] . '" ya está en uso. Por favor usa un código diferente.';
                } else {
                    $error = 'Error al crear herramienta: ' . $e->getMessage();
                }
            }
        }

        $this->render('tools/create-warehouse-tool', ['error' => $error ?? null]);
    }

    // � ENTREGA DIRECTA DE HERRAMIENTA (Admin)
    public function checkoutTool($toolId) {
        $this->requireRole(1);
        
        $tool = $this->warehouseTool->find($toolId);
        if (!$tool) {
            $_SESSION['error'] = 'Herramienta no encontrada';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        if (!in_array($tool['status'], ['disponible', 'solicitada'])) {
            $_SESSION['error'] = 'La herramienta no está disponible para entregar';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        // Get all mechanics for selection
        $mechanics = $this->user->getByRole(2); // Role 2 = Mechanic
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            
            $mechanicId = $_POST['mechanic_id'] ?? null;
            $notes = $_POST['notes'] ?? '';
            $expectedReturnDate = $_POST['expected_return_date'] ?? date('Y-m-d', strtotime('+1 day'));
            
            if (!$mechanicId) {
                $error = 'Debes seleccionar un mecánico';
            } else {
                // Create tool request (auto-approved since admin is delivering)
                $requestData = [
                    'mechanic_id' => $mechanicId,
                    'warehouse_tool_id' => $toolId,
                    'request_date' => date('Y-m-d'),
                    'expected_return_date' => $expectedReturnDate,
                    'notes' => $notes,
                    'status' => 'aprobada'
                ];
                
                if ($this->toolRequest->create($requestData)) {
                    // Update tool status
                    $this->warehouseTool->updateStatus($toolId, 'prestada');
                    
                    // Log activity
                    $mechanic = $this->user->find($mechanicId);
                    $this->logActivity('tool_checkout', 'Entrega de herramienta', "{$tool['name']} entregada a {$mechanic['name']}");
                    
                    $_SESSION['success'] = 'Herramienta entregada correctamente a ' . $mechanic['name'];
                    redirect($this->basePath . '/tools/warehouse');
                    exit;
                } else {
                    $error = 'Error al registrar la entrega';
                }
            }
        }
        
        $this->render('tools/checkout', [
            'tool' => $tool,
            'mechanics' => $mechanics,
            'error' => $error ?? null
        ]);
    }
    
    // 📥 DEVOLUCIÓN DE HERRAMIENTA DE BODEGA
    public function returnWarehouseTool($toolId) {
        $this->requireAuth();
        
        $tool = $this->warehouseTool->find($toolId);
        if (!$tool) {
            $_SESSION['error'] = 'Herramienta no encontrada';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        // Get active loan for this tool
        $activeLoan = $this->toolRequest->getActiveLoanForTool($toolId);
        if (!$activeLoan) {
            $_SESSION['error'] = 'No hay préstamo activo para esta herramienta';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        // Check if user can return (admin or the mechanic who borrowed it)
        $userRole = getUserRole();
        if ($userRole != 1 && $activeLoan['mechanic_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'No tienes permiso para devolver esta herramienta';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            
            $condition = $_POST['condition'] ?? 'buena';
            $notes = $_POST['notes'] ?? '';
            
            // Update request status
            $this->toolRequest->returnTool($activeLoan['id'], $condition, $notes);
            
            // Update tool status
            $this->warehouseTool->updateStatus($toolId, 'disponible');
            
            // Log activity
            $this->logActivity('tool_return', 'Devolución de herramienta', "{$tool['name']} devuelta en condición: {$condition}");
            
            $_SESSION['success'] = 'Herramienta devuelta correctamente';
            redirect($this->basePath . '/tools/warehouse');
            exit;
        }
        
        $this->render('tools/return', [
            'tool' => $tool,
            'loan' => $activeLoan,
            'error' => $error ?? null
        ]);
    }

    // MANDAR HERRAMIENTA A REPARACIÓN
    public function sendToRepair($toolId) {
        $this->requireRole(1);
        
        $tool = $this->warehouseTool->find($toolId);
        if (!$tool) {
            $_SESSION['error'] = 'Herramienta no encontrada';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        if (!in_array($tool['status'], ['disponible', 'solicitada', 'danada'])) {
            $_SESSION['error'] = 'Solo se pueden mandar a reparar herramientas disponibles, solicitadas o dañadas';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        if ($this->warehouseTool->updateStatus($toolId, 'en_mantenimiento')) {
            $this->logActivity('tool_repair', 'Herramienta a reparación', "{$tool['name']} enviada a reparación");
            $_SESSION['success'] = 'Herramienta enviada a reparación correctamente';
        } else {
            $_SESSION['error'] = 'Error al enviar herramienta a reparación';
        }
        
        redirect($this->basePath . '/tools/warehouse');
    }
    
    // MARCAR HERRAMIENTA COMO REPARADA
    public function markAsRepaired($toolId) {
        $this->requireRole(1);
        
        $tool = $this->warehouseTool->find($toolId);
        if (!$tool) {
            $_SESSION['error'] = 'Herramienta no encontrada';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        if ($tool['status'] != 'en_mantenimiento') {
            $_SESSION['error'] = 'Solo herramientas en mantenimiento pueden ser marcadas como reparadas';
            redirect($this->basePath . '/tools/warehouse');
        }
        
        if ($this->warehouseTool->updateStatus($toolId, 'disponible')) {
            $this->logActivity('tool_repaired', 'Herramienta reparada', "{$tool['name']} marcada como disponible después de reparación");
            $_SESSION['success'] = 'Herramienta marcada como reparada y disponible';
        } else {
            $_SESSION['error'] = 'Error al marcar herramienta como reparada';
        }
        
        redirect($this->basePath . '/tools/warehouse');
    }

    // SOLICITUDES DE HERRAMIENTAS
    
    public function requests() {
        $this->requireAuth();
        
        $mechanicId = $_SESSION['user_id'];
        $role = $_SESSION['user_role'] ?? 0;
        
        if ($role == 1) {
            // Admin ve todo
            $pending = $this->toolRequest->getPendingRequests();
            $active = $this->toolRequest->getActiveLoans();
            $overdue = $this->toolRequest->getOverdueLoans();
            $all = $this->toolRequest->getAllWithDetails();
        } else {
            // Mecánico solo ve sus solicitudes
            $pending = [];
            $active = [];
            $overdue = [];
            $all = $this->toolRequest->getByMechanic($mechanicId);
        }

        $this->render('tools/requests', [
            'pending' => $pending,
            'active' => $active,
            'overdue' => $overdue,
            'all' => $all,
            'available_tools' => $this->warehouseTool->getAvailable(),
            'user_role' => $role
        ]);
    }

    public function createRequest() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            
            $tool = $this->warehouseTool->find($_POST['warehouse_tool_id']);
            
            if (!$tool || $tool['status'] != 'disponible') {
                $_SESSION['error'] = 'La herramienta no está disponible';
                header('Location: ' . $this->basePath . '/tools/requests');
                exit;
            }

            $data = [
                'warehouse_tool_id' => $_POST['warehouse_tool_id'],
                'mechanic_id' => $_SESSION['user_id'],
                'request_date' => $_POST['request_date'],
                'return_date' => $_POST['return_date'] ?? null,
                'notes' => $_POST['notes'] ?? null
            ];

            if ($this->toolRequest->create($data)) {
                $this->logActivity('tool_requested', 'Herramienta solicitada', "Solicitud de {$tool['name']} por mecánico ID: {$_SESSION['user_id']}");
                $_SESSION['success'] = 'Solicitud enviada correctamente';
            } else {
                $_SESSION['error'] = 'Error al enviar solicitud';
            }
            
            header('Location: ' . $this->basePath . '/tools/requests');
            exit;
        }

        $available = $this->warehouseTool->getAvailable();
        $this->render('tools/create-request', ['available_tools' => $available]);
    }

    public function approveRequest($id) {
        $this->requireRole(1);
        $this->verifyCsrf();

        if ($this->toolRequest->approve($id, $_SESSION['user_id'])) {
            $this->logActivity('tool_approved', 'Solicitud aprobada', "Solicitud ID: $id aprobada");
            $_SESSION['success'] = 'Solicitud aprobada';
        } else {
            $_SESSION['error'] = 'Error al aprobar';
        }

        header('Location: ' . $this->basePath . '/tools/requests');
        exit;
    }

    public function rejectRequest($id) {
        $this->requireRole(1);
        $this->verifyCsrf();

        if ($this->toolRequest->reject($id, $_SESSION['user_id'])) {
            $this->logActivity('tool_rejected', 'Solicitud rechazada', "Solicitud ID: $id rechazada");
            $_SESSION['success'] = 'Solicitud rechazada';
        } else {
            $_SESSION['error'] = 'Error al rechazar';
        }

        header('Location: ' . $this->basePath . '/tools/requests');
        exit;
    }

    public function deliverTool($id) {
        $this->requireRole(1);
        $this->verifyCsrf();

        if ($this->toolRequest->deliver($id)) {
            $this->logActivity('tool_delivered', 'Herramienta entregada', "Herramienta entregada para solicitud ID: $id");
            $_SESSION['success'] = 'Herramienta marcada como entregada';
        } else {
            $_SESSION['error'] = 'Error al registrar entrega';
        }

        header('Location: ' . $this->basePath . '/tools/requests');
        exit;
    }

    public function returnTool($id) {
        $this->requireAuth();
        $this->verifyCsrf();

        $request = $this->toolRequest->find($id);
        
        // Solo admin o el mecánico que solicitó puede devolver
        if ($_SESSION['user_role'] != 1 && $request['mechanic_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'No autorizado';
            header('Location: ' . $this->basePath . '/tools/requests');
            exit;
        }

        $conditionNotes = $_POST['condition_notes'] ?? null;

        if ($this->toolRequest->returnTool($id, $conditionNotes)) {
            $this->logActivity('tool_returned', 'Herramienta devuelta', "Herramienta devuelta para solicitud ID: $id");
            $_SESSION['success'] = 'Herramienta devuelta correctamente';
        } else {
            $_SESSION['error'] = 'Error al registrar devolución';
        }

        header('Location: ' . $this->basePath . '/tools/requests');
        exit;
    }

    public function myTools() {
        $this->requireAuth();
        
        $mechanicId = $_SESSION['user_id'];
        
        $assignedTools = $this->mechanicTool->getByMechanic($mechanicId);
        $activeLoans = $this->toolRequest->getActiveLoans();
        // Filtrar solo las del mecánico actual
        $activeLoans = array_filter($activeLoans, function($loan) use ($mechanicId) {
            return $loan['mechanic_id'] == $mechanicId;
        });
        $requestHistory = $this->toolRequest->getByMechanic($mechanicId);

        $this->render('tools/my-tools', [
            'assigned_tools' => $assignedTools,
            'active_loans' => $activeLoans,
            'request_history' => $requestHistory
        ]);
    }

    // 🛒 SOLICITAR COMPRA DE HERRAMIENTA
    public function purchaseRequest() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            
            $toolName = $_POST['tool_name'] ?? '';
            $brand = $_POST['brand'] ?? '';
            $model = $_POST['model'] ?? '';
            $estimatedPrice = $_POST['estimated_price'] ?? 0;
            $priority = $_POST['priority'] ?? 'media';
            $reason = $_POST['reason'] ?? '';
            
            if (empty($toolName) || empty($reason)) {
                $this->render('tools/purchase-request', [
                    'error' => 'El nombre de la herramienta y el motivo son obligatorios'
                ]);
                return;
            }
            
            // Get mechanic info
            $mechanicId = $_SESSION['user_id'];
            $mechanic = $this->user->find($mechanicId);
            $mechanicName = $mechanic['name'] ?? 'Mecánico';
            
            // Create notification for admin
            $notificationModel = new \App\Models\Notification();
            $notificationModel->create([
                'user_id' => 1, // Admin user ID
                'title' => "Solicitud de compra: $toolName",
                'message' => "$mechanicName solicita comprar: $toolName. Prioridad: $priority. Motivo: $reason",
                'type' => 'tool_purchase_request',
                'link' => '/tools/purchase-requests'
            ]);
            
            // Log activity
            $this->logActivity('purchase_requested', 'Solicitud de compra', "$mechanicName solicitó comprar: $toolName ($priority)");
            
            $_SESSION['success_message'] = 'Solicitud de compra enviada al administrador correctamente';
            header('Location: ' . $this->basePath . '/tools/purchase-request');
            exit;
        }
        
        // Verificar mensajes de sesión
        $success = $_SESSION['success_message'] ?? '';
        $error = $_SESSION['error_message'] ?? '';
        unset($_SESSION['success_message'], $_SESSION['error_message']);
        
        $this->render('tools/purchase-request', [
            'success' => $success,
            'error' => $error
        ]);
    }
}
