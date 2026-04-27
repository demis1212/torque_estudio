<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Reminders - Torque Studio ERP</title>
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
            --whatsapp: #25D366;
            --outline: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--surface); color: var(--on-surface); display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background: linear-gradient(180deg, var(--surface-container) 0%, var(--surface) 100%); border-right: 1px solid var(--outline); padding: 20px 16px; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar-header { display: flex; align-items: center; gap: 12px; padding: 8px 8px 20px; margin-bottom: 8px; border-bottom: 1px solid var(--outline); }
        .logo-icon { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .sidebar-header h2 { font-size: 18px; margin: 0; color: var(--on-surface); font-family: 'Space Grotesk', sans-serif; font-weight: 600; }
        .nav-section { margin-bottom: 8px; }
        .nav-section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 0.15em; color: var(--on-surface-variant); padding: 16px 8px 8px; font-weight: 600; }
        .nav-item { display: flex; align-items: center; gap: 12px; color: var(--on-surface-variant); text-decoration: none; padding: 10px 12px; border-radius: 8px; margin-bottom: 4px; font-weight: 500; font-size: 14px; transition: all 0.2s; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--on-surface); }
        .nav-item.active { background: linear-gradient(135deg, var(--primary-container) 0%, rgba(77,142,255,0.8) 100%); color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        .nav-item i { width: 20px; text-align: center; }
        .main-content { flex: 1; padding: 32px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header h1 { margin: 0; font-size: 28px; }
        .btn-primary { padding: 10px 20px; background: linear-gradient(135deg, var(--whatsapp) 0%, #128C7E 100%); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%); border: 1px solid var(--outline); border-radius: 12px; padding: 16px; text-align: center; }
        .stat-card .value { font-size: 24px; font-weight: 700; color: var(--whatsapp); }
        .stat-card .label { font-size: 12px; color: var(--on-surface-variant); }
        
        .two-columns { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
        .card { background: var(--surface-container-high); border-radius: 16px; padding: 24px; border: 1px solid var(--outline); }
        .card h3 { margin-bottom: 16px; font-size: 18px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; color: var(--on-surface-variant); }
        .form-group select, .form-group input, .form-group textarea {
            width: 100%; background: var(--surface-container); border: 1px solid var(--outline); 
            color: var(--on-surface); padding: 10px 12px; border-radius: 8px; font-size: 14px;
        }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .template-btn { display: block; width: 100%; padding: 10px; margin-bottom: 8px; background: var(--surface-container); 
            border: 1px solid var(--outline); color: var(--on-surface-variant); border-radius: 8px; 
            cursor: pointer; text-align: left; font-size: 13px; }
        .template-btn:hover { border-color: var(--whatsapp); color: var(--whatsapp); }
        
        .table-container { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--outline); font-size: 13px; }
        th { font-family: 'Space Grotesk', sans-serif; text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; color: var(--on-surface-variant); }
        .status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .status-programado { background: rgba(251, 191, 36, 0.15); color: var(--warning); }
        .status-enviado { background: rgba(37, 211, 102, 0.15); color: var(--whatsapp); }
        .status-fallido { background: rgba(248, 113, 113, 0.15); color: var(--danger); }
        .status-cancelado { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }
        .actions { display: flex; gap: 6px; }
        .btn-action { padding: 6px 10px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; text-decoration: none; }
        .btn-send { background: var(--whatsapp); color: #fff; }
        .btn-cancel { background: rgba(255,255,255,0.1); color: var(--on-surface-variant); }
        .type-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 4px 8px; border-radius: 6px; background: rgba(138, 180, 248, 0.15); color: var(--primary); }
    </style>
</head>
<body>
    <?php $userRole = $_SESSION['user_role'] ?? 0; ?>
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
            <a href="<?= $basePath ?>/workshop-ops" class="nav-item"><i class="fas fa-stopwatch"></i> Operación Inteligente</a>
            <a href="<?= $basePath ?>/parts" class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
        </nav>
        <nav class="nav-section">
            <div class="nav-section-title">Comunicación</div>
            <a href="<?= $basePath ?>/whatsapp-reminders" class="nav-item active"><i class="fab fa-whatsapp" style="color: var(--whatsapp);"></i> WhatsApp</a>
        </nav>
    </aside>

    <div class="main-content">
        <div class="header">
            <h1><i class="fab fa-whatsapp" style="margin-right: 12px; color: var(--whatsapp);"></i>Recordatorios WhatsApp</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="value"><?= $pendingCount ?></div>
                <div class="label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="value"><?= $todayCount ?></div>
                <div class="label">Para Hoy</div>
            </div>
            <div class="stat-card">
                <div class="value"><?= count($reminders) ?></div>
                <div class="label">Total Enviados</div>
            </div>
        </div>

        <div class="two-columns">
            <div class="card">
                <h3><i class="fas fa-plus-circle" style="margin-right: 8px;"></i>Nuevo Recordatorio</h3>
                <form method="POST" action="<?= $basePath ?>/whatsapp-reminders/store">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label>Cliente</label>
                        <select name="client_id" required>
                            <option value="">Seleccionar cliente...</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= esc($client['id']) ?>">
                                    <?= esc($client['name']) ?> <?= $client['whatsapp'] ? '(' . esc($client['whatsapp']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tipo de Mensaje</label>
                        <select name="reminder_type" id="reminder_type" required onchange="loadTemplate()">
                            <?php foreach ($templates as $key => $template): ?>
                                <option value="<?= $key ?>"><?= $template['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Plantillas Rápidas</label>
                        <?php foreach ($templates as $key => $template): ?>
                            <button type="button" class="template-btn" onclick="loadTemplate('<?= $key ?>')">
                                <i class="fas fa-comment"></i> <?= $template['label'] ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group">
                        <label>Mensaje</label>
                        <textarea name="message" id="message" required placeholder="Escriba su mensaje..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Fecha y Hora de Envío</label>
                        <input type="datetime-local" name="scheduled_at" required value="<?= date('Y-m-d\TH:i') ?>">
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fab fa-whatsapp"></i> Programar Mensaje
                    </button>
                </form>
            </div>

            <div class="card table-container">
                <h3><i class="fas fa-history" style="margin-right: 8px;"></i>Historial de Mensajes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Mensaje</th>
                            <th>Programado</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reminders as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($r['client_name']) ?></strong><br>
                                    <small style="color: var(--on-surface-variant);"><?= esc($r['whatsapp_number']) ?></small>
                                </td>
                                <td>
                                    <span class="type-badge">
                                        <i class="fas fa-tag"></i> <?= ucfirst($r['reminder_type']) ?>
                                    </span>
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= esc($r['message']) ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($r['scheduled_at'])) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $r['status'] ?>">
                                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <?php if ($r['status'] === 'programado'): ?>
                                        <form method="POST" action="<?= $basePath ?>/whatsapp-reminders/send/<?= $r['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn-action btn-send"><i class="fas fa-paper-plane"></i></button>
                                        </form>
                                        <form method="POST" action="<?= $basePath ?>/whatsapp-reminders/cancel/<?= $r['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn-action btn-cancel"><i class="fas fa-times"></i></button>
                                        </form>
                                    <?php elseif ($r['status'] === 'enviado'): ?>
                                        <small style="color: var(--whatsapp);">
                                            <i class="fas fa-check-double"></i> 
                                            <?= $r['sent_at'] ? date('H:i', strtotime($r['sent_at'])) : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const templates = <?= json_encode(array_map(fn($t) => $t['template'], $templates)) ?>;
        
        function loadTemplate(type = null) {
            if (!type) {
                type = document.getElementById('reminder_type').value;
            } else {
                document.getElementById('reminder_type').value = type;
            }
            document.getElementById('message').value = templates[type] || '';
        }
        
        // Cargar template inicial
        loadTemplate();
    </script>
</body>
</html>
