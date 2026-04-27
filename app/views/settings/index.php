<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$groupLabels = [
    'general' => 'General',
    'billing' => 'Facturación',
    'work_orders' => 'Órdenes de Trabajo',
    'inventory' => 'Inventario',
    'notifications' => 'Notificaciones'
];

$groupIcons = [
    'general' => 'fa-cog',
    'billing' => 'fa-dollar-sign',
    'work_orders' => 'fa-wrench',
    'inventory' => 'fa-boxes',
    'notifications' => 'fa-bell'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .sidebar h2 { font-size: 20px; margin-top: 0; margin-bottom: 32px; color: var(--primary); }
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
        .nav-item i { width: 20px; text-align: center; margin-right: 8px; }
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
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
        }
        .settings-card {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .settings-card h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #c2c6d6;
            margin-bottom: 6px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            background-color: #0F1115;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }
        .form-group small {
            color: #888;
            font-size: 12px;
            display: block;
            margin-top: 4px;
        }
        .btn-primary {
            padding: 12px 24px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
        }
        .btn-secondary {
            padding: 10px 20px;
            background-color: transparent;
            color: #c2c6d6;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .actions {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 12px;
        }
        .info-box {
            background-color: rgba(77, 142, 255, 0.1);
            border: 1px solid rgba(77, 142, 255, 0.2);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #c2c6d6;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-wrench" style="margin-right: 8px;"></i>Torque Studio</h2>
        <a href="<?= $basePath ?>/dashboard" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="<?= $basePath ?>/clients" class="nav-item"><i class="fas fa-users"></i> Clientes</a>
        <a href="<?= $basePath ?>/vehicles" class="nav-item"><i class="fas fa-car"></i> Vehiculos</a>
        <a href="<?= $basePath ?>/work-orders" class="nav-item"><i class="fas fa-clipboard-list"></i> Ordenes</a>
        <a href="<?= $basePath ?>/services" class="nav-item"><i class="fas fa-wrench"></i> Servicios</a>
        <a href="<?= $basePath ?>/workshop-ops" class="nav-item"><i class="fas fa-stopwatch"></i> Operación Inteligente</a>
        <a href="<?= $basePath ?>/parts" class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= $basePath ?>/reports" class="nav-item"><i class="fas fa-chart-bar"></i> Reportes</a>
        <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item"><i class="fas fa-book"></i> Manuales</a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item"><i class="fas fa-search"></i> Decodificador VIN</a>
        <a href="<?= $basePath ?>/dtc" class="nav-item"><i class="fas fa-exclamation-triangle"></i> DTC Codes</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/settings" class="nav-item active"><i class="fas fa-cog"></i> Configuracion</a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-cog"></i> Configuracion del Sistema</h1>
            <a href="<?= $basePath ?>/dashboard" class="btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
        </div>

        <div class="info-box">
            <p>Aquí puedes configurar los parámetros generales del taller. Los cambios se aplican inmediatamente.</p>
        </div>

        <form method="POST" action="<?= $basePath ?>/settings/update">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="settings-grid">
                <?php foreach ($grouped_settings as $group => $groupSettings): ?>
                    <div class="settings-card">
                        <h3>
                            <i class="fas <?= $groupIcons[$group] ?? 'fa-cog' ?>"></i>
                            <?= $groupLabels[$group] ?? ucfirst($group) ?>
                        </h3>
                        
                        <?php foreach ($groupSettings as $setting): ?>
                            <div class="form-group">
                                <label for="<?= $setting['key'] ?>">
                                    <?= ucwords(str_replace('_', ' ', $setting['key'])) ?>
                                </label>
                                <input 
                                    type="text" 
                                    id="<?= $setting['key'] ?>" 
                                    name="settings[<?= $setting['key'] ?>]" 
                                    value="<?= esc($setting['value']) ?>"
                                >
                                <?php if ($setting['description']): ?>
                                    <small><?= esc($setting['description']) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">💾 Guardar Cambios</button>
                <a href="<?= $basePath ?>/settings/create" class="btn-secondary">+ Nueva Configuración</a>
            </div>
        </form>
    </div>
</body>
</html>
