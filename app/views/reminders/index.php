<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorios - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --surface: #10131a;
            --on-surface: #e1e2ec;
            --primary: #adc6ff;
            --primary-container: #4d8eff;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: var(--surface);
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
        }
        .sidebar h2 { font-size: 20px; margin-top: 0; margin-bottom: 32px; color: var(--primary); }
        .nav-item {
            display: block;
            color: #c2c6d6;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .nav-item:hover { background-color: rgba(255, 255, 255, 0.05); }
        .nav-item.active { background-color: var(--primary-container); color: #fff; }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .header h1 { margin: 0; font-size: 32px; }
        .new-reminder-btn {
            padding: 12px 24px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .reminders-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
        }
        .reminder-form {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            height: fit-content;
        }
        .reminder-form h3 { margin: 0 0 20px 0; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            color: #c2c6d6;
            margin-bottom: 8px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            background-color: #0F1115;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            box-sizing: border-box;
        }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
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
        }
        .reminders-list {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .reminders-list h3 { margin: 0 0 20px 0; }
        .reminder-item {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .reminder-item:last-child { border-bottom: none; }
        .reminder-item.overdue { border-left: 3px solid #dc3545; }
        .reminder-item.upcoming { border-left: 3px solid #ffc107; }
        .reminder-item.completed { opacity: 0.5; }
        .reminder-content { flex: 1; }
        .reminder-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        .reminder-description {
            color: #888;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .reminder-date {
            font-size: 12px;
            color: #666;
        }
        .reminder-date.overdue { color: #dc3545; }
        .reminder-date.upcoming { color: #ffc107; }
        .reminder-actions {
            display: flex;
            gap: 8px;
        }
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-complete { background-color: #28a745; color: #fff; }
        .btn-delete { background-color: #dc3545; color: #fff; }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #666;
        }
        .section-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .tab-btn {
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #c2c6d6;
            cursor: pointer;
        }
        .tab-btn.active {
            background-color: var(--primary-container);
            color: #fff;
            border-color: var(--primary-container);
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
        <a href="<?= $basePath ?>/reminders" class="nav-item active">⏰ Recordatorios</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item">📚 Manuales</a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item">🔍 Decodificador VIN</a>
        <a href="<?= $basePath ?>/dtc" class="nav-item">🔧 DTC Codes</a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>⏰ Recordatorios</h1>
        </div>

        <div class="reminders-grid">
            <div class="reminder-form">
                <h3>Nuevo Recordatorio</h3>
                <form method="POST" action="<?= $basePath ?>/reminders">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" name="title" required placeholder="ej: Llamar al cliente">
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" placeholder="Detalles del recordatorio..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Fecha y Hora</label>
                        <input type="datetime-local" name="reminder_date" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Crear Recordatorio</button>
                    </div>
                </form>
            </div>

            <div class="reminders-list">
                <div class="section-tabs">
                    <button class="tab-btn active" onclick="showTab('all')">Todos</button>
                    <button class="tab-btn" onclick="showTab('upcoming')">Próximos (<?= count($upcoming) ?>)</button>
                    <button class="tab-btn" onclick="showTab('overdue')">Vencidos (<?= count($overdue) ?>)</button>
                </div>

                <h3>Mis Recordatorios</h3>
                
                <?php if (empty($reminders)): ?>
                    <div class="empty-state">
                        <p>No tienes recordatorios</p>
                        <p style="font-size: 14px;">Crea uno nuevo usando el formulario</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $now = new DateTime();
                    foreach ($reminders as $reminder): 
                        $reminderDate = new DateTime($reminder['reminder_date']);
                        $isOverdue = $reminderDate < $now && !$reminder['is_completed'];
                        $isUpcoming = $reminderDate > $now && $reminderDate < (clone $now)->modify('+7 days') && !$reminder['is_completed'];
                        $class = $reminder['is_completed'] ? 'completed' : ($isOverdue ? 'overdue' : ($isUpcoming ? 'upcoming' : ''));
                    ?>
                        <div class="reminder-item <?= $class ?>">
                            <div class="reminder-content">
                                <div class="reminder-title">
                                    <?= esc($reminder['title']) ?>
                                    <?php if ($reminder['is_completed']): ?>
                                        <span style="color: #28a745;">✓ Completado</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($reminder['description']): ?>
                                    <div class="reminder-description"><?= esc($reminder['description']) ?></div>
                                <?php endif; ?>
                                <div class="reminder-date <?= $isOverdue ? 'overdue' : ($isUpcoming ? 'upcoming' : '') ?>">
                                    <?= $reminderDate->format('d/m/Y H:i') ?>
                                    <?php if ($isOverdue): ?>⚠️ VENCIDO<?php endif; ?>
                                    <?php if ($isUpcoming): ?>📅 PRÓXIMO<?php endif; ?>
                                </div>
                            </div>
                            <div class="reminder-actions">
                                <?php if (!$reminder['is_completed']): ?>
                                    <form method="POST" action="<?= $basePath ?>/reminders/complete/<?= $reminder['id'] ?>" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn-small btn-complete">✓</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="<?= $basePath ?>/reminders/delete/<?= $reminder['id'] ?>" style="display: inline;" onsubmit="return confirm('¿Eliminar este recordatorio?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn-small btn-delete">🗑</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tab) {
            // This would filter the reminders in a real implementation
            console.log('Showing tab: ' + tab);
        }
    </script>
</body>
</html>
