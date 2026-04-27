<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OT #<?= (int)$order['id'] ?> | Operación Inteligente</title>
    <style>
        body { font-family: Arial, sans-serif; background: #0e1116; color: #e7ecf3; margin: 0; }
        .wrap { max-width: 1250px; margin: 22px auto; padding: 0 16px; }
        .top { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .card { background: #171c24; border: 1px solid #283140; border-radius: 12px; padding: 14px; margin-bottom: 14px; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .kpi { background: #141922; border: 1px solid #263247; border-radius: 10px; padding: 12px; }
        .kpi small { color: #9aa3b2; }
        .kpi b { display: block; margin-top: 4px; font-size: 20px; }
        .btn { display: inline-block; border: 0; border-radius: 8px; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-main { background: #4d8eff; color: #fff; }
        .btn-danger { background: #b43a3a; color: #fff; }
        .btn-ok { background: #267a4b; color: #fff; }
        .row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        input, select, textarea { background: #0f131a; border: 1px solid #2c3645; color: #e7ecf3; border-radius: 8px; padding: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #2b3545; padding: 8px; text-align: left; }
        th { font-size: 12px; color: #9bb7ff; text-transform: uppercase; }
        label { font-size: 13px; color: #b5becc; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top card">
        <div>
            <h2 style="margin:0;">OT #<?= (int)$order['id'] ?> · <?= esc($order['client_name']) ?></h2>
            <div style="color:#9aa3b2;"><?= esc($order['brand']) ?> <?= esc($order['model']) ?> <?= esc($order['year']) ?> · Patente <?= esc($order['plate']) ?></div>
        </div>
        <div class="row">
            <a class="btn btn-main" href="<?= $basePath ?>/workshop-ops">Volver</a>
            <a class="btn btn-main" href="<?= $basePath ?>/work-orders/edit/<?= (int)$order['id'] ?>">Ir a OT</a>
        </div>
    </div>

    <div class="card grid">
        <div class="kpi"><small>Horas reales</small><b><?= round(((int)$metrics['worked_minutes']) / 60, 2) ?> h</b></div>
        <div class="kpi"><small>Tiempo muerto/pausas</small><b><?= round(((int)$metrics['paused_minutes']) / 60, 2) ?> h</b></div>
        <div class="kpi"><small>Horas cobrables</small><b><?= round(((int)$metrics['billable_minutes']) / 60, 2) ?> h</b></div>
        <div class="kpi"><small>Horas no cobrables</small><b><?= round(((int)$metrics['non_billable_minutes']) / 60, 2) ?> h</b></div>
    </div>

    <div class="card grid">
        <div class="kpi"><small>Labor</small><b>$<?= number_format((float)$metrics['labor_cost'], 0, ',', '.') ?></b></div>
        <div class="kpi"><small>Servicios</small><b>$<?= number_format((float)$metrics['services_cost'], 0, ',', '.') ?></b></div>
        <div class="kpi"><small>Repuestos</small><b>$<?= number_format((float)$metrics['parts_cost'], 0, ',', '.') ?></b></div>
        <div class="kpi"><small>Total + IVA</small><b>$<?= number_format((float)$metrics['total_amount'], 0, ',', '.') ?></b></div>
    </div>

    <div class="card">
        <h3>⏱️ Control de Tiempo Mecánico</h3>
        <?php if ($activeEntry): ?>
            <p>Estado actual: <b><?= esc($activeEntry['status']) ?></b> · Tarifa: <b><?= esc($activeEntry['rate_code']) ?></b></p>
        <?php else: ?>
            <p>No hay trabajo activo para esta OT.</p>
        <?php endif; ?>

        <div class="row" style="margin-top: 10px;">
            <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= (int)$order['id'] ?>/start" class="row">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <label>Tarifa</label>
                <select name="rate_code">
                    <?php foreach ($rates as $rate): ?>
                        <option value="<?= esc($rate['code']) ?>"><?= esc($rate['label']) ?> ($<?= number_format((float)$rate['amount'], 0, ',', '.') ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="notes" placeholder="Notas inicio">
                <button class="btn btn-ok" type="submit">INICIAR TRABAJO</button>
            </form>

            <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= (int)$order['id'] ?>/pause" class="row">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <select name="reason">
                    <option value="espera_repuestos">Espera repuestos</option>
                    <option value="cambio_mecanico">Cambio mecánico</option>
                    <option value="almuerzo">Almuerzo</option>
                    <option value="cliente_autoriza">Cliente autoriza</option>
                    <option value="espera_diagnostico">Espera diagnóstico</option>
                    <option value="falta_herramienta">Falta herramienta</option>
                    <option value="otro">Otro</option>
                </select>
                <input type="text" name="notes" placeholder="Notas pausa">
                <button class="btn btn-danger" type="submit">PAUSAR</button>
            </form>

            <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= (int)$order['id'] ?>/resume" class="row">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button class="btn btn-main" type="submit">REANUDAR</button>
            </form>

            <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= (int)$order['id'] ?>/finish" class="row">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button class="btn btn-ok" type="submit">FINALIZAR</button>
            </form>
        </div>

        <h4 style="margin-top: 16px;">Bitácora de tiempos</h4>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Mecánico</th>
                <th>Tarifa</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Pausa</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= (int)$entry['id'] ?></td>
                    <td><?= esc($entry['mechanic_name']) ?></td>
                    <td><?= esc($entry['rate_label']) ?></td>
                    <td><?= esc($entry['started_at']) ?></td>
                    <td><?= esc($entry['ended_at'] ?? '-') ?></td>
                    <td><?= (int)$entry['paused_minutes'] ?> min</td>
                    <td><?= esc($entry['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>✅ Control de Calidad Final</h3>
        <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= (int)$order['id'] ?>/quality">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <?php
                $q = $quality ?: [];
            ?>
            <div class="row">
                <label><input type="checkbox" name="work_done_ok" <?= !empty($q['work_done_ok']) ? 'checked' : '' ?>> Trabajo realizado correctamente</label>
                <label><input type="checkbox" name="torque_applied_ok" <?= !empty($q['torque_applied_ok']) ? 'checked' : '' ?>> Torque aplicado</label>
                <label><input type="checkbox" name="no_leaks_ok" <?= !empty($q['no_leaks_ok']) ? 'checked' : '' ?>> Sin fugas</label>
                <label><input type="checkbox" name="no_dashboard_lights_ok" <?= !empty($q['no_dashboard_lights_ok']) ? 'checked' : '' ?>> Sin luces tablero</label>
                <label><input type="checkbox" name="road_test_ok" <?= !empty($q['road_test_ok']) ? 'checked' : '' ?>> Prueba ruta OK</label>
                <label><input type="checkbox" name="cleaning_ok" <?= !empty($q['cleaning_ok']) ? 'checked' : '' ?>> Limpieza OK</label>
                <label><input type="checkbox" name="client_informed_ok" <?= !empty($q['client_informed_ok']) ? 'checked' : '' ?>> Cliente informado</label>
            </div>
            <div class="row" style="margin-top:10px;">
                <input type="text" name="signed_name" value="<?= esc($q['signed_name'] ?? $userName) ?>" placeholder="Firma jefe taller" style="min-width: 250px;">
                <textarea name="notes" placeholder="Notas" rows="2" cols="60"><?= esc($q['notes'] ?? '') ?></textarea>
                <button class="btn btn-ok" type="submit">Guardar Checklist</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>🧾 Facturación / Boleta</h3>
        <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= (int)$order['id'] ?>/billing" class="row">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <select name="document_type">
                <option value="boleta">Boleta</option>
                <option value="factura">Factura</option>
                <option value="cotizacion">Cotización</option>
                <option value="presupuesto">Presupuesto</option>
            </select>
            <input type="text" name="document_number" placeholder="Número documento" required>
            <select name="payment_method">
                <option value="">Método pago</option>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="tarjeta">Tarjeta</option>
            </select>
            <select name="payment_status">
                <option value="pendiente">Pendiente</option>
                <option value="pagado">Pagado</option>
                <option value="abono">Abono</option>
            </select>
            <button class="btn btn-main" type="submit">Generar Documento</button>
        </form>

        <table style="margin-top:12px;">
            <thead>
            <tr>
                <th>Tipo</th>
                <th>Número</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Total</th>
                <th>Saldo</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($billingDocs as $doc): ?>
                <tr>
                    <td><?= esc($doc['document_type']) ?></td>
                    <td><?= esc($doc['document_number']) ?></td>
                    <td><?= esc($doc['issued_at']) ?></td>
                    <td><?= esc($doc['payment_status']) ?></td>
                    <td>$<?= number_format((float)$doc['total_amount'], 0, ',', '.') ?></td>
                    <td>$<?= number_format((float)$doc['pending_balance'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 🔧 GESTIÓN INTELIGENTE DE REPUESTOS -->
    <div class="card" style="margin-top: 20px;">
        <h3>🔧 Solicitudes de Repuestos</h3>
        
        <!-- Formulario para solicitar repuesto -->
        <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= $order['id'] ?>/request-part" style="margin-bottom: 20px; padding: 15px; background: rgba(138,180,248,0.05); border-radius: 8px;">
            <h4 style="margin-top: 0; color: var(--primary);">Solicitar Nuevo Repuesto</h4>
            <div class="row" style="gap: 10px; flex-wrap: wrap;">
                <div style="flex: 2; min-width: 200px;">
                    <label>Repuesto (seleccionar del inventario o escribir nombre)</label>
                    <select name="part_id" style="width: 100%; padding: 8px; margin-bottom: 8px;">
                        <option value="">-- Seleccionar del inventario --</option>
                        <?php foreach ($parts as $part): ?>
                            <option value="<?= $part['id'] ?>">
                                <?= esc($part['name']) ?> (Stock: <?= $part['stock'] ?>) - $<?= number_format($part['price'], 0, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="part_name" placeholder="O escribir nombre si no está en inventario" style="width: 100%; padding: 8px;">
                </div>
                <div style="flex: 1; min-width: 100px;">
                    <label>Cantidad</label>
                    <input type="number" name="quantity" value="1" min="1" style="width: 100%; padding: 8px;">
                </div>
                <div style="flex: 2; min-width: 200px;">
                    <label>Notas</label>
                    <input type="text" name="notes" placeholder="Notas adicionales..." style="width: 100%; padding: 8px;">
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-main" style="height: fit-content;">Solicitar</button>
                </div>
            </div>
        </form>

        <!-- Lista de solicitudes -->
        <?php if (!empty($partRequests)): ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(255,255,255,0.05);">
                        <th style="padding: 10px; text-align: left;">Repuesto</th>
                        <th style="padding: 10px;">Cant.</th>
                        <th style="padding: 10px;">Estado</th>
                        <th style="padding: 10px;">Solicitado por</th>
                        <th style="padding: 10px;">Notas</th>
                        <?php if ($userRole == 1): ?>
                            <th style="padding: 10px;">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partRequests as $req): ?>
                        <?php
                            $statusColors = [
                                'pendiente' => '#ffc107',
                                'aprobado' => '#28a745',
                                'rechazado' => '#dc3545',
                                'despachado' => '#17a2b8'
                            ];
                            $statusColor = $statusColors[$req['status']] ?? '#6c757d';
                        ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 10px;">
                                <strong><?= esc($req['part_name']) ?></strong>
                                <?php if ($req['part_id']): ?>
                                    <br><small style="color: #888;">(En inventario)</small>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px; text-align: center;"><?= (int)$req['quantity'] ?></td>
                            <td style="padding: 10px; text-align: center;">
                                <span style="background: <?= $statusColor ?>20; color: <?= $statusColor ?>; padding: 4px 12px; border-radius: 12px; font-size: 12px; text-transform: uppercase;">
                                    <?= esc($req['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 10px; text-align: center;"><?= esc($req['requested_by_name'] ?? 'N/A') ?></td>
                            <td style="padding: 10px;"><small><?= esc($req['notes'] ?? '-') ?></small></td>
                            <?php if ($userRole == 1): ?>
                                <td style="padding: 10px; text-align: center;">
                                    <?php if ($req['status'] == 'pendiente'): ?>
                                        <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= $order['id'] ?>/approve-part/<?= $req['id'] ?>" style="display: inline;">
                                            <button type="submit" class="btn btn-ok" style="padding: 4px 12px; font-size: 12px;">Aprobar</button>
                                        </form>
                                        <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= $order['id'] ?>/reject-part/<?= $req['id'] ?>" style="display: inline;">
                                            <button type="submit" class="btn" style="padding: 4px 12px; font-size: 12px; background: rgba(220,53,69,0.2); color: #f87171;">Rechazar</button>
                                        </form>
                                    <?php elseif ($req['status'] == 'aprobado'): ?>
                                        <form method="POST" action="<?= $basePath ?>/workshop-ops/<?= $order['id'] ?>/despachar-part/<?= $req['id'] ?>" style="display: inline;">
                                            <button type="submit" class="btn btn-main" style="padding: 4px 12px; font-size: 12px;">Despachar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #888; text-align: center; padding: 20px;">No hay solicitudes de repuestos para esta orden.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
