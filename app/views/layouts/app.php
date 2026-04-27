<?php
/**
 * Main Application Layout
 * Include at the start of all views: require_once __DIR__ . '/../layouts/app.php';
 * Then set $pageTitle, $pageContent variables
 */

// Prevent direct access
if (!isset($pageTitle)) {
    $pageTitle = 'Torque Studio ERP';
}

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

// Role labels
$roleLabels = [
    1 => 'Administrador',
    2 => 'Mecánico', 
    3 => 'Recepcionista'
];

$userName = $_SESSION['user_name'] ?? 'Usuario';
$userRoleId = getUserRole();
$userRole = $roleLabels[$userRoleId] ?? 'Usuario';

// Active page detection
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPage = basename($currentPath);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Torque Studio ERP</title>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= $basePath ?>/css/erp-theme.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
        }
        
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
            background: var(--surface);
        }
        
        /* Sidebar */
        .app-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--surface-container) 0%, var(--surface) 100%);
            border-right: 1px solid var(--outline);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            flex-shrink: 0;
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 16px;
            border-bottom: 1px solid var(--outline);
        }
        
        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .sidebar-title {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 600;
            color: var(--on-surface);
        }
        
        .nav-section {
            padding: 8px 0;
        }
        
        .nav-section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--on-surface-variant);
            padding: 16px 20px 8px;
            font-weight: 600;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--on-surface-variant);
            text-decoration: none;
            padding: 10px 16px;
            margin: 0 8px;
            border-radius: var(--radius-sm);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: var(--on-surface);
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, var(--primary-container) 0%, rgba(77,142,255,0.8) 100%);
            color: #fff;
            box-shadow: var(--shadow-sm);
        }
        
        .nav-item i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid var(--outline);
        }
        
        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--surface-container-high);
            border-radius: var(--radius-md);
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--surface);
            font-size: 14px;
        }
        
        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--on-surface);
        }
        
        .user-role {
            font-size: 11px;
            color: var(--on-surface-variant);
        }
        
        /* Main Content */
        .app-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .app-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: var(--surface-container);
            border-bottom: 1px solid var(--outline);
        }
        
        .topbar-search {
            position: relative;
            width: 400px;
        }
        
        .topbar-search input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            background: var(--surface);
            border: 1px solid var(--outline);
            border-radius: var(--radius-md);
            color: var(--on-surface);
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .topbar-search input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(138,180,248,0.1);
        }
        
        .topbar-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--on-surface-variant);
        }
        
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .topbar-btn {
            position: relative;
            width: 40px;
            height: 40px;
            background: none;
            border: 1px solid var(--outline);
            border-radius: var(--radius-sm);
            color: var(--on-surface-variant);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .topbar-btn:hover {
            background: rgba(255,255,255,0.05);
            color: var(--on-surface);
        }
        
        .topbar-btn .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--error);
            color: #fff;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .btn-logout {
            padding: 10px 18px;
            background: linear-gradient(135deg, rgba(248,113,113,0.2) 0%, rgba(248,113,113,0.1) 100%);
            color: var(--error);
            border: 1px solid rgba(248,113,113,0.3);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-logout:hover {
            background: linear-gradient(135deg, rgba(248,113,113,0.3) 0%, rgba(248,113,113,0.2) 100%);
        }
        
        .app-content {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 24px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 28px;
            font-family: var(--font-display);
            font-weight: 600;
        }
        
        .page-header p {
            margin: 8px 0 0;
            color: var(--on-surface-variant);
            font-size: 14px;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .app-sidebar {
                position: fixed;
                left: -100%;
                z-index: 1000;
                transition: left 0.3s;
            }
            
            .app-sidebar.open {
                left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s;
            }
            
            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🔧</div>
                <span class="sidebar-title">Torque Studio</span>
            </div>
            
            <nav class="nav-section">
                <div class="nav-section-title">Principal</div>
                <a href="<?= $basePath ?>/dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <?php if($userRoleId == 1 || $userRoleId == 3): ?>
                    <a href="<?= $basePath ?>/clients" class="nav-item <?= $currentPage === 'clients' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                    <a href="<?= $basePath ?>/vehicles" class="nav-item <?= $currentPage === 'vehicles' ? 'active' : '' ?>">
                        <i class="fas fa-car"></i> Vehículos
                    </a>
                <?php endif; ?>
                <a href="<?= $basePath ?>/work-orders" class="nav-item <?= $currentPage === 'work-orders' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i> Órdenes
                </a>
            </nav>
            
            <nav class="nav-section">
                <div class="nav-section-title">Operaciones</div>
                <a href="<?= $basePath ?>/services" class="nav-item <?= $currentPage === 'services' ? 'active' : '' ?>">
                    <i class="fas fa-wrench"></i> Servicios
                </a>
                <a href="<?= $basePath ?>/parts" class="nav-item <?= $currentPage === 'parts' ? 'active' : '' ?>">
                    <i class="fas fa-boxes"></i> Inventario
                </a>
                <a href="<?= $basePath ?>/tools" class="nav-item <?= strpos($currentPath, '/tools') !== false ? 'active' : '' ?>">
                    <i class="fas fa-tools"></i> Herramientas
                </a>
            </nav>
            
            <nav class="nav-section">
                <div class="nav-section-title">Herramientas</div>
                <a href="<?= $basePath ?>/manuals" class="nav-item <?= $currentPage === 'manuals' ? 'active' : '' ?>">
                    <i class="fas fa-book"></i> Manuales
                </a>
                <a href="<?= $basePath ?>/vin-decoder" class="nav-item <?= $currentPage === 'vin-decoder' ? 'active' : '' ?>">
                    <i class="fas fa-search"></i> VIN Decoder
                </a>
                <a href="<?= $basePath ?>/dtc" class="nav-item <?= $currentPage === 'dtc' ? 'active' : '' ?>">
                    <i class="fas fa-exclamation-triangle"></i> DTC Codes
                </a>
            </nav>
            
            <nav class="nav-section">
                <div class="nav-section-title">Administración</div>
                <a href="<?= $basePath ?>/reports" class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> Reportes
                </a>
                <?php if($userRoleId == 1): ?>
                    <a href="<?= $basePath ?>/users" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
                        <i class="fas fa-user-cog"></i> Usuarios
                    </a>
                    <a href="<?= $basePath ?>/settings" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i> Configuración
                    </a>
                <?php endif; ?>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-card">
                    <div class="user-avatar"><?= substr($userName, 0, 1) ?></div>
                    <div>
                        <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                        <div class="user-role"><?= $userRole ?></div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        
        <!-- Main Content -->
        <main class="app-main">
            <!-- Top Bar -->
            <header class="app-topbar">
                <div class="topbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar órdenes, clientes, vehículos..." id="globalSearch">
                </div>
                <div class="topbar-actions">
                    <button class="topbar-btn show-mobile" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a href="<?= $basePath ?>/notifications" class="topbar-btn">
                        <i class="fas fa-bell"></i>
                        <?php if (($notification_count ?? 0) > 0): ?>
                            <span class="badge"><?= $notification_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= $basePath ?>/logout" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="app-content">
                <?php if (isset($pageContent)): ?>
                    <?= $pageContent ?>
                <?php else: ?>
                    <!-- Content will be injected here -->
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Include Components -->
    <?php include __DIR__ . '/../components/toast.php'; ?>
    <?php include __DIR__ . '/../components/modal.php'; ?>
    <?php include __DIR__ . '/../components/confirm-delete.php'; ?>
    
    <script>
        // Global Search
        document.getElementById('globalSearch')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = '<?= $basePath ?>/search?q=' + encodeURIComponent(query);
                }
            }
        });
        
        // Mobile Sidebar Toggle
        function toggleSidebar() {
            document.getElementById('appSidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }
    </script>
</body>
</html>
