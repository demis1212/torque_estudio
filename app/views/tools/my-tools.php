<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
$user_role = $user_role ?? ($_SESSION['user_role'] ?? 0);

$statusColors = [
    'activa' => '#28a745',
    'danada' => '#dc3545',
    'perdida' => '#6c757d',
    'en_reparacion' => '#ffc107'
];

$statusLabels = [
    'activa' => 'Activa',
    'danada' => 'Dañada',
    'perdida' => 'Perdida',
    'en_reparacion' => 'En Reparación'
];

$reqStatusColors = [
    'pendiente' => '#ffc107',
    'aprobada' => '#17a2b8',
    'rechazada' => '#dc3545',
    'entregada' => '#4d8eff',
    'devuelta' => '#28a745',
    'atrasada' => '#dc3545'
];

$reqStatusLabels = [
    'pendiente' => 'Pendiente',
    'aprobada' => 'Aprobada',
    'rechazada' => 'Rechazada',
    'entregada' => 'En mi poder',
    'devuelta' => 'Devuelta',
    'atrasada' => 'Atrasada'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Herramientas - Torque Studio ERP</title>
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
            margin-bottom: 32px;
        }
        .header h1 { margin: 0; font-size: 28px; }
        .section {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }
        .tool-card {
            background-color: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .tool-name {
            font-weight: 600;
            margin-bottom: 8px;
        }
        .tool-info {
            font-size: 13px;
            color: #888;
            margin-bottom: 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 8px;
        }
        .loan-card {
            background-color: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .loan-info h4 {
            margin: 0 0 4px 0;
        }
        .loan-info p {
            margin: 0;
            font-size: 13px;
            color: #888;
        }
        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-return {
            background-color: #17a2b8;
            color: #fff;
        }
        .empty-state {
            text-align: center;
            padding: 32px;
            color: #888;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background-color: #1F2430;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
        }
        .stat-label {
            font-size: 13px;
            color: #888;
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
            <h1>🛠️ Mis Herramientas</h1>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value"><?= count($assigned_tools) ?></div>
                <div class="stat-label">Herramientas Asignadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($active_loans) ?></div>
                <div class="stat-label">Préstamos Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($request_history) ?></div>
                <div class="stat-label">Solicitudes Totales</div>
            </div>
        </div>

        <!-- Herramientas Asignadas Permanentemente -->
        <div class="section">
            <div class="section-title">
                <span>🔧 Herramientas Asignadas</span>
                <span style="font-size: 14px; color: #888;">Estas herramientas son tu responsabilidad</span>
            </div>
            
            <?php if (empty($assigned_tools)): ?>
                <div class="empty-state">
                    <p>No tienes herramientas asignadas permanentemente</p>
                </div>
            <?php else: ?>
                <div class="tools-grid">
                    <?php foreach ($assigned_tools as $tool): ?>
                    <div class="tool-card">
                        <div class="tool-name"><?= esc($tool['name']) ?></div>
                        <div class="tool-info">Código: <?= $tool['code'] ? esc($tool['code']) : 'N/A' ?></div>
                        <?php if ($tool['brand']): ?>
                            <div class="tool-info">Marca: <?= esc($tool['brand']) ?></div>
                        <?php endif; ?>
                        <?php if ($tool['description']): ?>
                            <div class="tool-info"><?= esc(substr($tool['description'], 0, 60)) ?>...</div>
                        <?php endif; ?>
                        <span class="status-badge" style="background-color: <?= $statusColors[$tool['status']] ?>20; color: <?= $statusColors[$tool['status']] ?>">
                            <?= $statusLabels[$tool['status']] ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Préstamos Activos de Bodega -->
        <div class="section">
            <div class="section-title">
                <span>📤 Herramientas Prestadas de Bodega</span>
                <a href="<?= $basePath ?>/tools/requests/create" class="btn-action" style="background-color: var(--primary-container); color: #fff;">+ Solicitar</a>
            </div>
            
            <?php if (empty($active_loans)): ?>
                <div class="empty-state">
                    <p>No tienes herramientas de bodega en préstamo</p>
                </div>
            <?php else: ?>
                <?php foreach ($active_loans as $loan): ?>
                <div class="loan-card">
                    <div class="loan-info">
                        <h4><?= esc($loan['tool_name']) ?></h4>
                        <p>Retirada: <?= date('d/m/Y', strtotime($loan['delivered_at'])) ?></p>
                        <?php if ($loan['return_date']): ?>
                            <p>Devolver antes: <?= date('d/m/Y', strtotime($loan['return_date'])) ?></p>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="<?= $basePath ?>/tools/requests/return/<?= $loan['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="btn-action btn-return">↩ Devolver</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Historial de Solicitudes -->
        <div class="section">
            <div class="section-title">
                <span>📜 Historial de Solicitudes</span>
            </div>
            
            <?php if (empty($request_history)): ?>
                <div class="empty-state">
                    <p>No has realizado ninguna solicitud</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach (array_slice($request_history, 0, 5) as $req): ?>
                    <div class="loan-card">
                        <div class="loan-info">
                            <h4><?= esc($req['tool_name']) ?></h4>
                            <p>Solicitado: <?= date('d/m/Y H:i', strtotime($req['requested_at'])) ?></p>
                        </div>
                        <span class="status-badge" style="background-color: <?= $reqStatusColors[$req['status']] ?>20; color: <?= $reqStatusColors[$req['status']] ?>">
                            <?= $reqStatusLabels[$req['status']] ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($request_history) > 5): ?>
                    <div style="text-align: center; margin-top: 16px;">
                        <a href="<?= $basePath ?>/tools/requests" style="color: var(--primary); text-decoration: none;">Ver todo el historial →</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
