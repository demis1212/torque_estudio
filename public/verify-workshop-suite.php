<?php
/**
 * Script de verificación del Workshop Intelligent Suite
 * Ejecutar en navegador: http://localhost/torque/verify-workshop-suite.php
 */

require_once __DIR__ . '/../config/database.php';

echo "<html><head><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #0a0c10; color: #e8eaf2; }
.success { color: #4ade80; } .error { color: #f87171; } .warning { color: #fbbf24; }
h1 { color: #8ab4f8; } h2 { color: #4d8eff; margin-top: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border-bottom: 1px solid #333; }
th { background: #1a1d26; }
.ok { color: #4ade80; font-weight: bold; }
.fail { color: #f87171; font-weight: bold; }
</style></head><body>";

echo "<h1>🔧 Workshop Intelligent Suite - Verificación de Instalación</h1>";

$errors = [];
$warnings = [];

try {
    $db = Database::getInstance()->getConnection();
    echo "<p class='success'>✅ Conexión a base de datos: OK</p>";
    
    // Verificar tablas
    $tables = [
        'work_order_time_entries' => 'Control de tiempo mecánico',
        'workshop_hourly_rates' => 'Tarifas por hora',
        'work_order_quality_checks' => 'Checklist de calidad',
        'billing_documents' => 'Documentos de facturación',
        'purchase_alerts' => 'Alertas de compra',
        'whatsapp_reminders' => 'Recordatorios WhatsApp'
    ];
    
    echo "<h2>📋 Verificación de Tablas</h2><table>";
    echo "<tr><th>Tabla</th><th>Descripción</th><th>Estado</th></tr>";
    
    foreach ($tables as $table => $desc) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        $status = $exists ? "<span class='ok'>✅ Existe</span>" : "<span class='fail'>❌ Faltante</span>";
        if (!$exists) $errors[] = "Tabla '$table' no existe";
        echo "<tr><td>$table</td><td>$desc</td><td>$status</td></tr>";
    }
    echo "</table>";
    
    // Verificar columnas en users
    echo "<h2>👤 Verificación de Columnas (users)</h2><table>";
    echo "<tr><th>Columna</th><th>Estado</th></tr>";
    
    $columns = ['hourly_rate'];
    foreach ($columns as $col) {
        try {
            $db->query("SELECT $col FROM users LIMIT 1");
            echo "<tr><td>$col</td><td class='ok'>✅ OK</td></tr>";
        } catch (PDOException $e) {
            echo "<tr><td>$col</td><td class='fail'>❌ Faltante</td></tr>";
            $errors[] = "Columna '$col' faltante en tabla users";
        }
    }
    echo "</table>";
    
    // Verificar archivos
    echo "<h2>📁 Verificación de Archivos</h2><table>";
    echo "<tr><th>Archivo</th><th>Estado</th></tr>";
    
    $files = [
        '../app/models/WorkshopOps.php' => 'Modelo Operación Inteligente',
        '../app/models/PurchaseAlert.php' => 'Modelo Alertas de Compra',
        '../app/models/WhatsAppReminder.php' => 'Modelo WhatsApp',
        '../app/controllers/WhatsAppReminderController.php' => 'Controlador WhatsApp',
        '../app/views/workshop-ops/index.php' => 'Vista Operación Inteligente',
        '../app/views/parts/alerts.php' => 'Vista Alertas de Compra',
        '../app/views/whatsapp-reminders/index.php' => 'Vista WhatsApp',
        '../app/views/reports/mechanic-productivity.php' => 'Vista Productividad',
        '../app/views/reports/manager-dashboard.php' => 'Vista Dashboard Gerencial'
    ];
    
    foreach ($files as $file => $desc) {
        $exists = file_exists(__DIR__ . '/' . $file);
        $status = $exists ? "<span class='ok'>✅ Existe</span>" : "<span class='fail'>❌ Faltante</span>";
        if (!$exists) $errors[] = "Archivo '$file' no encontrado";
        echo "<tr><td>$desc</td><td>$status</td></tr>";
    }
    echo "</table>";
    
    // Verificar rutas
    echo "<h2>🔗 Verificación de Rutas</h2><table>";
    echo "<tr><th>Ruta</th><th>Descripción</th></tr>";
    
    $routes = [
        '/workshop-ops' => 'Operación Inteligente',
        '/parts/alerts' => 'Alertas de Compra',
        '/whatsapp-reminders' => 'WhatsApp Reminders',
        '/reports/mechanic-productivity' => 'Productividad Mecánico',
        '/reports/manager-dashboard' => 'Dashboard Gerencial'
    ];
    
    foreach ($routes as $route => $desc) {
        echo "<tr><td><a href='$route' style='color: #8ab4f8;'>$route</a></td><td>$desc</td></tr>";
    }
    echo "</table>";
    
    // Conteos de datos
    echo "<h2>📊 Resumen de Datos</h2><table>";
    echo "<tr><th>Métrica</th><th>Valor</th></tr>";
    
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM purchase_alerts WHERE status = 'pendiente'");
        $pendingAlerts = $stmt->fetchColumn();
        echo "<tr><td>Alertas de compra pendientes</td><td>$pendingAlerts</td></tr>";
        
        $stmt = $db->query("SELECT COUNT(*) FROM whatsapp_reminders WHERE status = 'programado'");
        $pendingWhatsApp = $stmt->fetchColumn();
        echo "<tr><td>Recordatorios WhatsApp programados</td><td>$pendingWhatsApp</td></tr>";
        
        $stmt = $db->query("SELECT COUNT(*) FROM billing_documents");
        $invoices = $stmt->fetchColumn();
        echo "<tr><td>Facturas emitidas</td><td>$invoices</td></tr>";
        
        $stmt = $db->query("SELECT COUNT(*) FROM work_order_time_entries");
        $timeEntries = $stmt->fetchColumn();
        echo "<tr><td>Registros de tiempo</td><td>$timeEntries</td></tr>";
        
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 2");
        $mechanics = $stmt->fetchColumn();
        echo "<tr><td>Mecánicos registrados</td><td>$mechanics</td></tr>";
        
    } catch (PDOException $e) {
        echo "<tr><td colspan='2' class='error'>Error al consultar datos: " . $e->getMessage() . "</td></tr>";
    }
    echo "</table>";
    
    // Resumen
    echo "<h2>🎯 Resumen</h2>";
    if (empty($errors)) {
        echo "<p class='success' style='font-size: 18px;'>✅ Todos los componentes están instalados correctamente</p>";
        echo "<p>El Workshop Intelligent Suite está listo para usar.</p>";
    } else {
        echo "<p class='error' style='font-size: 18px;'>❌ Se encontraron " . count($errors) . " errores</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li class='error'>$error</li>";
        }
        echo "</ul>";
        echo "<p><strong>Para corregir:</strong> Ejecute <a href='install-workshop-ops.php' style='color: #4ade80;'>install-workshop-ops.php</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error de conexión a base de datos: " . $e->getMessage() . "</p>";
}

echo "<hr style='margin: 30px 0; border-color: #333;'>";
echo "<p style='text-align: center; color: #9aa3b2;'>Torque Studio ERP - Workshop Intelligent Suite v1.0</p>";
echo "</body></html>";
