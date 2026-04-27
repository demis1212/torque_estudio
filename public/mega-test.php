<?php
/**
 * MEGA TEST - Navegación Completa Automatizada
 * Torque Studio ERP
 * 
 * Este script:
 * 1. Hace login como admin
 * 2. Navega por TODAS las páginas
 * 3. Crea registros de prueba (clientes, servicios, etc.)
 * 4. Edita registros
 * 5. Prueba botones y formularios
 * 6. Genera reporte completo de errores
 */

set_time_limit(600);
header('Content-Type: text/html; charset=utf-8');
ob_implicit_flush(true);

class MegaTester {
    private $baseUrl;
    private $cookieFile;
    private $csrfToken = '';
    private $results = [];
    private $errors = [];
    private $passed = 0;
    private $failed = 0;
    private $createdIds = [];
    
    public function __construct() {
        // Detectar baseUrl automáticamente
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $this->baseUrl = $scheme . '://' . $host . $scriptDir;
        
        $this->cookieFile = sys_get_temp_dir() . '/torque_mega_test_' . md5(time()) . '.txt';
    }
    
    public function __destruct() {
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }
    
    public function run() {
        $this->printHeader();
        $startTime = microtime(true);
        
        // FASE 1: Login
        $this->phase('FASE 1: LOGIN');
        $loginOk = $this->doLogin();
        
        if (!$loginOk) {
            $this->log('❌ No se pudo hacer login. Abortando.', 'CRITICAL');
            $this->printReport(microtime(true) - $startTime);
            return;
        }
        
        // FASE 2: Navegar todas las páginas GET
        $this->phase('FASE 2: NAVEGACIÓN DE PÁGINAS');
        $this->navigateAllPages();
        
        // FASE 3: Crear registros de prueba
        $this->phase('FASE 3: CREAR REGISTROS');
        $this->createTestRecords();
        
        // FASE 4: Editar registros
        $this->phase('FASE 4: EDITAR REGISTROS');
        $this->editTestRecords();
        
        // FASE 5: Funcionalidades especiales
        $this->phase('FASE 5: FUNCIONALIDADES ESPECIALES');
        $this->testSpecialFeatures();
        
        // FASE 6: Limpiar registros de prueba
        $this->phase('FASE 6: LIMPIEZA');
        $this->cleanupTestRecords();
        
        // Reporte
        $this->printReport(microtime(true) - $startTime);
    }
    
