<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Repuesto - Torque Studio ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --surface: #10131a;
            --on-surface: #e1e2ec;
            --primary: #adc6ff;
            --primary-container: #4d8eff;
            --background: #10131a;
            --error: #ffb4ab;
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
        .nav-item:hover { background-color: rgba(255, 255, 255, 0.05); }
        .nav-item.active { background-color: var(--primary-container); color: #fff; }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        .header { margin-bottom: 32px; }
        .header h1 { margin: 0; font-size: 32px; }
        .form-container {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            max-width: 700px;
        }
        .form-group { margin-bottom: 20px; }
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
        input:focus, select:focus, textarea:focus { border-color: var(--primary-container); }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
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
        .error {
            background-color: rgba(220, 53, 69, 0.2);
            color: var(--error);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        .stock-display {
            background-color: rgba(77, 142, 255, 0.1);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 3px solid var(--primary-container);
        }
        .stock-display strong {
            color: var(--primary);
            font-size: 24px;
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
        <a href="<?= $basePath ?>/parts" class="nav-item active">Inventario</a>
        <a href="<?= $basePath ?>/reports" class="nav-item">📊 Reportes</a>
        <a href="<?= $basePath ?>/tools" class="nav-item">🔧 Herramientas</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item">📚 Manuales</a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item">🔍 Decodificador VIN</a>
        <a href="<?= $basePath ?>/dtc" class="nav-item">🔧 DTC Codes</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Editar Repuesto</h1>
        </div>
        
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="error"><?= esc($error) ?></div>
            <?php endif; ?>

            <div class="stock-display">
                Stock actual: <strong><?= $part['quantity'] ?></strong> unidades
                (Mínimo: <?= $part['min_stock'] ?>)
            </div>
            
            <form method="POST" action="<?= $basePath ?>/parts/edit/<?= $part['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code">Código *</label>
                        <input type="text" name="code" id="code" value="<?= esc($part['code']) ?>" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="name">Nombre *</label>
                        <input type="text" name="name" id="name" value="<?= esc($part['name']) ?>" required maxlength="150">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" rows="3"><?= esc($part['description']) ?></textarea>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label for="category">Categoría *</label>
                        <select name="category" id="category" required>
                            <option value="">Seleccionar</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= esc($cat) ?>" <?= $part['category'] === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="supplier">Proveedor</label>
                        <input type="text" name="supplier" id="supplier" value="<?= esc($part['supplier']) ?>" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="location">Ubicación</label>
                        <input type="text" name="location" id="location" value="<?= esc($part['location']) ?>" maxlength="50">
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label for="quantity">Cantidad *</label>
                        <input type="number" name="quantity" id="quantity" value="<?= $part['quantity'] ?>" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="min_stock">Stock Mínimo *</label>
                        <input type="number" name="min_stock" id="min_stock" value="<?= $part['min_stock'] ?>" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="unit_type">Unidad de Medida *</label>
                        <select name="unit_type" id="unit_type" required>
                            <option value="unidad" <?= ($part['unit_type'] ?? 'unidad') === 'unidad' ? 'selected' : '' ?>>Unidad (u)</option>
                            <option value="litros" <?= ($part['unit_type'] ?? '') === 'litros' ? 'selected' : '' ?>>Litros (L)</option>
                            <option value="kilos" <?= ($part['unit_type'] ?? '') === 'kilos' ? 'selected' : '' ?>>Kilogramos (kg)</option>
                            <option value="metros" <?= ($part['unit_type'] ?? '') === 'metros' ? 'selected' : '' ?>>Metros (m)</option>
                            <option value="pares" <?= ($part['unit_type'] ?? '') === 'pares' ? 'selected' : '' ?>>Pares</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label for="cost_price">Precio Costo (CLP) *</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;">$</span>
                            <input type="number" name="cost_price" id="cost_price" value="<?= $part['cost_price'] ?>" required step="1" min="0" style="padding-left: 28px;">
                        </div>
                        <small style="color: #888; font-size: 11px;">Precio que pagas al proveedor</small>
                    </div>
                    <div class="form-group">
                        <label for="sale_price">Precio Venta (CLP) *</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;">$</span>
                            <input type="number" name="sale_price" id="sale_price" value="<?= $part['sale_price'] ?>" required step="1" min="0" style="padding-left: 28px;">
                        </div>
                        <small style="color: #888; font-size: 11px;">Precio que cobras al cliente</small>
                    </div>
                    <div class="form-group">
                        <label>Margen Actual</label>
                        <div style="padding: 12px; background: rgba(40,167,69,0.2); border-radius: 8px; color: #4ade80; font-weight: 600; text-align: center;">
                            <?php 
                            $cost = floatval($part['cost_price']);
                            $sale = floatval($part['sale_price']);
                            if ($cost > 0 && $sale > 0) {
                                $margin = (($sale - $cost) / $cost) * 100;
                                $profit = $sale - $cost;
                                echo number_format($margin, 1) . '%<br><small style="font-size: 10px;">Ganancia: $' . number_format($profit, 0, ',', '.') . '</small>';
                            } else {
                                echo '0%';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-primary">Actualizar Repuesto</button>
                    <a href="<?= $basePath ?>/parts" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
