<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Orden - Torque Studio ERP</title>
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
            max-width: 800px;
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
        input, select, textarea {
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
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary-container);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .services-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .service-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background-color: #0F1115;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .service-item input[type="checkbox"] {
            width: auto;
        }
        .service-item input[type="number"] {
            width: 80px;
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
        <a href="<?= $basePath ?>/reports" class="nav-item"><i class="fas fa-chart-bar"></i> Reportes</a>
        <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item"><i class="fas fa-book"></i> Manuales</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Nueva Orden de Trabajo</h1>
        </div>
        
        <div class="form-container">
            <form method="POST" action="<?= $basePath ?>/work-orders/create">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="client_id">Cliente *</label>
                        <select name="client_id" id="client_id" required>
                            <option value="">Seleccionar cliente</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= esc($client['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="vehicle_id">Vehículo *</label>
                        <select name="vehicle_id" id="vehicle_id" required>
                            <option value="">Seleccionar vehículo</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?= $vehicle['id'] ?>"><?= esc($vehicle['brand']) ?> <?= esc($vehicle['model']) ?> - <?= esc($vehicle['plate'] ?? 'S/P') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Estado Inicial</label>
                        <select name="status" id="status">
                            <option value="recepcion">Recepción</option>
                            <option value="diagnostico">Diagnóstico</option>
                            <option value="reparacion">Reparación</option>
                            <option value="terminado">Terminado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mechanic_id">Mecánico Asignado</label>
                        <select name="mechanic_id" id="mechanic_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($mechanics as $mechanic): ?>
                                <option value="<?= $mechanic['id'] ?>"><?= esc($mechanic['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Descripción del problema</label>
                    <textarea name="description" id="description" placeholder="Describe el problema del vehículo..."></textarea>
                </div>

                <div class="form-group">
                    <label for="problem_reported">Problema reportado por cliente</label>
                    <textarea name="problem_reported" id="problem_reported" placeholder="Síntomas reportados por el cliente"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="diagnosis">Diagnóstico inicial</label>
                        <textarea name="diagnosis" id="diagnosis" placeholder="Diagnóstico técnico inicial"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="priority">Prioridad</label>
                        <select name="priority" id="priority">
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                            <option value="critica">Crítica</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="estimated_delivery_at">Fecha estimada de entrega</label>
                    <input type="datetime-local" name="estimated_delivery_at" id="estimated_delivery_at">
                </div>

                <div class="services-section">
                    <label>Servicios a realizar</label>
                    <?php foreach ($services as $service): ?>
                        <div class="service-item">
                            <input type="checkbox" name="services[<?= $service['id'] ?>][id]" value="<?= $service['id'] ?>" id="service_<?= $service['id'] ?>">
                            <label for="service_<?= $service['id'] ?>" style="margin: 0; flex: 1;">
                                <?= esc($service['name']) ?> - $<?= number_format($service['price'], 0, ',', '.') ?> CLP
                            </label>
                            <input type="number" name="services[<?= $service['id'] ?>][quantity]" value="1" min="1" placeholder="Cantidad">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-primary">Crear Orden</button>
                    <a href="<?= $basePath ?>/work-orders" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
