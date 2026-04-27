<?php
/**
 * Script de Diagnóstico Completo - Torque Studio ERP
 * Ejecutar: http://localhost/torque/diagnostico.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Diagnóstico Torque Studio</title><style>
body { font-family: monospace; background: #0a0c10; color: #e8eaf2; padding: 20px; }
.section { background: #11131a; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; margin-bottom: 20px; }
.ok { color: #4ade80; }
.error { color: #f87171; }
.warning { color: #fbbf24; }
.info { color: #8ab4f8; }
h2 { margin-top: 0; color: #8ab4f8; border-bottom: 2px solid rgba(138,180,248,0.3); padding-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { padding: 8px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); }
th { color: #9aa3b2; }
</style></head><body>";

echo "<h1>🔧 Diagnóstico Torque Studio ERP</h1>";

// ============================================
// SECCIÓN 1: Configuración PHP
// ============================================
echo "<div class='section'>";
echo "<h2>📋 Configuración PHP</h2>";
echo "<table>";
echo "<tr><th>Configuración</th><th>Valor</th><th>Estado</th></tr>";

$configs = [
    'display_errors' => ini_get('display_errors'),
    'display_startup_errors' => ini_get('display_startup_errors'),
    'error_reporting' => ini_get('error_reporting'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
];

foreach ($configs as $key => $value) {
    $status = ($key == 'display_errors' && $value) || ($key == 'error_reporting' && $value > 0) 
        ? "<span class='ok'>✓ OK</span>" 
        : "<span class='info'>$value</span>";
    echo "<tr><td>$key</td><td>$value</td><td>$status</td></tr>";
}
echo "</table></div>";

// ============================================
// SECCIÓN 2: Conexión Base de Datos
// ============================================
echo "<div class='section'>";
echo "<h2>🗄️ Base de Datos</h2>";

try {
    require_once __DIR__ . '/../config/database.php';
    $db = \Config\Database::getConnection();
    echo "<p class='ok'>✓ Conexión a BD exitosa</p>";
    
    // Verificar tablas
    $requiredTables = [
        'users', 'clients', 'vehicles', 'work_orders', 'services', 'parts',
        'work_order_time_entries', 'workshop_hourly_rates', 'billing_documents',
        'purchase_alerts', 'whatsapp_reminders', 'work_order_part_requests',
        'work_order_quality_checks', 'mechanic_tools', 'warehouse_tools', 
        'tool_requests'
    ];
    
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Estado</th></tr>";
    
    foreach ($requiredTables as $table) {
        try {
            $db->query("SELECT 1 FROM $table LIMIT 1");
            echo "<tr><td>$table</td><td class='ok'>✓ Existe</td></tr>";
        } catch (PDOException $e) {
            echo "<tr><td>$table</td><td class='error'>✗ Falta: " . $e->getMessage() . "</td></tr>";
        }
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error de conexión: " . $e->getMessage() . "</p>";
}
echo "</div>";

// ============================================
// SECCIÓN 3: Verificar Archivos Críticos
// ============================================
echo "<div class='section'>";
echo "<h2>📁 Archivos del Sistema</h2>";

echo "<table>";
echo "<tr><th>Archivo</th><th>Estado</th></tr>";

$criticalFiles = [
    '../app/helpers.php',
    '../app/models/Model.php',
    '../routes/web.php',
    '../app/controllers/AuthController.php',
    '../app/controllers/DashboardController.php',
    '../app/controllers/WorkOrderController.php',
    '../app/controllers/WorkshopOpsController.php',
    '../app/controllers/WhatsAppReminderController.php',
    '../app/models/WorkOrder.php',
    '../app/models/WorkshopOps.php',
    '../app/models/WhatsAppReminder.php',
    '../app/models/WorkOrderPartRequest.php',
    '../app/views/dashboard.php',
    '../app/views/work-orders/index.php',
    '../app/views/workshop-ops/index.php',
];

foreach ($criticalFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $syntax = shell_exec('php -l ' . escapeshellarg($fullPath) . ' 2>&1');
        if (strpos($syntax, 'No syntax errors') !== false) {
            echo "<tr><td>$file</td><td class='ok'>✓ OK</td></tr>";
        } else {
            echo "<tr><td>$file</td><td class='error'>✗ Error de sintaxis</td></tr>";
        }
    } else {
        echo "<tr><td>$file</td><td class='error'>✗ No existe</td></tr>";
    }
}
echo "</table></div>";

// ============================================
// SECCIÓN 4: Permisos de Carpetas
// ============================================
echo "<div class='section'>";
echo "<h2>🔐 Permisos</h2>";

echo "<table>";
echo "<tr><th>Carpeta/Archivo</th><th>Permisos</th><th>Estado</th></tr>";

$paths = [
    '../storage' => 'Escritura para logs',
    '../public' => 'Lectura',
    '../app/views' => 'Lectura',
];

foreach ($paths as $path => $requirement) {
    $fullPath = __DIR__ . '/' . $path;
    if (is_dir($fullPath) || is_file($fullPath)) {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        $isWritable = is_writable($fullPath);
        $status = ($requirement == 'Escritura para logs' && $isWritable) || $requirement == 'Lectura' 
            ? "<span class='ok'>✓ OK ($perms)</span>" 
            : "<span class='warning'>⚠ Verificar ($perms)</span>";
        echo "<tr><td>$path</td><td>$perms</td><td>$status</td></tr>";
    } else {
        echo "<tr><td>$path</td><td>-</td><td class='error'>✗ No existe</td></tr>";
    }
}
echo "</table></div>";

// ============================================
// SECCIÓN 5: Sesión y Variables
// ============================================
echo "<div class='section'>";
echo "<h2>👤 Sesión Actual</h2>";

session_start();

if (isset($_SESSION['user_id'])) {
    echo "<p class='ok'>✓ Usuario logueado</p>";
    echo "<table>";
    echo "<tr><th>Variable</th><th>Valor</th></tr>";
    foreach ($_SESSION as $key => $value) {
        $display = is_array($value) ? json_encode($value) : htmlspecialchars($value);
        echo "<tr><td>$key</td><td>$display</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠ No hay sesión activa</p>";
}
echo "</div>";

// ============================================
// SECCIÓN 6: Extensiones PHP Requeridas
// ============================================
echo "<div class='section'>";
echo "<h2>🔌 Extensiones PHP</h2>";

echo "<table>";
echo "<tr><th>Extensión</th><th>Estado</th></tr>";

$extensions = ['pdo', 'pdo_mysql', 'session', 'json', 'mbstring', 'openssl'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "<span class='ok'>✓ Cargada</span>" : "<span class='error'>✗ No cargada</span>";
    echo "<tr><td>$ext</td><td>$status</td></tr>";
}
echo "</table></div>";

echo "<div class='section'>";
echo "<h2>✅ Diagnóstico Completado</h2>";
echo "<p class='info'>Si encuentras errores en rojo, indícame cuáles son para solucionarlos.</p>";
echo "<p><a href='/torque/dashboard' style='color: #8ab4f8;'>← Volver al Dashboard</a></p>";
echo "</div>";

echo "</body></html>";
