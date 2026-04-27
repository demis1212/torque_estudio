<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operación Inteligente | Torque</title>
    <style>
        body { font-family: Arial, sans-serif; background: #0e1116; color: #e7ecf3; margin: 0; }
        .wrap { max-width: 1200px; margin: 24px auto; padding: 0 16px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .title { font-size: 28px; margin: 0; }
        .muted { color: #9aa3b2; }
        .card { background: #171c24; border: 1px solid #283140; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #2b3545; text-align: left; }
        th { color: #9bb7ff; font-size: 13px; text-transform: uppercase; }
        .btn { display: inline-block; background: #4d8eff; color: #fff; padding: 8px 12px; border-radius: 8px; text-decoration: none; border: 0; cursor: pointer; }
        .status { font-size: 12px; padding: 4px 8px; border-radius: 999px; }
        .recepcion { background: #364152; }
        .diagnostico { background: #7a4a1f; }
        .reparacion { background: #004d61; }
        .terminado { background: #165a2b; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
        .kpi { background: #141922; border: 1px solid #263247; border-radius: 10px; padding: 12px; }
        .kpi b { display: block; font-size: 20px; margin-top: 4px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div>
            <h1 class="title">🧠 Operación Inteligente del Taller</h1>
            <div class="muted">Horas, pausas, rentabilidad, calidad y facturación por OT</div>
        </div>
        <div>
            <a class="btn" href="<?= $basePath ?>/dashboard">Volver al Dashboard</a>
        </div>
    </div>

    <div class="card">
        <h3>Tarifas por Hora Configuradas</h3>
        <div class="grid">
            <?php foreach ($rates as $rate): ?>
                <div class="kpi">
                    <small><?= esc($rate['label']) ?></small>
                    <b>$<?= number_format((float)$rate['amount'], 0, ',', '.') ?></b>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h3>Órdenes con Métricas en Tiempo Real</h3>
        <table>
            <thead>
            <tr>
                <th>OT</th>
                <th>Cliente / Vehículo</th>
                <th>Estado</th>
                <th>Horas Trab.</th>
                <th>Horas Cob.</th>
                <th>Labor</th>
                <th>Servicios</th>
                <th>Repuestos</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int)$o['id'] ?></td>
                    <td><?= esc($o['client_name']) ?> · <?= esc($o['brand']) ?> <?= esc($o['model']) ?> (<?= esc($o['plate']) ?>)</td>
                    <td><span class="status <?= esc($o['status']) ?>"><?= esc($o['status']) ?></span></td>
                    <td><?= round(((int)$o['worked_minutes']) / 60, 2) ?> h</td>
                    <td><?= round(((int)$o['billable_minutes']) / 60, 2) ?> h</td>
                    <td>$<?= number_format((float)$o['labor_cost'], 0, ',', '.') ?></td>
                    <td>$<?= number_format((float)$o['services_cost'], 0, ',', '.') ?></td>
                    <td>$<?= number_format((float)$o['parts_cost'], 0, ',', '.') ?></td>
                    <td><a class="btn" href="<?= $basePath ?>/workshop-ops/<?= (int)$o['id'] ?>">Gestionar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
