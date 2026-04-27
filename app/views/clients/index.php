<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $basePath ?>/css/erp-theme.css">
    <style>
        :root {
            --surface: #0a0c10;
            --surface-container: #11131a;
            --surface-container-high: #1a1d26;
            --on-surface: #e8eaf2;
            --on-surface-variant: #9aa3b2;
            --primary: #8ab4f8;
            --primary-container: #4d8eff;
            --error: #f87171;
            --outline: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--surface); color: var(--on-surface); display: flex; height: 100vh; overflow: hidden; }
        
        /* Sidebar */
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
        
        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-bar { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; background: var(--surface-container); border-bottom: 1px solid var(--outline); }
        .top-bar h1 { margin: 0; font-size: 24px; font-family: 'Space Grotesk', sans-serif; font-weight: 600; }
        .btn-primary { padding: 10px 18px; background: linear-gradient(135deg, var(--primary-container) 0%, #3b7de8 100%); color: #fff; border: none; border-radius: 10px; font-weight: 500; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(77,142,255,0.4); }
        
        .content-area { flex: 1; padding: 24px; overflow-y: auto; }
        
        /* Table */
        .table-container { background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%); border: 1px solid var(--outline); border-radius: 16px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: rgba(0,0,0,0.2); padding: 16px; text-align: left; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--on-surface-variant); border-bottom: 1px solid var(--outline); }
        td { padding: 16px; border-bottom: 1px solid var(--outline); }
        tr:hover td { background: rgba(255,255,255,0.02); }
        tr:last-child td { border-bottom: none; }
        
        .client-name { display: flex; align-items: center; gap: 12px; }
        .client-avatar { width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--surface); font-size: 14px; }
        .client-info strong { color: var(--on-surface); font-weight: 500; }
        .client-info small { color: var(--on-surface-variant); font-size: 12px; }
        
        .actions { display: flex; gap: 8px; }
        .btn-icon { width: 36px; height: 36px; background: rgba(255,255,255,0.05); border: 1px solid var(--outline); border-radius: 8px; color: var(--on-surface-variant); display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s; }
        .btn-icon:hover { background: rgba(255,255,255,0.1); color: var(--on-surface); }
        .btn-icon.edit:hover { background: rgba(138,180,248,0.15); color: var(--primary); border-color: rgba(138,180,248,0.3); }
        .btn-icon.delete:hover { background: rgba(248,113,113,0.15); color: var(--error); border-color: rgba(248,113,113,0.3); }
        
        .empty-state { text-align: center; padding: 60px 24px; color: var(--on-surface-variant); }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
        .empty-state h4 { color: var(--on-surface); margin-bottom: 8px; }
    </style>
</head>
<body>
    <?php
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRole = $_SESSION['user_role'] ?? 0;
    ?>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">🔧</div>
            <h2>Torque Studio</h2>
        </div>
        
        <nav class="nav-section">
            <div class="nav-section-title">Principal</div>
            <a href="<?= $basePath ?>/dashboard" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
            <a href="<?= $basePath ?>/clients" class="nav-item active"><i class="fas fa-users"></i> Clientes</a>
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
    
    <!-- Main Content -->
    <main class="main-content">
        <header class="top-bar">
            <h1><i class="fas fa-users" style="margin-right: 12px; color: var(--primary);"></i>Clientes</h1>
            <a href="<?= $basePath ?>/clients/create" class="btn-primary"><i class="fas fa-plus"></i>Nuevo Cliente</a>
        </header>
        
        <div class="content-area">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Vehículos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($clients as $client): ?>
                        <tr>
                            <td>
                                <div class="client-name">
                                    <div class="client-avatar"><?= substr($client['name'], 0, 1) ?></div>
                                    <div class="client-info">
                                        <strong><?= esc($client['name']) ?></strong>
                                        <small>ID: #<?= $client['id'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="client-info">
                                    <div><i class="fas fa-phone" style="width: 16px; color: var(--primary);"></i> <?= esc($client['phone'] ?? 'N/A') ?></div>
                                    <small><i class="fas fa-envelope" style="width: 16px;"></i> <?= esc($client['email'] ?? 'N/A') ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info"><i class="fas fa-car" style="margin-right: 4px;"></i><?= $client['vehicle_count'] ?? 0 ?></span>
                            </td>
                            <td class="actions">
                                <a href="<?= $basePath ?>/clients/edit/<?= esc($client['id']) ?>" class="btn-icon edit" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn-icon delete" title="Eliminar" onclick="confirmDelete('<?= esc($client['name']) ?>', '<?= $basePath ?>/clients/delete/<?= $client['id'] ?>'); return false;"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($clients)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h4>No hay clientes registrados</h4>
                                    <p>Comienza agregando tu primer cliente</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <!-- Include Components -->
    <?php include __DIR__ . '/../components/confirm-delete.php'; ?>
    <?php include __DIR__ . '/../components/toast.php'; ?>
</body>
</html>
