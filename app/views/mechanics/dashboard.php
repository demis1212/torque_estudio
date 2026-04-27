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
    <title>Panel de Mecánico - Torque Studio ERP</title>
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
            margin-bottom: 32px;
        }
        .header h1 { margin: 0; font-size: 32px; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .stat-card h4 {
            font-family: 'Space Grotesk', sans-serif;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.1em;
            margin: 0 0 12px 0;
            color: #a7b6cc;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
        }
        .orders-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        .orders-box {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .orders-box h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-item {
            padding: 16px;
            background-color: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            margin-bottom: 12px;
            border-left: 4px solid var(--primary-container);
        }
        .order-item:hover { background-color: rgba(255, 255, 255, 0.05); }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .order-title { font-weight: 600; font-size: 16px; }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .order-info {
            color: #888;
            font-size: 14px;
            margin-bottom: 12px;
        }
        .order-actions {
            display: flex;
            gap: 8px;
        }
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-primary { background-color: var(--primary-container); color: #fff; }
        .btn-secondary { background-color: rgba(255, 255, 255, 0.1); color: #c2c6d6; }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .notification-box {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .notification-box h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
        }
        .notification-item {
            padding: 12px;
            background-color: rgba(255, 193, 7, 0.1);
            border-left: 3px solid #ffc107;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .notification-item strong { font-size: 14px; }
        .notification-item p {
            margin: 4px 0 0 0;
            font-size: 12px;
            color: #888;
        }
        .quick-actions {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        .btn-action {
            padding: 12px 24px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🔧 Panel Mecánico</h2>
        <a href="<?= $basePath ?>/mechanic/dashboard" class="nav-item active">Dashboard</a>
        <a href="<?= $basePath ?>/mechanic/orders" class="nav-item">Mis Órdenes</a>
        <a href="<?= $basePath ?>/workshop-ops" class="nav-item">Operación Inteligente</a>
        <a href="<?= $basePath ?>/work-orders" class="nav-item">Todas las Órdenes</a>
        <a href="<?= $basePath ?>/parts" class="nav-item">Inventario</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
        <a href="<?= $basePath ?>/dashboard" class="nav-item">← Dashboard General</a>
        <a href="<?= $basePath ?>/logout" class="nav-item">Cerrar Sesión</a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Bienvenido, <?= esc($user_name) ?></h1>
            <div style="color: #888;"><?= date('d/m/Y H:i') ?></div>
        </div>

        <div class="quick-actions">
            <a href="<?= $basePath ?>/mechanic/orders?status=reparacion" class="btn-action">🔧 Ver en Reparación</a>
            <a href="<?= $basePath ?>/work-orders/kanban" class="btn-action">📋 Tablero Kanban</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Asignadas</h4>
                <p class="number"><?= $stats['total_assigned'] ?></p>
            </div>
            <div class="stat-card">
                <h4>Pendientes</h4>
                <p class="number" style="color: #ffc107;"><?= $stats['pending'] ?></p>
            </div>
            <div class="stat-card">
                <h4>En Reparación</h4>
                <p class="number" style="color: #fd7e14;"><?= $stats['in_progress'] ?></p>
            </div>
            <div class="stat-card">
                <h4>Completadas</h4>
                <p class="number" style="color: #28a745;"><?= $stats['completed'] ?></p>
            </div>
        </div>

        <div class="orders-section">
            <div class="orders-box">
                <h3>
                    Órdenes Pendientes
                    <a href="<?= $basePath ?>/mechanic/orders" style="font-size: 14px; color: var(--primary);">Ver todas →</a>
                </h3>
                
                <?php if (empty($pending_orders)): ?>
                    <div class="empty-state">
                        <p>No tienes órdenes pendientes 🎉</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_orders as $order): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <span class="order-title">Orden #<?= $order['id'] ?> - <?= esc($order['client_name']) ?></span>
                                <span class="status-badge" style="background-color: <?= $statusColors[$order['status']] ?>20; color: <?= $statusColors[$order['status']] ?>">
                                    <?= $statusLabels[$order['status']] ?>
                                </span>
                            </div>
                            <div class="order-info">
                                <?= esc($order['brand']) ?> <?= esc($order['model']) ?> | Placa: <?= esc($order['plate']) ?>
                            </div>
                            <div class="order-actions">
                                <a href="<?= $basePath ?>/work-orders/edit/<?= $order['id'] ?>" class="btn-small btn-primary">Ver Detalle</a>
                                <?php if ($order['status'] === 'recepcion'): ?>
                                    <form method="POST" action="<?= $basePath ?>/mechanic/order/<?= $order['id'] ?>/status" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="status" value="diagnostico">
                                        <button type="submit" class="btn-small btn-secondary">Iniciar Diagnóstico</button>
                                    </form>
                                <?php elseif ($order['status'] === 'diagnostico'): ?>
                                    <form method="POST" action="<?= $basePath ?>/mechanic/order/<?= $order['id'] ?>/status" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="status" value="reparacion">
                                        <button type="submit" class="btn-small btn-secondary">Iniciar Reparación</button>
                                    </form>
                                <?php elseif ($order['status'] === 'reparacion'): ?>
                                    <form method="POST" action="<?= $basePath ?>/mechanic/order/<?= $order['id'] ?>/status" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="status" value="terminado">
                                        <button type="submit" class="btn-small btn-secondary" style="background-color: #28a745; color: #fff;">Marcar Terminado</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div>
                <?php if (!empty($notifications)): ?>
                <div class="notification-box">
                    <h3>🔔 Notificaciones</h3>
                    <?php foreach (array_slice($notifications, 0, 5) as $notif): ?>
                        <div class="notification-item">
                            <strong><?= esc($notif['title']) ?></strong>
                            <p><?= esc($notif['message']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="orders-box" style="margin-top: 24px;">
                    <h3>Últimas Completadas</h3>
                    <?php if (empty($completed_orders)): ?>
                        <div class="empty-state">
                            <p>Sin órdenes completadas</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($completed_orders as $order): ?>
                            <div class="order-item" style="border-left-color: #28a745;">
                                <div class="order-header">
                                    <span class="order-title">Orden #<?= $order['id'] ?></span>
                                    <span class="status-badge" style="background-color: #28a74520; color: #28a745;">Terminado</span>
                                </div>
                                <div class="order-info">
                                    <?= esc($order['client_name']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
