<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

function formatMoney($amount) {
    return '$' . number_format($amount, 0, ',', '.');
}

// Prepare chart data
$chartLabels = [];
$chartRevenue = [];
foreach ($data['daily_trend'] as $day) {
    $chartLabels[] = date('d/m', strtotime($day['date']));
    $chartRevenue[] = $day['revenue'];
}

$totalRevenue = (float)$data['financial']['total_revenue'];
$collected = (float)$data['financial']['collected_amount'];
$pending = (float)$data['financial']['pending_collection'];
$margin = $totalRevenue > 0 ? ($collected / $totalRevenue) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gerencial - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --surface: #0a0c10;
            --surface-container: #11131a;
            --surface-container-high: #1a1d26;
            --on-surface: #e8eaf2;
            --on-surface-variant: #9aa3b2;
            --primary: #8ab4f8;
            --primary-container: #4d8eff;
            --success: #4ade80;
            --warning: #fbbf24;
            --danger: #f87171;
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
        .main-content { flex: 1; padding: 24px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .date-filter { display: flex; gap: 12px; align-items: center; background: var(--surface-container); padding: 10px 16px; border-radius: 8px; border: 1px solid var(--outline); }
        .date-filter input { background: var(--surface-container-high); border: 1px solid var(--outline); color: var(--on-surface); padding: 6px 10px; border-radius: 6px; font-size: 13px; }
        .date-filter button { background: var(--primary-container); color: #fff; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
        
        /* KPI Cards */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px; }
        .kpi-card { background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%); border: 1px solid var(--outline); border-radius: 12px; padding: 16px; position: relative; }
        .kpi-card .icon { position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .kpi-card .icon.revenue { background: rgba(74, 222, 128, 0.15); color: var(--success); }
        .kpi-card .icon.orders { background: rgba(138, 180, 248, 0.15); color: var(--primary); }
        .kpi-card .icon.customers { background: rgba(251, 191, 36, 0.15); color: var(--warning); }
        .kpi-card .icon.alert { background: rgba(248, 113, 113, 0.15); color: var(--danger); }
        .kpi-card .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: var(--on-surface-variant); margin-bottom: 4px; }
        .kpi-card .value { font-size: 22px; font-weight: 700; color: var(--on-surface); }
        .kpi-card .trend { font-size: 11px; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
        .trend.up { color: var(--success); }
        .trend.down { color: var(--danger); }
        
        /* Two column layout */
        .two-columns { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px; }
        .card { background: var(--surface-container-high); border-radius: 12px; padding: 20px; border: 1px solid var(--outline); }
        .card h3 { margin: 0 0 16px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.1em; color: var(--on-surface-variant); display: flex; align-items: center; gap: 8px; }
        
        /* Progress bars */
        .progress-item { margin-bottom: 12px; }
        .progress-header { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 12px; }
        .progress-bar { height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }
        .progress-fill.success { background: var(--success); }
        .progress-fill.primary { background: var(--primary); }
        .progress-fill.warning { background: var(--warning); }
        .progress-fill.danger { background: var(--danger); }
        
        /* Table */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid var(--outline); font-size: 12px; }
        th { font-family: 'Space Grotesk', sans-serif; text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; color: var(--on-surface-variant); }
        tr:hover { background: rgba(255,255,255,0.02); }
        td:nth-child(3) { text-align: right; font-weight: 600; color: var(--success); }
        
        /* Status pills */
        .status-pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; }
        .pill-success { background: rgba(74, 222, 128, 0.15); color: var(--success); }
        .pill-warning { background: rgba(251, 191, 36, 0.15); color: var(--warning); }
        .pill-danger { background: rgba(248, 113, 113, 0.15); color: var(--danger); }
        
        /* Bottom grid */
        .bottom-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    </style>
