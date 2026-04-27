<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$subtotal = $workOrder['total_cost'];
$taxRate = 18;
$tax = $subtotal * ($taxRate / 100);
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?= $workOrder['id'] ?> - Torque Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .company-info h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            color: #10131a;
        }
        .company-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0 0 8px 0;
            font-size: 24px;
            color: #4d8eff;
        }
        .invoice-details p {
            margin: 4px 0;
            color: #666;
            font-size: 14px;
        }
        .customer-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .customer-info h3 {
            margin: 0 0 12px 0;
            font-size: 16px;
            color: #666;
            text-transform: uppercase;
        }
        .customer-info p {
            margin: 4px 0;
            font-size: 14px;
        }
        .vehicle-info {
            margin-bottom: 30px;
        }
        .vehicle-info h3 {
            margin: 0 0 12px 0;
            font-size: 16px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            color: #666;
        }
        td { font-size: 14px; }
        .text-right { text-align: right; }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals table { margin-bottom: 0; }
        .totals td {
            border: none;
            padding: 8px 12px;
        }
        .totals .total-row {
            font-size: 18px;
            font-weight: 700;
            border-top: 2px solid #333;
        }
        .notes {
            margin-top: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .notes h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #666;
        }
        .notes p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #4d8eff;
            color: #fff;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-container { box-shadow: none; max-width: 100%; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1>Torque Studio</h1>
                <p>Taller Automotriz Especializado</p>
                <p>Av. Principal #123, Santo Domingo</p>
                <p>Tel: 809-555-0000</p>
            </div>
            <div class="invoice-details">
                <h2>FACTURA</h2>
                <p><strong>Nº:</strong> <?= str_pad($workOrder['id'], 6, '0', STR_PAD_LEFT) ?></p>
                <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($workOrder['created_at'])) ?></p>
                <p><strong>Estado:</strong> <?= ucfirst($workOrder['status']) ?></p>
            </div>
        </div>

        <div class="customer-info">
            <h3>Cliente</h3>
            <p><strong><?= esc($workOrder['client_name']) ?></strong></p>
        </div>

        <div class="vehicle-info">
            <h3>Vehículo</h3>
            <p><strong><?= esc($workOrder['brand']) ?> <?= esc($workOrder['model']) ?></strong> - Placa: <?= esc($workOrder['plate']) ?></p>
        </div>

        <?php if (!empty($services)): ?>
        <h3>Servicios</h3>
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= esc($service['service_name']) ?></td>
                    <td class="text-right"><?= $service['quantity'] ?></td>
                    <td class="text-right">$<?= number_format($service['price'], 2) ?></td>
                    <td class="text-right">$<?= number_format($service['quantity'] * $service['price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if (!empty($parts)): ?>
        <h3>Repuestos</h3>
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parts as $part): ?>
                <tr>
                    <td><?= esc($part['part_name']) ?> (<?= esc($part['code']) ?>)</td>
                    <td class="text-right"><?= $part['quantity'] ?></td>
                    <td class="text-right">$<?= number_format($part['price'], 2) ?></td>
                    <td class="text-right">$<?= number_format($part['quantity'] * $part['price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">$<?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr>
                    <td>ITBIS (<?= $taxRate ?>%):</td>
                    <td class="text-right">$<?= number_format($tax, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td class="text-right">$<?= number_format($total, 2) ?></td>
                </tr>
            </table>
        </div>

        <div class="notes">
            <h4>Notas</h4>
            <p><?= esc($workOrder['description']) ?: 'Sin notas adicionales.' ?></p>
        </div>

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
            <a href="<?= $basePath ?>/work-orders/edit/<?= $workOrder['id'] ?>" class="btn btn-secondary">← Volver a la Orden</a>
        </div>
    </div>
</body>
</html>
