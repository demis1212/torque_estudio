<?php
header('Content-Type: text/html; charset=utf-8');

echo '<h1>🛠️ Instalador Workshop Ops</h1>';

try {
    require_once dirname(__DIR__) . '/config/database.php';
    $db = Config\Database::getConnection();

    $sqlFile = dirname(__DIR__) . '/database/workshop_intelligent_upgrade.sql';
    if (!file_exists($sqlFile)) {
        throw new RuntimeException('No existe el archivo SQL: ' . $sqlFile);
    }

    $sql = file_get_contents($sqlFile);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('El archivo SQL está vacío o no se pudo leer.');
    }

    // Ejecutar sentencias separadas por ;
    $statements = preg_split('/;\s*\n/', $sql);
    $executed = 0;
    $skipped = 0;

    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '' || strpos($stmt, '--') === 0) {
            $skipped++;
            continue;
        }

        try {
            $db->exec($stmt);
            $executed++;
        } catch (Throwable $e) {
            // Ignorar errores benignos por columnas/tablas ya existentes
            $msg = $e->getMessage();
            if (
                stripos($msg, 'Duplicate') !== false ||
                stripos($msg, 'already exists') !== false ||
                stripos($msg, 'Duplicate column') !== false
            ) {
                $skipped++;
                continue;
            }

            echo '<p style="color:#f87171;">❌ Error en sentencia SQL:</p>';
            echo '<pre style="background:#1f2937;color:#e5e7eb;padding:12px;border-radius:8px;white-space:pre-wrap;">' . htmlspecialchars($stmt) . "\n\n" . htmlspecialchars($msg) . '</pre>';
            throw $e;
        }
    }

    // Verificación mínima de tablas críticas
    $required = [
        'workshop_hourly_rates',
        'work_order_time_entries',
        'work_order_pause_events',
        'work_order_quality_checks',
        'billing_documents',
        'whatsapp_reminders',
        'work_order_media',
    ];

    $ok = true;
    echo '<h2>✅ Verificación</h2><ul>';
    foreach ($required as $table) {
        $q = $db->query("SHOW TABLES LIKE '" . $table . "'");
        $exists = $q && $q->fetchColumn();
        if ($exists) {
            echo '<li>✅ ' . htmlspecialchars($table) . '</li>';
        } else {
            echo '<li>❌ ' . htmlspecialchars($table) . ' (no creada)</li>';
            $ok = false;
        }
    }
    echo '</ul>';

    echo '<p>Sentencias ejecutadas: <strong>' . $executed . '</strong><br>Omitidas: <strong>' . $skipped . '</strong></p>';

    if ($ok) {
        echo '<p style="color:#22c55e;"><strong>Instalación completada correctamente.</strong></p>';
        echo '<p><a href="/torque/workshop-ops" style="display:inline-block;padding:10px 16px;background:#4d8eff;color:#fff;text-decoration:none;border-radius:8px;">Ir a Workshop Ops</a></p>';
    } else {
        echo '<p style="color:#f59e0b;"><strong>Instalación parcial. Revisa la verificación.</strong></p>';
    }

} catch (Throwable $e) {
    echo '<p style="color:#f87171;"><strong>Fallo:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
}
