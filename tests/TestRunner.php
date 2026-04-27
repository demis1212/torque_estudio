<?php
/**
 * Test Runner - Sistema de Pruebas Automatizadas para Torque Studio ERP
 * 
 * Uso: php tests/TestRunner.php [módulo]
 * Ejemplos:
 *   php tests/TestRunner.php              -- Ejecuta TODAS las pruebas
 *   php tests/TestRunner.php tools        -- Prueba módulo de herramientas
 *   php tests/TestRunner.php dtc          -- Prueba módulo DTC
 *   php tests/TestRunner.php utf8         -- Prueba encoding UTF-8
 */

namespace Tests;

class TestRunner {
    private static $results = [];
    private static $totalTests = 0;
    private static $passedTests = 0;
    private static $failedTests = 0;
    
    /**
     * Ejecutar todas las pruebas
     */
    public static function runAll() {
        echo "\n╔════════════════════════════════════════╗\n";
        echo "║     TORQUE STUDIO ERP - TEST SUITE     ║\n";
        echo "╚════════════════════════════════════════╝\n\n";
        
        $startTime = microtime(true);
        
        // 1. Pruebas de Encoding UTF-8
        self::runUtf8Tests();
        
        // 2. Pruebas de Módulo de Herramientas
        self::runToolsTests();
        
        // 3. Pruebas de DTC Codes
        self::runDtcTests();
        
        // 4. Pruebas de Base de Datos
        self::runDatabaseTests();
        
        // 5. Pruebas de Rutas
        self::runRouteTests();
        
        // 6. Pruebas de Seguridad
        self::runSecurityTests();
        
        // Reporte final
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 3);
        
