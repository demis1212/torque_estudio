<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Torque Studio ERP</title>
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
            --warning: #fbbf24;
            --danger: #f87171;
            --success: #4ade80;
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
            display: flex;
            flex-direction: column;
            overflow-y: auto;
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
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header h1 { margin: 0; font-size: 32px; }
        .filters {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .filters input, .filters select {
            padding: 10px 14px;
            background-color: #1F2430;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            font-family: 'Inter', sans-serif;
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
        .btn-secondary {
            padding: 10px 20px;
            background-color: transparent;
            color: #c2c6d6;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background-color: #1F2430;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .stat-card h4 {
            font-family: 'Space Grotesk', sans-serif;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.1em;
            margin: 0 0 8px 0;
            color: #a7b6cc;
        }
        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }
        .alert-section {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .alert-section h3 {
            color: var(--danger);
            margin: 0 0 12px 0;
            font-size: 16px;
        }
        .alert-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .alert-item {
            background-color: rgba(220, 53, 69, 0.2);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
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
            font-size: 11px;
            letter-spacing: 0.1em;
            color: #a7b6cc;
        }
        .stock-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .stock-ok { background-color: rgba(40, 167, 69, 0.2); color: var(--success); }
        .stock-low { background-color: rgba(220, 53, 69, 0.2); color: var(--danger); }
        .actions {
            display: flex;
            gap: 8px;
        }
        .actions a, .actions button {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-edit { background-color: var(--primary-container); color: #fff; }
        .btn-delete { background-color: var(--danger); color: #fff; }
        .btn-stock { background-color: var(--warning); color: #000; }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #c2c6d6;
        }
        .category-badge {
            background-color: rgba(77, 142, 255, 0.2);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 20px;
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
            <a href="<?= $basePath ?>/workshop-ops" class="nav-item"><i class="fas fa-stopwatch"></i> Operación Inteligente</a>
            <a href="<?= $basePath ?>/parts" class="nav-item active"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="<?= $basePath ?>/parts/alerts" class="nav-item"><i class="fas fa-bell"></i> Alertas de Compra</a>
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
    <div class="main-content">
        <div class="header">
            <h1>Gestión de Inventario</h1>
            <div class="filters">
                <form method="GET" action="<?= $basePath ?>/parts" style="display: flex; gap: 12px;">
                    <input type="text" name="search" placeholder="Buscar repuesto..." value="<?= $_GET['search'] ?? '' ?>">
                    <select name="category">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>" <?= ($_GET['category'] ?? '') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-secondary">Filtrar</button>
                </form>
                <a href="<?= $basePath ?>/parts/create" class="btn-primary">+ Nuevo Repuesto</a>
            </div>
        </div>

        <?php
        $totalParts = count($parts);
        $totalValue = array_sum(array_map(fn($p) => $p['quantity'] * $p['cost_price'], $parts));
        $lowStockCount = count(array_filter($parts, fn($p) => $p['quantity'] <= $p['min_stock']));
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Repuestos</h4>
                <p class="number"><?= $totalParts ?></p>
            </div>
            <div class="stat-card">
                <h4>Valor en Inventario</h4>
                <p class="number">$<?= number_format($totalValue, 0, ',', '.') ?> CLP</p>
            </div>
            <div class="stat-card">
                <h4>Bajo Stock</h4>
                <p class="number" style="color: <?= $lowStockCount > 0 ? 'var(--danger)' : 'var(--success)' ?>"><?= $lowStockCount ?></p>
            </div>
        </div>

        <?php if (!empty($lowStock)): ?>
        <div class="alert-section">
            <h3>⚠️ Alertas de Stock Bajo</h3>
            <div class="alert-list">
                <?php foreach ($lowStock as $item): ?>
                    <span class="alert-item"><?= esc($item['code']) ?> (<?= $item['quantity'] ?> unid.)</span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <?php if (empty($parts)): ?>
                <div class="empty-state">
                    <p>No hay repuestos registrados.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Unidad</th>
                            <th>Stock</th>
                            <th>Precio Costo</th>
                            <th>Precio Venta</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parts as $part): ?>
                            <tr>
                                <td><strong><?= esc($part['code']) ?></strong></td>
                                <td><?= esc($part['name']) ?></td>
                                <td><span class="category-badge"><?= esc($part['category']) ?></span></td>
                                <td><span style="font-size: 11px; color: #888;"><?= $part['unit_type'] ?? 'unidad' ?></span></td>
                                <td>
                                    <?php $isLow = $part['quantity'] <= $part['min_stock']; ?>
                                    <span class="stock-indicator <?= $isLow ? 'stock-low' : 'stock-ok' ?>">
                                        <?= $part['quantity'] ?> / <?= $part['min_stock'] ?> min
                                    </span>
                                </td>
                                <td>$<?= number_format($part['cost_price'], 0, ',', '.') ?></td>
                                <td>$<?= number_format($part['sale_price'], 0, ',', '.') ?></td>
                                <td><?= esc($part['location']) ?></td>
                                <td class="actions">
                                    <a href="<?= $basePath ?>/parts/edit/<?= $part['id'] ?>" class="btn-edit">Editar</a>
                                    <form method="POST" action="<?= $basePath ?>/parts/stock/<?= $part['id'] ?>" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="adjustment" value="1">
                                        <input type="hidden" name="reason" value="Ajuste manual">
                                        <button type="submit" class="btn-stock">+1</button>
                                    </form>
                                    <form method="POST" action="<?= $basePath ?>/parts/delete/<?= $part['id'] ?>" style="display: inline;" onsubmit="return confirm('¿Eliminar este repuesto?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn-delete">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Toast Notifications -->
    <?php include __DIR__ . '/../components/toast.php'; ?>
</body>
</html>
