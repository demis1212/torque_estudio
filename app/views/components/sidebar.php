<?php
// Sidebar Component - Consistent across all pages
$userRole = $_SESSION['user_role'] ?? 0;
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPage = basename($currentPath);

function isActive($path) {
    global $currentPath, $currentPage;
    if ($path === '/dashboard' && $currentPage === 'dashboard') return true;
    if ($path === '/clients' && $currentPage === 'clients') return true;
    if ($path === '/vehicles' && $currentPage === 'vehicles') return true;
    if ($path === '/work-orders' && strpos($currentPath, 'work-orders') !== false) return true;
    if ($path === '/workshop-ops' && strpos($currentPath, 'workshop-ops') !== false) return true;
    if ($path === '/services' && $currentPage === 'services') return true;
    if ($path === '/parts' && $currentPage === 'parts') return true;
    if ($path === '/tools' && strpos($currentPath, 'tools') !== false) return true;
    if ($path === '/manuals' && $currentPage === 'manuals') return true;
    if ($path === '/vin-decoder' && $currentPage === 'vin-decoder') return true;
    if ($path === '/dtc' && $currentPage === 'dtc') return true;
    if ($path === '/reports' && $currentPage === 'reports') return true;
    if ($path === '/users' && $currentPage === 'users') return true;
    if ($path === '/settings' && $currentPage === 'settings') return true;
    return false;
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon"><i class="fas fa-wrench"></i></div>
        <h2>Torque Studio</h2>
    </div>
    
    <nav class="nav-section">
        <div class="nav-section-title">Principal</div>
        <a href="<?= $basePath ?>/dashboard" class="nav-item <?= isActive('/dashboard') ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="<?= $basePath ?>/clients" class="nav-item <?= isActive('/clients') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Clientes
        </a>
        <a href="<?= $basePath ?>/vehicles" class="nav-item <?= isActive('/vehicles') ? 'active' : '' ?>">
            <i class="fas fa-car"></i> Vehículos
        </a>
        <a href="<?= $basePath ?>/work-orders" class="nav-item <?= isActive('/work-orders') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> Órdenes
        </a>
    </nav>
    
    <nav class="nav-section">
        <div class="nav-section-title">Operaciones</div>
        <a href="<?= $basePath ?>/services" class="nav-item <?= isActive('/services') ? 'active' : '' ?>">
            <i class="fas fa-wrench"></i> Servicios
        </a>
        <a href="<?= $basePath ?>/workshop-ops" class="nav-item <?= isActive('/workshop-ops') ? 'active' : '' ?>">
            <i class="fas fa-cogs"></i> Operación Inteligente
        </a>
        <a href="<?= $basePath ?>/parts" class="nav-item <?= isActive('/parts') ? 'active' : '' ?>">
            <i class="fas fa-boxes"></i> Inventario
        </a>
    </nav>
    
    <nav class="nav-section">
        <div class="nav-section-title">Herramientas</div>
        <a href="<?= $basePath ?>/tools" class="nav-item <?= isActive('/tools') ? 'active' : '' ?>">
            <i class="fas fa-tools"></i> Herramientas
        </a>
        <a href="<?= $basePath ?>/manuals" class="nav-item <?= isActive('/manuals') ? 'active' : '' ?>">
            <i class="fas fa-book"></i> Manuales
        </a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item <?= isActive('/vin-decoder') ? 'active' : '' ?>">
            <i class="fas fa-search"></i> VIN Decoder
        </a>
        <a href="<?= $basePath ?>/dtc" class="nav-item <?= isActive('/dtc') ? 'active' : '' ?>">
            <i class="fas fa-exclamation-triangle"></i> DTC Codes
        </a>
    </nav>
    
    <nav class="nav-section">
        <div class="nav-section-title">Administración</div>
        <a href="<?= $basePath ?>/reports" class="nav-item <?= isActive('/reports') ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>
        <?php if($userRole == 1): ?>
        <a href="<?= $basePath ?>/users" class="nav-item <?= isActive('/users') ? 'active' : '' ?>">
            <i class="fas fa-user-cog"></i> Usuarios
        </a>
        <a href="<?= $basePath ?>/settings" class="nav-item <?= isActive('/settings') ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Configuración
        </a>
        <?php endif; ?>
    </nav>
</aside>

<style>
/* Sidebar Styles - Standardized */
.sidebar {
    width: 260px;
    background: linear-gradient(180deg, var(--surface-container, #11131a) 0%, var(--surface, #0a0c10) 100%);
    border-right: 1px solid var(--outline, rgba(255,255,255,0.08));
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    flex-shrink: 0;
}
.sidebar-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 8px 20px;
    margin-bottom: 8px;
    border-bottom: 1px solid var(--outline, rgba(255,255,255,0.08));
}
.logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #8ab4f8 0%, #4d8eff 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}
.sidebar-header h2 {
    font-size: 18px;
    margin: 0;
    color: #e8eaf2;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 600;
}
.nav-section {
    margin-bottom: 8px;
}
.nav-section-title {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: #9aa3b2;
    padding: 16px 8px 8px;
    font-weight: 600;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #9aa3b2;
    text-decoration: none;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 4px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s;
}
.nav-item:hover {
    background: rgba(255,255,255,0.05);
    color: #e8eaf2;
}
.nav-item.active {
    background: linear-gradient(135deg, #4d8eff 0%, rgba(77,142,255,0.8) 100%);
    color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
.nav-item i {
    width: 20px;
    text-align: center;
}
</style>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
