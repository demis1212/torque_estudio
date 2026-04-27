<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$statusLabels = [
    'recepcion' => 'Recepción',
    'diagnostico' => 'Diagnóstico',
    'reparacion' => 'Reparación',
    'terminado' => 'Terminado'
];

$statusColors = [
    'recepcion' => '#ffc107',
    'diagnostico' => '#17a2b8',
    'reparacion' => '#fd7e14',
    'terminado' => '#28a745'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Órdenes - Torque Studio ERP</title>
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
        .filter-btn {
            padding: 8px 16px;
            background-color: #1F2430;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #c2c6d6;
            text-decoration: none;
            font-size: 14px;
        }
        .filter-btn.active {
            background-color: var(--primary-container);
            color: #fff;
            border-color: var(--primary-container);
        }
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .order-card {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-left: 4px solid var(--primary-container);
        }
        .order-card:hover { background-color: rgba(255, 255, 255, 0.03); }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .order-number { font-size: 20px; font-weight: 700; }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .client-info { margin-bottom: 12px; }
        .client-info strong { display: block; margin-bottom: 4px; }
        .client-info span { color: #888; font-size: 14px; }
        .vehicle-info {
            background-color: rgba(255, 255, 255, 0.03);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .order-actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary { background-color: var(--primary-container); color: #fff; }
        .btn-secondary { background-color: rgba(255, 255, 255, 0.1); color: #c2c6d6; }
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🔧 Panel Mecánico</h2>
        <a href="<?= $basePath ?>/mechanic/dashboard" class="nav-item">Dashboard</a>
        <a href="<?= $basePath ?>/mechanic/orders" class="nav-item active">Mis Órdenes</a>
        <a href="<?= $basePath ?>/work-orders" class="nav-item">Todas las Órdenes</a>
        <a href="<?= $basePath ?>/parts" class="nav-item">Inventario</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
        <a href="<?= $basePath ?>/dashboard" class="nav-item">← Dashboard General</a>
        <a href="<?= $basePath ?>/logout" class="nav-item">Cerrar Sesión</a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Mis Órdenes Asignadas</h1>
        </div>

        <div class="filters">
            <a href="<?= $basePath ?>/mechanic/orders" class="filter-btn <?= !$filter_status ? 'active' : '' ?>">Todas</a>
            <a href="<?= $basePath ?>/mechanic/orders?status=recepcion" class="filter-btn <?= $filter_status === 'recepcion' ? 'active' : '' ?>">Recepción</a>
            <a href="<?= $basePath ?>/mechanic/orders?status=diagnostico" class="filter-btn <?= $filter_status === 'diagnostico' ? 'active' : '' ?>">Diagnóstico</a>
            <a href="<?= $basePath ?>/mechanic/orders?status=reparacion" class="filter-btn <?= $filter_status === 'reparacion' ? 'active' : '' ?>">Reparación</a>
            <a href="<?= $basePath ?>/mechanic/orders?status=terminado" class="filter-btn <?= $filter_status === 'terminado' ? 'active' : '' ?>">Terminado</a>
        </div>

        <div class="orders-grid">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <h2>No tienes órdenes asignadas</h2>
                    <p>Las órdenes que te sean asignadas aparecerán aquí.</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card" style="border-left-color: <?= $statusColors[$order['status']] ?>">
                        <div class="order-header">
                            <span class="order-number">Orden #<?= $order['id'] ?></span>
                            <span class="status-badge" style="background-color: <?= $statusColors[$order['status']] ?>20; color: <?= $statusColors[$order['status']] ?>">
                                <?= $statusLabels[$order['status']] ?>
                            </span>
                        </div>
                        
                        <div class="client-info">
                            <strong><?= esc($order['client_name']) ?></strong>
                            <span>Cliente</span>
                        </div>
                        
                        <div class="vehicle-info">
                            <?= esc($order['brand']) ?> <?= esc($order['model']) ?> | Placa: <?= esc($order['plate']) ?>
                        </div>
                        
                        <div class="order-actions">
                            <a href="<?= $basePath ?>/work-orders/edit/<?= $order['id'] ?>" class="btn btn-primary">Ver Detalle</a>
                            
                            <?php if ($order['status'] === 'recepcion'): ?>
                                <form method="POST" action="<?= $basePath ?>/mechanic/order/<?= $order['id'] ?>/status" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="status" value="diagnostico">
                                    <button type="submit" class="btn btn-secondary">Iniciar Diagnóstico</button>
                                </form>
                            <?php elseif ($order['status'] === 'diagnostico'): ?>
                                <form method="POST" action="<?= $basePath ?>/mechanic/order/<?= $order['id'] ?>/status" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="status" value="reparacion">
                                    <button type="submit" class="btn btn-secondary">Iniciar Reparación</button>
                                </form>
                            <?php elseif ($order['status'] === 'reparacion'): ?>
                                <form method="POST" action="<?= $basePath ?>/mechanic/order/<?= $order['id'] ?>/status" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="status" value="terminado">
                                    <button type="submit" class="btn" style="background-color: #28a745; color: #fff;">Marcar Terminado</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
