<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$actionLabels = [
    'create' => 'Creación',
    'update' => 'Actualización',
    'delete' => 'Eliminación',
    'assign' => 'Asignación',
    'status_change' => 'Cambio de Estado',
    'add_part' => 'Repuesto Agregado',
    'adjust_stock' => 'Ajuste de Stock',
    'login' => 'Inicio de Sesión',
    'logout' => 'Cierre de Sesión'
];

$actionColors = [
    'create' => '#28a745',
    'update' => '#17a2b8',
    'delete' => '#dc3545',
    'assign' => '#ffc107',
    'status_change' => '#fd7e14',
    'add_part' => '#6f42c1',
    'adjust_stock' => '#20c997',
    'login' => '#6c757d',
    'logout' => '#6c757d'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Actividad - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --surface: #10131a;
            --on-surface: #e1e2ec;
            --primary: #adc6ff;
            --primary-container: #4d8eff;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: var(--surface);
            color: var(--on-surface);
            font-family: 'Inter', sans-serif;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #0F1115;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 24px;
        }
        .sidebar h2 { font-size: 20px; margin-top: 0; margin-bottom: 32px; color: var(--primary); }
        .nav-item {
            display: block;
            color: #c2c6d6;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .nav-item:hover { background-color: rgba(255, 255, 255, 0.05); }
        .nav-item.active { background-color: var(--primary-container); color: #fff; }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .header h1 { margin: 0; font-size: 32px; }
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        .filters select, .filters input {
            padding: 10px 14px;
            background-color: #1F2430;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
        }
        .logs-container {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .log-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .log-item:last-child { border-bottom: none; }
        .log-timeline {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 40px;
        }
        .log-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-top: 4px;
        }
        .log-line {
            width: 2px;
            flex: 1;
            background-color: rgba(255, 255, 255, 0.1);
            margin-top: 8px;
        }
        .log-content {
            flex: 1;
        }
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .log-action {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .log-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        .log-description {
            color: #888;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .log-meta {
            font-size: 12px;
            color: #666;
        }
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #666;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }
        .page-btn {
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: #c2c6d6;
            text-decoration: none;
        }
        .page-btn.active {
            background-color: var(--primary-container);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Torque Studio</h2>
        <a href="<?= $basePath ?>/dashboard" class="nav-item">Dashboard</a>
        <a href="<?= $basePath ?>/clients" class="nav-item">Clientes</a>
        <a href="<?= $basePath ?>/vehicles" class="nav-item">Vehículos</a>
        <a href="<?= $basePath ?>/work-orders" class="nav-item">Órdenes</a>
        <a href="<?= $basePath ?>/services" class="nav-item">Servicios</a>
        <a href="<?= $basePath ?>/parts" class="nav-item">Inventario</a>
        <a href="<?= $basePath ?>/reports" class="nav-item">📊 Reportes</a>
        <a href="<?= $basePath ?>/tools" class="nav-item">🔧 Herramientas</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item">📚 Manuales</a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item">🔍 Decodificador VIN</a>
        <a href="<?= $basePath ?>/dtc" class="nav-item">🔧 DTC Codes</a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>📋 Logs de Actividad</h1>
        </div>

        <div class="filters">
            <select name="action">
                <option value="">Todas las acciones</option>
                <option value="create">Creaciones</option>
                <option value="update">Actualizaciones</option>
                <option value="delete">Eliminaciones</option>
            </select>
            <select name="entity">
                <option value="">Todas las entidades</option>
                <option value="work_order">Órdenes</option>
                <option value="part">Repuestos</option>
                <option value="client">Clientes</option>
            </select>
            <input type="date" name="date" placeholder="Fecha">
            <button style="padding: 10px 20px; background-color: var(--primary-container); color: #fff; border: none; border-radius: 8px; cursor: pointer;">Filtrar</button>
        </div>

        <div class="logs-container">
            <?php if (empty($logs)): ?>
                <div class="empty-state">
                    <h2>No hay registros de actividad</h2>
                    <p>Los logs aparecerán aquí cuando haya actividad en el sistema.</p>
                </div>
            <?php else: ?>
                <?php foreach ($logs as $index => $log): ?>
                    <div class="log-item">
                        <div class="log-timeline">
                            <div class="log-dot" style="background-color: <?= $actionColors[$log['action']] ?? '#666' ?>"></div>
                            <?php if ($index < count($logs) - 1): ?>
                                <div class="log-line"></div>
                            <?php endif; ?>
                        </div>
                        <div class="log-content">
                            <div class="log-header">
                                <span class="log-action" style="background-color: <?= ($actionColors[$log['action']] ?? '#666') ?>20; color: <?= $actionColors[$log['action']] ?? '#666' ?>">
                                    <?= $actionLabels[$log['action']] ?? $log['action'] ?>
                                </span>
                                <span class="log-meta"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></span>
                            </div>
                            <div class="log-title">
                                <?= esc($log['user_name'] ?? 'Sistema') ?> 
                                <?= $actionLabels[$log['action']] ?? $log['action'] ?> 
                                <?= $log['entity_type'] ?> #<?= $log['entity_id'] ?>
                            </div>
                            <div class="log-description"><?= esc($log['description']) ?></div>
                            <div class="log-meta">IP: <?= $log['ip_address'] ?? 'N/A' ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <a href="#" class="page-btn">← Anterior</a>
            <a href="#" class="page-btn active">1</a>
            <a href="#" class="page-btn">2</a>
            <a href="#" class="page-btn">3</a>
            <a href="#" class="page-btn">Siguiente →</a>
        </div>
    </div>
</body>
</html>
