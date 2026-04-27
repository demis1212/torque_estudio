<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
$userRole = $user_role ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Compra de Herramienta - Torque Studio ERP</title>
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
        .header { margin-bottom: 32px; }
        .header h1 { margin: 0; font-size: 28px; font-family: 'Space Grotesk', sans-serif; }
        
        .form-container { background: var(--surface-container); border: 1px solid var(--outline); border-radius: 16px; padding: 32px; max-width: 600px; }
        .form-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--on-surface); }
        label .required { color: var(--danger); margin-left: 4px; }
        input, textarea, select { width: 100%; padding: 12px 16px; background: var(--surface); border: 1px solid var(--outline); border-radius: 10px; color: var(--on-surface); font-size: 14px; transition: border-color 0.2s; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--primary); }
        textarea { min-height: 100px; resize: vertical; }
        
        .btn-primary { padding: 14px 28px; background: linear-gradient(135deg, var(--success) 0%, #22c55e 100%); color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 14px; transition: transform 0.2s, box-shadow 0.2s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(74, 222, 128, 0.3); }
        .btn-secondary { padding: 14px 28px; background: transparent; color: var(--on-surface-variant); border: 1px solid var(--outline); border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 14px; text-decoration: none; display: inline-block; margin-left: 12px; }
        .btn-secondary:hover { background: rgba(255,255,255,0.05); color: var(--on-surface); }
        
        .info-box { background: rgba(138, 180, 248, 0.1); border: 1px solid rgba(138, 180, 248, 0.3); border-radius: 10px; padding: 16px; margin-bottom: 24px; font-size: 14px; }
        .info-box i { color: var(--primary); margin-right: 8px; }
        
        .error { background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.3); color: var(--danger); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .success { background: rgba(74, 222, 128, 0.1); border: 1px solid rgba(74, 222, 128, 0.3); color: var(--success); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
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
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-shopping-cart" style="margin-right: 12px; color: var(--success);"></i>Solicitar Compra de Herramienta</h1>
        </div>

        <div class="form-container">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Completa este formulario para solicitar la compra de una herramienta. El administrador recibirá una notificación.
            </div>

            <?php if (!empty($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= $basePath ?>/tools/purchase-request">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-group">
                    <label>Nombre de la Herramienta <span class="required">*</span></label>
                    <input type="text" name="tool_name" placeholder="Ej: Llave de torque digital" required>
                </div>

                <div class="form-group">
                    <label>Marca Preferida</label>
                    <input type="text" name="brand" placeholder="Ej: Snap-on, Craftsman, etc.">
                </div>

                <div class="form-group">
                    <label>Modelo/Especificaciones</label>
                    <input type="text" name="model" placeholder="Ej: 1/2 drive, 10-150 ft-lb">
                </div>

                <div class="form-group">
                    <label>Precio Estimado ($)</label>
                    <input type="number" name="estimated_price" placeholder="0.00" step="0.01" min="0">
                </div>

                <div class="form-group">
                    <label>Prioridad</label>
                    <select name="priority">
                        <option value="baja">Baja - Se necesita eventualmente</option>
                        <option value="media" selected>Media - Necesario para trabajos comunes</option>
                        <option value="alta">Alta - Urgente para diagnósticos/reparaciones</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Motivo / Justificación <span class="required">*</span></label>
                    <textarea name="reason" placeholder="Explica por qué se necesita esta herramienta y cómo mejorará el trabajo..." required></textarea>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Enviar Solicitud
                </button>
                <a href="<?= $basePath ?>/tools" class="btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</body>
</html>
