<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$statusLabels = [
    'recepcion' => 'En Recepción',
    'diagnostico' => 'En Diagnóstico',
    'reparacion' => 'En Reparación',
    'terminado' => 'Terminado',
    'entregado' => 'Entregado'
];

$statusColors = [
    'recepcion' => '#ffc107',
    'diagnostico' => '#17a2b8',
    'reparacion' => '#fd7e14',
    'terminado' => '#28a745',
    'entregado' => '#6c757d'
];

$status = $order['status'] ?? 'recepcion';
$statusLabel = $statusLabels[$status] ?? $status;
$statusColor = $statusColors[$status] ?? '#6c757d';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Trabajo #<?= $order['id'] ?> - Torque Studio</title>
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
            --success: #4ade80;
            --warning: #fbbf24;
            --danger: #f87171;
            --outline: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--surface); color: var(--on-surface); display: flex; height: 100vh; overflow: hidden; }
        
        .sidebar { width: 260px; background: linear-gradient(180deg, var(--surface-container) 0%, var(--surface) 100%); border-right: 1px solid var(--outline); padding: 20px 16px; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar-header { display: flex; align-items: center; gap: 12px; padding: 8px 8px 20px; margin-bottom: 8px; border-bottom: 1px solid var(--outline); }
        .logo-icon { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white; }
        .sidebar-header h2 { font-size: 18px; margin: 0; color: var(--on-surface); font-family: 'Space Grotesk', sans-serif; font-weight: 600; }
        .nav-section { margin-bottom: 8px; }
        .nav-section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 0.15em; color: var(--on-surface-variant); padding: 16px 8px 8px; font-weight: 600; }
        .nav-item { display: flex; align-items: center; gap: 12px; color: var(--on-surface-variant); text-decoration: none; padding: 10px 12px; border-radius: 8px; margin-bottom: 4px; font-weight: 500; font-size: 14px; transition: all 0.2s; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--on-surface); }
        .nav-item.active { background: linear-gradient(135deg, var(--primary-container) 0%, rgba(77,142,255,0.8) 100%); color: #fff; }
        .nav-item i { width: 20px; text-align: center; }
        
        .main-content { flex: 1; padding: 32px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header h1 { margin: 0; font-size: 28px; font-family: 'Space Grotesk', sans-serif; }
        
        .card { background: var(--surface-container); border: 1px solid var(--outline); border-radius: 12px; padding: 24px; margin-bottom: 20px; }
        .card-title { font-size: 18px; font-weight: 600; margin-bottom: 16px; color: var(--primary); }
        
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: uppercase; }
        
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
        .info-item { background: rgba(255,255,255,0.03); padding: 16px; border-radius: 8px; }
        .info-label { font-size: 12px; color: var(--on-surface-variant); text-transform: uppercase; margin-bottom: 4px; }
        .info-value { font-size: 16px; font-weight: 500; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%); color: white; }
        .btn-secondary { background: rgba(255,255,255,0.1); color: var(--on-surface); }
        .btn:hover { transform: translateY(-2px); }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--outline); }
        .table th { color: var(--on-surface-variant); font-weight: 500; font-size: 12px; text-transform: uppercase; }
        
        .actions { display: flex; gap: 12px; margin-top: 24px; }
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
            <a href="<?= $basePath ?>/work-orders" class="nav-item"><i class="fas fa-clipboard-list"></i> Órdenes</a>
            <a href="<?= $basePath ?>/clients" class="nav-item"><i class="fas fa-users"></i> Clientes</a>
            <a href="<?= $basePath ?>/vehicles" class="nav-item"><i class="fas fa-car"></i> Vehículos</a>
        </nav>
    </aside>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Orden de Trabajo #<?= $order['id'] ?></h1>
                <span class="status-badge" style="background: <?= $statusColor ?>20; color: <?= $statusColor ?>;">
                    <?= $statusLabel ?>
                </span>
            </div>
            <div class="actions">
                <a href="<?= $basePath ?>/work-orders/edit/<?= $order['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="<?= $basePath ?>/work-orders" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-title">Información General</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Cliente</div>
                    <div class="info-value"><?= htmlspecialchars($order['client_name'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Vehículo</div>
                    <div class="info-value">
                        <?= htmlspecialchars(($order['brand'] ?? '') . ' ' . ($order['model'] ?? '')) ?><br>
                        <small style="color: var(--on-surface-variant);">
                            Patente: <?= htmlspecialchars($order['plate'] ?? 'N/A') ?>
                        </small>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Creación</div>
                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total</div>
                    <div class="info-value">$<?= number_format($order['total_cost'] ?? 0, 0, ',', '.') ?> CLP</div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($order['description'])): ?>
        <div class="card">
            <div class="card-title">Descripción del Problema</div>
            <p style="line-height: 1.6;"><?= nl2br(htmlspecialchars($order['description'])) ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($selectedServices)): ?>
        <div class="card">
            <div class="card-title">Servicios</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($selectedServices as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['service_name']) ?></td>
                        <td><?= $service['quantity'] ?></td>
                        <td>$<?= number_format($service['price'], 0, ',', '.') ?> CLP</td>
                        <td>$<?= number_format($service['price'] * $service['quantity'], 0, ',', '.') ?> CLP</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($selectedParts)): ?>
        <div class="card">
            <div class="card-title">Repuestos Utilizados</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Repuesto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($selectedParts as $part): ?>
                    <tr>
                        <td><?= htmlspecialchars($part['part_name'] ?? $part['name']) ?></td>
                        <td><?= $part['quantity'] ?></td>
                        <td>$<?= number_format($part['price'], 0, ',', '.') ?> CLP</td>
                        <td>$<?= number_format($part['price'] * $part['quantity'], 0, ',', '.') ?> CLP</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($assignments)): ?>
        <div class="card">
            <div class="card-title">Mecánicos Asignados</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mecánico</th>
                        <th>Fecha de Asignación</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?= htmlspecialchars($assignment['mechanic_name'] ?? 'N/A') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($assignment['assigned_at'])) ?></td>
                        <td><?= htmlspecialchars($assignment['notes'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
