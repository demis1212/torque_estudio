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

// Group orders by status
$ordersByStatus = [
    'recepcion' => [],
    'diagnostico' => [],
    'reparacion' => [],
    'terminado' => []
];
foreach ($orders as $order) {
    $ordersByStatus[$order['status']][] = $order;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban - Órdenes de Trabajo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --surface: #10131a;
            --surface-container-high: #272a31;
            --on-surface: #e1e2ec;
            --primary: #adc6ff;
            --primary-container: #4d8eff;
            --background: #10131a;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: var(--background);
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
            display: flex;
            flex-direction: column;
        }
        .sidebar h2 {
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 32px;
            color: var(--primary);
        }
        .nav-item {
            display: block;
            color: #c2c6d6;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .nav-item.active {
            background-color: var(--primary-container);
            color: #fff;
        }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-x: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .btn-secondary {
            padding: 10px 20px;
            background-color: transparent;
            color: #c2c6d6;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }
        .kanban-board {
            display: flex;
            gap: 16px;
            min-width: 1000px;
        }
        .kanban-column {
            flex: 1;
            min-width: 220px;
            background-color: #1F2430;
            border-radius: 12px;
            padding: 16px;
        }
        .kanban-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .kanban-header h3 {
            margin: 0;
            font-size: 14px;
            font-family: 'Space Grotesk', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .kanban-count {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .kanban-card {
            background-color: #0F1115;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .kanban-card:hover {
            transform: translateY(-2px);
        }
        .kanban-card h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
        }
        .kanban-card p {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #c2c6d6;
        }
        .kanban-card .vehicle {
            font-size: 11px;
            color: #888;
        }
        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .kanban-card .actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .kanban-card .actions a {
            font-size: 11px;
            color: var(--primary);
            text-decoration: none;
        }
        .status-form {
            margin-top: 8px;
        }
        .status-form select {
            width: 100%;
            padding: 6px;
            font-size: 11px;
            background-color: #272a31;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            color: var(--on-surface);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Torque Studio</h2>
        <a href="<?= $basePath ?>/dashboard" class="nav-item">Dashboard</a>
        <a href="<?= $basePath ?>/clients" class="nav-item">Clientes</a>
        <a href="<?= $basePath ?>/vehicles" class="nav-item">Vehículos</a>
        <a href="<?= $basePath ?>/work-orders" class="nav-item active">Órdenes</a>
        <a href="<?= $basePath ?>/work-orders/kanban.php" class="nav-item">Kanban</a>
        <a href="<?= $basePath ?>/services" class="nav-item">Servicios</a>
        <a href="<?= $basePath ?>/parts" class="nav-item">Inventario</a>
        <a href="<?= $basePath ?>/reports" class="nav-item">📊 Reportes</a>
        <a href="<?= $basePath ?>/tools" class="nav-item">🔧 Herramientas</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item">📚 Manuales</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Kanban - Órdenes de Trabajo</h1>
            <a href="<?= $basePath ?>/work-orders" class="btn-secondary">Vista Lista</a>
            <a href="<?= $basePath ?>/work-orders/kanban.php" class="btn-secondary">Kanban</a>
        </div>
        
        <div class="kanban-board">
            <?php foreach ($ordersByStatus as $status => $statusOrders): ?>
                <div class="kanban-column">
                    <div class="kanban-header">
                        <h3>
                            <span class="status-dot" style="background-color: <?= $statusColors[$status] ?>"></span>
                            <?= $statusLabels[$status] ?>
                        </h3>
                        <span class="kanban-count"><?= count($statusOrders) ?></span>
                    </div>
                    <?php foreach ($statusOrders as $order): ?>
                        <div class="kanban-card">
                            <h4>#<?= $order['id'] ?> - <?= esc($order['client_name']) ?></h4>
                            <p class="vehicle"><?= esc($order['brand']) ?> <?= esc($order['model']) ?></p>
                            <p>$<?= number_format($order['total_cost'], 2) ?></p>
                            <div class="actions">
                                <a href="<?= $basePath ?>/work-orders/edit/<?= $order['id'] ?>">Editar</a>
                            </div>
                            <form class="status-form" method="POST" action="<?= $basePath ?>/work-orders/status/<?= $order['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <?php foreach ($statusLabels as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= $order['status'] == $key ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
