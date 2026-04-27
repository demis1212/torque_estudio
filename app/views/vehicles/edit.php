<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vehículo - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --surface: #10131a;
            --surface-container-high: #272a31;
            --on-surface: #e1e2ec;
            --primary: #adc6ff;
            --primary-container: #4d8eff;
            --on-primary-container: #00285d;
            --background: #10131a;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: var(--background);
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
            display: flex;
            flex-direction: column;
        }
        .sidebar h2 {
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 32px;
            color: var(--primary);
        }
        .nav-item {
            display: block;
            color: #c2c6d6;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        .header {
            margin-bottom: 32px;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
        }
        .form-container {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
            color: #c2c6d6;
        }
        input, select {
            width: 100%;
            padding: 12px;
            background-color: #0F1115;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
            outline: none;
        }
        input:focus, select:focus {
            border-color: var(--primary-container);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .btn-container {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn-primary {
            padding: 12px 24px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-secondary {
            padding: 12px 24px;
            background-color: transparent;
            color: #c2c6d6;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Torque Studio</h2>
        <a href="<?= $basePath ?>/dashboard" class="nav-item">Dashboard</a>
        <a href="<?= $basePath ?>/clients" class="nav-item">Clientes</a>
        <a href="<?= $basePath ?>/vehicles" class="nav-item">Vehículos</a>
        <a href="<?= $basePath ?>/work-orders" class="nav-item">Órdenes</a>
        <a href="<?= $basePath ?>/services" class="nav-item">Servicios</a>
        <a href="<?= $basePath ?>/parts" class="nav-item">Inventario</a>
        <a href="<?= $basePath ?>/reports" class="nav-item">📊 Reportes</a>
        <a href="<?= $basePath ?>/tools" class="nav-item">🔧 Herramientas</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item">📚 Manuales</a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item">🔍 Decodificador VIN</a>
        <a href="<?= $basePath ?>/dtc" class="nav-item">🔧 DTC Codes</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Editar Vehículo</h1>
        </div>
        
        <div class="form-container">
            <form method="POST" action="<?= $basePath ?>/vehicles/edit/<?= $vehicle['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-group">
                    <label for="client_id">Cliente *</label>
                    <select name="client_id" id="client_id" required>
                        <option value="">Seleccionar cliente</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" <?= $client['id'] == $vehicle['client_id'] ? 'selected' : '' ?>>
                                <?= esc($client['name']) ?> - <?= esc($client['phone'] ?? 'Sin teléfono') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="vin">VIN *</label>
                        <input type="text" name="vin" id="vin" value="<?= esc($vehicle['vin']) ?>" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="plate">Placa</label>
                        <input type="text" name="plate" id="plate" value="<?= esc($vehicle['plate'] ?? '') ?>" maxlength="20">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="brand">Marca *</label>
                        <input type="text" name="brand" id="brand" value="<?= esc($vehicle['brand']) ?>" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="model">Modelo *</label>
                        <input type="text" name="model" id="model" value="<?= esc($vehicle['model']) ?>" required maxlength="50">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" name="color" id="color" value="<?= esc($vehicle['color'] ?? '') ?>" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="mileage">Kilometraje</label>
                        <input type="number" name="mileage" id="mileage" value="<?= esc($vehicle['mileage'] ?? '') ?>" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="year">Año *</label>
                        <input type="number" name="year" id="year" value="<?= esc($vehicle['year']) ?>" required min="1900" max="2030">
                    </div>
                    <div class="form-group">
                        <label for="engine">Motor</label>
                        <input type="text" name="engine" id="engine" value="<?= esc($vehicle['engine'] ?? '') ?>" maxlength="50">
                    </div>
                </div>

                <div class="form-group">
                    <label for="transmission">Transmisión</label>
                    <select name="transmission" id="transmission">
                        <option value="">Seleccionar</option>
                        <option value="Manual" <?= $vehicle['transmission'] == 'Manual' ? 'selected' : '' ?>>Manual</option>
                        <option value="Automática" <?= $vehicle['transmission'] == 'Automática' ? 'selected' : '' ?>>Automática</option>
                        <option value="CVT" <?= $vehicle['transmission'] == 'CVT' ? 'selected' : '' ?>>CVT</option>
                        <option value="DSG" <?= $vehicle['transmission'] == 'DSG' ? 'selected' : '' ?>>DSG</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notas del vehículo</label>
                    <input type="text" name="notes" id="notes" value="<?= esc($vehicle['notes'] ?? '') ?>" maxlength="255" placeholder="Observaciones del vehículo">
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-primary">Actualizar Vehículo</button>
                    <a href="<?= $basePath ?>/vehicles" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
