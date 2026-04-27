<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manuales Técnicos - Torque Studio ERP</title>
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
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 24px;
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
        .nav-item.active {
            background: linear-gradient(135deg, var(--primary-container) 0%, rgba(77,142,255,0.8) 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .nav-item i { width: 20px; text-align: center; }
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
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            color: #c2c6d6;
            text-decoration: none;
            font-size: 13px;
        }
        .filter-btn:hover, .filter-btn.active {
            background-color: var(--primary-container);
            color: #fff;
        }
        .manuals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .manual-card {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.2s;
        }
        .manual-card:hover {
            transform: translateY(-4px);
            background-color: rgba(255, 255, 255, 0.02);
        }
        .manual-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }
        .manual-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--on-surface);
        }
        .manual-meta {
            font-size: 13px;
            color: #888;
            margin-bottom: 12px;
        }
        .manual-description {
            font-size: 14px;
            color: #c2c6d6;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .manual-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .views-count {
            font-size: 12px;
            color: #888;
        }
        .btn-view {
            padding: 8px 16px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-primary {
            padding: 12px 24px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .search-box {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        .search-box input {
            flex: 1;
            padding: 12px;
            background-color: #1F2430;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            font-size: 14px;
        }
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #666;
        }
        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            background-color: rgba(77, 142, 255, 0.1);
            color: var(--primary);
            border-radius: 20px;
            font-size: 11px;
            margin-bottom: 12px;
        }
        
        /* Hierarchical View Styles */
        .manuals-hierarchy {
            margin-top: 24px;
        }
        .brand-section {
            margin-bottom: 32px;
            background: var(--surface-container-high);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--outline);
        }
        .brand-title {
            font-size: 24px;
            color: var(--primary);
            margin: 0 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary);
        }
        .model-section {
            margin-left: 20px;
            margin-bottom: 20px;
            padding-left: 20px;
            border-left: 3px solid rgba(138, 180, 248, 0.3);
        }
        .model-title {
            font-size: 18px;
            color: var(--on-surface);
            margin: 0 0 16px 0;
        }
        .year-section {
            margin-left: 20px;
            margin-bottom: 16px;
        }
        .year-title {
            font-size: 14px;
            color: var(--on-surface-variant);
            margin: 0 0 12px 0;
            font-weight: 500;
        }
        .manuals-grid-compact {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
            margin-left: 20px;
        }
        .manual-card-compact {
            background: var(--surface);
            border-radius: 10px;
            padding: 16px;
            border: 1px solid var(--outline);
            transition: all 0.2s;
        }
        .manual-card-compact:hover {
            border-color: var(--primary);
        }
        .manual-title-small {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--on-surface);
        }
        .manual-stats-small {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--on-surface-variant);
        }
        .btn-view-small {
            padding: 4px 12px;
            background: var(--primary-container);
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php $userRole = $_SESSION['user_role'] ?? 0; ?>
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
            <a href="<?= $basePath ?>/parts" class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Herramientas</div>
            <a href="<?= $basePath ?>/manuals" class="nav-item active"><i class="fas fa-book"></i> Manuales</a>
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
            <h1>📚 Manuales Técnicos</h1>
            <a href="<?= $basePath ?>/manuals/create" class="btn-primary">+ Subir Manual</a>
        </div>

        <form method="GET" action="<?= $basePath ?>/manuals/search" class="search-box">
            <input type="text" name="q" placeholder="Buscar por título, marca o modelo..." value="<?= $_GET['q'] ?? '' ?>">
            <button type="submit" class="btn-primary">Buscar</button>
        </form>

        <!-- View Toggle -->
        <div style="display: flex; gap: 12px; margin-bottom: 20px;">
            <a href="<?= $basePath ?>/manuals?view=grouped" class="filter-btn <?= $current_view === 'grouped' ? 'active' : '' ?>">
                <i class="fas fa-folder-tree"></i> Por Marca/Modelo
            </a>
            <a href="<?= $basePath ?>/manuals?view=list" class="filter-btn <?= $current_view === 'list' ? 'active' : '' ?>">
                <i class="fas fa-list"></i> Lista Completa
            </a>
        </div>

        <div class="filters">
            <a href="<?= $basePath ?>/manuals" class="filter-btn <?= !$selected_category ? 'active' : '' ?>">Todos</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= $basePath ?>/manuals?category=<?= urlencode($cat) ?>" class="filter-btn <?= $selected_category === $cat ? 'active' : '' ?>">
                    <?= esc($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($grouped_manuals)): ?>
            <!-- GROUPED VIEW: Organized by Brand > Model > Year -->
            <div class="manuals-hierarchy">
                <?php foreach ($grouped_manuals as $brand => $models): ?>
                    <div class="brand-section">
                        <h2 class="brand-title"><i class="fas fa-industry"></i> <?= esc($brand) ?></h2>
                        
                        <?php foreach ($models as $model => $years): ?>
                            <div class="model-section">
                                <h3 class="model-title"><i class="fas fa-car"></i> <?= esc($model) ?></h3>
                                
                                <?php foreach ($years as $year => $manuals): ?>
                                    <div class="year-section">
                                        <h4 class="year-title"><i class="fas fa-calendar"></i> <?= esc($year) ?></h4>
                                        <div class="manuals-grid-compact">
                                            <?php foreach ($manuals as $manual): ?>
                                                <div class="manual-card-compact">
                                                    <span class="category-badge"><?= esc($manual['category']) ?></span>
                                                    <div class="manual-title-small"><?= esc($manual['title']) ?></div>
                                                    <div class="manual-stats-small">
                                                        <span><i class="fas fa-eye"></i> <?= $manual['views'] ?></span>
                                                        <a href="<?= $basePath ?>/manuals/view/<?= $manual['id'] ?>" class="btn-view-small">Ver</a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($manuals)): ?>
            <!-- LIST VIEW: Sorted by Brand, Model, Year -->
            <div class="manuals-grid">
                <?php foreach ($manuals as $manual): ?>
                    <div class="manual-card">
                        <div class="manual-icon"><i class="fas fa-file-alt"></i></div>
                        <span class="category-badge"><?= esc($manual['category']) ?></span>
                        <div class="manual-title"><?= esc($manual['title']) ?></div>
                        <div class="manual-meta">
                            <strong><?= $manual['brand'] ? esc($manual['brand']) : 'Sin Marca' ?></strong> 
                            <?= $manual['model'] ? esc($manual['model']) : '' ?>
                            <?= $manual['year'] ? '(' . esc($manual['year']) . ')' : '' ?>
                        </div>
                        <?php if ($manual['description']): ?>
                            <div class="manual-description">
                                <?= substr(esc($manual['description']), 0, 100) ?>...
                            </div>
                        <?php endif; ?>
                        <div class="manual-stats">
                            <span class="views-count"><i class="fas fa-eye"></i> <?= $manual['views'] ?> vistas</span>
                            <a href="<?= $basePath ?>/manuals/view/<?= $manual['id'] ?>" class="btn-view">Ver Manual</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>No hay manuales disponibles</h2>
                <p>Sube el primer manual técnico usando el botón "Subir Manual"</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
