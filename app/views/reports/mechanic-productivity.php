<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

function formatHours($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf('%dh %02dm', $hours, $mins);
}

function formatMoney($amount) {
    return '$' . number_format($amount, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productividad de Mecánicos - Torque Studio ERP</title>
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
        .header h1 { margin: 0; font-size: 28px; }
        .date-filter { display: flex; gap: 12px; align-items: center; background: var(--surface-container); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--outline); }
        .date-filter input { background: var(--surface-container-high); border: 1px solid var(--outline); color: var(--on-surface); padding: 8px 12px; border-radius: 6px; }
        .date-filter button { background: var(--primary-container); color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .stat-card { background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%); border: 1px solid var(--outline); border-radius: 16px; padding: 20px; }
        .stat-card .label { font-size: 12px; color: var(--on-surface-variant); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }
        .stat-card .value { font-size: 28px; font-weight: 700; color: var(--primary); }
        .stat-card .value.success { color: var(--success); }
        .stat-card .value.warning { color: var(--warning); }
        .stat-card .subvalue { font-size: 13px; color: var(--on-surface-variant); margin-top: 4px; }
        
        .table-container { background: var(--surface-container-high); border-radius: 16px; padding: 24px; border: 1px solid var(--outline); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--outline); }
        th { font-family: 'Space Grotesk', sans-serif; text-transform: uppercase; font-size: 11px; letter-spacing: 0.1em; color: var(--on-surface-variant); }
        tr:hover { background: rgba(255,255,255,0.02); }
        .mechanic-name { font-weight: 600; color: var(--primary); }
        .hours-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .hours-billable { background: rgba(74, 222, 128, 0.15); color: var(--success); }
        .hours-nonbillable { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }
        .efficiency-bar { width: 100px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; }
        .efficiency-fill { height: 100%; background: linear-gradient(90deg, var(--success), var(--primary)); border-radius: 3px; }
        .empty-state { text-align: center; padding: 48px; color: var(--on-surface-variant); }
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
            <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
        </nav>
        <nav class="nav-section">
            <div class="nav-section-title">Reportes</div>
            <a href="<?= $basePath ?>/reports/mechanic-productivity" class="nav-item active"><i class="fas fa-chart-line"></i> Productividad</a>
            <a href="<?= $basePath ?>/reports/activity" class="nav-item"><i class="fas fa-list"></i> Actividad</a>
        </nav>
    </aside>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-users-cog" style="margin-right: 12px; color: var(--primary);"></i>Productividad por Mecánico</h1>
            <form method="GET" class="date-filter">
                <label>Desde:</label>
                <input type="date" name="start_date" value="<?= $startDate ?>">
                <label>Hasta:</label>
                <input type="date" name="end_date" value="<?= $endDate ?>">
                <button type="submit"><i class="fas fa-filter"></i> Filtrar</button>
            </form>
        </div>

        <?php if ($teamMetrics): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Mecánicos Activos</div>
                <div class="value"><?= $teamMetrics['total_mechanics'] ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Órdenes Trabajadas</div>
                <div class="value"><?= $teamMetrics['total_orders'] ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Horas Totales</div>
                <div class="value warning"><?= formatHours($teamMetrics['total_minutes']) ?></div>
                <div class="subvalue">Facturables: <?= formatHours($teamMetrics['billable_minutes']) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Facturación Total</div>
                <div class="value success"><?= formatMoney($teamMetrics['total_billed']) ?></div>
                <div class="subvalue">Promedio por hora</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <h3 style="margin-bottom: 16px;"><i class="fas fa-table" style="margin-right: 8px; color: var(--primary);"></i>Desglose por Mecánico</h3>
            
            <?php if (empty($productivity)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-clock" style="font-size: 48px; color: var(--on-surface-variant); margin-bottom: 16px;"></i>
                    <h3>Sin datos de productividad</h3>
                    <p>No hay registros de trabajo en el período seleccionado.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Mecánico</th>
                            <th>OTs</th>
                            <th>Sesiones</th>
                            <th>Horas Totales</th>
                            <th>Horas Facturables</th>
                            <th>Horas No Fact.</th>
                            <th>Eficiencia</th>
                            <th>Valor Hora</th>
                            <th>Total Facturado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productivity as $m): 
                            $efficiency = $m['total_minutes'] > 0 ? round(($m['billable_minutes'] / $m['total_minutes']) * 100) : 0;
                        ?>
                            <tr>
                                <td>
                                    <div class="mechanic-name"><i class="fas fa-user" style="margin-right: 6px;"></i><?= esc($m['mechanic_name']) ?></div>
                                </td>
                                <td><?= $m['orders_worked'] ?></td>
                                <td><?= $m['sessions_count'] ?></td>
                                <td>
                                    <span class="hours-badge hours-nonbillable">
                                        <i class="fas fa-clock"></i> <?= formatHours($m['total_minutes']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="hours-badge hours-billable">
                                        <i class="fas fa-dollar-sign"></i> <?= formatHours($m['billable_minutes']) ?>
                                    </span>
                                </td>
                                <td><?= formatHours($m['non_billable_minutes']) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="efficiency-bar">
                                            <div class="efficiency-fill" style="width: <?= min($efficiency, 100) ?>%"></div>
                                        </div>
                                        <span style="font-size: 12px; color: var(--on-surface-variant);"><?= $efficiency ?>%</span>
                                    </div>
                                </td>
                                <td><?= formatMoney($m['hourly_rate']) ?></td>
                                <td style="font-weight: 600; color: var(--success);"><?= formatMoney($m['total_billed']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
