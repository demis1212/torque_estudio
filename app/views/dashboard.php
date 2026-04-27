<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --surface: #0a0c10;
            --surface-container: #14171f;
            --surface-container-high: #1a1f2a;
            --surface-container-highest: #232838;
            --on-surface: #e8eaf2;
            --on-surface-variant: #9aa3b2;
            --primary: #8ab4f8;
            --primary-container: #4d8eff;
            --on-primary-container: #fff;
            --secondary: #c2e9fb;
            --success: #4ade80;
            --warning: #fbbf24;
            --error: #f87171;
            --info: #60a5fa;
            --outline: rgba(255,255,255,0.08);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.3);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.4);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.5);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
        }
        
        * { box-sizing: border-box; }
        
        body {
            margin: 0;
            padding: 0;
            background-color: var(--surface);
            color: var(--on-surface);
            font-family: 'Inter', sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--surface-container) 0%, var(--surface) 100%);
            border-right: 1px solid var(--outline);
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 8px 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid var(--outline);
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .sidebar-header h2 {
            font-size: 18px;
            margin: 0;
            color: var(--on-surface);
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
            color: var(--on-surface-variant);
            padding: 16px 8px 8px;
            font-weight: 600;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--on-surface-variant);
            text-decoration: none;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 4px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .nav-item:hover {
            background-color: rgba(255,255,255,0.05);
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
        
        .nav-badge {
            margin-left: auto;
            background-color: var(--error);
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .sidebar-footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid var(--outline);
        }
        
        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background-color: var(--surface-container-high);
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
        
        .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--on-surface);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-role {
            font-size: 11px;
            color: var(--on-surface-variant);
        }
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* ===== TOP BAR ===== */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background-color: var(--surface-container);
            border-bottom: 1px solid var(--outline);
        }
        
        .search-box {
            position: relative;
            width: 400px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            background-color: var(--surface);
            border: 1px solid var(--outline);
            border-radius: var(--radius-md);
            color: var(--on-surface);
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(138,180,248,0.1);
        }
        
        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--on-surface-variant);
        }
        
        .top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .top-btn {
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
        
        .top-btn:hover {
            background-color: rgba(255,255,255,0.05);
            color: var(--on-surface);
        }
        
        .top-btn .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background-color: var(--error);
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
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-logout:hover {
            background: linear-gradient(135deg, rgba(248,113,113,0.3) 0%, rgba(248,113,113,0.2) 100%);
        }
        
        /* ===== CONTENT AREA ===== */
        .content-area {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
        }
        
        .page-header {
            margin-bottom: 24px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .page-header p {
            margin: 8px 0 0;
            color: var(--on-surface-variant);
            font-size: 14px;
        }
        
        /* ===== QUICK ACTIONS ===== */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 20px 16px;
            background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%);
            border: 1px solid var(--outline);
            border-radius: var(--radius-lg);
            color: var(--on-surface);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .quick-action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: rgba(138,180,248,0.3);
        }
        
        .quick-action-btn:hover::before {
            opacity: 1;
        }
        
        .quick-action-btn i {
            font-size: 24px;
            color: var(--primary);
            transition: transform 0.3s;
        }
        
        .quick-action-btn:hover i {
            transform: scale(1.1);
        }
        
        .quick-action-btn span {
            font-size: 12px;
            font-weight: 500;
        }
        
        /* ===== STATS GRID ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
        
        .stat-card {
            background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%);
            border: 1px solid var(--outline);
            border-radius: var(--radius-lg);
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-color, var(--primary));
        }
        
        .stat-card.clients { --accent-color: var(--primary); }
        .stat-card.vehicles { --accent-color: var(--secondary); }
        .stat-card.pending { --accent-color: var(--warning); }
        .stat-card.completed { --accent-color: var(--success); }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(138,180,248,0.1);
            color: var(--primary);
        }
        
        .stat-card.vehicles .stat-icon {
            background: rgba(194,233,251,0.1);
            color: var(--secondary);
        }
        
        .stat-card.pending .stat-icon {
            background: rgba(251,191,36,0.1);
            color: var(--warning);
        }
        
        .stat-card.completed .stat-icon {
            background: rgba(74,222,128,0.1);
            color: var(--success);
        }
        
        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: var(--success);
        }
        
        .stat-card h4 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin: 0;
            color: var(--on-surface-variant);
            font-weight: 500;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: var(--on-surface);
            margin: 8px 0 0;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        /* ===== DASHBOARD GRID ===== */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        
        @media (max-width: 1024px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
        
        .card {
            background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%);
            border: 1px solid var(--outline);
            border-radius: var(--radius-lg);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: var(--shadow-sm);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--on-surface);
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .card-title i {
            color: var(--primary);
        }
        
        .card-action {
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: gap 0.2s;
        }
        
        .card-action:hover {
            gap: 8px;
        }
        
        /* ===== ORDER ITEMS ===== */
        .order-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            background-color: var(--surface);
            border-radius: var(--radius-md);
            border: 1px solid var(--outline);
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        
        .order-item:hover {
            background-color: var(--surface-container-highest);
            border-color: rgba(138,180,248,0.2);
            transform: translateX(4px);
        }
        
        .order-status-indicator {
            width: 4px;
            height: 40px;
            border-radius: 2px;
            flex-shrink: 0;
        }
        
        .order-info {
            flex: 1;
            min-width: 0;
        }
        
        .order-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .order-meta {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: var(--on-surface-variant);
        }
        
        .order-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .order-amount {
            font-weight: 700;
            font-size: 15px;
            color: var(--primary);
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .status-pill {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-recepcion { background: rgba(251,191,36,0.15); color: var(--warning); }
        .status-diagnostico { background: rgba(96,165,250,0.15); color: var(--info); }
        .status-reparacion { background: rgba(138,180,248,0.15); color: var(--primary); }
        .status-terminado { background: rgba(74,222,128,0.15); color: var(--success); }
        
        /* ===== ACTIVITY TIMELINE ===== */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .activity-item {
            display: flex;
            gap: 12px;
            padding: 12px;
            background-color: var(--surface);
            border-radius: var(--radius-md);
            border: 1px solid var(--outline);
        }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .activity-icon.create { background: rgba(74,222,128,0.15); color: var(--success); }
        .activity-icon.update { background: rgba(138,180,248,0.15); color: var(--primary); }
        .activity-icon.delete { background: rgba(248,113,113,0.15); color: var(--error); }
        .activity-icon.tool { background: rgba(251,191,36,0.15); color: var(--warning); }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-text {
            font-size: 13px;
            line-height: 1.5;
        }
        
        .activity-text strong {
            color: var(--on-surface);
        }
        
        .activity-time {
            font-size: 11px;
            color: var(--on-surface-variant);
            margin-top: 4px;
        }
        
        /* ===== ALERTS ===== */
        .alert-card {
            background: linear-gradient(145deg, rgba(251,191,36,0.1) 0%, rgba(251,191,36,0.05) 100%);
            border: 1px solid rgba(251,191,36,0.3);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .alert-card.error {
            background: linear-gradient(145deg, rgba(248,113,113,0.1) 0%, rgba(248,113,113,0.05) 100%);
            border-color: rgba(248,113,113,0.3);
        }
        
        .alert-icon {
            width: 44px;
            height: 44px;
            background: rgba(251,191,36,0.2);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--warning);
        }
        
        .alert-card.error .alert-icon {
            background: rgba(248,113,113,0.2);
            color: var(--error);
        }
        
        .alert-content h4 {
            margin: 0 0 4px;
            font-size: 14px;
            color: var(--on-surface);
        }
        
        .alert-content p {
            margin: 0;
            font-size: 13px;
            color: var(--on-surface-variant);
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--on-surface-variant);
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h4 {
            margin: 0 0 8px;
            font-size: 16px;
            color: var(--on-surface);
        }
        
        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--surface);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--surface-container-highest);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--on-surface-variant);
        }
    </style>
