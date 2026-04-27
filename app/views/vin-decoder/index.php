<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decodificador VIN - Torque Studio ERP</title>
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
        .header h1 { margin: 0; font-size: 32px; }
        .decoder-box {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            max-width: 800px;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            color: #c2c6d6;
            margin-bottom: 8px;
        }
        .vin-input {
            width: 100%;
            padding: 16px;
            background-color: #0F1115;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            font-family: 'Inter', sans-serif;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-sizing: border-box;
        }
        .vin-input:focus {
            outline: none;
            border-color: var(--primary-container);
        }
        .btn-primary {
            padding: 14px 32px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
        }
        .results-box {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            max-width: 800px;
        }
        .results-box h3 {
            margin: 0 0 20px 0;
            font-size: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 12px;
        }
        .vin-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .info-item {
            background-color: rgba(255, 255, 255, 0.03);
            padding: 16px;
            border-radius: 8px;
        }
        .info-item label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 4px;
        }
        .info-item span {
            font-size: 16px;
            font-weight: 600;
        }
        .error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ffb4ab;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .vin-structure {
            background-color: rgba(77, 142, 255, 0.1);
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 14px;
            text-align: center;
        }
        .vin-structure span {
            display: inline-block;
            padding: 4px 8px;
            margin: 2px;
            border-radius: 4px;
        }
        .wmi { background-color: #4d8eff; }
        .vds { background-color: #28a745; }
        .vis { background-color: #ffc107; color: #000; }
        .actions {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 12px;
        }
        .btn-secondary {
            padding: 12px 24px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #c2c6d6;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
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
            <a href="<?= $basePath ?>/vin-decoder" class="nav-item active"><i class="fas fa-search"></i> VIN Decoder</a>
            <a href="<?= $basePath ?>/dtc" class="nav-item"><i class="fas fa-exclamation-triangle"></i> DTC Codes</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Administración</div>
            <a href="<?= $basePath ?>/reports" class="nav-item"><i class="fas fa-chart-bar"></i> Reportes</a>
            <?php if($userRole == 1): ?>
            <a href="<?= $basePath ?>/users" class="nav-item"><i class="fas fa-user-cog"></i> Usuarios</a>
            <a href="<?= $basePath ?>/settings" class="nav-item"><i class="fas fa-cog"></i> Configuración</a>
            <?php endif; ?>
        </nav>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>🔍 Decodificador VIN</h1>
        </div>

        <div class="decoder-box">
            <p style="color: #888; margin-bottom: 20px;">
                Introduce el número VIN (Vehicle Identification Number) de 17 caracteres para obtener información del vehículo.
            </p>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?= esc($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?= $basePath ?>/vin-decoder">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-group">
                    <label>Número VIN (17 caracteres)</label>
                    <input type="text" name="vin" class="vin-input" maxlength="17" placeholder="Ej: 1HGCM82633A123456" value="<?= esc($vin ?? '') ?>" required>
                </div>
                
                <button type="submit" class="btn-primary">Decodificar VIN</button>
            </form>
        </div>

        <?php if (!empty($decoded)): ?>
        <div class="results-box">
            <h3>Información Decodificada</h3>
            
            <div class="vin-structure">
                <span class="wmi"><?= substr($vin, 0, 3) ?></span>
                <span class="vds"><?= substr($vin, 3, 6) ?></span>
                <span class="vis"><?= substr($vin, 9, 8) ?></span>
                <br>
                <small style="color: #888;">WMI (3) | VDS (6) | VIS (8)</small>
            </div>
            
            <div class="vin-info">
                <div class="info-item">
                    <label>Fabricante</label>
                    <span><?= esc($decoded['manufacturer']) ?></span>
                </div>
                <div class="info-item">
                    <label>País de Origen</label>
                    <span><?= esc($decoded['country']) ?></span>
                </div>
                <div class="info-item">
                    <label>Año del Modelo</label>
                    <span><?= esc($decoded['year']) ?></span>
                </div>
                <div class="info-item">
                    <label>Tipo de Carrocería</label>
                    <span><?= esc($decoded['body_type']) ?></span>
                </div>
                <div class="info-item">
                    <label>Planta de Ensamblaje</label>
                    <span><?= esc($decoded['plant']) ?></span>
                </div>
                <div class="info-item">
                    <label>Número de Serie</label>
                    <span><?= esc($decoded['serial']) ?></span>
                </div>
            </div>
            
            <div class="actions">
                <a href="<?= $basePath ?>/vehicles/create?vin=<?= urlencode($vin) ?>&brand=<?= urlencode($decoded['manufacturer']) ?>&year=<?= urlencode($decoded['year']) ?>" class="btn-primary">+ Crear Vehículo con estos datos</a>
                <a href="<?= $basePath ?>/vin-decoder/lookup" class="btn-secondary">🔎 Buscar en base de datos</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
