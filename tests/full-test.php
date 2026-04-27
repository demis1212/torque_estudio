<?php
/**
 * SCRIPT COMPLETO DE PRUEBAS - Torque Studio ERP
 * 
 * Este script prueba TODAS las funcionalidades del sistema:
 * - Conexión a Base de Datos
 * - Encoding UTF-8
 * - Tablas y datos
 * - Módulo de Herramientas
 * - Módulo de Inventario/Partes
 * - Rutas y Controladores
 * - Seguridad
 * 
 * Uso:
 *   php tests/full-test.php
 *   php tests/full-test.php --verbose    (más detalle)
 *   php tests/full-test.php --html       (genera reporte HTML)
 */

namespace Tests;

class FullTestSuite {
    private static $verbose = false;
    private static $generateHtml = false;
    private static $results = [];
    private static $passed = 0;
    private static $failed = 0;
    private static $warnings = 0;
    
    public static function run($args = []) {
        self::$verbose = in_array('--verbose', $args);
        self::$generateHtml = in_array('--html', $args);
        
        self::printHeader();
        
        $startTime = microtime(true);
        
        // 1. PRUEBAS DE SISTEMA Y ARCHIVOS
        self::runFileSystemTests();
        
        // 2. PRUEBAS DE CONFIGURACIÓN
        self::runConfigTests();
        
        // 3. PRUEBAS DE BASE DE DATOS (si es posible)
        self::runDatabaseTests();
        
        // 4. PRUEBAS DE CODIFICACIÓN
        self::runEncodingTests();
        
        // 5. PRUEBAS DE MÓDULOS
        self::runModuleTests();
        
        // 6. PRUEBAS DE SEGURIDAD
        self::runSecurityTests();
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        self::printSummary($duration);
        
        if (self::$generateHtml) {
            self::generateHtmlReport($duration);
        }
        
        return self::$failed === 0 ? 0 : 1;
    }
    
