<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$statusColors = [
    'pendiente' => '#ffc107',
    'aprobada' => '#17a2b8',
    'rechazada' => '#dc3545',
    'entregada' => '#4d8eff',
    'devuelta' => '#28a745',
    'atrasada' => '#dc3545'
];

$statusLabels = [
    'pendiente' => 'Pendiente',
    'aprobada' => 'Aprobada',
    'rechazada' => 'Rechazada',
    'entregada' => 'Entregada',
    'devuelta' => 'Devuelta',
    'atrasada' => 'Atrasada'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Herramientas - Torque Studio ERP</title>
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
            --outline: rgba(255,255,255,0.08);
            --danger: #dc3545;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--on-surface);
            display: flex;
            height: 100vh;
            overflow: hidden;
            margin: 0;
        }
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
        .nav-section { margin-bottom: 8px; }
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
            border-radius: 8px;
            margin-bottom: 4px;
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .nav-item i { width: 20px; text-align: center; }
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
        .header h1 { margin: 0; font-size: 28px; }
        .btn-primary {
            padding: 10px 20px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .sections-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 12px;
        }
        .tab-btn {
            padding: 8px 16px;
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 14px;
            position: relative;
        }
        .tab-btn.active {
            color: var(--primary);
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -13px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--primary-container);
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        .table-container {
            background-color: #1F2430;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        th {
            background-color: rgba(255, 255, 255, 0.02);
            font-weight: 600;
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 4px;
        }
        .btn-approve {
            background-color: #28a745;
            color: #fff;
        }
        .btn-reject {
            background-color: #dc3545;
            color: #fff;
        }
        .btn-deliver {
            background-color: #4d8eff;
            color: #fff;
        }
        .btn-return {
            background-color: #17a2b8;
            color: #fff;
        }
        .alert-box {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #888;
        }
        .tool-value {
            font-weight: 600;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Sidebar Consistente -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">🔧</div>
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
            <a href="<?= $basePath ?>/parts" class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="<?= $basePath ?>/tools" class="nav-item active"><i class="fas fa-tools"></i> Herramientas</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Herramientas</div>
            <a href="<?= $basePath ?>/manuals" class="nav-item"><i class="fas fa-book"></i> Manuales</a>
            <a href="<?= $basePath ?>/vin-decoder" class="nav-item"><i class="fas fa-search"></i> VIN Decoder</a>
            <a href="<?= $basePath ?>/dtc" class="nav-item"><i class="fas fa-exclamation-triangle"></i> DTC Codes</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Administración</div>
            <a href="<?= $basePath ?>/reports" class="nav-item"><i class="fas fa-chart-bar"></i> Reportes</a>
            <?php if($user_role == 1): ?>
            <a href="<?= $basePath ?>/users" class="nav-item"><i class="fas fa-user-cog"></i> Usuarios</a>
            <a href="<?= $basePath ?>/settings" class="nav-item"><i class="fas fa-cog"></i> Configuración</a>
            <?php endif; ?>
        </nav>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>📋 Solicitudes de Herramientas</h1>
            <a href="<?= $basePath ?>/tools/requests/create" class="btn-primary">+ Nueva Solicitud</a>
        </div>

        <?php if (!empty($overdue)): ?>
        <div class="alert-box">
            <strong>⚠️ Préstamos Atrasados</strong>
            <p>Hay <?= count($overdue) ?> herramienta(s) que no han sido devueltas a tiempo.</p>
        </div>
        <?php endif; ?>

        <div class="sections-tabs">
            <?php if ($user_role == 1): ?>
                <button class="tab-btn active" onclick="showSection('pending')">Pendientes (<?= count($pending) ?>)</button>
                <button class="tab-btn" onclick="showSection('active')">Activos (<?= count($active) ?>)</button>
                <button class="tab-btn" onclick="showSection('all')">Histórico</button>
            <?php else: ?>
                <button class="tab-btn active" onclick="showSection('all')">Mis Solicitudes</button>
            <?php endif; ?>
        </div>

        <!-- PENDIENTES (Admin) -->
        <?php if ($user_role == 1): ?>
        <div id="pending-section" class="section active">
            <div class="table-container">
                <?php if (empty($pending)): ?>
                    <div class="empty-state">
                        <h3>No hay solicitudes pendientes</h3>
                        <p>Todas las solicitudes han sido procesadas</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Herramienta</th>
                                <th>Mecánico</th>
                                <th>Valor</th>
                                <th>Requiere Auth</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending as $req): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($req['requested_at'])) ?></td>
                                <td>
                                    <strong><?= esc($req['tool_name']) ?></strong>
                                    <br><small><?= $req['tool_code'] ? esc($req['tool_code']) : 'Sin código' ?></small>
                                </td>
                                <td><?= esc($req['mechanic_name']) ?></td>
                                <td class="tool-value">$<?= number_format($req['tool_cost'] ?? 0, 2) ?></td>
                                <td><?= $req['requires_auth'] ? '⚠️ Sí' : 'No' ?></td>
                                <td>
                                    <form method="POST" action="<?= $basePath ?>/tools/requests/approve/<?= $req['id'] ?>" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn-action btn-approve">✓ Aprobar</button>
                                    </form>
                                    <form method="POST" action="<?= $basePath ?>/tools/requests/reject/<?= $req['id'] ?>" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn-action btn-reject">✗ Rechazar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ACTIVOS (Admin) -->
        <div id="active-section" class="section">
            <div class="table-container">
                <?php if (empty($active)): ?>
                    <div class="empty-state">
                        <h3>No hay préstamos activos</h3>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Herramienta</th>
                                <th>Mecánico</th>
                                <th>Fecha Entrega</th>
                                <th>Devolución</th>
                                <th>Valor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active as $req): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($req['tool_name']) ?></strong>
                                    <br><small><?= $req['tool_code'] ? esc($req['tool_code']) : 'Sin código' ?></small>
                                </td>
                                <td><?= esc($req['mechanic_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($req['delivered_at'])) ?></td>
                                <td><?= $req['return_date'] ? date('d/m/Y', strtotime($req['return_date'])) : 'Sin fecha' ?></td>
                                <td class="tool-value">$<?= number_format($req['tool_cost'] ?? 0, 2) ?></td>
                                <td>
                                    <form method="POST" action="<?= $basePath ?>/tools/requests/return/<?= $req['id'] ?>" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="text" name="condition_notes" placeholder="Estado al devolver..." style="padding: 4px; background: #0F1115; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; color: #fff; font-size: 12px; width: 150px;">
                                        <button type="submit" class="btn-action btn-return">↩ Devolver</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- HISTÓRICO / MIS SOLICITUDES -->
        <div id="all-section" class="section <?= $user_role != 1 ? 'active' : '' ?>">
            <div class="table-container">
                <?php if (empty($all)): ?>
                    <div class="empty-state">
                        <h3>No hay solicitudes registradas</h3>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha Solicitud</th>
                                <th>Herramienta</th>
                                <th><?= $user_role == 1 ? 'Mecánico' : 'Estado' ?></th>
                                <th>Estado</th>
                                <th>Fecha Devolución</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all as $req): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($req['requested_at'])) ?></td>
                                <td>
                                    <strong><?= esc($req['tool_name']) ?></strong>
                                    <br><small><?= $req['tool_code'] ? esc($req['tool_code']) : 'Sin código' ?></small>
                                </td>
                                <td>
                                    <?php if ($user_role == 1): ?>
                                        <?= esc($req['mechanic_name']) ?>
                                    <?php else: ?>
                                        <?php if ($req['status'] == 'aprobada' && !$req['delivered_at']): ?>
                                            <form method="POST" action="<?= $basePath ?>/tools/requests/deliver/<?= $req['id'] ?>" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <button type="submit" class="btn-action btn-deliver">📦 Retirar</button>
                                            </form>
                                        <?php elseif ($req['status'] == 'entregada'): ?>
                                            <form method="POST" action="<?= $basePath ?>/tools/requests/return/<?= $req['id'] ?>" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <button type="submit" class="btn-action btn-return">↩ Devolver</button>
                                            </form>
                                        <?php else: ?>
                                            <?= $statusLabels[$req['status']] ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge" style="background-color: <?= $statusColors[$req['status']] ?>20; color: <?= $statusColors[$req['status']] ?>">
                                        <?= $statusLabels[$req['status']] ?>
                                    </span>
                                </td>
                                <td><?= $req['return_date'] ? date('d/m/Y', strtotime($req['return_date'])) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showSection(section) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
            document.getElementById(section + '-section').classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
