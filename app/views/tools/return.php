<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devolver Herramienta - <?= esc($tool['name']) ?></title>
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
            --warning: #fd7e14;
            --success: #28a745;
            --error: #f87171;
            --outline: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--on-surface);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: var(--surface-container);
            border: 1px solid var(--outline);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--warning) 0%, #ffc107 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 24px;
        }
        h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .tool-info {
            background: var(--surface-container-high);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .tool-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }
        .loan-info {
            color: var(--on-surface-variant);
            font-size: 14px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--outline);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--on-surface-variant);
            font-size: 14px;
        }
        select, textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--surface);
            border: 1px solid var(--outline);
            border-radius: 10px;
            color: var(--on-surface);
            font-size: 14px;
            transition: border-color 0.2s;
        }
        select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--warning) 0%, #ffc107 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(253, 126, 20, 0.3);
        }
        .btn-secondary {
            display: block;
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 1px solid var(--outline);
            border-radius: 10px;
            color: var(--on-surface-variant);
            text-align: center;
            text-decoration: none;
            margin-top: 12px;
        }
        .error {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.3);
            color: var(--error);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">📥</div>
            <h1>Devolver Herramienta</h1>
        </div>
        
        <div class="tool-info">
            <div class="tool-name"><?= esc($tool['name']) ?></div>
            <div class="tool-code">Código: <?= esc($tool['code'] ?? 'N/A') ?></div>
            <div class="loan-info">
                <div><strong>Prestada a:</strong> <?= esc($loan['mechanic_name'] ?? 'Desconocido') ?></div>
                <div><strong>Fecha de préstamo:</strong> <?= date('d/m/Y', strtotime($loan['request_date'])) ?></div>
                <div><strong>Devolución esperada:</strong> <?= date('d/m/Y', strtotime($loan['expected_return_date'])) ?></div>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="form-group">
                <label>Condición de la Herramienta *</label>
                <select name="condition" required>
                    <option value="buena">✅ Buena - Sin daños</option>
                    <option value="regular">⚠️ Regular - Desgaste normal</option>
                    <option value="danada">🔧 Dañada - Requiere reparación</option>
                    <option value="perdida">❌ Perdida - No devuelta</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Observaciones (opcional)</label>
                <textarea name="notes" rows="3" placeholder="Estado específico, daños observados, etc..."></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Confirmar Devolución</button>
            <a href="<?= $basePath ?>/tools/warehouse" class="btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
