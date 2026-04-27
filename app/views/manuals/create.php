<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Manual - Torque Studio ERP</title>
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
        .nav-item:hover, .nav-item.active {
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { 
            margin: 0; 
            font-size: 28px;
            font-family: 'Space Grotesk', sans-serif;
        }
        .form-container {
            background: var(--surface-container);
            border: 1px solid var(--outline);
            border-radius: 16px;
            padding: 32px;
            max-width: 700px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--on-surface);
        }
        label .required {
            color: #f87171;
            margin-left: 4px;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--surface);
            border: 1px solid var(--outline);
            border-radius: 10px;
            color: var(--on-surface);
            font-size: 14px;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .file-upload {
            border: 2px dashed var(--outline);
            border-radius: 10px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .file-upload:hover {
            border-color: var(--primary);
            background: rgba(138, 180, 248, 0.05);
        }
        .file-upload i {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 12px;
        }
        .file-upload input[type="file"] {
            display: none;
        }
        .btn-primary {
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--primary-container) 0%, var(--primary) 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(77, 142, 255, 0.3);
        }
        .btn-secondary {
            padding: 14px 28px;
            background: transparent;
            color: var(--on-surface-variant);
            border: 1px solid var(--outline);
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            margin-left: 12px;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.05);
            color: var(--on-surface);
        }
        .info-box {
            background: rgba(138, 180, 248, 0.1);
            border: 1px solid rgba(138, 180, 248, 0.3);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .info-box i {
            color: var(--primary);
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon"><i class="fas fa-wrench"></i></div>
            <h2>Torque Studio</h2>
        </div>
        
        <nav>
            <a href="<?= $basePath ?>/dashboard" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
            <a href="<?= $basePath ?>/users" class="nav-item"><i class="fas fa-users"></i> Usuarios</a>
            <a href="<?= $basePath ?>/clients" class="nav-item"><i class="fas fa-users"></i> Clientes</a>
            <a href="<?= $basePath ?>/vehicles" class="nav-item"><i class="fas fa-car"></i> Vehiculos</a>
            <a href="<?= $basePath ?>/work-orders" class="nav-item"><i class="fas fa-clipboard-list"></i> Ordenes</a>
            <a href="<?= $basePath ?>/services" class="nav-item"><i class="fas fa-wrench"></i> Servicios</a>
            <a href="<?= $basePath ?>/parts" class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="<?= $basePath ?>/reports" class="nav-item"><i class="fas fa-chart-bar"></i> Reportes</a>
            <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
            <a href="<?= $basePath ?>/manuals" class="nav-item active"><i class="fas fa-book"></i> Manuales</a>
            <a href="<?= $basePath ?>/vin-decoder" class="nav-item"><i class="fas fa-search"></i> VIN Decoder</a>
            <a href="<?= $basePath ?>/dtc" class="nav-item"><i class="fas fa-exclamation-triangle"></i> DTC Codes</a>
        </nav>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-file-upload"></i> Subir Manual Tecnico</h1>
            <a href="<?= $basePath ?>/manuals" class="btn-secondary">Volver</a>
        </div>

        <div class="form-container">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Sube manuales tecnicos, diagramas o documentacion para el taller. 
                Formatos permitidos: PDF, DOC, DOCX, TXT.
            </div>

            <form method="POST" action="<?= $basePath ?>/manuals/create" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-group">
                    <label>Titulo <span class="required">*</span></label>
                    <input type="text" name="title" placeholder="Ej: Manual de Taller Toyota Corolla 2020" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Categoria <span class="required">*</span></label>
                        <select name="category" required>
                            <option value="">Selecciona categoria</option>
                            <option value="Mecanica">Mecanica</option>
                            <option value="Electricidad">Electricidad</option>
                            <option value="Transmision">Transmision</option>
                            <option value="Frenos">Frenos</option>
                            <option value="Suspension">Suspension</option>
                            <option value="Diagnostico">Diagnostico</option>
                            <option value="Diagramas">Diagramas</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="brand" placeholder="Ej: Toyota">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="model" placeholder="Ej: Corolla">
                    </div>
                    <div class="form-group">
                        <label>Ano</label>
                        <input type="text" name="year" placeholder="Ej: 2020">
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripcion</label>
                    <textarea name="description" placeholder="Breve descripcion del contenido del manual..."></textarea>
                </div>

                <div class="form-group">
                    <label>Contenido / Procedimiento</label>
                    <textarea name="content" placeholder="Pega aqui el contenido del manual o procedimiento..."></textarea>
                </div>

                <div class="form-group">
                    <label>Archivo <span class="required">*</span></label>
                    <div class="file-upload" onclick="document.getElementById('file').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Haz clic para seleccionar archivo</p>
                        <p style="font-size: 12px; color: var(--on-surface-variant); margin-top: 8px;">
                            PDF, DOC, DOCX, TXT (Max 10MB)
                        </p>
                        <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.txt" required onchange="updateFileName(this)">
                    </div>
                    <p id="file-name" style="margin-top: 8px; font-size: 14px; color: var(--primary);"></p>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-upload"></i> Subir Manual
                </button>
                <a href="<?= $basePath ?>/manuals" class="btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0]?.name || '';
            document.getElementById('file-name').textContent = fileName ? 'Archivo: ' + fileName : '';
        }
    </script>
</body>
</html>
