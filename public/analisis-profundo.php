<?php
/**
 * Análisis Profundo del Sistema - Torque Studio ERP
 * Ejecutar: http://localhost/torque/analisis-profundo.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

echo "<!DOCTYPE html><html><head>
<meta charset='UTF-8'>
<title>Análisis Profundo - Torque Studio</title>
<style>
body { font-family: 'Segoe UI', monospace; background: #0a0c10; color: #e8eaf2; padding: 20px; line-height: 1.6; }
.section { background: #11131a; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; margin-bottom: 20px; }
.subsection { background: rgba(138,180,248,0.05); border-left: 3px solid #8ab4f8; padding: 15px; margin: 10px 0; }
.ok { color: #4ade80; }
.error { color: #f87171; font-weight: bold; }
.warning { color: #fbbf24; }
.info { color: #8ab4f8; }
h1 { color: #8ab4f8; text-align: center; border-bottom: 2px solid rgba(138,180,248,0.3); padding-bottom: 20px; }
h2 { color: #8ab4f8; margin-top: 0; border-bottom: 1px solid rgba(138,180,248,0.2); padding-bottom: 10px; }
h3 { color: #fbbf24; font-size: 16px; margin: 15px 0 10px 0; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
th, td { padding: 10px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); }
th { color: #9aa3b2; background: rgba(255,255,255,0.02); }
.code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 12px; }
pre { background: rgba(0,0,0,0.3); padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 12px; }
.summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
.summary-card { background: rgba(138,180,248,0.1); padding: 15px; border-radius: 8px; text-align: center; }
.summary-number { font-size: 32px; font-weight: bold; color: #8ab4f8; }
.summary-label { font-size: 12px; color: #9aa3b2; margin-top: 5px; }
.progress { width: 100%; height: 20px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
.progress-bar { height: 100%; background: linear-gradient(90deg, #4ade80, #8ab4f8); transition: width 0.3s; }
.critical { border-left: 4px solid #f87171; padding-left: 15px; margin: 10px 0; }
</style></head><body>";

echo "<h1>🔍 Análisis Profundo del Sistema</h1>";
echo "<p style='text-align: center; color: #9aa3b2;'>Generado: " . date('d/m/Y H:i:s') . "</p>";

$totalChecks = 0;
$passedChecks = 0;
$warnings = 0;
$errors = 0;

function check($condition, $message) {
    global $totalChecks, $passedChecks, $warnings, $errors;
    $totalChecks++;
    if ($condition) {
        $passedChecks++;
        return "<span class='ok'>✓ $message</span>";
    } else {
        $errors++;
        return "<span class='error'>✗ $message</span>";
    }
}

function warn($condition, $message) {
    global $warnings;
    if (!$condition) {
        $warnings++;
        return "<span class='warning'>⚠ $message</span>";
    }
    return "";
}

// ============================================
// RESUMEN EJECUTIVO
// ============================================
echo "<div class='section'>";
echo "<h2>📊 Resumen Ejecutivo</h2>";

// Verificar conexión primero
$dbConnected = false;
$dbError = '';
try {
    require_once __DIR__ . '/../config/database.php';
    $db = \Config\Database::getConnection();
    $dbConnected = true;
} catch (Exception $e) {
    $dbError = $e->getMessage();
}

// Contar archivos
$totalControllers = count(glob(__DIR__ . '/../app/controllers/*.php'));
$totalModels = count(glob(__DIR__ . '/../app/models/*.php'));
$totalViews = count(glob(__DIR__ . '/../app/views/**/*.php', GLOB_BRACE));

// Verificar tablas críticas
$criticalTables = ['users', 'clients', 'vehicles', 'work_orders', 'services', 'parts'];
$existingTables = 0;
if ($dbConnected) {
    foreach ($criticalTables as $table) {
        try {
            $db->query("SELECT 1 FROM $table LIMIT 1");
            $existingTables++;
        } catch (Exception $e) {}
    }
}

echo "<div class='summary'>";
echo "<div class='summary-card'><div class='summary-number'>$existingTables/" . count($criticalTables) . "</div><div class='summary-label'>Tablas Críticas OK</div></div>";
echo "<div class='summary-card'><div class='summary-number'>$totalControllers</div><div class='summary-label'>Controladores</div></div>";
echo "<div class='summary-card'><div class='summary-number'>$totalModels</div><div class='summary-label'>Modelos</div></div>";
echo "<div class='summary-card'><div class='summary-number'>$totalViews</div><div class='summary-label'>Vistas</div></div>";
echo "</div>";

