<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

$statusLabels = [
    'recepcion' => 'Recepción',
    'diagnostico' => 'Diagnóstico',
    'reparacion' => 'Reparación',
    'terminado' => 'Terminado',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Vehículo - <?= esc($vehicle['plate'] ?? 'Sin patente') ?></title>
    <style>
        body { margin: 0; background: #0f131a; color: #e7ecf3; font-family: Arial, sans-serif; }
        .wrap { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .card { background: #1a202b; border: 1px solid #2b3442; border-radius: 12px; padding: 16px; margin-bottom: 14px; }
        h1, h2, h3 { margin-top: 0; }
        .muted { color: #9aa3b2; }
        .btn { display: inline-block; background: #4d8eff; color: #fff; text-decoration: none; padding: 10px 14px; border-radius: 8px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .item { background: #121822; border-radius: 8px; padding: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; border-bottom: 1px solid #2b3442; padding: 10px; }
        th { color: #9bb7ff; font-size: 12px; text-transform: uppercase; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <a class="btn" href="<?= $basePath ?>/vehicles">Volver a vehículos</a>
        <h1 style="margin-top: 12px;">🚗 Historial Completo del Vehículo</h1>
        <p class="muted">Patente: <strong><?= esc($vehicle['plate'] ?? 'Sin patente') ?></strong> · VIN: <strong><?= esc($vehicle['vin']) ?></strong></p>

        <div class="grid">
            <div class="item"><small>Cliente</small><div><strong><?= esc($vehicle['client_name']) ?></strong></div></div>
            <div class="item"><small>Marca / Modelo</small><div><strong><?= esc($vehicle['brand']) ?> <?= esc($vehicle['model']) ?></strong></div></div>
            <div class="item"><small>Año</small><div><strong><?= esc($vehicle['year']) ?></strong></div></div>
            <div class="item"><small>Color</small><div><strong><?= esc($vehicle['color'] ?? '-') ?></strong></div></div>
            <div class="item"><small>Motor</small><div><strong><?= esc($vehicle['engine'] ?? '-') ?></strong></div></div>
            <div class="item"><small>Kilometraje</small><div><strong><?= esc($vehicle['mileage'] ?? '-') ?></strong></div></div>
        </div>

        <?php if (!empty($vehicle['notes'])): ?>
            <p><strong>Notas:</strong> <?= esc($vehicle['notes']) ?></p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>📚 Órdenes de Trabajo Anteriores</h2>
        <?php if (empty($history)): ?>
            <p class="muted">No hay historial registrado para esta patente.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>OT</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Problema</th>
                        <th>Diagnóstico</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td>#<?= (int)$row['id'] ?></td>
                        <td><?= esc($row['created_at']) ?></td>
                        <td><?= esc($statusLabels[$row['status']] ?? $row['status']) ?></td>
                        <td><?= esc($row['problem_reported'] ?? $row['description'] ?? '-') ?></td>
                        <td><?= esc($row['diagnosis'] ?? '-') ?></td>
                        <td>$<?= number_format((float)$row['total_cost'], 0, ',', '.') ?></td>
                        <td><a class="btn" href="<?= $basePath ?>/work-orders/edit/<?= (int)$row['id'] ?>">Ver OT</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
