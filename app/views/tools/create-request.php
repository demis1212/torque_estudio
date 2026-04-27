<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

// Si se pasa un tool_id por GET, preseleccionar esa herramienta
$selectedTool = $_GET['tool_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Herramienta - Torque Studio ERP</title>
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
        .nav-item i { width: 20px; text-align: center; }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        .header {
            margin-bottom: 32px;
        }
        .header h1 { margin: 0; font-size: 28px; }
        .form-container {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            background-color: #0F1115;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            font-size: 14px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        .tool-info {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .tool-name {
            font-weight: 600;
            font-size: 16px;
        }
        .tool-cost {
            color: var(--primary);
            font-weight: 600;
        }
        .auth-warning {
            background-color: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            color: #ffc107;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
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
            display: inline-block;
            margin-left: 12px;
        }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #888;
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
            <a href="<?= $basePath ?>/tools" class="nav-item active"><i class="fas fa-tools"></i> Herramientas</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Herramientas</div>
            <a href="<?= $basePath ?>/manuals" class="nav-item"><i class="fas fa-book"></i> Manuales</a>
            <a href="<?= $basePath ?>/vin-decoder" class="nav-item"><i class="fas fa-search"></i> VIN Decoder</a>
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
            <h1>📋 Solicitar Herramienta de Bodega</h1>
        </div>

        <div class="form-container">
            <?php if (empty($available_tools)): ?>
                <div class="empty-state">
                    <h3>No hay herramientas disponibles</h3>
                    <p>Todas las herramientas de bodega están prestadas o en mantenimiento</p>
                    <a href="<?= $basePath ?>/tools/warehouse" class="btn-secondary">Ver Inventario</a>
                </div>
            <?php else: ?>
                <form method="POST" action="<?= $basePath ?>/tools/requests/create">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label>Herramienta *</label>
                        <select name="warehouse_tool_id" id="tool_select" required onchange="updateToolInfo()">
                            <option value="">Seleccione una herramienta</option>
                            <?php foreach ($available_tools as $tool): ?>
                                <option value="<?= $tool['id'] ?>" 
                                        data-cost="<?= $tool['cost'] ?>"
                                        data-auth="<?= $tool['requires_auth'] ? '1' : '0' ?>"
                                        <?= $selectedTool == $tool['id'] ? 'selected' : '' ?>>
                                    <?= esc($tool['name']) ?> <?= $tool['code'] ? '(' . esc($tool['code']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="tool-info" class="tool-info" style="display: none;">
                        <div class="tool-name" id="tool-name"></div>
                        <div class="tool-cost" id="tool-cost"></div>
                        <div id="auth-warning" class="auth-warning" style="display: none;">
                            ⚠️ Esta herramienta requiere autorización del administrador
                        </div>
                    </div>

                    <!-- Fechas automáticas, no editables -->
                    <input type="hidden" name="request_date" value="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="return_date" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    
                    <div class="form-group">
                        <label>Fecha de Solicitud</label>
                        <input type="date" value="<?= date('Y-m-d') ?>" disabled style="background: rgba(255,255,255,0.05); color: var(--on-surface-variant);">
                        <small style="color: var(--on-surface-variant); font-size: 12px;">Fecha automática</small>
                    </div>

                    <div class="form-group">
                        <label>Fecha Estimada de Devolución</label>
                        <input type="date" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" disabled style="background: rgba(255,255,255,0.05); color: var(--on-surface-variant);">
                        <small style="color: var(--on-surface-variant); font-size: 12px;">1 día por defecto (el administrador puede modificar al entregar)</small>
                    </div>

                    <div class="form-group">
                        <label>Motivo / Notas</label>
                        <textarea name="notes" placeholder="Indica para qué necesitas esta herramienta..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary">Enviar Solicitud</button>
                    <a href="<?= $basePath ?>/tools/warehouse" class="btn-secondary">Cancelar</a>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateToolInfo() {
            const select = document.getElementById('tool_select');
            const info = document.getElementById('tool-info');
            const name = document.getElementById('tool-name');
            const cost = document.getElementById('tool-cost');
            const authWarning = document.getElementById('auth-warning');
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                const toolCost = option.getAttribute('data-cost');
                const requiresAuth = option.getAttribute('data-auth') === '1';
                
                name.textContent = option.text;
                cost.textContent = 'Valor: $' + parseFloat(toolCost).toFixed(2);
                authWarning.style.display = requiresAuth ? 'block' : 'none';
                info.style.display = 'block';
            } else {
                info.style.display = 'none';
            }
        }
        
        // Llamar al cargar si hay selección
        document.addEventListener('DOMContentLoaded', updateToolInfo);
    </script>
</body>
</html>