</head>
<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

// Definir colores de estado
$statusColors = [
    'recepcion' => 'var(--warning)',
    'diagnostico' => 'var(--info)',
    'reparacion' => 'var(--primary)',
    'terminado' => 'var(--success)'
];

$statusClasses = [
    'recepcion' => 'status-recepcion',
    'diagnostico' => 'status-diagnostico',
    'reparacion' => 'status-reparacion',
    'terminado' => 'status-terminado'
];

$statusLabels = [
    'recepcion' => 'Recepción',
    'diagnostico' => 'Diagnóstico',
    'reparacion' => 'Reparación',
    'terminado' => 'Terminado'
];

// Roles
$roleLabels = [
    1 => 'Administrador',
    2 => 'Mecánico',
    3 => 'Recepcionista'
];
?>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">🔧</div>
            <h2>Torque Studio</h2>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Principal</div>
            <a href="<?= $basePath ?>/dashboard" class="nav-item active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <?php if($user_role == 1 || $user_role == 3): ?>
                <a href="<?= $basePath ?>/clients" class="nav-item">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="<?= $basePath ?>/vehicles" class="nav-item">
                    <i class="fas fa-car"></i> Vehículos
                </a>
            <?php endif; ?>
            <a href="<?= $basePath ?>/work-orders" class="nav-item">
                <i class="fas fa-clipboard-list"></i> Órdenes
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Operaciones</div>
            <a href="<?= $basePath ?>/services" class="nav-item">
                <i class="fas fa-wrench"></i> Servicios
            </a>
            <a href="<?= $basePath ?>/workshop-ops" class="nav-item">
                <i class="fas fa-stopwatch"></i> Operación Inteligente
            </a>
            <a href="<?= $basePath ?>/parts" class="nav-item">
                <i class="fas fa-boxes"></i> Inventario
            </a>
            <a href="<?= $basePath ?>/tools" class="nav-item">
                <i class="fas fa-tools"></i> Herramientas
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Herramientas</div>
            <a href="<?= $basePath ?>/manuals" class="nav-item">
                <i class="fas fa-book"></i> Manuales
            </a>
            <a href="<?= $basePath ?>/vin-decoder" class="nav-item">
                <i class="fas fa-search"></i> VIN Decoder
            </a>
            <a href="<?= $basePath ?>/dtc" class="nav-item">
                <i class="fas fa-exclamation-triangle"></i> DTC Codes
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Administración</div>
            <a href="<?= $basePath ?>/reports" class="nav-item">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
            <a href="<?= $basePath ?>/reports/mechanic-productivity" class="nav-item">
                <i class="fas fa-users-cog"></i> Productividad
            </a>
            <a href="<?= $basePath ?>/whatsapp-reminders" class="nav-item">
                <i class="fab fa-whatsapp" style="color: #25D366;"></i> WhatsApp
            </a>
            <?php if($user_role == 1): ?>
                <a href="<?= $basePath ?>/users" class="nav-item">
                    <i class="fas fa-user-cog"></i> Usuarios
                </a>
                <a href="<?= $basePath ?>/settings" class="nav-item">
                    <i class="fas fa-cog"></i> Configuración
                </a>
            <?php endif; ?>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar"><?= substr($user_name, 0, 1) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= esc($user_name) ?></div>
                    <div class="user-role"><?= $roleLabels[$user_role] ?? 'Usuario' ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOP BAR -->
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar órdenes, clientes, vehículos..." id="globalSearch">
            </div>
            <div class="top-actions">
                <a href="<?= $basePath ?>/notifications" class="top-btn">
                    <i class="fas fa-bell"></i>
                    <?php if ($notification_count > 0): ?>
                        <span class="badge"><?= $notification_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= $basePath ?>/logout" class="btn-logout">
                    <i class="fas fa-sign-out-alt" style="margin-right: 6px;"></i>Salir
                </a>
            </div>
        </div>
        
        <!-- CONTENT AREA -->
        <div class="content-area">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Bienvenido de vuelta, <?= esc($user_name) ?>. Aquí está el resumen de tu taller hoy.</p>
            </div>
            
            <!-- ALERTS -->
            <?php if (!empty($low_stock_alerts)): ?>
            <div class="alert-card error">
                <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="alert-content">
                    <h4>⚠️ Alerta de Inventario Bajo</h4>
                    <p><?= count($low_stock_alerts) ?> productos están por debajo del stock mínimo. <a href="<?= $basePath ?>/parts" style="color: var(--primary);">Ver inventario →</a></p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- QUICK ACTIONS -->
            <div class="quick-actions">
                <a href="<?= $basePath ?>/work-orders/create" class="quick-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nueva Orden</span>
                </a>
                <a href="<?= $basePath ?>/clients/create" class="quick-action-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>Nuevo Cliente</span>
                </a>
                <a href="<?= $basePath ?>/vehicles/create" class="quick-action-btn">
                    <i class="fas fa-car-side"></i>
                    <span>Nuevo Vehículo</span>
                </a>
                <a href="<?= $basePath ?>/services/create" class="quick-action-btn">
                    <i class="fas fa-cog"></i>
                    <span>Nuevo Servicio</span>
                </a>
                <a href="<?= $basePath ?>/tools/requests/create" class="quick-action-btn">
                    <i class="fas fa-hand-holding"></i>
                    <span>Solicitar Tool</span>
                </a>
                <a href="<?= $basePath ?>/reports" class="quick-action-btn">
                    <i class="fas fa-chart-pie"></i>
                    <span>Ver Reportes</span>
                </a>
            </div>
            
            <!-- STATS GRID -->
            <div class="stats-grid">
                <div class="stat-card clients">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-trend"><i class="fas fa-arrow-up"></i> +<?= rand(2,8) ?>%</div>
                    </div>
                    <h4>Total Clientes</h4>
                    <p class="number"><?= $stats['total_clients'] ?></p>
                </div>
                <div class="stat-card vehicles">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-car"></i></div>
                        <div class="stat-trend"><i class="fas fa-arrow-up"></i> +<?= rand(1,5) ?>%</div>
                    </div>
                    <h4>Total Vehículos</h4>
                    <p class="number"><?= $stats['total_vehicles'] ?></p>
                </div>
                <div class="stat-card pending">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-trend" style="color: var(--warning);"><i class="fas fa-exclamation"></i> Activas</div>
                    </div>
                    <h4>Órdenes Activas</h4>
                    <p class="number"><?= $stats['pending_orders'] ?></p>
                </div>
                <div class="stat-card completed">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-trend"><i class="fas fa-arrow-up"></i> +<?= rand(5,15) ?>%</div>
                    </div>
                    <h4>Completadas</h4>
                    <p class="number"><?= $stats['completed_orders'] ?></p>
                </div>
            </div>
            
            <!-- DASHBOARD GRID -->
            <div class="dashboard-grid">
                <!-- RECENT ORDERS -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Órdenes Recientes</h3>
                        <a href="<?= $basePath ?>/work-orders" class="card-action">Ver todas <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="order-list">
                        <?php if (empty($recent_orders)): ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard"></i>
                                <h4>No hay órdenes recientes</h4>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($recent_orders, 0, 6) as $order): ?>
                                <a href="<?= $basePath ?>/work-orders/show/<?= $order['id'] ?>" class="order-item">
                                    <div class="order-status-indicator" style="background-color: <?= $statusColors[$order['status']] ?>"></div>
                                    <div class="order-info">
                                        <div class="order-title">#<?= $order['id'] ?> - <?= esc($order['client_name']) ?></div>
                                        <div class="order-meta">
                                            <span><i class="fas fa-car"></i> <?= esc($order['brand']) ?> <?= esc($order['model']) ?></span>
                                            <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="order-amount">$<?= number_format($order['total_cost'], 0) ?></div>
                                    <span class="status-pill <?= $statusClasses[$order['status']] ?>"><?= $statusLabels[$order['status']] ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- RIGHT COLUMN -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- ACTIVITY -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Actividad Reciente</h3>
                        </div>
                        <div class="activity-list">
                            <?php if (empty($recent_activity ?? [])): ?>
                                <div class="empty-state" style="padding: 24px;">
                                    <i class="fas fa-clock"></i>
                                    <h4>Sin actividad reciente</h4>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?= $activity['type'] ?? 'update' ?>">
                                            <i class="fas fa-<?= $activity['icon'] ?? 'edit' ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-text"><strong><?= esc($activity['user'] ?? 'Sistema') ?></strong> <?= $activity['action'] ?></div>
                                            <div class="activity-time"><i class="fas fa-clock" style="margin-right: 4px;"></i><?= timeAgo($activity['created_at']) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Activity placeholder -->
                            <div class="activity-item">
                                <div class="activity-icon create"><i class="fas fa-plus"></i></div>
                                <div class="activity-content">
                                    <div class="activity-text"><strong>Sistema</strong> registró una nueva orden de trabajo</div>
                                    <div class="activity-time"><i class="fas fa-clock" style="margin-right: 4px;"></i>hace 2 horas</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon update"><i class="fas fa-edit"></i></div>
                                <div class="activity-content">
                                    <div class="activity-text"><strong>Admin</strong> actualizó el estado de una orden</div>
                                    <div class="activity-time"><i class="fas fa-clock" style="margin-right: 4px;"></i>hace 4 horas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TOOLS STATUS -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tools"></i> Estado de Herramientas</h3>
                            <a href="<?= $basePath ?>/tools" class="card-action">Ver <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon tool"><i class="fas fa-warehouse"></i></div>
                                <div class="activity-content">
                                    <div class="activity-text"><strong>Bodega</strong> <?= $warehouse_tools_count ?? 0 ?> herramientas registradas</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon tool" style="background: rgba(74,222,128,0.15); color: var(--success);"><i class="fas fa-hand-holding"></i></div>
                                <div class="activity-content">
                                    <div class="activity-text"><strong>Préstamos</strong> <?= $active_loans_count ?? 0 ?> herramientas prestadas</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon tool" style="background: rgba(248,113,113,0.15); color: var(--error);"><i class="fas fa-exclamation"></i></div>
                                <div class="activity-content">
                                    <div class="activity-text"><strong>Pendientes</strong> <?= $pending_requests_count ?? 0 ?> solicitudes por aprobar</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notifications -->
    <?php include __DIR__ . '/components/toast.php'; ?>
    
    <script>
        // Simple search functionality
        document.getElementById('globalSearch')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = '<?= $basePath ?>/search?q=' + encodeURIComponent(query);
                }
            }
        });
    </script>
</body>
</html>
