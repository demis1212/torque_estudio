<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --surface: #10131a;
            --surface-container-high: #272a31;
            --on-surface: #e1e2ec;
            --primary: #adc6ff;
            --primary-container: #4d8eff;
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
        .nav-item.active {
            background-color: var(--primary-container);
            color: #fff;
        }
        .nav-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }
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
        .header h1 {
            margin: 0;
            font-size: 32px;
        }
        .btn-primary {
            padding: 10px 20px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-danger {
            padding: 6px 12px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        .table-container {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            font-family: 'Space Grotesk', sans-serif;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.1em;
            color: #a7b6cc;
        }
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-admin { background-color: #dc3545; color: #fff; }
        .role-mecanico { background-color: #fd7e14; color: #000; }
        .role-recepcionista { background-color: #17a2b8; color: #fff; }
        .actions {
            display: flex;
            gap: 8px;
        }
        .actions a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
        }
        .actions form {
            display: inline;
        }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #c2c6d6;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-wrench" style="margin-right: 8px;"></i>Torque Studio</h2>
        <a href="<?= $basePath ?>/dashboard" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="<?= $basePath ?>/users" class="nav-item active"><i class="fas fa-user-cog"></i> Usuarios</a>
        <a href="<?= $basePath ?>/clients" class="nav-item"><i class="fas fa-users"></i> Clientes</a>
        <a href="<?= $basePath ?>/vehicles" class="nav-item"><i class="fas fa-car"></i> Vehículos</a>
        <a href="<?= $basePath ?>/work-orders" class="nav-item"><i class="fas fa-clipboard-list"></i> Órdenes</a>
        <a href="<?= $basePath ?>/services" class="nav-item"><i class="fas fa-wrench"></i> Servicios</a>
        <a href="<?= $basePath ?>/workshop-ops" class="nav-item"><i class="fas fa-stopwatch"></i> Operación Inteligente</a>
        <a href="<?= $basePath ?>/parts" class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= $basePath ?>/reports" class="nav-item"><i class="fas fa-chart-bar"></i> Reportes</a>
        <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item"><i class="fas fa-book"></i> Manuales</a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item"><i class="fas fa-search"></i> Decodificador VIN</a>
        <a href="<?= $basePath ?>/dtc" class="nav-item"><i class="fas fa-exclamation-triangle"></i> DTC Codes</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Gestión de Usuarios</h1>
            <a href="<?= $basePath ?>/users/create" class="btn-primary">+ Nuevo Usuario</a>
        </div>
        
        <div class="table-container">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <p>No hay usuarios registrados.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Valor Hora</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= esc($user['name']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td>
                                    <?php 
                                    $roleClass = 'role-' . strtolower($user['role_name']);
                                    ?>
                                    <span class="role-badge <?= $roleClass ?>"><?= esc($user['role_name']) ?></span>
                                </td>
                                <td>$<?= number_format((float)($user['hourly_rate'] ?? 0), 0, ',', '.') ?></td>
                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td class="actions">
                                    <a href="<?= $basePath ?>/users/edit/<?= $user['id'] ?>">Editar</a>
                                    <form method="POST" action="<?= $basePath ?>/users/delete/<?= $user['id'] ?>" onsubmit="return confirm('¿Eliminar usuario?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Preservar posicion del scroll del sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const savedScroll = sessionStorage.getItem('sidebarScroll');
            if (savedScroll) {
                sidebar.scrollTop = parseInt(savedScroll);
            }
            
            // Guardar posicion antes de salir
            window.addEventListener('beforeunload', function() {
                sessionStorage.setItem('sidebarScroll', sidebar.scrollTop);
            });
        });
    </script>
</body>
</html>