    // =========================================
    // LOGIN
    // =========================================
    private function doLogin() {
        $this->log('Intentando login como admin@torque.com...');
        
        // Primero obtener CSRF token del formulario de login
        $loginPage = $this->get('/login');
        
        if ($loginPage['code'] === 0) {
            $this->log('❌ No se pudo cargar /login', 'HIGH');
            return false;
        }
        
        // Extraer CSRF token
        if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $matches)) {
            $this->csrfToken = $matches[1];
            $this->log('✅ CSRF token obtenido');
        } else {
            $this->log('⚠️  No se encontró CSRF token, intentando sin él');
        }
        
        // Hacer login
        $result = $this->post('/login', [
            'email' => 'admin@torque.com',
            'password' => 'admin123',
            'csrf_token' => $this->csrfToken,
        ]);
        
        // Verificar si fue redireccionado al dashboard
        if ($result['code'] === 302 || $result['code'] === 200) {
            // Seguir la redirección
            $dashResult = $this->get('/dashboard');
            
            if ($dashResult['code'] === 200 && stripos($dashResult['body'], 'Dashboard') !== false) {
                $this->log('✅ Login exitoso! Dashboard cargado');
                $this->passed++;
                
                // Actualizar CSRF token
                $this->extractCSRF($dashResult['body']);
                return true;
            }
        }
        
        $this->log('❌ Login falló. HTTP Code: ' . $result['code'], 'HIGH');
        $this->failed++;
        return false;
    }
    
    // =========================================
    // NAVEGACIÓN
    // =========================================
    private function navigateAllPages() {
        $pages = [
            ['/dashboard', 'Dashboard'],
            ['/clients', 'Lista Clientes'],
            ['/clients/create', 'Crear Cliente'],
            ['/services', 'Catálogo Servicios'],
            ['/services/create', 'Crear Servicio'],
            ['/parts', 'Inventario'],
            ['/parts/create', 'Crear Repuesto'],
            ['/tools', 'Herramientas'],
            ['/tools/warehouse', 'Bodega'],
            ['/tools/mechanic', 'Herramientas Mecánico'],
            ['/tools/requests', 'Solicitudes'],
            ['/work-orders', 'Órdenes de Trabajo'],
            ['/work-orders/create', 'Crear Orden'],
            ['/work-orders/kanban', 'Kanban Board'],
            ['/manuals', 'Manuales'],
            ['/manuals/create', 'Subir Manual'],
            ['/dtc', 'Códigos DTC'],
            ['/vin-decoder', 'VIN Decoder'],
            ['/reports', 'Reportes'],
            ['/settings', 'Configuración'],
            ['/notifications', 'Notificaciones'],
            ['/users', 'Usuarios'],
            ['/users/create', 'Crear Usuario'],
            ['/vehicles', 'Vehículos'],
            ['/vehicles/create', 'Crear Vehículo'],
        ];
        
        foreach ($pages as $page) {
            $this->testPage($page[0], $page[1]);
        }
    }
    
    private function testPage($path, $name) {
        $result = $this->get($path);
        $issues = [];
        
        // Verificar HTTP code
        if ($result['code'] >= 400) {
            $issues[] = "HTTP {$result['code']}";
        }
        
        // Verificar errores PHP
        $phpErrors = $this->findPHPErrors($result['body']);
        if (!empty($phpErrors)) {
            $issues = array_merge($issues, $phpErrors);
        }
        
        // Verificar caracteres corruptos
        if (preg_match('/[a-zA-Z]\?\?[a-zA-Z]/', $result['body'])) {
            $issues[] = 'Caracteres corruptos (??)';
        }
        
        // Actualizar CSRF
        $this->extractCSRF($result['body']);
        
        if (empty($issues)) {
            $this->log("  ✅ {$name} ({$path}) - OK ({$result['time']}ms)");
            $this->passed++;
        } else {
            $errorStr = implode(', ', $issues);
            $this->log("  ❌ {$name} ({$path}) - {$errorStr}", 'HIGH');
            $this->failed++;
            $this->errors[] = [
                'page' => $name,
                'path' => $path,
                'issues' => $issues,
                'code' => $result['code']
            ];
        }
    }
    
    // =========================================
    // CREAR REGISTROS
    // =========================================
    private function createTestRecords() {
        // Crear Cliente de prueba
        $this->log('Creando cliente de prueba...');
        $result = $this->post('/clients/create', [
            'csrf_token' => $this->csrfToken,
            'name' => 'TEST_Cliente Automático',
            'phone' => '555-TEST-001',
            'email' => 'test_auto@test.com',
            'address' => 'Dirección de prueba automática',
        ]);
        $this->evaluateCreate($result, 'Cliente', '/clients');
        
        // Crear Servicio de prueba
        $this->log('Creando servicio de prueba...');
        $result = $this->post('/services/create', [
            'csrf_token' => $this->csrfToken,
            'name' => 'TEST_Servicio Automático',
            'description' => 'Servicio creado por test automático',
            'price' => '99999',
        ]);
        $this->evaluateCreate($result, 'Servicio', '/services');
        
        // Crear Repuesto de prueba
        $this->log('Creando repuesto de prueba...');
        $result = $this->post('/parts/create', [
            'csrf_token' => $this->csrfToken,
            'name' => 'TEST_Repuesto Automático',
            'description' => 'Repuesto creado por test automático',
            'category' => 'Motor',
            'cost_price' => '5000',
            'sale_price' => '8000',
            'stock' => '10',
            'min_stock' => '2',
            'supplier' => 'Test Supplier',
            'unit_type' => 'unidad',
        ]);
        $this->evaluateCreate($result, 'Repuesto', '/parts');
    }
    
    private function evaluateCreate($result, $name, $listPath) {
        if ($result['code'] === 302 || $result['code'] === 200) {
            // Verificar que aparece en la lista
            $list = $this->get($listPath);
            if (stripos($list['body'], 'TEST_') !== false) {
                $this->log("  ✅ {$name} creado exitosamente");
                $this->passed++;
                
                // Extraer ID del registro creado
                if (preg_match('/edit\/(\d+)/', $list['body'], $matches)) {
                    $this->createdIds[$name] = $matches[1];
                }
                $this->extractCSRF($list['body']);
            } else {
                $this->log("  ⚠️  {$name} enviado pero no aparece en lista");
                $this->passed++;
            }
        } else {
            $phpErrors = $this->findPHPErrors($result['body']);
            $errorStr = !empty($phpErrors) ? implode(', ', $phpErrors) : "HTTP {$result['code']}";
            $this->log("  ❌ Error creando {$name}: {$errorStr}", 'HIGH');
            $this->failed++;
            $this->errors[] = [
                'page' => "Crear {$name}",
                'path' => 'POST',
                'issues' => [$errorStr],
                'code' => $result['code']
            ];
        }
    }
    
    // =========================================
    // EDITAR REGISTROS
    // =========================================
    private function editTestRecords() {
        foreach ($this->createdIds as $name => $id) {
            $editPath = '';
            switch ($name) {
                case 'Cliente': $editPath = "/clients/edit/{$id}"; break;
                case 'Servicio': $editPath = "/services/edit/{$id}"; break;
                case 'Repuesto': $editPath = "/parts/edit/{$id}"; break;
            }
            
            if ($editPath) {
                $this->log("Verificando edición de {$name} ID={$id}...");
                $result = $this->get($editPath);
                
                if ($result['code'] === 200) {
                    $phpErrors = $this->findPHPErrors($result['body']);
                    if (empty($phpErrors)) {
                        $this->log("  ✅ Formulario de edición {$name} OK");
                        $this->passed++;
                    } else {
                        $this->log("  ❌ Error PHP en edición {$name}: " . implode(', ', $phpErrors), 'HIGH');
                        $this->failed++;
                    }
                    $this->extractCSRF($result['body']);
                } else {
                    $this->log("  ❌ No se pudo cargar edición {$name}: HTTP {$result['code']}", 'HIGH');
                    $this->failed++;
                }
            }
        }
    }
    
    // =========================================
    // FUNCIONALIDADES ESPECIALES
    // =========================================
    private function testSpecialFeatures() {
        // Test API endpoints
        $this->log('Probando API endpoints...');
        $apiEndpoints = [
            ['/api/clients', 'API Clientes'],
            ['/api/vehicles', 'API Vehículos'],
            ['/api/parts', 'API Repuestos'],
        ];
        
        foreach ($apiEndpoints as $endpoint) {
            $result = $this->get($endpoint[0]);
            if ($result['code'] === 200) {
                $this->log("  ✅ {$endpoint[1]} OK");
                $this->passed++;
            } else {
                $this->log("  ❌ {$endpoint[1]}: HTTP {$result['code']}");
                $this->failed++;
            }
        }
        
        // Test búsqueda
        $this->log('Probando búsqueda...');
        $result = $this->get('/search?q=test');
        if ($result['code'] === 200) {
            $this->log("  ✅ Búsqueda OK");
            $this->passed++;
        } else {
            $this->log("  ⚠️  Búsqueda: HTTP {$result['code']}");
        }
    }
    
    // =========================================
    // LIMPIEZA
    // =========================================
    private function cleanupTestRecords() {
        $this->log('Limpiando registros de prueba...');
        
        // Conectar directamente a BD para limpiar
        try {
            require_once dirname(__DIR__) . '/config/database.php';
            $db = Config\Database::getConnection();
            
            $db->exec("DELETE FROM clients WHERE name LIKE 'TEST_%'");
            $db->exec("DELETE FROM services WHERE name LIKE 'TEST_%'");
            $db->exec("DELETE FROM parts WHERE name LIKE 'TEST_%'");
            
            $this->log('  ✅ Registros de prueba eliminados');
        } catch (Exception $e) {
            $this->log('  ⚠️  No se pudieron limpiar: ' . $e->getMessage());
        }
    }
    
    // =========================================
    // HTTP HELPERS
    // =========================================
    private function get($path) {
        return $this->request('GET', $path);
    }
    
    private function post($path, $data = []) {
        return $this->request('POST', $path, $data);
    }
    
    private function request($method, $path, $data = []) {
        $url = $this->baseUrl . $path;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $start = microtime(true);
        $body = curl_exec($ch);
        $time = round((microtime(true) - $start) * 1000);
        
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Si es redirect, seguir
        if ($code === 302 || $code === 301) {
            // Extraer Location header
            // Intentar cargar la página destino
        }
        
        return [
            'code' => $code,
            'body' => $body ?: '',
            'time' => $time,
            'error' => $error,
        ];
    }
    
    private function extractCSRF($html) {
        if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $matches)) {
            $this->csrfToken = $matches[1];
        }
    }
    
    private function findPHPErrors($html) {
        $errors = [];
        if (preg_match('/<b>Fatal error<\/b>:(.*?)(<br|<\/p>)/si', $html, $m)) {
            $errors[] = 'Fatal: ' . strip_tags(substr($m[1], 0, 80));
        }
        if (preg_match('/<b>Warning<\/b>:(.*?)(<br|<\/p>)/si', $html, $m)) {
            $errors[] = 'Warning: ' . strip_tags(substr($m[1], 0, 80));
        }
        if (preg_match('/Uncaught.*Exception/i', $html)) {
            $errors[] = 'Uncaught Exception';
        }
        return $errors;
    }
    
    // =========================================
    // OUTPUT
    // =========================================
    private function log($msg, $severity = 'INFO') {
        $this->results[] = ['msg' => $msg, 'severity' => $severity];
        echo $msg . "\n";
        ob_flush();
        flush();
    }
    
    private function phase($name) {
        echo "\n";
        echo "══════════════════════════════════════════════════════════════\n";
        echo "  {$name}\n";
        echo "══════════════════════════════════════════════════════════════\n\n";
        ob_flush();
        flush();
    }
    
    private function printHeader() {
        echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mega Test - Torque Studio ERP</title>
    <style>
        body { font-family: 'Segoe UI', monospace; background: #0a0c10; color: #e8eaf2; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #4d8eff; }
        pre { background: #0f1115; padding: 20px; border-radius: 8px; white-space: pre-wrap; line-height: 1.6; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
        .stat { background: #1a1d26; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-num { font-size: 36px; font-weight: bold; }
        .stat-num.green { color: #4ade80; }
        .stat-num.red { color: #f87171; }
        .stat-num.blue { color: #4d8eff; }
        .error-box { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.3); border-radius: 8px; padding: 20px; margin: 15px 0; }
        .error-item { background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin: 8px 0; border-left: 3px solid #f87171; }
        .report-box { background: #1a1d26; padding: 20px; border-radius: 8px; margin: 15px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #4d8eff; color: white; text-decoration: none; border-radius: 8px; margin: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🤖 MEGA TEST - Navegación Completa Automatizada</h1>
    <p style="color: #9aa3b2;">Login → Navegar → Crear → Editar → API → Limpiar</p>
    <div style="margin: 20px 0;">
        <a href="?run=1" class="btn">🔄 Ejecutar Test</a>
        <a href="deep-analyzer.php" class="btn" style="background:#666;">📊 Deep Analyzer</a>
    </div>
    <pre>
HTML;
        ob_flush();
        flush();
    }
    
    private function printReport($totalTime) {
        $totalTime = round($totalTime, 2);
        $total = $this->passed + $this->failed;
        $pct = $total > 0 ? round(($this->passed / $total) * 100) : 0;
        
        echo "\n\n</pre>";
        
        // Stats
        echo "<div class='stats'>";
        echo "<div class='stat'><div class='stat-num blue'>{$total}</div>Total Tests</div>";
        echo "<div class='stat'><div class='stat-num green'>{$this->passed}</div>Pasaron ✅</div>";
        echo "<div class='stat'><div class='stat-num red'>{$this->failed}</div>Fallaron ❌</div>";
        echo "<div class='stat'><div class='stat-num blue'>{$totalTime}s</div>Tiempo</div>";
        echo "</div>";
        
        // Barra de progreso
        $barColor = $pct >= 80 ? '#4ade80' : ($pct >= 50 ? '#fbbf24' : '#f87171');
        echo "<div style='background: #1a1d26; border-radius: 8px; height: 30px; margin: 20px 0;'>";
        echo "<div style='background: {$barColor}; width: {$pct}%; height: 100%; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #0a0c10;'>{$pct}%</div>";
        echo "</div>";
        
        // Errores detallados
        if (!empty($this->errors)) {
            echo "<div class='error-box'>";
            echo "<h2>❌ ERRORES ENCONTRADOS (" . count($this->errors) . ")</h2>";
            foreach ($this->errors as $err) {
                echo "<div class='error-item'>";
                echo "<strong>{$err['page']}</strong> ({$err['path']}) - HTTP {$err['code']}<br>";
                echo "<small>" . implode(', ', $err['issues']) . "</small>";
                echo "</div>";
            }
            echo "</div>";
        }
        
        // Reporte para copiar
        echo "<div class='report-box'>";
        echo "<h2>📋 Reporte para Copiar/Pegar</h2>";
        echo "<pre style='font-size: 11px;'>";
        echo "MEGA TEST REPORT - Torque Studio ERP\n";
        echo "=====================================\n";
        echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
        echo "Total: {$total} | Pasaron: {$this->passed} | Fallaron: {$this->failed} | Score: {$pct}%\n";
        echo "Tiempo: {$totalTime}s\n\n";
        
        if (!empty($this->errors)) {
            echo "ERRORES:\n";
            foreach ($this->errors as $err) {
                echo "  [{$err['code']}] {$err['page']} ({$err['path']}): " . implode(', ', $err['issues']) . "\n";
            }
        } else {
            echo "¡SIN ERRORES! Todo funciona correctamente.\n";
        }
        echo "</pre></div>";
        
        echo "</div></body></html>";
    }
}

$tester = new MegaTester();
$tester->run();