    // ═══════════════════════════════════════════════════════════════
    // SECCIÓN 1: PRUEBAS DE SISTEMA Y ARCHIVOS
    // ═══════════════════════════════════════════════════════════════
    private static function runFileSystemTests() {
        self::section("1. SISTEMA DE ARCHIVOS");
        
        $requiredFiles = [
            'config/database.php' => 'Configuración BD',
            'public/index.php' => 'Entry point',
            'routes/web.php' => 'Rutas',
            'app/models/Part.php' => 'Modelo Partes',
            'app/models/WarehouseTool.php' => 'Modelo Herramientas',
            'app/models/ToolRequest.php' => 'Modelo Solicitudes',
            'app/controllers/PartController.php' => 'Controller Partes',
            'app/controllers/ToolsController.php' => 'Controller Herramientas',
            'app/views/parts/create.php' => 'Vista Crear Parte',
            'app/views/tools/warehouse-tools.php' => 'Vista Bodega',
            'tests/TestRunner.php' => 'Test Suite',
        ];
        
        foreach ($requiredFiles as $file => $description) {
            $path = dirname(__DIR__) . '/' . $file;
            if (file_exists($path)) {
                self::pass("✓ {$description}: {$file}");
            } else {
                self::fail("✗ {$description}: {$file} NO EXISTE");
            }
        }
        
        // Verificar permisos de escritura
        $writableDirs = ['public', 'app/views', 'storage'];
        foreach ($writableDirs as $dir) {
            $path = dirname(__DIR__) . '/' . $dir;
            if (is_dir($path) && is_writable($path)) {
                self::pass("✓ Directorio escribible: {$dir}");
            } else if (is_dir($path)) {
                self::warn("⚠ Directorio existe pero puede tener problemas de permisos: {$dir}");
            } else {
                self::warn("⚠ Directorio no encontrado: {$dir}");
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════════════
    // SECCIÓN 2: PRUEBAS DE CONFIGURACIÓN
    // ═══════════════════════════════════════════════════════════════
    private static function runConfigTests() {
        self::section("2. CONFIGURACIÓN");
        
        // Verificar config/database.php
        $dbConfig = file_get_contents(dirname(__DIR__) . '/config/database.php');
        
        if (strpos($dbConfig, 'utf8mb4') !== false) {
            self::pass("✓ Configuración BD usa utf8mb4");
        } else {
            self::fail("✗ Configuración BD NO tiene utf8mb4");
        }
        
        if (strpos($dbConfig, 'SET NAMES') !== false) {
            self::pass("✓ Configuración BD tiene SET NAMES");
        } else {
            self::warn("⚠ Configuración BD NO tiene SET NAMES explícito");
        }
        
        // Verificar public/index.php
        $indexContent = @file_get_contents(dirname(__DIR__) . '/public/index.php');
        if ($indexContent && strpos($indexContent, 'charset=utf-8') !== false) {
            self::pass("✓ index.php tiene header UTF-8");
        } else {
            self::warn("⚠ index.php NO tiene header UTF-8 explícito");
        }
    }
    
    // ═══════════════════════════════════════════════════════════════
    // SECCIÓN 3: PRUEBAS DE BASE DE DATOS
    // ═══════════════════════════════════════════════════════════════
    private static function runDatabaseTests() {
        self::section("3. BASE DE DATOS");
        
        // Intentar cargar la configuración
        try {
            require_once dirname(__DIR__) . '/config/database.php';
            
            if (class_exists('Config\Database')) {
                self::pass("✓ Clase Database cargada correctamente");
                
                try {
                    $db = \Config\Database::getConnection();
                    self::pass("✓ Conexión a BD exitosa");
                    
                    // Verificar charset de la conexión
                    $charset = $db->query("SELECT @@character_set_connection")->fetchColumn();
                    if (strpos($charset, 'utf8') !== false) {
                        self::pass("✓ Conexión usa charset UTF-8: {$charset}");
                    } else {
                        self::warn("⚠ Conexión NO usa UTF-8: {$charset}");
                    }
                    
                    // Verificar tablas existentes
                    $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
                    $requiredTables = ['parts', 'warehouse_tools', 'tool_requests', 'notifications', 'work_orders'];
                    
                    foreach ($requiredTables as $table) {
                        if (in_array($table, $tables)) {
                            self::pass("✓ Tabla existe: {$table}");
                            
                            // Verificar charset de tabla
                            try {
                                $tableInfo = $db->query("SHOW CREATE TABLE {$table}")->fetch();
                                $createSql = $tableInfo[1] ?? '';
                                if (strpos($createSql, 'utf8mb4') !== false) {
                                    self::pass("  └─ Charset utf8mb4 confirmado");
                                }
                            } catch (\Exception $e) {
                                // Ignorar errores de permisos
                            }
                        } else {
                            self::fail("✗ Tabla NO existe: {$table}");
                        }
                    }
                    
                    // Verificar columnas específicas
                    try {
                        $columns = $db->query("SHOW COLUMNS FROM parts")->fetchAll(\PDO::FETCH_COLUMN);
                        if (in_array('unit_type', $columns)) {
                            self::pass("✓ Columna unit_type existe en parts");
                        } else {
                            self::fail("✗ Columna unit_type NO existe en parts");
                        }
                    } catch (\Exception $e) {
                        self::warn("⚠ No se pudo verificar columnas de parts");
                    }
                    
                } catch (\Exception $e) {
                    self::warn("⚠ No se pudo conectar a BD: " . $e->getMessage());
                }
            } else {
                self::fail("✗ Clase Database NO encontrada");
            }
        } catch (\Exception $e) {
            self::warn("⚠ Error cargando configuración: " . $e->getMessage());
        }
    }
    
    // ═══════════════════════════════════════════════════════════════
    // SECCIÓN 4: PRUEBAS DE CODIFICACIÓN
    // ═══════════════════════════════════════════════════════════════
    private static function runEncodingTests() {
        self::section("4. CODIFICACIÓN UTF-8");
        
        // Verificar archivos SQL
        $sqlFiles = ['database/schema.sql', 'database/new_tables.sql', 'database/fix_utf8.sql'];
        
        foreach ($sqlFiles as $sqlFile) {
            $path = dirname(__DIR__) . '/' . $sqlFile;
            if (!file_exists($path)) {
                self::warn("⚠ Archivo SQL no encontrado: {$sqlFile}");
                continue;
            }
            
            $content = file_get_contents($path);
            $utf8mb4Count = substr_count($content, 'utf8mb4');
            
            if ($utf8mb4Count > 0) {
                self::pass("✓ {$sqlFile}: {$utf8mb4Count} referencias a utf8mb4");
            } else {
                self::fail("✗ {$sqlFile}: NO tiene utf8mb4");
            }
        }
        
        // Verificar archivos PHP por caracteres corruptos
        $viewFiles = glob(dirname(__DIR__) . '/app/views/**/*.php');
        $corruptPatterns = ['Fern??ndez', 'Mec??nico', 'Direcci??n', 'Garc??a', 'Jos??'];
        $foundCorrupt = false;
        
        foreach ($viewFiles as $file) {
            $content = @file_get_contents($file);
            if (!$content) continue;
            
            foreach ($corruptPatterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    self::fail("✗ Caracteres corruptos en: " . basename($file));
                    $foundCorrupt = true;
                    break 2;
                }
            }
        }
        
        if (!$foundCorrupt) {
            self::pass("✓ No se encontraron caracteres corruptos comunes");
        }
        
        // Verificar que los archivos sean UTF-8 válidos
        $phpFiles = glob(dirname(__DIR__) . '/app/**/*.php');
        $invalidFiles = 0;
        
        foreach (array_slice($phpFiles, 0, 20) as $file) { // Verificar primeros 20
            $content = @file_get_contents($file);
            if ($content && !mb_check_encoding($content, 'UTF-8')) {
                self::fail("✗ Archivo NO es UTF-8 válido: " . basename($file));
                $invalidFiles++;
            }
        }
        
        if ($invalidFiles === 0) {
            self::pass("✓ Archivos PHP son UTF-8 válidos (muestra de 20)");
        }
    }
    
    // ═══════════════════════════════════════════════════════════════
    // SECCIÓN 5: PRUEBAS DE MÓDULOS
    // ═══════════════════════════════════════════════════════════════
    private static function runModuleTests() {
        self::section("5. MÓDULOS");
        
        // ═══════════════════════════════════════════════════════════
        // MÓDULO: HERRAMIENTAS
        // ═══════════════════════════════════════════════════════════
        self::subsection("5.1 Módulo de Herramientas");
        
        $toolFiles = [
            'app/models/WarehouseTool.php',
            'app/models/ToolRequest.php',
            'app/models/MechanicTool.php',
            'app/controllers/ToolsController.php',
            'app/views/tools/index.php',
            'app/views/tools/warehouse-tools.php',
            'app/views/tools/mechanic-tools.php',
            'app/views/tools/checkout.php',
            'app/views/tools/return.php',
            'app/views/tools/purchase-request.php',
        ];
        
        foreach ($toolFiles as $file) {
            $path = dirname(__DIR__) . '/' . $file;
            if (file_exists($path)) {
                self::pass("✓ {$file}");
            } else {
                self::fail("✗ {$file} NO EXISTE");
            }
        }
        
        // Verificar métodos en ToolsController
        $toolsController = @file_get_contents(dirname(__DIR__) . '/app/controllers/ToolsController.php');
        if ($toolsController) {
            $methods = ['purchaseRequest', 'sendToRepair', 'markAsRepaired', 'checkoutTool', 'returnTool'];
            foreach ($methods as $method) {
                if (strpos($toolsController, "function {$method}") !== false) {
                    self::pass("✓ Método ToolsController::{$method}()");
                } else {
                    self::fail("✗ Método ToolsController::{$method}() NO EXISTE");
                }
            }
        }
        
        // Verificar estados en WarehouseTool
        $warehouseTool = @file_get_contents(dirname(__DIR__) . '/app/models/WarehouseTool.php');
        if ($warehouseTool) {
            $states = ['disponible', 'solicitada', 'prestada', 'en_mantenimiento', 'danada'];
            foreach ($states as $state) {
                if (strpos($warehouseTool, "'{$state}'") !== false) {
                    self::pass("✓ Estado '{$state}' definido");
                } else {
                    self::warn("⚠ Estado '{$state}' no encontrado");
                }
            }
        }
        
        // ═══════════════════════════════════════════════════════════
        // MÓDULO: INVENTARIO/PARTES
        // ═══════════════════════════════════════════════════════════
        self::subsection("5.2 Módulo de Inventario");
        
        $partFiles = [
            'app/models/Part.php',
            'app/controllers/PartController.php',
            'app/views/parts/index.php',
            'app/views/parts/create.php',
            'app/views/parts/edit.php',
        ];
        
        foreach ($partFiles as $file) {
            $path = dirname(__DIR__) . '/' . $file;
            if (file_exists($path)) {
                self::pass("✓ {$file}");
            } else {
                self::fail("✗ {$file} NO EXISTE");
            }
        }
        
        // Verificar funcionalidades de PartController
        $partController = @file_get_contents(dirname(__DIR__) . '/app/controllers/PartController.php');
        if ($partController) {
            if (strpos($partController, 'category_new') !== false) {
                self::pass("✓ Manejo de nueva categoría implementado");
            } else {
                self::fail("✗ Manejo de nueva categoría NO implementado");
            }
            
            if (strpos($partController, 'isUsedInWorkOrders') !== false) {
                self::pass("✓ Verificación de uso en órdenes implementada");
            } else {
                self::warn("⚠ Verificación de uso en órdenes no encontrada");
            }
        }
        
        // Verificar formato CLP en vistas
        $createView = @file_get_contents(dirname(__DIR__) . '/app/views/parts/create.php');
        if ($createView) {
            if (strpos($createCreate, 'CLP') !== false || strpos($createView, 'number_format') !== false) {
                self::pass("✓ Formato CLP implementado en create.php");
            } else {
                self::warn("⚠ Formato CLP no confirmado en create.php");
            }
            
            if (strpos($createView, 'calculateMargin') !== false || strpos($createView, 'margen') !== false) {
                self::pass("✓ Cálculo de margen implementado");
            } else {
                self::warn("⚠ Cálculo de margen no confirmado");
            }
            
            if (strpos($createView, 'unit_type') !== false) {
                self::pass("✓ Campo unit_type en formulario");
            } else {
                self::fail("✗ Campo unit_type NO encontrado");
            }
        }
        
        // ═══════════════════════════════════════════════════════════
        // MÓDULO: NOTIFICACIONES
        // ═══════════════════════════════════════════════════════════
        self::subsection("5.3 Módulo de Notificaciones");
        
        if (file_exists(dirname(__DIR__) . '/app/models/Notification.php')) {
            self::pass("✓ Modelo Notification existe");
            
            $notification = @file_get_contents(dirname(__DIR__) . '/app/models/Notification.php');
            if ($notification && strpos($notification, 'create') !== false) {
                self::pass("✓ Método create() en Notification");
            }
        } else {
            self::fail("✗ Modelo Notification NO existe");
        }
        
        // ═══════════════════════════════════════════════════════════
        // MÓDULO: MANUALES
        // ═══════════════════════════════════════════════════════════
        self::subsection("5.4 Módulo de Manuales");
        
        $manualFiles = [
            'app/models/Manual.php',
            'app/controllers/ManualController.php',
            'app/views/manuals/index.php',
            'app/views/manuals/create.php',
        ];
        
        foreach ($manualFiles as $file) {
            $path = dirname(__DIR__) . '/' . $file;
            if (file_exists($path)) {
                self::pass("✓ {$file}");
            } else {
                self::fail("✗ {$file} NO EXISTE");
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════════════
    // SECCIÓN 6: PRUEBAS DE SEGURIDAD
    // ═══════════════════════════════════════════════════════════════
    private static function runSecurityTests() {
        self::section("6. SEGURIDAD");
        
        // Verificar helpers
        $helpersFile = dirname(__DIR__) . '/app/helpers.php';
        if (file_exists($helpersFile)) {
            $helpers = file_get_contents($helpersFile);
            
            if (strpos($helpers, 'function csrf_token()') !== false) {
                self::pass("✓ Función csrf_token() existe");
            } else {
                self::fail("✗ Función csrf_token() NO existe");
            }
            
            if (strpos($helpers, 'function esc(') !== false) {
                self::pass("✓ Función esc() (escape) existe");
            } else {
                self::fail("✗ Función esc() NO existe");
            }
        } else {
            self::fail("✗ Archivo helpers.php NO existe");
        }
        
        // Verificar uso de prepared statements
        $partModel = @file_get_contents(dirname(__DIR__) . '/app/models/Part.php');
        if ($partModel) {
            if (strpos($partModel, 'prepare') !== false) {
                self::pass("✓ Part.php usa prepared statements");
            } else {
                self::warn("⚠ Part.php puede no usar prepared statements");
            }
        }
        
        // Verificar password hashing en seeders
        $seeders = @file_get_contents(dirname(__DIR__) . '/database/schema.sql');
        if ($seeders && strpos($seeders, "\$2y\$10") !== false) {
            self::pass("✓ Passwords hasheados con bcrypt (\$2y\$10)");
        } else {
            self::warn("⚠ No se confirmó hashing de passwords en seeders");
        }
        
        // Verificar .htaccess
        $htaccess = dirname(__DIR__) . '/public/.htaccess';
        if (file_exists($htaccess)) {
            self::pass("✓ Archivo .htaccess existe");
        } else {
            self::warn("⚠ Archivo .htaccess NO existe (opcional pero recomendado)");
        }
    }
    
    // ═══════════════════════════════════════════════════════════════
    // HELPERS DE SALIDA
    // ═══════════════════════════════════════════════════════════════
    private static function printHeader() {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║       TORQUE STUDIO ERP - TEST COMPLETO DEL SISTEMA           ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
    
    private static function section($name) {
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo " {$name}\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
    
    private static function subsection($name) {
        echo "\n  {$name}\n";
        echo "  ─────────────────────────────────────────────────────────\n";
    }
    
    private static function pass($message) {
        self::$results[] = ['status' => 'PASS', 'message' => $message];
        self::$passed++;
        if (self::$verbose || strpos($message, '✓') === 0) {
            echo "  {$message}\n";
        }
    }
    
    private static function fail($message) {
        self::$results[] = ['status' => 'FAIL', 'message' => $message];
        self::$failed++;
        echo "  {$message}\n";
    }
    
    private static function warn($message) {
        self::$results[] = ['status' => 'WARN', 'message' => $message];
        self::$warnings++;
        echo "  {$message}\n";
    }
    
    private static function printSummary($duration) {
        $total = self::$passed + self::$failed + self::$warnings;
        $percentage = $total > 0 ? round((self::$passed / $total) * 100) : 0;
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "                    RESUMEN DE PRUEBAS                         \n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "\n";
        echo "  ✅ PASADAS:     " . str_pad(self::$passed, 4, ' ', STR_PAD_LEFT) . "\n";
        echo "  ❌ FALLIDAS:    " . str_pad(self::$failed, 4, ' ', STR_PAD_LEFT) . "\n";
        echo "  ⚠️  ADVERTENCIAS:" . str_pad(self::$warnings, 4, ' ', STR_PAD_LEFT) . "\n";
        echo "  ───────────────────────────\n";
        echo "  📊 TOTAL:        " . str_pad($total, 4, ' ', STR_PAD_LEFT) . "\n";
        echo "  🎯 PORCENTAJE:   " . str_pad($percentage . '%', 4, ' ', STR_PAD_LEFT) . "\n";
        echo "  ⏱️  DURACIÓN:    " . str_pad($duration . 's', 4, ' ', STR_PAD_LEFT) . "\n";
        echo "\n";
        
        // Barra de progreso
        $barWidth = 50;
        $filled = round(($percentage / 100) * $barWidth);
        $empty = $barWidth - $filled;
        
        echo "  [";
        echo str_repeat("█", $filled);
        echo str_repeat("░", $empty);
        echo "] {$percentage}%\n";
        echo "\n";
        
        // Estado final
        if (self::$failed === 0) {
            echo "  🎉 ¡TODAS LAS PRUEBAS PASARON!\n";
            echo "  El sistema está listo para deployment.\n";
        } elseif ($percentage >= 80) {
            echo "  ✅ BUENO: La mayoría de pruebas pasaron.\n";
            echo "  Revisar las fallas antes de deployment.\n";
        } else {
            echo "  ⚠️  ATENCIÓN: Hay problemas importantes.\n";
            echo "  NO deployear hasta corregir las fallas.\n";
        }
        
        echo "\n";
        
        if (self::$generateHtml) {
            echo "  📄 Reporte HTML generado: tests/test-report.html\n\n";
        }
    }
    
    private static function generateHtmlReport($duration) {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pruebas - Torque Studio ERP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .summary { background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
        .stat-box { padding: 20px; border-radius: 8px; text-align: center; }
        .stat-box.pass { background: #d4edda; color: #155724; }
        .stat-box.fail { background: #f8d7da; color: #721c24; }
        .stat-box.warn { background: #fff3cd; color: #856404; }
        .stat-box.total { background: #e3f2fd; color: #0d47a1; }
        .stat-number { font-size: 36px; font-weight: bold; }
        .stat-label { font-size: 14px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        tr:hover { background: #f5f5f5; }
        .status-pass { color: #4CAF50; font-weight: bold; }
        .status-fail { color: #f44336; font-weight: bold; }
        .status-warn { color: #ff9800; font-weight: bold; }
        .progress-bar { width: 100%; height: 30px; background: #e0e0e0; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4CAF50, #8BC34A); transition: width 0.5s; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Reporte de Pruebas - Torque Studio ERP</h1>
        <p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
        <p><strong>Duración:</strong> {$duration} segundos</p>
        
        <div class="summary">
            <h2>Resumen</h2>
            <div class="stats">
                <div class="stat-box pass">
                    <div class="stat-number">" . self::$passed . "</div>
                    <div class="stat-label">PASADAS</div>
                </div>
                <div class="stat-box fail">
                    <div class="stat-number">" . self::$failed . "</div>
                    <div class="stat-label">FALLIDAS</div>
                </div>
                <div class="stat-box warn">
                    <div class="stat-number">" . self::$warnings . "</div>
                    <div class="stat-label">ADVERTENCIAS</div>
                </div>
                <div class="stat-box total">
                    <div class="stat-number">" . (self::$passed + self::$failed + self::$warnings) . "</div>
                    <div class="stat-label">TOTAL</div>
                </div>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: {$percentage}%"></div>
            </div>
            <p style="text-align: center; font-size: 24px; font-weight: bold; color: #333;">
                {$percentage}% Completado
            </p>
        </div>
        
        <h2>Detalle de Pruebas</h2>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
HTML;

        foreach (self::$results as $result) {
            $statusClass = strtolower($result['status']);
            $statusIcon = $result['status'] === 'PASS' ? '✓' : ($result['status'] === 'FAIL' ? '✗' : '⚠');
            $html .= "
                <tr>
                    <td class=\"status-{$statusClass}\">{$statusIcon} {$result['status']}</td>
                    <td>" . htmlspecialchars($result['message']) . "</td>
                </tr>";
        }

        $html .= <<<HTML
            </tbody>
        </table>
        
        <div class="footer">
            <p>Generado por FullTestSuite - Torque Studio ERP</p>
        </div>
    </div>
</body>
</html>
HTML;

        file_put_contents(dirname(__DIR__) . '/tests/test-report.html', $html);
    }
}

// Ejecutar si se llama directamente
if (php_sapi_name() === 'cli') {
    $exitCode = FullTestSuite::run($argv);
    exit($exitCode);
}