$healthPercent = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;
echo "<div style='margin: 20px 0;'>";
echo "<p>Salud del Sistema: <strong>$healthPercent%</strong></p>";
echo "<div class='progress'><div class='progress-bar' style='width: $health%'></div></div>";
echo "<p style='margin-top: 10px;'>";
echo "<span class='ok'>✓ $passedChecks OK</span> | ";
echo "<span class='warning'>⚠ $warnings Advertencias</span> | ";
echo "<span class='error'>✗ $errors Errores</span>";
echo "</p>";
echo "</div>";
echo "</div>";

// ============================================
// 1. ANÁLISIS DE BASE DE DATOS (DETALLADO)
// ============================================
echo "<div class='section'>";
echo "<h2>🗄️ Análisis de Base de Datos</h2>";

if (!$dbConnected) {
    echo "<div class='critical'>";
    echo "<p class='error'>✗ NO SE PUEDE CONECTAR A LA BASE DE DATOS</p>";
    echo "<p>Error: " . htmlspecialchars($dbError) . "</p>";
    echo "</div>";
} else {
    echo "<div class='subsection'>";
    echo "<h3>Conexión y Configuración</h3>";
    echo "<table>";
    echo "<tr><th>Parámetro</th><th>Valor</th><th>Estado</th></tr>";
    
    // Verificar charset
    $charset = $db->query("SHOW VARIABLES LIKE 'character_set_database'")->fetchColumn(1);
    echo "<tr><td>Charset Base de Datos</td><td class='code'>$charset</td><td>" . check($charset == 'utf8mb4', 'UTF8MB4 configurado') . "</td></tr>";
    
    // Verificar modo SQL
    $sqlMode = $db->query("SELECT @@sql_mode")->fetchColumn();
    echo "<tr><td>SQL Mode</td><td class='code'>$sqlMode</td><td class='info'>ℹ Informativo</td></tr>";
    
    // Verificar versión
    $version = $db->query("SELECT VERSION()")->fetchColumn();
    echo "<tr><td>Versión MySQL</td><td class='code'>$version</td><td class='ok'>✓ OK</td></tr>";
    
    echo "</table>";
    echo "</div>";
    
    // Análisis detallado de tablas
    echo "<div class='subsection'>";
    echo "<h3>Estructura de Tablas</h3>";
    
    $tables = [
        'users' => ['required_columns' => ['id', 'name', 'email', 'password', 'role_id']],
        'clients' => ['required_columns' => ['id', 'name', 'email', 'phone', 'rut', 'whatsapp']],
        'vehicles' => ['required_columns' => ['id', 'client_id', 'brand', 'model', 'plate', 'vin']],
        'work_orders' => ['required_columns' => ['id', 'client_id', 'mechanic_id', 'vehicle_id', 'status', 'total_cost']],
        'services' => ['required_columns' => ['id', 'name', 'price', 'description']],
        'parts' => ['required_columns' => ['id', 'name', 'stock', 'price', 'category']],
        'work_order_time_entries' => ['required_columns' => ['id', 'work_order_id', 'mechanic_id', 'status', 'started_at']],
        'whatsapp_reminders' => ['required_columns' => ['id', 'client_id', 'scheduled_at', 'status', 'created_by']],
        'purchase_alerts' => ['required_columns' => ['id', 'part_id', 'status']],
        'work_order_part_requests' => ['required_columns' => ['id', 'work_order_id', 'part_id', 'status']],
    ];
    
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Existe</th><th>Columnas Requeridas</th><th>Registros</th><th>Estado</th></tr>";
    
    foreach ($tables as $table => $info) {
        echo "<tr>";
        echo "<td><strong class='code'>$table</strong></td>";
        
        try {
            // Verificar si existe
            $db->query("SELECT 1 FROM $table LIMIT 1");
            echo "<td class='ok'>✓</td>";
            
            // Verificar columnas
            $columns = $db->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);
            $missingCols = array_diff($info['required_columns'], $columns);
            
            if (empty($missingCols)) {
                echo "<td class='ok'>✓ Todas</td>";
            } else {
                echo "<td class='error'>✗ Faltan: " . implode(', ', $missingCols) . "</td>";
            }
            
            // Contar registros
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "<td>$count</td>";
            
            // Estado general
            if (empty($missingCols)) {
                echo "<td class='ok'>✓ OK</td>";
                $passedChecks++;
            } else {
                echo "<td class='error'>✗ Incompleta</td>";
                $errors++;
            }
            $totalChecks++;
            
        } catch (PDOException $e) {
            echo "<td class='error'>✗ No existe</td>";
            echo "<td class='error'>-</td>";
            echo "<td class='error'>0</td>";
            echo "<td class='error'>✗ ERROR</td>";
            $errors++;
            $totalChecks++;
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Verificar claves foráneas
    echo "<div class='subsection'>";
    echo "<h3>Integridad de Claves Foráneas</h3>";
    
    $foreignKeys = [
        ['table' => 'vehicles', 'column' => 'client_id', 'references' => 'clients.id'],
        ['table' => 'work_orders', 'column' => 'client_id', 'references' => 'clients.id'],
        ['table' => 'work_orders', 'column' => 'vehicle_id', 'references' => 'vehicles.id'],
        ['table' => 'work_orders', 'column' => 'mechanic_id', 'references' => 'users.id'],
    ];
    
    echo "<table>";
    echo "<tr><th>Tabla.Columna</th><th>Referencia</th><th>Estado</th></tr>";
    
    foreach ($foreignKeys as $fk) {
        echo "<tr>";
        echo "<td class='code'>{$fk['table']}.{$fk['column']}</td>";
        echo "<td class='code'>{$fk['references']}</td>";
        
        try {
            // Verificar si hay registros huérfanos
            $stmt = $db->query("
                SELECT COUNT(*) FROM {$fk['table']} t
                LEFT JOIN " . explode('.', $fk['references'])[0] . " r ON t.{$fk['column']} = r.id
                WHERE t.{$fk['column']} IS NOT NULL AND r.id IS NULL
            ");
            $orphans = $stmt->fetchColumn();
            
            if ($orphans == 0) {
                echo "<td class='ok'>✓ Sin huérfanos</td>";
                $passedChecks++;
            } else {
                echo "<td class='error'>✗ $orphans huérfanos</td>";
                $errors++;
            }
            $totalChecks++;
        } catch (Exception $e) {
            echo "<td class='warning'>⚠ No se pudo verificar</td>";
            $warnings++;
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}
echo "</div>";

// ============================================
// 2. ANÁLISIS DE CÓDIGO PHP
// ============================================
echo "<div class='section'>";
echo "<h2>📁 Análisis de Código</h2>";

$directories = [
    '../app/controllers' => 'Controladores',
    '../app/models' => 'Modelos',
    '../app/views' => 'Vistas',
    '../routes' => 'Rutas',
];

echo "<div class='subsection'>";
echo "<h3>Sintaxis de Archivos PHP</h3>";
echo "<table>";
echo "<tr><th>Directorio</th><th>Archivos</th><th>Sintaxis OK</th><th>Con Errores</th><th>Estado</th></tr>";

foreach ($directories as $dir => $name) {
    $fullPath = realpath(__DIR__ . '/' . $dir);
    if (!$fullPath || !is_dir($fullPath)) {
        echo "<tr><td>$name</td><td class='error' colspan='4'>✗ Directorio no encontrado</td></tr>";
        $errors++;
        continue;
    }
    
    $files = glob($fullPath . '/*.php');
    $okCount = 0;
    $errorCount = 0;
    $errorFiles = [];
    
    foreach ($files as $file) {
        $output = shell_exec('php -l ' . escapeshellarg($file) . ' 2>&1');
        if (strpos($output, 'No syntax errors') !== false) {
            $okCount++;
        } else {
            $errorCount++;
            $errorFiles[] = basename($file);
        }
        $totalChecks++;
    }
    
    $total = count($files);
    echo "<tr>";
    echo "<td><strong>$name</strong><br><small class='code'>$dir</small></td>";
    echo "<td>$total</td>";
    echo "<td class='ok'>$okCount</td>";
    echo "<td class='