        self::printFinalReport($duration);
    }
    
    /**
     * Pruebas de Encoding UTF-8
     */
    private static function runUtf8Tests() {
        echo "\n📋 PRUEBAS DE ENCODING UTF-8\n";
        echo "─────────────────────────────────\n";
        
        // Test 1: Configuración de base de datos
        self::test('DB Charset Config', function() {
            $config = file_get_contents(__DIR__ . '/../config/database.php');
            return strpos($config, 'utf8mb4') !== false && 
                   strpos($config, 'SET NAMES utf8mb4') !== false;
        });
        
        // Test 2: Schema SQL tiene UTF-8
        self::test('Schema SQL UTF-8', function() {
            $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
            return strpos($schema, 'utf8mb4') !== false;
        });
        
        // Test 3: Archivos PHP tienen charset UTF-8
        self::test('PHP Files UTF-8 Meta', function() {
            $login = file_get_contents(__DIR__ . '/../app/views/login.php');
            return strpos($login, 'charset="UTF-8"') !== false;
        });
        
        // Test 4: No hay caracteres corruptos en seeders
        self::test('No Broken Chars in Seeders', function() {
            $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
            // Buscar patrones comunes de encoding roto
            $brokenPatterns = ['Fern??ndez', 'Mec??nico', 'Direcci??n'];
            foreach ($brokenPatterns as $pattern) {
                if (strpos($schema, $pattern) !== false) return false;
            }
            return true;
        });
        
        // Test 5: Archivo index.php tiene header UTF-8
        self::test('PHP Header UTF-8', function() {
            $index = file_get_contents(__DIR__ . '/../public/index.php');
            return strpos($index, "header('Content-Type: text/html; charset=utf-8')") !== false;
        });
    }
    
    /**
     * Pruebas de Módulo de Herramientas
     */
    private static function runToolsTests() {
        echo "\n🔧 PRUEBAS DE MÓDULO HERRAMIENTAS\n";
        echo "─────────────────────────────────\n";
        
        // Test 1: WarehouseTool model existe
        self::test('WarehouseTool Model', function() {
            return file_exists(__DIR__ . '/../app/models/WarehouseTool.php');
        });
        
        // Test 2: ToolRequest model existe
        self::test('ToolRequest Model', function() {
            return file_exists(__DIR__ . '/../app/models/ToolRequest.php');
        });
        
        // Test 3: Controlador tiene métodos de reparación
        self::test('Repair Methods Exist', function() {
            $controller = file_get_contents(__DIR__ . '/../app/controllers/ToolsController.php');
            return strpos($controller, 'sendToRepair') !== false && 
                   strpos($controller, 'markAsRepaired') !== false;
        });
        
        // Test 4: Rutas de reparación definidas
        self::test('Repair Routes', function() {
            $routes = file_get_contents(__DIR__ . '/../routes/web.php');
            return strpos($routes, '/tools/warehouse/repair/') !== false &&
                   strpos($routes, '/tools/warehouse/repaired/') !== false;
        });
        
        // Test 5: Vista checkout existe
        self::test('Checkout View', function() {
            return file_exists(__DIR__ . '/../app/views/tools/checkout.php');
        });
        
        // Test 6: Vista return existe
        self::test('Return View', function() {
            return file_exists(__DIR__ . '/../app/views/tools/return.php');
        });
        
        // Test 7: Estados de herramientas correctos
        self::test('Tool Status ENUM', function() {
            $model = file_get_contents(__DIR__ . '/../app/models/WarehouseTool.php');
            return strpos($model, "'solicitada'") !== false &&
                   strpos($model, "'disponible'") !== false &&
                   strpos($model, "'prestada'") !== false;
        });
        
        // Test 8: Botón Solicitar Compra existe
        self::test('Purchase Request Button', function() {
            $view = file_get_contents(__DIR__ . '/../app/views/tools/index.php');
            return strpos($view, 'Solicitar Compra') !== false;
        });
        
        // Test 9: Ruta de solicitud de compra
        self::test('Purchase Request Route', function() {
            $routes = file_get_contents(__DIR__ . '/../routes/web.php');
            return strpos($routes, '/tools/purchase-request') !== false;
        });
    }
    
    /**
     * Pruebas de DTC Codes
     */
    private static function runDtcTests() {
        echo "\n🔍 PRUEBAS DE DTC CODES\n";
        echo "─────────────────────────────────\n";
        
        // Test 1: Vista DTC existe
        self::test('DTC View Exists', function() {
            return file_exists(__DIR__ . '/../app/views/dtc/index.php');
        });
        
        // Test 2: Controlador DTC existe
        self::test('DTC Controller', function() {
            return file_exists(__DIR__ . '/../app/controllers/DtcController.php');
        });
        
        // Test 3: Rutas DTC definidas
        self::test('DTC Routes', function() {
            $routes = file_get_contents(__DIR__ . '/../routes/web.php');
            return strpos($routes, '/dtc') !== false;
        });
        
        // Test 4: Datos DTC tienen señales visuales
        self::test('DTC Signal Visual', function() {
            $view = file_get_contents(__DIR__ . '/../app/views/dtc/index.php');
            return strpos($view, 'digital') !== false || 
                   strpos($view, 'analog') !== false ||
                   strpos($view, 'analógica') !== false;
        });
        
        // Test 5: Códigos relacionados en DTC
        self::test('DTC Related Codes', function() {
            $view = file_get_contents(__DIR__ . '/../app/views/dtc/index.php');
            return strpos($view, 'relacionado') !== false ||
                   strpos($view, 'similar') !== false;
        });
    }
    
    /**
     * Pruebas de Base de Datos
     */
    private static function runDatabaseTests() {
        echo "\n🗄️  PRUEBAS DE BASE DE DATOS\n";
        echo "─────────────────────────────────\n";
        
        // Test 1: Conexión a DB
        self::test('DB Connection', function() {
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = \Config\Database::getConnection();
                return $db !== null;
            } catch (\Exception $e) {
                return false;
            }
        });
        
        // Test 2: Tablas principales existen
        self::test('Core Tables', function() {
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = \Config\Database::getConnection();
                $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
                $required = ['users', 'clients', 'vehicles', 'work_orders', 'parts', 'services'];
                foreach ($required as $table) {
                    if (!in_array($table, $tables)) return false;
                }
                return true;
            } catch (\Exception $e) {
                return false;
            }
        });
        
        // Test 3: Tablas de herramientas existen
        self::test('Tools Tables', function() {
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = \Config\Database::getConnection();
                $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
                return in_array('warehouse_tools', $tables) && 
                       in_array('tool_requests', $tables);
            } catch (\Exception $e) {
                return false;
            }
        });
        
        // Test 4: Charset de tablas es UTF-8
        self::test('Table Charset UTF-8', function() {
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = \Config\Database::getConnection();
                $result = $db->query("SHOW CREATE TABLE users")->fetch();
                return strpos($result['Create Table'], 'utf8mb4') !== false;
            } catch (\Exception $e) {
                return false;
            }
        });
        
        // Test 5: Datos seeders cargados
        self::test('Seeder Data', function() {
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = \Config\Database::getConnection();
                $users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
                return $users >= 3;
            } catch (\Exception $e) {
                return false;
            }
        });
    }
    
    /**
     * Pruebas de Rutas
     */
    private static function runRouteTests() {
        echo "\n🌐 PRUEBAS DE RUTAS\n";
        echo "─────────────────────────────────\n";
        
        $routes = file_get_contents(__DIR__ . '/../routes/web.php');
        
        // Test 1: Rutas principales
        self::test('Main Routes', function() use ($routes) {
            return strpos($routes, '/dashboard') !== false &&
                   strpos($routes, '/login') !== false &&
                   strpos($routes, '/logout') !== false;
        });
        
        // Test 2: Rutas de herramientas
        self::test('Tools Routes', function() use ($routes) {
            return strpos($routes, '/tools') !== false &&
                   strpos($routes, '/tools/warehouse') !== false;
        });
        
        // Test 3: Rutas de órdenes
        self::test('Work Orders Routes', function() use ($routes) {
            return strpos($routes, '/work-orders') !== false;
        });
        
        // Test 4: Rutas de inventario
        self::test('Parts Routes', function() use ($routes) {
            return strpos($routes, '/parts') !== false;
        });
        
        // Test 5: Rutas protegidas por rol
        self::test('Role Protected Routes', function() use ($routes) {
            return strpos($routes, 'requireRole') !== false ||
                   strpos($routes, 'requireAuth') !== false;
        });
    }
    
    /**
     * Pruebas de Seguridad
     */
    private static function runSecurityTests() {
        echo "\n🔒 PRUEBAS DE SEGURIDAD\n";
        echo "─────────────────────────────────\n";
        
        // Test 1: CSRF helper existe
        self::test('CSRF Helper', function() {
            $helpers = file_get_contents(__DIR__ . '/../app/helpers.php');
            return strpos($helpers, 'csrf_token') !== false;
        });
        
        // Test 2: Password hashing
        self::test('Password Hashing', function() {
            $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
            return strpos($schema, 'password_hash') !== false ||
                   strpos($schema, '$2y$') !== false;
        });
        
        // Test 3: Escaping en vistas
        self::test('View Escaping', function() {
            $view = file_get_contents(__DIR__ . '/../app/views/users/index.php');
            return strpos($view, 'esc(') !== false ||
                   strpos($view, 'htmlspecialchars') !== false;
        });
        
        // Test 4: No SQL injection directo
        self::test('No Raw SQL', function() {
            $controller = file_get_contents(__DIR__ . '/../app/controllers/UserController.php');
            // Verificar que se usan prepared statements
            return strpos($controller, 'prepare') !== false;
        });
    }
    
    /**
     * Ejecutar un test individual
     */
    private static function test($name, callable $callback) {
        self::$totalTests++;
        try {
            $result = $callback();
            if ($result) {
                self::$passedTests++;
                echo "  ✅ PASS: $name\n";
                self::$results[] = ['name' => $name, 'status' => 'PASS'];
            } else {
                self::$failedTests++;
                echo "  ❌ FAIL: $name\n";
                self::$results[] = ['name' => $name, 'status' => 'FAIL'];
            }
        } catch (\Exception $e) {
            self::$failedTests++;
            echo "  ❌ FAIL: $name (Error: " . $e->getMessage() . ")\n";
            self::$results[] = ['name' => $name, 'status' => 'FAIL', 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Imprimir reporte final
     */
    private static function printFinalReport($duration) {
        echo "\n═════════════════════════════════════════\n";
        echo "         REPORTE FINAL\n";
        echo "═════════════════════════════════════════\n";
        echo "Total Tests:    " . self::$totalTests . "\n";
        echo "✅ Pasados:     " . self::$passedTests . "\n";
        echo "❌ Fallidos:    " . self::$failedTests . "\n";
        echo "⏱️  Duración:    {$duration}s\n";
        echo "─────────────────────────────────\n";
        
        $percentage = self::$totalTests > 0 
            ? round((self::$passedTests / self::$totalTests) * 100, 1) 
            : 0;
        
        if ($percentage >= 90) {
            echo "🎉 EXCELENTE: {$percentage}% - Listo para producción\n";
        } elseif ($percentage >= 70) {
            echo "⚠️  ADECUADO: {$percentage}% - Revisar fallos antes de deploy\n";
        } else {
            echo "🚨 CRÍTICO: {$percentage}% - NO deployear hasta corregir\n";
        }
        
        echo "═════════════════════════════════════════\n\n";
        
        // Guardar reporte en archivo
        $report = [
            'date' => date('Y-m-d H:i:s'),
            'duration' => $duration,
            'total' => self::$totalTests,
            'passed' => self::$passedTests,
            'failed' => self::$failedTests,
            'percentage' => $percentage,
            'results' => self::$results
        ];
        
        $reportFile = __DIR__ . '/reports/test-report-' . date('Y-m-d-His') . '.json';
        if (!is_dir(__DIR__ . '/reports')) {
            mkdir(__DIR__ . '/reports', 0755, true);
        }
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        echo "📄 Reporte guardado: $reportFile\n";
    }
}

// Ejecutar si se llama desde línea de comandos
if (php_sapi_name() === 'cli') {
    TestRunner::runAll();
}
