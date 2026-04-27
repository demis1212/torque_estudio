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
    <title>Herramientas - Torque Studio ERP</title>
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
        
        /* Sidebar Consistente */
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
        
        .content-area { flex: 1; padding: 24px; overflow-y: auto; }
        
        /* Stats Cards */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .card { background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%); border: 1px solid var(--outline); border-radius: 16px; padding: 20px; transition: all 0.3s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
        .card-icon { font-size: 28px; margin-bottom: 12px; }
        .card-title { font-size: 13px; color: var(--on-surface-variant); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-value { font-size: 28px; font-weight: 700; font-family: 'Space Grotesk', sans-serif; }
        
        /* Tools Menu */
        .tools-section { background: linear-gradient(145deg, var(--surface-container-high) 0%, var(--surface-container) 100%); border: 1px solid var(--outline); border-radius: 16px; padding: 24px; }
        .section-header { margin-bottom: 20px; }
        .section-title { font-size: 18px; font-weight: 600; font-family: 'Space Grotesk', sans-serif; }
        
        .tools-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
        .tool-item { background: rgba(255,255,255,0.03); border: 1px solid var(--outline); border-radius: 12px; padding: 20px; text-decoration: none; color: inherit; transition: all 0.3s; display: flex; flex-direction: column; }
        .tool-item:hover { background: rgba(255,255,255,0.06); transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); border-color: rgba(138,180,248,0.2); }
        .tool-item-icon { font-size: 32px; margin-bottom: 12px; }
        .tool-item-title { font-size: 16px; font-weight: 600; margin-bottom: 6px; }
        .tool-item-desc { font-size: 13px; color: var(--on-surface-variant); line-height: 1.5; }
        
        /* Alert */
        .alert-box { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.3); border-radius: 10px; padding: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
        .alert-box strong { color: var(--danger); }
    </style>
</head>
<body>
    <!-- Sidebar Consistente -->
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
            <a href="<?= $basePath ?>/tools" class="nav-item active"><i class="fas fa-tools"></i> Herramientas</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Herramientas</div>
            <a href="<?= $basePath ?>/tools/my-tools" class="nav-item"><i class="fas fa-toolbox"></i> Mis Herramientas</a>
            <a href="<?= $basePath ?>/tools/requests/create" class="nav-item"><i class="fas fa-hand-holding"></i> Solicitar Herramienta</a>
            <a href="<?= $basePath ?>/tools/purchase-request" class="nav-item"><i class="fas fa-shopping-cart"></i> Solicitar Compra</a>
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
            <h1><i class="fas fa-tools" style="margin-right: 12px; color: var(--primary);"></i>Gestión de Herramientas</h1>
        </header>
        
        <div class="content-area">
            <?php if ($overdue_loans > 0): ?>
            <div class="alert-box">
                <i class="fas fa-exclamation-triangle" style="color: var(--danger); font-size: 20px;"></i>
                <div>
                    <strong>Préstamos Atrasados:</strong> Hay <?= $overdue_loans ?> herramienta(s) que no han sido devueltas a tiempo. 
                    <a href="<?= $basePath ?>/tools/requests" style="color: var(--primary); text-decoration: underline;">Ver detalles →</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="cards-grid">
                <div class="card">
                    <div class="card-icon"><i class="fas fa-wrench"></i></div>
                    <div class="card-title">Herramientas Asignadas</div>
                    <div class="card-value"><?= $mechanic_tools_count ?></div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-warehouse"></i></div>
                    <div class="card-title">Herramientas en Bodega</div>
                    <div class="card-value"><?= $warehouse_tools_count ?></div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                    <div class="card-title">Solicitudes Pendientes</div>
                    <div class="card-value"><?= $pending_requests ?></div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-hand-holding"></i></div>
                    <div class="card-title">Préstamos Activos</div>
                    <div class="card-value"><?= $active_loans ?></div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="card-title">Valor Total Bodega</div>
                    <div class="card-value">$<?= number_format($total_warehouse_value, 0) ?></div>
                </div>
            </div>

            <div class="tools-section">
                <div class="section-header">
                    <span class="section-title">Menú de Herramientas</span>
                </div>
                <div class="tools-menu">
                    <a href="<?= $basePath ?>/tools/mechanic" class="tool-item">
                        <div class="tool-item-icon"><i class="fas fa-wrench"></i></div>
                        <div class="tool-item-title">Herramientas de Mecánico</div>
                        <div class="tool-item-desc">Gestionar herramientas asignadas permanentemente a cada mecánico del taller</div>
                    </a>
                    <a href="<?= $basePath ?>/tools/warehouse" class="tool-item">
                        <div class="tool-item-icon"><i class="fas fa-warehouse"></i></div>
                        <div class="tool-item-title">Herramientas de Bodega</div>
                        <div class="tool-item-desc">Inventario de herramientas de alto valor disponibles para préstamo diario</div>
                    </a>
                    <?php if($userRole == 1): ?>
                    <a href="<?= $basePath ?>/tools/warehouse/create" class="tool-item" style="border-color: var(--primary); background: linear-gradient(135deg, rgba(77,142,255,0.1) 0%, rgba(77,142,255,0.05) 100%);">
                        <div class="tool-item-icon" style="color: var(--primary);"><i class="fas fa-plus"></i></div>
                        <div class="tool-item-title">Agregar Herramienta</div>
                        <div class="tool-item-desc">Registrar nueva herramienta de alto valor en la bodega para préstamo</div>
                    </a>
                    <?php endif; ?>
                    <a href="<?= $basePath ?>/tools/requests" class="tool-item">
                        <div class="tool-item-icon"><i class="fas fa-clipboard-list"></i></div>
                        <div class="tool-item-title">Solicitudes de Préstamo</div>
                        <div class="tool-item-desc">Aprobar, entregar y gestionar solicitudes de préstamo de herramientas</div>
                    </a>
                    <a href="<?= $basePath ?>/tools/my-tools" class="tool-item">
                        <div class="tool-item-icon"><i class="fas fa-toolbox"></i></div>
                        <div class="tool-item-title">Mis Herramientas</div>
                        <div class="tool-item-desc">Ver mis herramientas asignadas y préstamos activos como mecánico</div>
                    </a>
                    <a href="<?= $basePath ?>/tools/purchase-request" class="tool-item" style="border-color: var(--success); background: linear-gradient(135deg, rgba(74,222,128,0.1) 0%, rgba(74,222,128,0.05) 100%);">
                        <div class="tool-item-icon" style="color: var(--success);"><i class="fas fa-shopping-cart"></i></div>
                        <div class="tool-item-title">Solicitar Compra</div>
                        <div class="tool-item-desc">Solicitar al administrador la compra de una nueva herramienta</div>
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
