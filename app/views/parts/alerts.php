<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas de Compra - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --surface: #0a0c10;
            --surface-container: #11131a;
            --surface-container-high: #1a1d26;
            --on-surface: #e8eaf2;
            --on-surface-variant: #9aa3b2;
            --primary: #8ab4f8;
            --primary-container: #4d8eff;
            --warning: #fbbf24;
            --danger: #f87171;
            --success: #4ade80;
            --outline: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--surface); color: var(--on-surface); display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background: linear-gradient(180deg, var(--surface-container) 0%, var(--surface) 100%); border-right: 1px solid var(--outline); padding: 20px 16px; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar-header { display: flex; align-items: center; gap: 12px; padding: 8px 8px 20px; margin-bottom: 8px; border-bottom: 1px solid var(--outline); }
        .logo-icon { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .sidebar-header h2 { font-size: 18px; margin: 0; color: var(--on-surface); font-family: 'Space Grotesk', sans-serif; font-weight: 600; }
        .nav-section { margin-bottom: 8px; }
        .nav-section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 0.15em; color: var(--on-surface-variant); padding: 16px 8px 8px; font-weight: 600; }
        .nav-item { display: flex; align-items: center; gap: 12px; color: var(--on-surface-variant); text-decoration: none; padding: 10px 12px; border-radius: 8px; margin-bottom: 4px; font-weight: 500; font-size: 14px; transition: all 0.2s; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--on-surface); }
        .nav-item.active { background: linear-gradient(135deg, var(--primary-container) 0%, rgba(77,142,255,0.8) 100%); color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        .nav-item i { width: 20px; text-align: center; }
        .main-content { flex: 1; padding: 32px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header h1 { margin: 0; font-size: 32px; }
        .btn-primary { padding: 10px 20px; background-color: var(--primary-container); color: #fff; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; font-weight: 600; }
        .table-container { background-color: #1F2430; border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        th { font-family: 'Space Grotesk', sans-serif; text-transform: uppercase; font-size: 11px; letter-spacing: 0.1em; color: #a7b6cc; }
        .alert-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-pendiente { background-color: rgba(251, 191, 36, 0.2); color: var(--warning); }
        .status-comprado { background-color: rgba(74, 222, 128, 0.2); color: var(--success); }
        .status-cancelado { background-color: rgba(148, 163, 184, 0.2); color: #94a3b8; }
        .stock-critical { color: var(--danger); font-weight: 600; }
        .actions { display: flex; gap: 8px; }
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; text-decoration: none; }
        .btn-buy { background-color: var(--success); color: #000; }
        .btn-cancel { background-color: rgba(255,255,255,0.1); color: #c2c6d6; }
        .empty-state { text-align: center; padding: 48px; color: #c2c6d6; }
        .stats-bar { display: flex; gap: 16px; margin-bottom: 24px; }
        .stat-item { background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%); border: 1px solid var(--outline); border-radius: 12px; padding: 16px 24px; }
        .stat-value { font-size: 24px; font-weight: 700; color: var(--primary); }
        .stat-label { font-size: 12px; color: var(--on-surface-variant); }
    </style>
</head>
<body>
    <?php $userRole = $_SESSION['user_role'] ?? 0; ?>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon"><i class="fas fa-wrench"></i></div>
            <h2>Torque Studio</h2>
        </div>
        <nav class="nav-section">
            <div class="nav-section-title">Principal</div>
            <a href="<?= $basePath ?>/dashboard" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
            <a href="<?= $basePath ?>/clients" class="nav-item"><i class="fas fa-users"></i> Clientes</a>
            <a href="<?= $basePath ?>/vehicles" class="nav-item"><i class="fas fa-car"></i> Vehículos</a>
            <a href="<?= $basePath ?>/work-orders" class="nav-item"><i class="fas fa-clipboard-list"></i> Órdenes</a>
        </nav>
        <nav class="nav-section">
            <div class="nav-section-title">Operaciones</div>
            <a href="<?= $basePath ?>/services" class="nav-item"><i class="fas fa-wrench"></i> Servicios</a>
            <a href="<?= $basePath ?>/workshop-ops" class="nav-item"><i class="fas fa-stopwatch"></i> Operación Inteligente</a>
            <a href="<?= $basePath ?>/parts" class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="<?= $basePath ?>/parts/alerts" class="nav-item active"><i class="fas fa-bell"></i> Alertas de Compra</a>
            <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
        </nav>
    </aside>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-bell" style="margin-right: 12px; color: var(--warning);"></i>Alertas de Compra Automáticas</h1>
            <a href="<?= $basePath ?>/parts" class="btn-primary">← Volver a Inventario</a>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-value"><?= count(array_filter($alerts, fn($a) => $a['status'] === 'pendiente')) ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= count($alerts) ?></div>
                <div class="stat-label">Total Alertas</div>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($alerts)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: var(--success); margin-bottom: 16px;"></i>
                    <h3>No hay alertas de compra</h3>
                    <p>El sistema creará alertas automáticamente cuando el stock de un repuesto sea bajo.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Repuesto</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Sugerido Comprar</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                            <tr>
                                <td><strong><?= esc($alert['part_code']) ?></strong></td>
                                <td><?= esc($alert['part_name']) ?></td>
                                <td class="stock-critical"><?= $alert['current_quantity'] ?></td>
                                <td><?= $alert['min_stock'] ?></td>
                                <td style="color: var(--success); font-weight: 600;">+<?= $alert['suggested_quantity'] ?></td>
                                <td>
                                    <span class="alert-badge status-<?= $alert['status'] ?>">
                                        <i class="fas fa-circle" style="font-size: 8px;"></i>
                                        <?= ucfirst($alert['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($alert['created_at'])) ?></td>
                                <td class="actions">
                                    <?php if ($alert['status'] === 'pendiente'): ?>
                                        <form method="POST" action="<?= $basePath ?>/parts/alerts/buy/<?= $alert['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn-action btn-buy">Marcar Comprado</button>
                                        </form>
                                        <form method="POST" action="<?= $basePath ?>/parts/alerts/cancel/<?= $alert['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn-action btn-cancel">Cancelar</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #888; font-size: 12px;">Resuelto el <?= date('d/m/Y', strtotime($alert['resolved_at'] ?? $alert['created_at'])) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
