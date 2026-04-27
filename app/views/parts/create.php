<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Repuesto - Torque Studio ERP</title>
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
            <h1>Nuevo Repuesto</h1>
        </div>
        
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="error"><?= esc($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?= $basePath ?>/parts/create">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code">Código *</label>
                        <input type="text" name="code" id="code" required maxlength="50" placeholder="ej: ACEITE-5W30">
                    </div>
                    <div class="form-group">
                        <label for="name">Nombre *</label>
                        <input type="text" name="name" id="name" required maxlength="150" placeholder="ej: Aceite Motor 5W-30">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" rows="3" placeholder="Descripción del repuesto"></textarea>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label for="category">Categoría *</label>
                        <select name="category" id="category" required>
                            <option value="">Seleccionar</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                            <?php endforeach; ?>
                            <option value="new">+ Nueva categoría</option>
                        </select>
                        <input type="text" name="category_new" id="category_new" style="margin-top: 8px; display: none;" placeholder="Nueva categoría">
                    </div>
                    <div class="form-group">
                        <label for="supplier">Proveedor</label>
                        <input type="text" name="supplier" id="supplier" maxlength="100" placeholder="Nombre del proveedor">
                    </div>
                    <div class="form-group">
                        <label for="location">Ubicación</label>
                        <input type="text" name="location" id="location" maxlength="50" placeholder="ej: Estante A1">
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label for="quantity">Cantidad Inicial *</label>
                        <input type="number" name="quantity" id="quantity" required min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="min_stock">Stock Mínimo *</label>
                        <input type="number" name="min_stock" id="min_stock" required min="1" value="5">
                    </div>
                    <div class="form-group">
                        <label for="unit_type">Unidad de Medida *</label>
                        <select name="unit_type" id="unit_type" required>
                            <option value="unidad">Unidad (u)</option>
                            <option value="litros">Litros (L)</option>
                            <option value="kilos">Kilogramos (kg)</option>
                            <option value="metros">Metros (m)</option>
                            <option value="pares">Pares</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="background: rgba(138,180,248,0.1); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="color: var(--primary);"><i class="fas fa-info-circle"></i> Ejemplo de Precios</label>
                        <p style="font-size: 13px; color: #c2c6d6; margin: 0;">
                            <strong>Valor Neto (Costo):</strong> Precio que pagas al proveedor por cada unidad<br>
                            <strong>Valor Venta:</strong> Precio que cobras al cliente (debe ser mayor al costo)<br>
                            <em>Ej: Costo $10.000 → Venta $15.000 (50% margen)</em>
                        </p>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label for="cost_price">Precio Costo (CLP) *</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;">$</span>
                            <input type="number" name="cost_price" id="cost_price" required step="1" min="0" value="0" style="padding-left: 28px;">
                        </div>
                        <small style="color: #888; font-size: 11px;">Precio que pagas al proveedor</small>
                    </div>
                    <div class="form-group">
                        <label for="sale_price">Precio Venta (CLP) *</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;">$</span>
                            <input type="number" name="sale_price" id="sale_price" required step="1" min="0" value="0" style="padding-left: 28px;">
                        </div>
                        <small style="color: #888; font-size: 11px;">Precio que cobras al cliente</small>
                    </div>
                    <div class="form-group">
                        <label>Margen Calculado</label>
                        <div id="margin_display" style="padding: 12px; background: rgba(40,167,69,0.2); border-radius: 8px; color: #4ade80; font-weight: 600; text-align: center;">
                            0%
                        </div>
                    </div>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-primary">Crear Repuesto</button>
                    <a href="<?= $basePath ?>/parts" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Manejo de nueva categoría
        document.getElementById('category').addEventListener('change', function() {
            var newInput = document.getElementById('category_new');
            if (this.value === 'new') {
                newInput.style.display = 'block';
                newInput.required = true;
                newInput.focus();
            } else {
                newInput.style.display = 'none';
                newInput.required = false;
                newInput.value = '';
            }
        });
        
        // Cálculo de margen en tiempo real
        function calculateMargin() {
            var cost = parseFloat(document.getElementById('cost_price').value) || 0;
            var sale = parseFloat(document.getElementById('sale_price').value) || 0;
            var marginDisplay = document.getElementById('margin_display');
            
            if (cost > 0 && sale > 0) {
                var margin = ((sale - cost) / cost) * 100;
                var profit = sale - cost;
                marginDisplay.innerHTML = margin.toFixed(1) + '%<br><small style="font-size: 10px;">Ganancia: $' + profit.toLocaleString('es-CL') + '</small>';
                
                if (margin < 0) {
                    marginDisplay.style.background = 'rgba(248,113,113,0.2)';
                    marginDisplay.style.color = '#f87171';
                } else if (margin < 20) {
                    marginDisplay.style.background = 'rgba(251,191,36,0.2)';
                    marginDisplay.style.color = '#fbbf24';
                } else {
                    marginDisplay.style.background = 'rgba(74,222,128,0.2)';
                    marginDisplay.style.color = '#4ade80';
                }
            } else {
                marginDisplay.innerHTML = '0%';
                marginDisplay.style.background = 'rgba(40,167,69,0.2)';
                marginDisplay.style.color = '#4ade80';
            }
        }
        
        document.getElementById('cost_price').addEventListener('input', calculateMargin);
        document.getElementById('sale_price').addEventListener('input', calculateMargin);
    </script>
</body>
</html>