</head>
<body>
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
            <div class="nav-section-title">Administración</div>
            <a href="<?= $basePath ?>/reports/manager-dashboard" class="nav-item active"><i class="fas fa-chart-line"></i> Dashboard Gerencial</a>
            <a href="<?= $basePath ?>/reports/mechanic-productivity" class="nav-item"><i class="fas fa-users-cog"></i> Productividad</a>
            <a href="<?= $basePath ?>/whatsapp-reminders" class="nav-item"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            <a href="<?= $basePath ?>/users" class="nav-item"><i class="fas fa-user-cog"></i> Usuarios</a>
            <a href="<?= $basePath ?>/settings" class="nav-item"><i class="fas fa-cog"></i> Configuración</a>
        </nav>
    </aside>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-chart-line" style="margin-right: 10px; color: var(--primary);"></i>Dashboard Gerencial</h1>
            <form method="GET" class="date-filter">
                <input type="date" name="start_date" value="<?= $startDate ?>">
                <span style="color: var(--on-surface-variant);">a</span>
                <input type="date" name="end_date" value="<?= $endDate ?>">
                <button type="submit"><i class="fas fa-filter"></i> Actualizar</button>
            </form>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="icon revenue"><i class="fas fa-dollar-sign"></i></div>
                <div class="label">Ingresos Totales</div>
                <div class="value" style="color: var(--success);"><?= formatMoney($totalRevenue) ?></div>
                <div class="trend <?= $comparison['revenue_growth'] >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $comparison['revenue_growth'] >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($comparison['revenue_growth']) ?>% vs período anterior
                </div>
            </div>
            <div class="kpi-card">
                <div class="icon orders"><i class="fas fa-clipboard-check"></i></div>
                <div class="label">Órdenes Completadas</div>
                <div class="value"><?= $data['work_orders']['completed_orders'] ?> / <?= $data['work_orders']['total_orders'] ?></div>
                <div class="trend <?= $comparison['order_growth'] >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $comparison['order_growth'] >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($comparison['order_growth']) ?>% vs período anterior
                </div>
            </div>
            <div class="kpi-card">
                <div class="icon customers"><i class="fas fa-users"></i></div>
                <div class="label">Clientes Atendidos</div>
                <div class="value"><?= $data['customers']['unique_customers'] ?></div>
                <div class="trend up">
                    <i class="fas fa-user-plus"></i>
                    <?= $data['customers']['new_customers_this_period'] ?> nuevos
                </div>
            </div>
            <div class="kpi-card">
                <div class="icon alert"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="label">Alertas Inventario</div>
                <div class="value" style="color: <?= $data['inventory']['low_stock_count'] > 0 ? 'var(--danger)' : 'var(--success)' ?>">
                    <?= $data['inventory']['low_stock_count'] ?>
                </div>
                <div class="trend <?= $data['inventory']['out_of_stock_count'] > 0 ? 'down' : 'up' ?>">
                    <?= $data['inventory']['out_of_stock_count'] ?> sin stock
                </div>
            </div>
        </div>

        <!-- Charts and Status -->
        <div class="two-columns">
            <div class="card">
                <h3><i class="fas fa-chart-area"></i> Tendencia de Ingresos Diarios</h3>
                <canvas id="revenueChart" height="180"></canvas>
            </div>
            <div class="card">
                <h3><i class="fas fa-tasks"></i> Estado de Órdenes</h3>
                <?php 
                $total = max($data['work_orders']['total_orders'], 1);
                $statuses = [
                    ['label' => 'Completadas', 'value' => $data['work_orders']['completed_orders'], 'class' => 'success'],
                    ['label' => 'En Progreso', 'value' => $data['work_orders']['in_progress_orders'], 'class' => 'primary'],
                    ['label' => 'En Recepción', 'value' => $data['work_orders']['pending_orders'], 'class' => 'warning'],
                    ['label' => 'Control Calidad', 'value' => $data['work_orders']['quality_check_orders'], 'class' => 'danger']
                ];
                foreach ($statuses as $status):
                    $pct = ($status['value'] / $total) * 100;
                ?>
                <div class="progress-item">
                    <div class="progress-header">
                        <span><?= $status['label'] ?></span>
                        <span><?= $status['value'] ?> (<?= round($pct) ?>%)</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?= $status['class'] ?>" style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="bottom-grid">
            <div class="card">
                <h3><i class="fas fa-star"></i> Top Servicios</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Cant.</th>
                                <th>Ingreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($data['top_services'], 0, 5) as $service): ?>
                            <tr>
                                <td><?= esc($service['service_name']) ?></td>
                                <td><?= $service['times_sold'] ?></td>
                                <td><?= formatMoney($service['total_revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <h3><i class="fas fa-pie-chart"></i> Desglose de Ingresos</h3>
                <canvas id="breakdownChart" height="150"></canvas>
            </div>
            <div class="card">
                <h3><i class="fas fa-wallet"></i> Resumen Financiero</h3>
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--on-surface-variant);">Facturado:</span>
                        <span style="font-weight: 600;"><?= formatMoney($totalRevenue) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--on-surface-variant);">Cobrado:</span>
                        <span style="font-weight: 600; color: var(--success);"><?= formatMoney($collected) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--on-surface-variant);">Por Cobrar:</span>
                        <span style="font-weight: 600; color: var(--warning);"><?= formatMoney($pending) ?></span>
                    </div>
                    <div style="height: 1px; background: var(--outline); margin: 12px 0;"></div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--on-surface-variant);">Cobranza:</span>
                        <span style="font-weight: 700; color: var(--primary);"><?= round($margin) ?>%</span>
                    </div>
                </div>
                <div class="progress-bar" style="height: 8px;">
                    <div class="progress-fill success" style="width: <?= $margin ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Revenue trend chart
        const ctx1 = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: <?= json_encode($chartRevenue) ?>,
                    borderColor: '#4ade80',
                    backgroundColor: 'rgba(74, 222, 128, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#9aa3b2', font: { size: 10 } }, grid: { display: false } },
                    y: { ticks: { color: '#9aa3b2', font: { size: 10 }, callback: v => '$' + (v/1000).toFixed(0) + 'k' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });

        // Revenue breakdown pie chart
        const ctx2 = document.getElementById('breakdownChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Mano de Obra', 'Repuestos', 'Servicios'],
                datasets: [{
                    data: [
                        <?= (float)$data['financial']['labor_revenue'] ?>,
                        <?= (float)$data['financial']['parts_revenue'] ?>,
                        <?= (float)$data['financial']['services_revenue'] ?>
                    ],
                    backgroundColor: ['#4ade80', '#8ab4f8', '#fbbf24'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#9aa3b2', font: { size: 10 }, boxWidth: 12 } }
                }
            }
        });
    </script>
</body>
</html>
