<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión BD
try {
    require_once __DIR__ . '/../config/database.php';
    $db = \Config\Database::getConnection();
    $dbStatus = 'OK';
} catch (Exception $e) {
    $dbStatus = 'ERROR: ' . $e->getMessage();
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
<style>
body { font-family: monospace; background: #0a0c10; color: #e8eaf2; padding: 20px; }
.ok { color: #4ade80; } .error { color: #f87171; } .warning { color: #fbbf24; }
h1 { color: #8ab4f8; } h2 { color: #fbbf24; border-bottom: 1px solid #333; padding-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { padding: 10px; border-bottom: 1px solid #333; text-align: left; }
th { color: #9aa3b2; background: rgba(255,255,255,0.05); }
</style></head><body>";

echo "<h1>🔍 Análisis del Sistema Torque Studio</h1>";

// 1. CONEXIÓN BD
echo "<h2>1. Base de Datos</h2>";
echo "<p>Estado: <b class='" . ($dbStatus == 'OK' ? 'ok' : 'error') . "'>$dbStatus</b></p>";

if ($dbStatus == 'OK') {
    // Tablas
    $tables = ['users','clients','vehicles','work_orders','services','parts',
               'work_order_time_entries','whatsapp_reminders','purchase_alerts',
               'work_order_part_requests','work_order_quality_checks'];
    echo "<table><tr><th>Tabla</th><th>Registros</th><th>Estado</th></tr>";
    foreach ($tables as $t) {
        try {
            $count = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
            echo "<tr><td>$t</td><td>$count</td><td class='ok'>OK</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td>$t</td><td>-</td><td class='error'>FALTA</td></tr>";
        }
    }
    echo "</table>";
}

// 2. ARCHIVOS PHP
echo "<h2>2. Archivos PHP - Sintaxis</h2>";
$dirs = [
    '../app/controllers' => 'Controllers',
    '../app/models' => 'Models', 
    '../app/views' => 'Views'
];

echo "<table><tr><th>Directorio</th><th>Total</th><th>OK</th><th>Errores</th></tr>";
foreach ($dirs as $dir => $name) {
    $files = glob(__DIR__ . "/$dir/*.php");
    $ok = 0; $err = 0;
    foreach ($files as $f) {
        $r = shell_exec('php -l ' . escapeshellarg($f) . ' 2>&1');
        (strpos($r, 'No syntax errors') !== false) ? $ok++ : $err++;
    }
    $cls = $err > 0 ? 'error' : 'ok';
    echo "<tr><td>$name</td><td>" . count($files) . "</td><td class='ok'>$ok</td><td class='$cls'>$err</td></tr>";
}
echo "</table>";

// 3. SESIÓN
echo "<h2>3. Sesión</h2>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p class='ok'>✓ Usuario logueado: " . $_SESSION['user_name'] . " (Rol: " . $_SESSION['user_role'] . ")</p>";
} else {
    echo "<p class='warning'>⚠ No hay sesión activa</p>";
}

// 4. PERMISOS CARPETAS
echo "<h2>4. Permisos de Carpetas</h2>";
$folders = ['../storage', '../public', '../app/views'];
echo "<table><tr><th>Carpeta</th><th>Existe</th><th>Escritura</th></tr>";
foreach ($folders as $f) {
    $exists = file_exists(__DIR__ . "/$f") ? 'ok' : 'error';
    $write = is_writable(__DIR__ . "/$f") ? 'ok' : 'error';
    echo "<tr><td>$f</td><td class='$exists'>" . ($exists=='ok'?'✓':'✗') . "</td><td class='$write'>" . ($write=='ok'?'✓':'✗') . "</td></tr>";
}
echo "</table>";

// 5. EXTENSIONES PHP
echo "<h2>5. Extensiones PHP Requeridas</h2>";
$exts = ['pdo', 'pdo_mysql', 'session', 'json', 'mbstring'];
echo "<table><tr><th>Extensión</th><th>Estado</th></tr>";
foreach ($exts as $e) {
    $loaded = extension_loaded($e);
    echo "<tr><td>$e</td><td class='" . ($loaded?'ok':'error') . "'>" . ($loaded?'✓ Cargada':'✗ No cargada') . "</td></tr>";
}
echo "</table>";

echo "<p><a href='/torque/dashboard' style='color:#8ab4f8'>← Volver al Dashboard</a></p>";
echo "</body></html>";
