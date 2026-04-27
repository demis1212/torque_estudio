<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$typeColors = [
    'info' => '#17a2b8',
    'warning' => '#ffc107',
    'success' => '#28a745',
    'error' => '#dc3545'
];

$typeLabels = [
    'info' => 'ℹ️ Info',
    'warning' => '⚠️ Alerta',
    'success' => '✅ Éxito',
    'error' => '❌ Error'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Torque Studio ERP</title>
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
        .btn-primary {
            padding: 10px 20px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .notifications-container {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .notification-item {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            transition: background 0.2s;
        }
        .notification-item:hover { background-color: rgba(255, 255, 255, 0.02); }
        .notification-item:last-child { border-bottom: none; }
        .notification-item.unread {
            background-color: rgba(77, 142, 255, 0.05);
            border-left: 3px solid var(--primary-container);
        }
        .notification-content { flex: 1; }
        .notification-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .notification-title {
            font-weight: 600;
            font-size: 16px;
            color: var(--on-surface);
        }
        .type-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .notification-message {
            color: #c2c6d6;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .notification-date {
            color: #888;
            font-size: 12px;
        }
        .notification-actions {
            display: flex;
            gap: 8px;
        }
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-mark-read { background-color: var(--primary-container); color: #fff; }
        .btn-delete { background-color: #dc3545; color: #fff; }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #c2c6d6;
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
        <a href="<?= $basePath ?>/notifications" class="nav-item active">🔔 Notificaciones</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item">📚 Manuales</a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item">🔍 Decodificador VIN</a>
        <a href="<?= $basePath ?>/dtc" class="nav-item">🔧 DTC Codes</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Notificaciones</h1>
            <?php if (!empty($notifications)): ?>
                <form method="POST" action="<?= $basePath ?>/notifications/read-all" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn-primary">Marcar todo como leído</button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="notifications-container">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <p>No tienes notificaciones.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                        <div class="notification-content">
                            <div class="notification-header">
                                <span class="notification-title"><?= esc($notification['title']) ?></span>
                                <span class="type-badge" style="background-color: <?= $typeColors[$notification['type']] ?? '#666' ?>20; color: <?= $typeColors[$notification['type']] ?? '#666' ?>">
                                    <?= $typeLabels[$notification['type']] ?? $notification['type'] ?>
                                </span>
                                <?php if (!$notification['is_read']): ?>
                                    <span style="background-color: var(--primary-container); color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 10px;">NUEVO</span>
                                <?php endif; ?>
                            </div>
                            <p class="notification-message"><?= esc($notification['message']) ?></p>
                            <span class="notification-date"><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></span>
                        </div>
                        <div class="notification-actions">
                            <?php if (!$notification['is_read']): ?>
                                <form method="POST" action="<?= $basePath ?>/notifications/read/<?= $notification['id'] ?>" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn-small btn-mark-read">Marcar leído</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($notification['link']): ?>
                                <a href="<?= $basePath ?><?= $notification['link'] ?>" class="btn-small btn-mark-read">Ver</a>
                            <?php endif; ?>
                            <form method="POST" action="<?= $basePath ?>/notifications/delete/<?= $notification['id'] ?>" style="display: inline;" onsubmit="return confirm('¿Eliminar esta notificación?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn-small btn-delete">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
