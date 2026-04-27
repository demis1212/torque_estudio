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
    <title>Reportes - Torque Studio ERP</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --surface: #0a0c10;
            --surface-container: #11131a;
            --surface-container-high: #1a1d26;
            --on-surface: #e8eaf2;
            --on-surface-variant: #9aa3b2;
            --outline: rgba(255,255,255,0.08);
            --primary: #adc6ff;
            --primary-container: #4d8eff;
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
        .export-btns {
            display: flex;
            gap: 12px;
        }
        .btn {
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
        .btn-secondary { background-color: #6c757d; }
        .reports-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        .report-card {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .report-card h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .stat-card {
            background-color: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat-card h4 {
            font-size: 12px;
            text-transform: uppercase;
            margin: 0 0 8px 0;
            color: #a7b6cc;
        }
        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }
        .tables-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .table-card {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .table-card h3 { margin: 0 0 20px 0; font-size: 18px; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            font-size: 12px;
            text-transform: uppercase;
            color: #a7b6cc;
        }
        td { font-size: 14px; }
        .text-right { text-align: right; }
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php $userRole = $_SESSION['user_role'] ?? 0; ?>
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
            <a href="<?= $basePath ?>/reports" class="nav-item active"><i class="fas fa-chart-bar"></i> Reportes</a>
            <?php if($userRole == 1): ?>
            <a href="<?= $basePath ?>/users" class="nav-item"><i class="fas fa-user-cog"></i> Usuarios</a>
            <a href="<?= $basePath ?>/settings" class="nav-item"><i class="fas fa-cog"></i> Configuración</a>
            <?php endif; ?>
        </nav>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>📊 Reportes y Estadísticas</h1>
            <div class="export-btns">
                <button class="btn btn-secondary" onclick="exportToExcel()">📥 Exportar Excel</button>
                <button class="btn" onclick="window.print()">🖨️ Imprimir</button>
            </div>
        </div>

        <div class="reports-grid">
            <div class="report-card">
                <h3>Ingresos por Mes (Últimos 12 meses)</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div>
                <div class="report-card" style="margin-bottom: 24px;">
                    <h3>Estado de Órdenes</h3>
                    <div class="chart-container" style="height: 200px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Total Ingresos</h4>
                        <p class="number">$<?= number_format(array_sum(array_column($revenue_by_month, 'total')), 0) ?></p>
                    </div>
                    <div class="stat-card">
                        <h4>Total Órdenes</h4>
                        <p class="number"><?= number_format(array_sum(array_column($orders_by_status, 'count')), 0) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="tables-section">
            <div class="table-card">
                <h3>🔧 Servicios más Solicitados</h3>
                <?php if (empty($top_services)): ?>
                    <p style="color: #666; text-align: center;">No hay datos disponibles</p>
                <?php else: ?>
                    <?php 
                    $maxRevenue = max(array_column($top_services, 'total_revenue'));
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Cantidad</th>
                                <th>Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_services as $service): ?>
                                <tr>
                                    <td><?= esc($service['name']) ?></td>
                                    <td><?= $service['total_qty'] ?></td>
                                    <td class="text-right">$<?= number_format($service['total_revenue'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="padding: 0 12px 12px 12px;">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= ($service['total_revenue'] / $maxRevenue * 100) ?>%; background-color: var(--primary-container);"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="table-card">
                <h3>👥 Clientes Top</h3>
                <?php if (empty($top_clients)): ?>
                    <p style="color: #666; text-align: center;">No hay datos disponibles</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Órdenes</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_clients as $client): ?>
                                <tr>
                                    <td><?= esc($client['name']) ?></td>
                                    <td><?= $client['order_count'] ?></td>
                                    <td class="text-right">$<?= number_format($client['total_spent'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(fn($r) => $r['month'], $revenue_by_month)) ?>,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: <?= json_encode(array_map(fn($r) => $r['total'], $revenue_by_month)) ?>,
                    backgroundColor: '#4d8eff',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#888',
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    },
                    x: {
                        ticks: { color: '#888' },
                        grid: { display: false }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_map(fn($s) => $statusLabels[$s['status']] ?? $s['status'], $orders_by_status)) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($orders_by_status, 'count')) ?>,
                    backgroundColor: <?= json_encode(array_map(fn($s) => $statusColors[$s['status']] ?? '#666', $orders_by_status)) ?>,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#c2c6d6', padding: 20 }
                    }
                }
            }
        });

        function exportToExcel() {
            alert('Función de exportación a Excel - Se implementaría con una librería como SheetJS');
        }
    </script>
</body>
</html>
