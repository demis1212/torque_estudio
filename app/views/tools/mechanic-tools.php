<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herramientas de Mecánico - Torque Studio ERP</title>
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--on-surface);
            display: flex;
            height: 100vh;
            overflow: hidden;
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
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: var(--surface-container);
            border-bottom: 1px solid var(--outline);
        }
        .top-bar h1 {
            margin: 0;
            font-size: 24px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
        }
        .content-area {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
        }
        .btn-primary {
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-container) 0%, #3b7de8 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(77,142,255,0.4);
        }
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        .filter-select {
            padding: 10px 16px;
            background-color: #1F2430;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .stat-card {
            background-color: #1F2430;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 12px;
            color: #888;
        }
        .table-container {
            background-color: #1F2430;
            border-radius: 16px;
            overflow: hidden;
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
        .btn-small {
            padding: 6px 12px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #c2c6d6;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #888;
        }
        .total-value {
            background-color: #1F2430;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: center;
        }
        .total-value h3 {
            margin: 0 0 8px 0;
            color: #888;
            font-size: 14px;
        }
        .total-value .amount {
            font-size: 32px;
            font-weight: 700;
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
            <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
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
    
    <main class="main-content">
        <header class="top-bar">
            <h1><i class="fas fa-wrench" style="margin-right: 12px; color: var(--primary);"></i>Herramientas de Mecánico</h1>
            <?php if ($user_role == 1): ?>
                <a href="<?= $basePath ?>/tools/mechanic/create" class="btn-primary"><i class="fas fa-plus"></i>Asignar Herramienta</a>
            <?php endif; ?>
        </header>

        <div class="content-area">
            <?php if ($mechanic): ?>
            <div class="total-value">
                <h3>Valor Total de Herramientas - <?= esc($mechanic['name']) ?></h3>
                <div class="amount">$<?= number_format($total_value, 2) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($stats)): ?>
            <div class="stats-grid">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <div class="stat-value" style="color: <?= $statusColors[$stat['status']] ?? '#888' ?>">
                        <?= $stat['count'] ?>
                    </div>
                    <div class="stat-label"><?= $statusLabels[$stat['status']] ?? $stat['status'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($user_role == 1): ?>
            <div class="filters">
                <form method="GET" action="<?= $basePath ?>/tools/mechanic" style="display: flex; gap: 12px;">
                    <select name="mechanic_id" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todos los mecánicos</option>
                        <?php foreach ($mechanics as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= ($_GET['mechanic_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                                <?= esc($m['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <?php endif; ?>

            <div class="table-container">
            <?php if (empty($tools)): ?>
                <div class="empty-state">
                    <h3>No hay herramientas registradas</h3>
                    <p><?= $mechanic ? 'Este mecánico no tiene herramientas asignadas' : 'No se encontraron herramientas' ?></p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Herramienta</th>
                            <th>Marca/Modelo</th>
                            <?php if (!isset($mechanic)): ?>
                                <th>Mecánico</th>
                            <?php endif; ?>
                            <th>Costo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tools as $tool): ?>
                        <tr>
                            <td><?= $tool['code'] ? esc($tool['code']) : '-' ?></td>
                            <td>
                                <strong><?= esc($tool['name']) ?></strong>
                                <?php if ($tool['description']): ?>
                                    <br><small style="color: #888;"><?= esc(substr($tool['description'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $tool['brand'] ? esc($tool['brand']) : '-' ?>
                                <?= $tool['model'] ? '<br><small>' . esc($tool['model']) . '</small>' : '' ?>
                            </td>
                            <?php if (!isset($mechanic)): ?>
                                <td><?= esc($tool['mechanic_name'] ?? '-') ?></td>
                            <?php endif; ?>
                            <td>$<?= number_format($tool['cost'] ?? 0, 2) ?></td>
                            <td>
                                <span class="status-badge" style="background-color: <?= $statusColors[$tool['status']] ?>20; color: <?= $statusColors[$tool['status']] ?>">
                                    <?= $statusLabels[$tool['status']] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user_role == 1): ?>
                                    <form method="POST" action="<?= $basePath ?>/tools/mechanic/update-status/<?= $tool['id'] ?>" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <select name="status" class="btn-small" onchange="this.form.submit()">
                                            <?php foreach ($statusLabels as $key => $label): ?>
                                                <option value="<?= $key ?>" <?= $tool['status'] == $key ? 'selected' : '' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-small" onclick="alert('<?= $tool['notes'] ? esc($tool['notes']) : 'Sin notas' ?>')">Ver Notas</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
