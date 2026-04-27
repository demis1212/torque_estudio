<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$statusLabels = [
    'recepcion' => 'Recepción',
    'diagnostico' => 'Diagnóstico',
    'reparacion' => 'Reparación',
    'terminado' => 'Terminado'
];

// Create array of selected service IDs for easy checking
$selectedServiceIds = [];
foreach ($selectedServices as $s) {
    $selectedServiceIds[$s['service_id']] = $s['quantity'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Orden #<?= $order['id'] ?> - Torque Studio ERP</title>
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
            font-size: 28px;
        }
        .order-info {
            background-color: #1F2430;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .order-info p {
            margin: 8px 0;
            color: #c2c6d6;
        }
        .order-info strong {
            color: var(--on-surface);
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
        .selected-services {
            background-color: #0F1115;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .selected-services h4 {
            margin: 0 0 12px 0;
            font-size: 14px;
            color: #a7b6cc;
        }
        .selected-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .total-cost {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px solid var(--primary-container);
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
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
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Editar Orden de Trabajo #<?= $order['id'] ?></h1>
        </div>

        <div class="order-info">
            <p><strong>Cliente:</strong> <?= esc($order['client_name']) ?></p>
            <p><strong>Vehículo:</strong> <?= esc($order['brand']) ?> <?= esc($order['model']) ?> (<?= esc($order['plate'] ?? 'S/P') ?>)</p>
            <p><strong>Creado por:</strong> <?= esc($order['user_name']) ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
        </div>
        
        <div class="form-container">
            <form method="POST" action="<?= $basePath ?>/work-orders/edit/<?= $order['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="client_id">Cliente *</label>
                        <select name="client_id" id="client_id" required>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" <?= $client['id'] == $order['client_id'] ? 'selected' : '' ?>>
                                    <?= esc($client['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="vehicle_id">Vehículo *</label>
                        <select name="vehicle_id" id="vehicle_id" required>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?= $vehicle['id'] ?>" <?= $vehicle['id'] == $order['vehicle_id'] ? 'selected' : '' ?>>
                                    <?= esc($vehicle['brand']) ?> <?= esc($vehicle['model']) ?> - <?= esc($vehicle['plate'] ?? 'S/P') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Estado</label>
                    <select name="status" id="status">
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $order['status'] == $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Descripción del problema</label>
                    <textarea name="description" id="description" placeholder="Describe el problema del vehículo..."><?= esc($order['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="problem_reported">Problema reportado por cliente</label>
                    <textarea name="problem_reported" id="problem_reported" placeholder="Síntomas reportados por el cliente"><?= esc($order['problem_reported'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="diagnosis">Diagnóstico</label>
                        <textarea name="diagnosis" id="diagnosis" placeholder="Diagnóstico técnico"><?= esc($order['diagnosis'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="priority">Prioridad</label>
                        <select name="priority" id="priority">
                            <option value="baja" <?= ($order['priority'] ?? 'media') === 'baja' ? 'selected' : '' ?>>Baja</option>
                            <option value="media" <?= ($order['priority'] ?? 'media') === 'media' ? 'selected' : '' ?>>Media</option>
                            <option value="alta" <?= ($order['priority'] ?? 'media') === 'alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="critica" <?= ($order['priority'] ?? 'media') === 'critica' ? 'selected' : '' ?>>Crítica</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="estimated_delivery_at">Fecha estimada de entrega</label>
                    <input type="datetime-local" name="estimated_delivery_at" id="estimated_delivery_at" value="<?= !empty($order['estimated_delivery_at']) ? date('Y-m-d\\TH:i', strtotime($order['estimated_delivery_at'])) : '' ?>">
                </div>

                <?php if (!empty($selectedServices)): ?>
                <div class="selected-services">
                    <h4>Servicios Actuales</h4>
                    <?php foreach ($selectedServices as $s): ?>
                        <div class="selected-item">
                            <span><?= esc($s['service_name']) ?> x<?= $s['quantity'] ?></span>
                            <span>$<?= number_format($s['price'] * $s['quantity'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="total-cost">
                        Total: $<?= number_format($order['total_cost'], 2) ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="services-section">
                    <label>Actualizar Servicios</label>
                    <?php foreach ($services as $service): ?>
                        <div class="service-item">
                            <?php 
                            $isChecked = isset($selectedServiceIds[$service['id']]);
                            $qty = $isChecked ? $selectedServiceIds[$service['id']] : 1;
                            ?>
                            <input type="checkbox" name="services[<?= $service['id'] ?>][id]" value="<?= $service['id'] ?>" id="service_<?= $service['id'] ?>" <?= $isChecked ? 'checked' : '' ?>>
                            <label for="service_<?= $service['id'] ?>" style="margin: 0; flex: 1;">
                                <?= esc($service['name']) ?> - $<?= number_format($service['price'], 2) ?>
                            </label>
                            <input type="number" name="services[<?= $service['id'] ?>][quantity]" value="<?= $qty ?>" min="1" placeholder="Cantidad">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-primary">Actualizar Orden</button>
                    <a href="<?= $basePath ?>/reports/invoice/<?= $order['id'] ?>" class="btn-secondary" target="_blank">📄 Ver Factura</a>
                    <a href="<?= $basePath ?>/work-orders" class="btn-secondary">Cancelar</a>
                </div>
            </form>

            <!-- Mechanics Assignment Section -->
            <div class="section-container" style="margin-top: 32px; background-color: #1F2430; border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <h3 style="margin-top: 0; margin-bottom: 16px;">👨‍🔧 Mecánicos Asignados</h3>
                
                <?php if (!empty($assignments)): ?>
                    <div style="margin-bottom: 20px;">
                        <?php foreach ($assignments as $assignment): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background-color: rgba(77, 142, 255, 0.1); border-radius: 8px; margin-bottom: 8px;">
                                <div>
                                    <strong><?= esc($assignment['mechanic_name']) ?></strong>
                                    <?php if ($assignment['notes']): ?>
                                        <span style="color: #888; font-size: 12px;"> - <?= esc($assignment['notes']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="<?= $basePath ?>/work-orders/<?= $order['id'] ?>/remove-mechanic/<?= $assignment['mechanic_id'] ?>" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" style="background-color: #dc3545; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px;" onclick="return confirm('¿Quitar este mecánico?')">Quitar</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #888;">No hay mecánicos asignados.</p>
                <?php endif; ?>

                <form method="POST" action="<?= $basePath ?>/work-orders/<?= $order['id'] ?>/assign-mechanic">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div style="display: flex; gap: 12px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 12px; text-transform: uppercase; margin-bottom: 8px; color: #c2c6d6;">Asignar Mecánico</label>
                            <select name="mechanic_id" style="width: 100%; padding: 12px; background-color: #0F1115; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--on-surface);">
                                <option value="">Seleccionar mecánico</option>
                                <?php foreach ($mechanics as $mechanic): ?>
                                    <option value="<?= $mechanic['id'] ?>"><?= esc($mechanic['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 12px; text-transform: uppercase; margin-bottom: 8px; color: #c2c6d6;">Notas</label>
                            <input type="text" name="notes" placeholder="Notas de asignación" style="width: 100%; padding: 12px; background-color: #0F1115; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--on-surface);">
                        </div>
                        <button type="submit" style="padding: 12px 20px; background-color: var(--primary-container); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Asignar</button>
                    </div>
                </form>
            </div>

            <!-- Parts Section -->
            <div class="section-container" style="margin-top: 24px; background-color: #1F2430; border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <h3 style="margin-top: 0; margin-bottom: 16px;">🔧 Repuestos Utilizados</h3>
                
                <?php if (!empty($selectedParts)): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                <th style="text-align: left; padding: 12px; font-size: 12px; text-transform: uppercase; color: #a7b6cc;">Código</th>
                                <th style="text-align: left; padding: 12px; font-size: 12px; text-transform: uppercase; color: #a7b6cc;">Nombre</th>
                                <th style="text-align: center; padding: 12px; font-size: 12px; text-transform: uppercase; color: #a7b6cc;">Cant.</th>
                                <th style="text-align: right; padding: 12px; font-size: 12px; text-transform: uppercase; color: #a7b6cc;">Precio</th>
                                <th style="text-align: right; padding: 12px; font-size: 12px; text-transform: uppercase; color: #a7b6cc;">Total</th>
                                <th style="padding: 12px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selectedParts as $p): ?>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td style="padding: 12px;"><?= esc($p['code']) ?></td>
                                    <td style="padding: 12px;"><?= esc($p['part_name']) ?></td>
                                    <td style="padding: 12px; text-align: center;"><?= $p['quantity'] ?></td>
                                    <td style="padding: 12px; text-align: right;">$<?= number_format($p['price'], 2) ?></td>
                                    <td style="padding: 12px; text-align: right;">$<?= number_format($p['quantity'] * $p['price'], 2) ?></td>
                                    <td style="padding: 12px; text-align: right;">
                                        <form method="POST" action="<?= $basePath ?>/work-orders/<?= $order['id'] ?>/remove-part/<?= $p['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" style="background-color: #dc3545; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px;" onclick="return confirm('¿Quitar este repuesto?')">Quitar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #888;">No se han agregado repuestos.</p>
                <?php endif; ?>

                <form method="POST" action="<?= $basePath ?>/work-orders/<?= $order['id'] ?>/add-part">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div style="display: flex; gap: 12px; align-items: flex-end;">
                        <div style="flex: 2;">
                            <label style="display: block; font-size: 12px; text-transform: uppercase; margin-bottom: 8px; color: #c2c6d6;">Agregar Repuesto</label>
                            <select name="part_id" style="width: 100%; padding: 12px; background-color: #0F1115; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--on-surface);">
                                <option value="">Seleccionar repuesto</option>
                                <?php foreach ($parts as $part): ?>
                                    <?php if ($part['quantity'] > 0): ?>
                                        <option value="<?= $part['id'] ?>"><?= esc($part['code']) ?> - <?= esc($part['name']) ?> (Stock: <?= $part['quantity'] ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 12px; text-transform: uppercase; margin-bottom: 8px; color: #c2c6d6;">Cantidad</label>
                            <input type="number" name="quantity" value="1" min="1" style="width: 100%; padding: 12px; background-color: #0F1115; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--on-surface);">
                        </div>
                        <button type="submit" style="padding: 12px 20px; background-color: var(--primary-container); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
