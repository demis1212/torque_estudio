<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$statusColors = [
    'disponible' => '#28a745',
    'solicitada' => '#6f42c1',
    'prestada' => '#ffc107',
    'en_mantenimiento' => '#17a2b8',
    'danada' => '#dc3545'
];

$statusLabels = [
    'disponible' => 'Disponible',
    'solicitada' => 'Solicitada',
    'prestada' => 'Prestada',
    'en_mantenimiento' => 'En Mant.',
    'danada' => 'Dañada'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herramientas de Bodega - Torque Studio ERP</title>
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
            --danger: #f87171;
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
            margin-bottom: 32px;
        }
        .header h1 { margin: 0; font-size: 28px; }
        .btn-primary {
            padding: 10px 20px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .stat-card {
            background-color: #1F2430;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 12px;
            color: #888;
        }
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .tool-card {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .tool-card:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        .tool-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .tool-title {
            font-size: 16px;
            font-weight: 600;
        }
        .tool-code {
            font-size: 12px;
            color: #888;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .tool-info {
            font-size: 13px;
            color: #888;
            margin-bottom: 8px;
        }
        .tool-cost {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            margin: 12px 0;
        }
        .tool-location {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #888;
            margin-bottom: 12px;
        }
        .auth-badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border-radius: 4px;
            font-size: 11px;
            margin-bottom: 12px;
        }
        .tool-actions {
            display: flex;
            gap: 8px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-small {
            flex: 1;
            padding: 8px;
            background-color: rgba(255, 255, 255, 0.05);
            color: #c2c6d6;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-small:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .total-value {
            background: linear-gradient(135deg, var(--primary-container), #2d5bba);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            text-align: center;
        }
        .total-value h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .total-value .amount {
            font-size: 36px;
            font-weight: 700;
        }
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #888;
        }
    </style>
</head>
<body>
    <!-- Sidebar Consistente -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">🔧</div>
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
            <a href="<?= $basePath ?>/tools" class="nav-item active"><i class="fas fa-tools"></i> Herramientas</a>
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
            <?php if($user_role == 1): ?>
            <a href="<?= $basePath ?>/users" class="nav-item"><i class="fas fa-user-cog"></i> Usuarios</a>
            <a href="<?= $basePath ?>/settings" class="nav-item"><i class="fas fa-cog"></i> Configuración</a>
            <?php endif; ?>
        </nav>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>🏭 Herramientas de Bodega</h1>
            <?php $userRole = $user_role ?? getUserRole(); ?>
            <a href="<?= $basePath ?>/tools/warehouse/create" class="btn-primary" style="margin-left: 10px;">
                <i class="fas fa-plus"></i> Agregar Herramienta
            </a>
        </div>

        <div class="total-value">
            <h3>Valor Total del Inventario de Bodega</h3>
            <div class="amount">$<?= number_format($total_value, 2) ?></div>
        </div>

        <div class="stats-grid">
            <?php foreach ($stats as $stat): 
                $statusKey = trim($stat['status'] ?? '');
                $label = $statusLabels[$statusKey] ?? ($statusKey ? ucfirst(str_replace('_', ' ', $statusKey)) : 'Sin estado');
                $color = $statusColors[$statusKey] ?? '#888';
            ?>
            <div class="stat-card">
                <div class="stat-value" style="color: <?= $color ?>">
                    <?= $stat['count'] ?>
                </div>
                <div class="stat-label"><?= $label ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="filters">
            <a href="<?= $basePath ?>/tools/warehouse" class="filter-btn <?= !$selected_status ? 'active' : '' ?>">Todas</a>
            <a href="<?= $basePath ?>/tools/warehouse?status=disponible" class="filter-btn <?= $selected_status == 'disponible' ? 'active' : '' ?>">Disponibles</a>
            <a href="<?= $basePath ?>/tools/warehouse?status=solicitada" class="filter-btn <?= $selected_status == 'solicitada' ? 'active' : '' ?>">Solicitadas</a>
            <a href="<?= $basePath ?>/tools/warehouse?status=prestada" class="filter-btn <?= $selected_status == 'prestada' ? 'active' : '' ?>">Prestadas</a>
            <a href="<?= $basePath ?>/tools/warehouse?status=en_mantenimiento" class="filter-btn <?= $selected_status == 'en_mantenimiento' ? 'active' : '' ?>">En Reparación</a>
            <a href="<?= $basePath ?>/tools/warehouse?status=danada" class="filter-btn <?= $selected_status == 'danada' ? 'active' : '' ?>">Dañadas</a>
        </div>

        <?php if (empty($tools)): ?>
            <div class="empty-state">
                <h3>No hay herramientas en bodega</h3>
                <p>Agrega herramientas de alto valor para préstamo diario</p>
            </div>
        <?php else: ?>
            <div class="tools-grid">
                <?php foreach ($tools as $tool): ?>
                <div class="tool-card">
                    <div class="tool-header">
                        <div>
                            <div class="tool-title"><?= esc($tool['name']) ?></div>
                            <div class="tool-code"><?= $tool['code'] ? esc($tool['code']) : 'Sin código' ?></div>
                        </div>
                        <span class="status-badge" style="background-color: <?= ($statusColors[$tool['status']] ?? '#888') ?>20; color: <?= ($statusColors[$tool['status']] ?? '#888') ?>">
                            <?= $statusLabels[trim($tool['status'] ?? '')] ?? (trim($tool['status'] ?? '') ?: 'Sin estado') ?>
                        </span>
                    </div>
                    
                    <?php if ($tool['requires_auth']): ?>
                        <span class="auth-badge">⚠️ Requiere Autorización</span>
                    <?php endif; ?>
                    
                    <div class="tool-info">
                        <?= $tool['brand'] ? esc($tool['brand']) : '' ?>
                        <?= $tool['model'] ? ' - ' . esc($tool['model']) : '' ?>
                    </div>
                    
                    <?php if ($tool['description']): ?>
                        <div class="tool-info"><?= esc(substr($tool['description'], 0, 100)) ?>...</div>
                    <?php endif; ?>
                    
                    <div class="tool-cost">$<?= number_format($tool['cost'] ?? 0, 2) ?></div>
                    
                    <div class="tool-location">📍 <?= $tool['location'] ? esc($tool['location']) : 'Sin ubicación' ?></div>
                    
                    <?php if ($tool['status'] == 'disponible'): ?>
                        <div class="tool-actions">
                            <a href="<?= $basePath ?>/tools/requests/create?tool_id=<?= $tool['id'] ?>" class="btn-small">Solicitar Préstamo</a>
                            <?php if ($userRole == 1): ?>
                                <form method="POST" action="<?= $basePath ?>/tools/warehouse/repair/<?= $tool['id'] ?>" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn-small" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);" onclick="return confirm('¿Mandar esta herramienta a reparación?')">🔧 Reparar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($tool['status'] == 'solicitada'): ?>
                        <div class="tool-actions">
                            <span class="btn-small" style="background: #6f42c1; cursor: default;">⏳ Solicitada</span>
                            <?php if ($userRole == 1): ?>
                                <a href="<?= $basePath ?>/tools/warehouse/checkout/<?= $tool['id'] ?>" class="btn-small" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">📤 Entregar</a>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($loan_info[$tool['id']])): ?>
                            <div class="tool-info" style="margin-top: 8px; color: #6f42c1; font-size: 13px;">
                                <i class="fas fa-user"></i> Solicitada por: <strong><?= esc($loan_info[$tool['id']]['mechanic_name']) ?></strong>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($tool['status'] == 'prestada'): ?>
                        <div class="tool-actions">
                            <a href="<?= $basePath ?>/tools/warehouse/return/<?= $tool['id'] ?>" class="btn-small" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">📥 Devolver</a>
                        </div>
                        <?php if (isset($loan_info[$tool['id']])): ?>
                            <div class="tool-info" style="margin-top: 8px; color: #ffc107; font-size: 13px;">
                                <i class="fas fa-user"></i> Prestada a: <strong><?= esc($loan_info[$tool['id']]['mechanic_name']) ?></strong>
                                <br><small>Desde: <?= date('d/m/Y', strtotime($loan_info[$tool['id']]['request_date'])) ?></small>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($tool['status'] == 'en_mantenimiento'): ?>
                        <div class="tool-actions">
                            <span class="btn-small" style="background: #6c757d; cursor: default;">🔧 En Reparación</span>
                            <?php if ($userRole == 1): ?>
                                <form method="POST" action="<?= $basePath ?>/tools/warehouse/repaired/<?= $tool['id'] ?>" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn-small" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);" onclick="return confirm('¿Marcar esta herramienta como reparada?')">✅ Reparada</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($tool['status'] == 'danada'): ?>
                        <div class="tool-actions">
                            <span class="btn-small" style="background: #dc3545; cursor: default;">⚠️ Dañada</span>
                            <?php if ($userRole == 1): ?>
                                <form method="POST" action="<?= $basePath ?>/tools/warehouse/repair/<?= $tool['id'] ?>" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn-small" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);" onclick="return confirm('¿Mandar esta herramienta a reparación?')">🔧 Mandar a Reparar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Toast Notifications -->
    <?php include __DIR__ . '/../components/toast.php'; ?>
</body>
</html>
