<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\ClientController;
use App\Controllers\VehicleController;
use App\Controllers\WorkOrderController;
use App\Controllers\ServiceController;
use App\Controllers\PartController;
use App\Controllers\ReportController;
use App\Controllers\NotificationController;
use App\Controllers\MechanicController;
use App\Controllers\ApiController;
use App\Controllers\ReminderController;
use App\Controllers\SettingsController;
use App\Controllers\VinDecoderController;
use App\Controllers\DtcController;
use App\Controllers\ManualController;
use App\Controllers\ToolsController;
use App\Controllers\WhatsAppReminderController;
use App\Controllers\WorkshopOpsController;

// Simple Router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Get the base path if the project is served from a subdirectory
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

// Remove base path from uri
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Compatibilidad: cuando el servidor recibe /torque/* aunque la app esté en raíz
if (strpos($uri, '/torque/') === 0) {
    $uri = substr($uri, strlen('/torque'));
} elseif ($uri === '/torque') {
    $uri = '/';
}

if ($uri === '') $uri = '/';

// Protected routes that require authentication
$protectedRoutes = [
    '/dashboard', '/work-orders', '/clients', '/vehicles', 
    '/services', '/parts', '/tools', '/reports', 
    '/workshop-ops', '/users', '/settings', '/notifications',
    '/work-orders/create', '/clients/create', '/vehicles/create',
    '/services/create', '/parts/create', '/users/create',
    '/manuals', '/vin-decoder', '/dtc', '/whatsapp-reminders',
    // API routes protegidas
    '/api/work-orders', '/api/clients', '/api/vehicles', 
    '/api/parts', '/api/stats', '/api/mechanic'
];

// Check if current URI starts with any protected route
$isProtected = false;
foreach ($protectedRoutes as $protectedRoute) {
    if (strpos($uri, $protectedRoute) === 0) {
        $isProtected = true;
        break;
    }
}

// Require authentication for protected routes
if ($isProtected && !isset($_SESSION['user_id'])) {
    // Debug: Log para verificar que el middleware funciona
    error_log("[AUTH] Acceso denegado a $uri - Redirigiendo a login");
    redirect($basePath . '/login');
    exit;
}

// Verificar timeout de sesión (30 minutos de inactividad) y vinculación IP
if (isset($_SESSION['user_id'])) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $timeout = 30 * 60; // 30 minutos
    
    // Verificar si la sesión ha expirado por inactividad
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        error_log("[AUTH] Sesión expirada por inactividad - IP: $ip");
        session_destroy();
        redirect($basePath . '/login');
        exit;
    }
    
    // Verificar vinculación de IP
    if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $ip) {
        error_log("[AUTH] Detección de robo de sesión - IP cambiada");
        session_destroy();
        redirect($basePath . '/login');
        exit;
    }
    
    // Actualizar timestamp de última actividad
    $_SESSION['last_activity'] = time();
}

// Debug: Log cuando el acceso es permitido
if ($isProtected) {
    error_log("[AUTH] Acceso permitido a $uri - Usuario autenticado");
}

// CSRF check for all POST requests
if ($method === 'POST') {
    verify_csrf();
}

// Extract ID from URI if present (e.g. /clients/edit/1)
$parts = explode('/', trim($uri, '/'));
$id = isset($parts[2]) ? (int)$parts[2] : null;

// Route definitions
if ($uri === '/') {
    if (isset($_SESSION['user_id'])) {
        redirect($basePath . '/dashboard');
    } else {
        redirect($basePath . '/login');
    }
} elseif ($uri === '/login') {
    $controller = new AuthController();
    if ($method === 'GET') $controller->showLogin();
    elseif ($method === 'POST') $controller->login();
} elseif ($uri === '/logout') {
    $controller = new AuthController();
    $controller->logout();
} elseif ($uri === '/dashboard') {
    $controller = new DashboardController();
    $controller->index();
} 

// User Routes
elseif ($uri === '/users') {
    $controller = new UserController();
    $controller->index();
} elseif ($uri === '/users/create') {
    $controller = new UserController();
    if ($method === 'GET') $controller->create();
    elseif ($method === 'POST') $controller->store();
} elseif (preg_match('#^/users/edit/(\d+)$#', $uri, $matches)) {
    $controller = new UserController();
    if ($method === 'GET') $controller->edit($matches[1]);
    elseif ($method === 'POST') $controller->update($matches[1]);
} elseif (preg_match('#^/users/delete/(\d+)$#', $uri, $matches)) {
    $controller = new UserController();
    if ($method === 'POST') $controller->delete($matches[1]);
}

// Client Routes
elseif ($uri === '/clients') {
    $controller = new ClientController();
    $controller->index();
} elseif ($uri === '/clients/create') {
    $controller = new ClientController();
    if ($method === 'GET') $controller->create();
    elseif ($method === 'POST') $controller->store();
} elseif (preg_match('#^/clients/edit/(\d+)$#', $uri, $matches)) {
    $controller = new ClientController();
    if ($method === 'GET') $controller->edit($matches[1]);
    elseif ($method === 'POST') $controller->update($matches[1]);
} elseif (preg_match('#^/clients/delete/(\d+)$#', $uri, $matches)) {
    $controller = new ClientController();
    if ($method === 'POST') $controller->delete($matches[1]);
} 

// Vehicle Routes
elseif ($uri === '/vehicles') {
    $controller = new VehicleController();
    $controller->index();
} elseif ($uri === '/vehicles/create') {
    $controller = new VehicleController();
    if ($method === 'GET') $controller->create();
    elseif ($method === 'POST') $controller->store();
} elseif (preg_match('#^/vehicles/edit/(\d+)$#', $uri, $matches)) {
    $controller = new VehicleController();
    if ($method === 'GET') $controller->edit($matches[1]);
    elseif ($method === 'POST') $controller->update($matches[1]);
} elseif (preg_match('#^/vehicles/show/(\d+)$#', $uri, $matches)) {
    $controller = new VehicleController();
    if ($method === 'GET') $controller->show($matches[1]);
} elseif (preg_match('#^/vehicles/delete/(\d+)$#', $uri, $matches)) {
    $controller = new VehicleController();
    if ($method === 'POST') $controller->delete($matches[1]);
}

// Work Order Routes
elseif ($uri === '/work-orders') {
    $controller = new WorkOrderController();
    $controller->index();
} elseif ($uri === '/work-orders/kanban') {
    $controller = new WorkOrderController();
    $controller->kanban();
} elseif ($uri === '/work-orders/create') {
    $controller = new WorkOrderController();
    if ($method === 'GET') $controller->create();
    elseif ($method === 'POST') $controller->store();
} elseif (preg_match('#^/work-orders/show/(\d+)$#', $uri, $matches)) {
    $controller = new WorkOrderController();
    if ($method === 'GET') $controller->show($matches[1]);
} elseif (preg_match('#^/work-orders/edit/(\d+)$#', $uri, $matches)) {
    $controller = new WorkOrderController();
    if ($method === 'GET') $controller->edit($matches[1]);
    elseif ($method === 'POST') $controller->update($matches[1]);
} elseif (preg_match('#^/work-orders/delete/(\d+)$#', $uri, $matches)) {
    $controller = new WorkOrderController();
    if ($method === 'POST') $controller->delete($matches[1]);
} elseif (preg_match('#^/work-orders/status/(\d+)$#', $uri, $matches)) {
    $controller = new WorkOrderController();
    if ($method === 'POST') $controller->updateStatus($matches[1]);
}

// Service Routes
elseif ($uri === '/services') {
    $controller = new ServiceController();
    $controller->index();
} elseif ($uri === '/services/create') {
    $controller = new ServiceController();
    if ($method === 'GET') $controller->create();
    elseif ($method === 'POST') $controller->store();
} elseif (preg_match('#^/services/edit/(\d+)$#', $uri, $matches)) {
    $controller = new ServiceController();
    if ($method === 'GET') $controller->edit($matches[1]);
    elseif ($method === 'POST') $controller->update($matches[1]);
} elseif (preg_match('#^/services/delete/(\d+)$#', $uri, $matches)) {
    $controller = new ServiceController();
    if ($method === 'POST') $controller->delete($matches[1]);
}

// Part Routes (Inventory)
elseif ($uri === '/parts') {
    $controller = new PartController();
    $controller->index();
} elseif ($uri === '/parts/create') {
    $controller = new PartController();
    if ($method === 'GET') $controller->create();
    elseif ($method === 'POST') $controller->store();
} elseif (preg_match('#^/parts/edit/(\d+)$#', $uri, $matches)) {
    $controller = new PartController();
    if ($method === 'GET') $controller->edit($matches[1]);
    elseif ($method === 'POST') $controller->update($matches[1]);
} elseif (preg_match('#^/parts/delete/(\d+)$#', $uri, $matches)) {
    $controller = new PartController();
    if ($method === 'POST') $controller->delete($matches[1]);
} elseif (preg_match('#^/parts/stock/(\d+)$#', $uri, $matches)) {
    $controller = new PartController();
    if ($method === 'POST') $controller->adjustStock($matches[1]);
} elseif ($uri === '/parts/alerts') {
    $controller = new PartController();
    if ($method === 'GET') $controller->alerts();
} elseif (preg_match('#^/parts/alerts/buy/(\d+)$#', $uri, $matches)) {
    $controller = new PartController();
    if ($method === 'POST') $controller->buyAlert($matches[1]);
} elseif (preg_match('#^/parts/alerts/cancel/(\d+)$#', $uri, $matches)) {
    $controller = new PartController();
    if ($method === 'POST') $controller->cancelAlert($matches[1]);
}

// Work Order Assignment Routes
elseif (preg_match('#^/work-orders/(\d+)/assign-mechanic$#', $uri, $matches)) {
    $controller = new WorkOrderController();
    if ($method === 'POST') $controller->assignMechanic($matches[1]);
} elseif (preg_match('#^/work-orders/(\d+)/remove-mechanic/(\d+)$#', $uri, $matches)) {
    $controller = new WorkOrderController();
    if ($method === 'POST') $controller->removeMechanic($matches[1], $matches[2]);
} elseif (preg_match('#^/work-orders/(\d+)/add-part$#', $uri, $matches)) {
    $controller = new WorkOrderController();
    if ($method === 'POST') $controller->addPart($matches[1]);
} elseif (preg_match('#^/work-orders/(\d+)/remove-part/(\d+)$#', $uri, $matches)) {
    $controller = new WorkOrderController();
    if ($method === 'POST') $controller->removePart($matches[1], $matches[2]);
}

// Report Routes
elseif ($uri === '/search') {
    $controller = new ReportController();
    $controller->search();
} elseif (preg_match('#^/reports/invoice/(\d+)$#', $uri, $matches)) {
    $controller = new ReportController();
    $controller->invoice($matches[1]);
} elseif ($uri === '/reports' || $uri === '/reports/index') {
    $controller = new ReportController();
    $controller->index();
} elseif ($uri === '/reports/data') {
    $controller = new ReportController();
    $controller->data();
} elseif ($uri === '/reports/activity') {
    $controller = new ReportController();
    $controller->activity();
} elseif ($uri === '/reports/mechanic-productivity') {
    $controller = new ReportController();
    $controller->mechanicProductivity();
} elseif ($uri === '/reports/manager-dashboard') {
    $controller = new ReportController();
    $controller->managerDashboard();
}

// WhatsApp Reminders Routes
elseif ($uri === '/whatsapp-reminders') {
    $controller = new WhatsAppReminderController();
    $controller->index();
} elseif ($uri === '/whatsapp-reminders/store') {
    $controller = new WhatsAppReminderController();
    if ($method === 'POST') $controller->store();
} elseif (preg_match('#^/whatsapp-reminders/send/(\d+)$#', $uri, $matches)) {
    $controller = new WhatsAppReminderController();
    if ($method === 'POST') $controller->sendNow($matches[1]);
} elseif (preg_match('#^/whatsapp-reminders/cancel/(\d+)$#', $uri, $matches)) {
    $controller = new WhatsAppReminderController();
    if ($method === 'POST') $controller->cancel($matches[1]);
} elseif ($uri === '/api/whatsapp/process-scheduled') {
    $controller = new WhatsAppReminderController();
    $controller->processScheduled();
} elseif ($uri === '/api/stats') {
    $controller = new ReportController();
    $controller->dashboardStats();
}

// Notification Routes
elseif ($uri === '/notifications') {
    $controller = new NotificationController();
    $controller->index();
} elseif (preg_match('#^/notifications/read/(\d+)$#', $uri, $matches)) {
    $controller = new NotificationController();
    if ($method === 'POST') $controller->markAsRead($matches[1]);
} elseif ($uri === '/notifications/read-all') {
    $controller = new NotificationController();
    if ($method === 'POST') $controller->markAllAsRead();
} elseif (preg_match('#^/notifications/delete/(\d+)$#', $uri, $matches)) {
    $controller = new NotificationController();
    if ($method === 'POST') $controller->delete($matches[1]);
}

// Mechanic Routes
elseif ($uri === '/mechanic/dashboard') {
    $controller = new MechanicController();
    $controller->dashboard();
} elseif ($uri === '/mechanic/orders') {
    $controller = new MechanicController();
    $controller->myOrders();
} elseif (preg_match('#^/mechanic/order/(\d+)/status$#', $uri, $matches)) {
    $controller = new MechanicController();
    if ($method === 'POST') $controller->updateOrderStatus($matches[1]);
}

// API Routes
elseif ($uri === '/api/work-orders') {
    $controller = new ApiController();
    if ($method === 'GET') $controller->workOrders();
} elseif (preg_match('#^/api/work-orders/(\d+)$#', $uri, $matches)) {
    $controller = new ApiController();
    if ($method === 'GET') $controller->workOrder($matches[1]);
} elseif (preg_match('#^/api/work-orders/(\d+)/status$#', $uri, $matches)) {
    $controller = new ApiController();
    if ($method === 'POST') $controller->updateStatus($matches[1]);
} elseif ($uri === '/api/clients') {
    $controller = new ApiController();
    if ($method === 'GET') $controller->clients();
} elseif ($uri === '/api/vehicles') {
    $controller = new ApiController();
    if ($method === 'GET') $controller->vehicles();
} elseif ($uri === '/api/parts') {
    $controller = new ApiController();
    if ($method === 'GET') $controller->parts();
} elseif ($uri === '/api/stats') {
    $controller = new ApiController();
    if ($method === 'GET') $controller->stats();
} elseif (preg_match('#^/api/mechanic/(\d+)/orders$#', $uri, $matches)) {
    $controller = new ApiController();
    if ($method === 'GET') $controller->mechanicOrders($matches[1]);
}

// Reminder Routes
elseif ($uri === '/reminders') {
    $controller = new ReminderController();
    if ($method === 'GET') $controller->index();
    elseif ($method === 'POST') $controller->store();
} elseif (preg_match('#^/reminders/complete/(\d+)$#', $uri, $matches)) {
    $controller = new ReminderController();
    if ($method === 'POST') $controller->complete($matches[1]);
} elseif (preg_match('#^/reminders/delete/(\d+)$#', $uri, $matches)) {
    $controller = new ReminderController();
    if ($method === 'POST') $controller->delete($matches[1]);
}

// Settings Routes
elseif ($uri === '/settings') {
    $controller = new SettingsController();
    if ($method === 'GET') $controller->index();
} elseif ($uri === '/settings/update') {
    $controller = new SettingsController();
    if ($method === 'POST') $controller->update();
} elseif ($uri === '/settings/create') {
    $controller = new SettingsController();
    if ($method === 'GET') $controller->create();
    elseif ($method === 'POST') $controller->create();
} elseif (preg_match('#^/settings/delete/([a-zA-Z0-9_-]+)$#', $uri, $matches)) {
    $controller = new SettingsController();
    if ($method === 'POST') $controller->delete($matches[1]);
}

// VIN Decoder Routes
elseif ($uri === '/vin-decoder') {
    $controller = new VinDecoderController();
    if ($method === 'GET') $controller->index();
    elseif ($method === 'POST') $controller->decode();
}

// DTC Routes
elseif ($uri === '/dtc') {
    $controller = new DtcController();
    if ($method === 'GET') $controller->index();
} elseif ($uri === '/dtc/search') {
    $controller = new DtcController();
    if ($method === 'POST') $controller->search();
}

// Manuals Routes
elseif ($uri === '/manuals') {
    $controller = new ManualController();
    if ($method === 'GET') $controller->index();
} elseif ($uri === '/manuals/create') {
    $controller = new ManualController();
    if ($method === 'GET') $controller->create();
    elseif ($method === 'POST') $controller->create();
} elseif (preg_match('#^/manuals/view/(\d+)$#', $uri, $matches)) {
    $controller = new ManualController();
    if ($method === 'GET') $controller->view($matches[1]);
} elseif (preg_match('#^/manuals/delete/(\d+)$#', $uri, $matches)) {
    $controller = new ManualController();
    if ($method === 'POST') $controller->delete($matches[1]);
}

// Tools Routes
elseif ($uri === '/tools') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->index();
} elseif ($uri === '/tools/mechanic') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->mechanicTools();
} elseif ($uri === '/tools/mechanic/create') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->createMechanicTool();
    elseif ($method === 'POST') $controller->createMechanicTool();
} elseif (preg_match('#^/tools/mechanic/update-status/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'POST') $controller->updateMechanicToolStatus($matches[1]);
} elseif ($uri === '/tools/warehouse') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->warehouseTools();
} elseif ($uri === '/tools/warehouse/create') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->createWarehouseTool();
    elseif ($method === 'POST') $controller->createWarehouseTool();
} elseif (preg_match('#^/tools/warehouse/checkout/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->checkoutTool($matches[1]);
    elseif ($method === 'POST') $controller->checkoutTool($matches[1]);
} elseif (preg_match('#^/tools/warehouse/return/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->returnWarehouseTool($matches[1]);
    elseif ($method === 'POST') $controller->returnWarehouseTool($matches[1]);
} elseif (preg_match('#^/tools/warehouse/repair/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'POST') $controller->sendToRepair($matches[1]);
} elseif (preg_match('#^/tools/warehouse/repaired/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'POST') $controller->markAsRepaired($matches[1]);
} elseif ($uri === '/tools/purchase-request' || $uri === '/tools/purchase-requests') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->purchaseRequest();
    elseif ($method === 'POST') $controller->purchaseRequest();
} elseif ($uri === '/tools/requests') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->requests();
} elseif ($uri === '/tools/requests/create') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->createRequest();
    elseif ($method === 'POST') $controller->createRequest();
} elseif (preg_match('#^/tools/requests/approve/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'POST') $controller->approveRequest($matches[1]);
} elseif (preg_match('#^/tools/requests/reject/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'POST') $controller->rejectRequest($matches[1]);
} elseif (preg_match('#^/tools/requests/deliver/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'POST') $controller->deliverTool($matches[1]);
} elseif (preg_match('#^/tools/requests/return/(\d+)$#', $uri, $matches)) {
    $controller = new ToolsController();
    if ($method === 'POST') $controller->returnTool($matches[1]);
} elseif ($uri === '/tools/my-tools') {
    $controller = new ToolsController();
    if ($method === 'GET') $controller->myTools();
}

// Workshop Intelligent Ops
elseif ($uri === '/workshop-ops') {
    $controller = new WorkshopOpsController();
    if ($method === 'GET') $controller->index();
} elseif (preg_match('#^/workshop-ops/(\d+)$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'GET') $controller->show($matches[1]);
} elseif (preg_match('#^/workshop-ops/(\d+)/start$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->start($matches[1]);
} elseif (preg_match('#^/workshop-ops/(\d+)/pause$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->pause($matches[1]);
} elseif (preg_match('#^/workshop-ops/(\d+)/resume$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->resume($matches[1]);
} elseif (preg_match('#^/workshop-ops/(\d+)/finish$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->finish($matches[1]);
} elseif (preg_match('#^/workshop-ops/(\d+)/quality$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->saveQuality($matches[1]);
} elseif (preg_match('#^/workshop-ops/(\d+)/billing$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->createBilling($matches[1]);
}

// Workshop Part Requests Routes (Gestión Inteligente de Repuestos)
elseif (preg_match('#^/workshop-ops/(\d+)/request-part$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->requestPart($matches[1]);
} elseif (preg_match('#^/workshop-ops/(\d+)/approve-part/(\d+)$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->approvePartRequest($matches[1], $matches[2]);
} elseif (preg_match('#^/workshop-ops/(\d+)/reject-part/(\d+)$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->rejectPartRequest($matches[1], $matches[2]);
} elseif (preg_match('#^/workshop-ops/(\d+)/despachar-part/(\d+)$#', $uri, $matches)) {
    $controller = new WorkshopOpsController();
    if ($method === 'POST') $controller->despacharPartRequest($matches[1], $matches[2]);
}

else {
    http_response_code(404);
    echo "404 Not Found";
}
