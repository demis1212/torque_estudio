<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Cliente - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --surface: #10131a;
            --on-surface: #e1e2ec;
            --primary-container: #4d8eff;
        }
        body { margin: 0; background: var(--surface); color: var(--on-surface); font-family: 'Inter', sans-serif; display: flex; height: 100vh; }
        .sidebar { width: 250px; background: #0F1115; border-right: 1px solid rgba(255,255,255,0.05); padding: 24px; }
        .sidebar h2 { color: #adc6ff; font-size: 20px; margin-top: 0; margin-bottom: 32px; }
        .nav-item { display: block; color: #c2c6d6; text-decoration: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; }
        .nav-item.active { background: var(--primary-container); color: #fff; }
        .main-content { flex: 1; padding: 32px; overflow-y: auto; }
        .card { background: #1F2430; border-radius: 16px; padding: 32px; border: 1px solid rgba(255,255,255,0.05); max-width: 600px; }
        .form-group { margin-bottom: 24px; }
        label { display: block; font-family: 'Space Grotesk', sans-serif; font-size: 12px; text-transform: uppercase; margin-bottom: 8px; color: #a7b6cc; }
        input, textarea { width: 100%; padding: 12px; background: #0F1115; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-family: 'Inter', sans-serif; box-sizing: border-box; }
        input:focus, textarea:focus { outline: none; border-color: var(--primary-container); }
        .btn-primary { background: var(--primary-container); color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-secondary { background: transparent; color: #e1e2ec; border: 1px solid rgba(255,255,255,0.2); padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-right: 8px; }
    </style>
</head>
<body>
    <?php
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
    ?>
    <div class="sidebar">
        <h2>Torque Studio</h2>
        <a href="<?= $basePath ?>/dashboard" class="nav-item">Dashboard</a>
        <a href="<?= $basePath ?>/clients" class="nav-item active">Clientes</a>
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
        <h1>Nuevo Cliente</h1>
        <div class="card">
            <form action="<?= $basePath ?>/clients/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>RUT</label>
                    <input type="text" name="rut" placeholder="12.345.678-9">
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="phone">
                </div>

                <div class="form-group">
                    <label>WhatsApp</label>
                    <input type="text" name="whatsapp" placeholder="+56912345678">
                </div>

                <div class="form-group">
                    <label style="text-transform:none; letter-spacing:normal; font-family:'Inter', sans-serif;">
                        <input type="checkbox" name="whatsapp_opt_in" value="1" style="width:auto; margin-right:8px;">
                        Cliente autoriza recordatorios por WhatsApp
                    </label>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>
                
                <div class="form-group">
                    <label>Dirección</label>
                    <textarea name="address" rows="3"></textarea>
                </div>
                
                <div>
                    <a href="<?= $basePath ?>/clients" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
