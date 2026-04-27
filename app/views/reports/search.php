<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar - Torque Studio ERP</title>
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
        }
        .nav-item.active { background-color: var(--primary-container); color: #fff; }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        .search-box {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .search-box h1 { margin: 0 0 20px 0; }
        .search-form {
            display: flex;
            gap: 12px;
        }
        .search-form input {
            flex: 1;
            padding: 16px 20px;
            background-color: #0F1115;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--on-surface);
            font-size: 18px;
            outline: none;
        }
        .search-form input:focus { border-color: var(--primary-container); }
        .search-form button {
            padding: 16px 32px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .results-section {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 20px;
        }
        .results-section h3 {
            font-family: 'Space Grotesk', sans-serif;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.1em;
            margin: 0 0 16px 0;
            color: #a7b6cc;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .result-count {
            background-color: var(--primary-container);
            color: #fff;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .result-item {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .result-item:last-child { border-bottom: none; }
        .result-item strong { color: var(--on-surface); display: block; }
        .result-item span { color: #888; font-size: 14px; }
        .result-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #c2c6d6;
        }
        .no-results {
            text-align: center;
            padding: 32px;
            color: #888;
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
        <a href="<?= $basePath ?>/search" class="nav-item active">🔍 Buscar</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 16px 0;">
        <a href="<?= $basePath ?>/manuals" class="nav-item">📚 Manuales</a>
        <a href="<?= $basePath ?>/vin-decoder" class="nav-item">🔍 Decodificador VIN</a>
        <a href="<?= $basePath ?>/dtc" class="nav-item">🔧 DTC Codes</a>
    </div>
    <div class="main-content">
        <div class="search-box">
            <h1>Buscar en el Sistema</h1>
            <form method="GET" action="<?= $basePath ?>/search" class="search-form">
                <input type="text" name="q" value="<?= esc($query) ?>" placeholder="Buscar clientes, órdenes, repuestos..." autofocus>
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if (empty($query)): ?>
            <div class="empty-state">
                <p>Ingresa al menos 2 caracteres para buscar</p>
            </div>
        <?php elseif (empty($results['work_orders']) && empty($results['clients']) && empty($results['parts'])): ?>
            <div class="no-results">
                <p>No se encontraron resultados para "<?= esc($query) ?>"</p>
            </div>
        <?php else: ?>
            
            <?php if (!empty($results['work_orders'])): ?>
            <div class="results-section">
                <h3>Órdenes de Trabajo <span class="result-count"><?= count($results['work_orders']) ?></span></h3>
                <?php foreach ($results['work_orders'] as $order): ?>
                    <div class="result-item">
                        <div>
                            <strong>Orden #<?= $order['id'] ?> - <?= esc($order['client_name']) ?></strong>
                            <span><?= esc($order['brand']) ?> <?= esc($order['model']) ?> | <?= esc($order['plate']) ?> | Estado: <?= $order['status'] ?></span>
                        </div>
                        <a href="<?= $basePath ?>/work-orders/edit/<?= $order['id'] ?>">Ver →</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['clients'])): ?>
            <div class="results-section">
                <h3>Clientes <span class="result-count"><?= count($results['clients']) ?></span></h3>
                <?php foreach ($results['clients'] as $client): ?>
                    <div class="result-item">
                        <div>
                            <strong><?= esc($client['name']) ?></strong>
                            <span><?= esc($client['phone']) ?> | <?= esc($client['email']) ?></span>
                        </div>
                        <a href="<?= $basePath ?>/clients/edit/<?= $client['id'] ?>">Ver →</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['parts'])): ?>
            <div class="results-section">
                <h3>Repuestos <span class="result-count"><?= count($results['parts']) ?></span></h3>
                <?php foreach ($results['parts'] as $part): ?>
                    <div class="result-item">
                        <div>
                            <strong><?= esc($part['code']) ?> - <?= esc($part['name']) ?></strong>
                            <span>Stock: <?= $part['quantity'] ?> | $<?= number_format($part['sale_price'], 2) ?></span>
                        </div>
                        <a href="<?= $basePath ?>/parts/edit/<?= $part['id'] ?>">Ver →</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>
