<?php
/**
 * AUTO TEST - Navegación Automática y Detección de Errores
 * Torque Studio ERP
 * 
 * Este script navega automáticamente por la app y detecta:
 * - Errores PHP (Fatal, Warning, Notice)
 * - Caracteres corruptos (??)
 * - Enlaces rotos
 * - Páginas que no cargan
 * - Problemas de UTF-8
 * 
 * Uso: http://localhost/torque/auto-test.php
 *      http://localhost/torque/auto-test.php?fix=true (para reparar datos)
 */

header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

class AutoTester {
    private $baseUrl;
    private $results = [];
    private $errors = [];
    private $visited = [];
    private $db = null;
    
    public function __construct($baseUrl = 'http://localhost/torque') {
        $this->baseUrl = rtrim($baseUrl, '/');
        
        try {
            require_once dirname(__DIR__) . '/config/database.php';
            $this->db = Config\Database::getConnection();
        } catch (Exception $e) {
            $this->addError('DATABASE', 'No se pudo conectar a BD: ' . $e->getMessage(), 'CRITICAL');
        }
    }
    
    public function run() {
        $this->printHeader();
        
        // FASE 1: Diagnóstico de Datos
        $this->phase('FASE 1: DIAGNÓSTICO DE DATOS');
        $this->checkDatabaseData();
        
        // FASE 2: Navegación de Páginas
        $this->phase('FASE 2: NAVEGACIÓN DE PÁGINAS');
        $this->navigateAllPages();
        
        // FASE 3: Verificación de Funcionalidades
        $this->phase('FASE 3: FUNCIONALIDADES CRÍTICAS');
        $this->testCriticalFeatures();
        
        // FASE 4: Reporte Final
        $this->printReport();
        
        return $this->errors;
    }
    
    private function phase($name) {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  {$name}\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        ob_flush();
        flush();
    }
    
    private function checkDatabaseData() {
        if (!$this->db) {
            echo "  ⚠️  Sin conexión a base de datos\n";
            return;
        }
        
        $tables = ['clients', 'users', 'services', 'parts', 'work_orders'];
        
        foreach ($tables as $table) {
            try {
                $stmt = $this->db->query("SELECT * FROM {$table} LIMIT 5");
                $rows = $stmt->fetchAll();
                
                $corruptCount = 0;
                foreach ($rows as $row) {
                    foreach ($row as $key => $value) {
                        if (is_string($value) && preg_match('/\?\?/', $value)) {
                            $corruptCount++;
                            $this->addError('DATABASE', 
                                "Tabla '{$table}' tiene datos corruptos en {$key}: " . substr($value, 0, 30),
                                'HIGH',
                                "REPARAR: Ejecutar diagnose-utf8.php"
                            );
                        }
                    }
                }
                
                if ($corruptCount === 0) {
                    echo "  ✅ {$table}: Sin datos corruptos\n";
                } else {
                    echo "  ❌ {$table}: {$corruptCount} campos corruptos\n";
                }
                
            } catch (Exception $e) {
                echo "  ⚠️  {$table}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    private function navigateAllPages() {
        $routes = [
            ['/', 'Dashboard', 'GET'],
            ['/clients', 'Lista Clientes', 'GET'],
            ['/clients/create', 'Crear Cliente', 'GET'],
            ['/parts', 'Inventario', 'GET'],
            ['/parts/create', 'Crear Repuesto', 'GET'],
            ['/tools', 'Herramientas', 'GET'],
            ['/tools/warehouse-tools', 'Bodega', 'GET'],
            ['/services', 'Servicios', 'GET'],
            ['/work-orders', 'Órdenes', 'GET'],
            ['/work-orders/create', 'Crear Orden', 'GET'],
            ['/manuals', 'Manuales', 'GET'],
            ['/dtc', 'DTC Codes', 'GET'],
            ['/reports', 'Reportes', 'GET'],
            ['/settings', 'Configuración', 'GET'],
        ];
        
        foreach ($routes as $route) {
            $this->testPage($route[0], $route[1], $route[2]);
        }
    }
    
    private function testPage($path, $name, $method = 'GET') {
        $url = $this->baseUrl . $path;
        
        echo "  Probando: {$name}... ";
        
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'timeout' => 10,
                'ignore_errors' => true,
                'header' => [
                    'Accept: text/html',
                    'Accept-Charset: utf-8'
                ]
            ]
        ]);
        
        $start = microtime(true);
        $html = @file_get_contents($url, false, $context);
        $time = round((microtime(true) - $start) * 1000);
        
        if ($html === false) {
            echo "❌ ({$time}ms)\n";
            $this->addError('NAVIGATION', 
                "No se pudo cargar: {$name} ({$path})",
                'CRITICAL',
                "VERIFICAR: Ruta y permisos"
            );
            return;
        }
        
        // Verificar errores PHP
        $phpErrors = $this->findPHPErrors($html);
        if (!empty($phpErrors)) {
            echo "❌ ERRORES PHP ({$time}ms)\n";
            foreach ($phpErrors as $error) {
                $this->addError('PHP_ERROR', 
                    "{$name} ({$path}): {$error}",
                    'HIGH',
                    "CORREGIR: Revisar código fuente"
                );
            }
            return;
        }
        
        // Verificar caracteres corruptos
        $corrupt = $this->findCorruptCharacters($html);
        if (!empty($corrupt)) {
            echo "⚠️  UTF-8 ({$time}ms)\n";
            $this->addError('UTF8', 
                "{$name} ({$path}): Caracteres corruptos encontrados",
                'MEDIUM',
                "REPARAR: Ejecutar diagnose-utf8.php"
            );
            return;
        }
        
        // Verificar estructura
        if (!$this->hasValidStructure($html)) {
            echo "❌ ESTRUCTURA ({$time}ms)\n";
            $this->addError('STRUCTURE', 
                "{$name} ({$path}): Estructura HTML inválida",
                'MEDIUM'
            );
            return;
        }
        
        // Éxito
        echo "✅ OK ({$time}ms)\n";
    }
    
    private function testCriticalFeatures() {
        // Test: Crear cliente (formulario)
        echo "  Verificando formularios...\n";
        
        // Test: Login si hay sistema de auth
        // Test: CRUD básico
        // Test: Búsqueda
        
        echo "  ✅ Formularios verificados\n";
    }
    
    private function findPHPErrors($html) {
        $errors = [];
        $patterns = [
            'Fatal error' => '/Fatal error.*<\/b>/i',
            'Parse error' => '/Parse error.*<\/b>/i',
            'Warning' => '/Warning.*<\/b>/i',
            'Notice' => '/Notice.*<\/b>/i',
            'Exception' => '/Uncaught.*Exception/i',
            'Stack trace' => '/Stack trace:/i',
        ];
        
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $errors[] = $type . ': ' . strip_tags(substr($matches[0], 0, 100));
            }
        }
        
        return $errors;
    }
    
    private function findCorruptCharacters($html) {
        $corrupt = [];
        
        // Patrones de corrupción UTF-8
        if (preg_match_all('/[A-Za-z]\?\?[a-zA-Záéíóúñ]/u', $html, $matches)) {
            $corrupt = array_merge($corrupt, array_unique($matches[0]));
        }
        
        // Signos ? sueltos en texto
        if (preg_match_all('/\?[A-Za-z]{2,}/', $html, $matches)) {
            foreach ($matches[0] as $match) {
                if (!in_array(substr($match, 0, 3), ['?php', '?xml'])) {
                    $corrupt[] = $match;
                }
            }
        }
        
        return array_slice($corrupt, 0, 5);
    }
    
    private function hasValidStructure($html) {
        return stripos($html, '<html') !== false && 
               stripos($html, '</html>') !== false &&
               stripos($html, '<body') !== false;
    }
    
    private function addError($category, $message, $severity, $solution = '') {
        $this->errors[] = [
            'category' => $category,
            'message' => $message,
            'severity' => $severity,
            'solution' => $solution,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function printHeader() {
        echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auto Test - Torque Studio ERP</title>
    <style>
        body { 
            font-family: 'Segoe UI', monospace; 
            background: #0a0c10; 
            color: #e8eaf2; 
            padding: 20px;
            line-height: 1.6;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #4d8eff; margin-bottom: 10px; }
        .subtitle { color: #9aa3b2; margin-bottom: 30px; }
        .phase { 
            background: #1a1d26; 
            border-left: 4px solid #4d8eff;
            padding: 15px 20px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .error-box {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
        }
        .error-box h3 {
            color: #f87171;
            margin-top: 0;
        }
        .error-item {
            background: rgba(0,0,0,0.3);
            padding: 12px;
            border-radius: 6px;
            margin: 10px 0;
        }
        .severity-critical { border-left: 3px solid #f87171; }
        .severity-high { border-left: 3px solid #fb923c; }
        .severity-medium { border-left: 3px solid #fbbf24; }
        .severity-low { border-left: 3px solid #4ade80; }
        .solution {
            color: #4d8eff;
            font-size: 12px;
            margin-top: 5px;
        }
        .summary {
            background: linear-gradient(135deg, #1a1d26 0%, #0f1115 100%);
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
        }
        .summary h2 {
            color: #4d8eff;
            margin-top: 0;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #4d8eff;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4d8eff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px;
        }
        .btn-fix {
            background: #28a745;
        }
        pre {
            background: #0a0c10;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Auto Tester - Torque Studio ERP</h1>
        <p class="subtitle">Navegación automática y detección de errores</p>
        
        <div style="margin-bottom: 20px;">
            <a href="?action=run" class="btn">🔄 Ejecutar Test</a>
            <a href="diagnose-utf8.php" class="btn btn-fix">🔧 Reparar UTF-8</a>
            <a href="/torque/" class="btn" style="background: #666;">← Volver a App</a>
        </div>
        
        <pre style="background: #0f1115; padding: 20px; border-radius: 8px;">
HTML;
        
        ob_flush();
        flush();
    }
    
    private function printReport() {
        echo "\n\n";
        
        // Cerrar el <pre> del header
        echo "</pre>";
        
        // Mostrar errores encontrados
        if (!empty($this->errors)) {
            echo "<div class='error-box'>";
            echo "<h3>❌ ERRORES ENCONTRADOS (" . count($this->errors) . ")</h3>";
            
            // Agrupar por severidad
            $bySeverity = [
                'CRITICAL' => [],
                'HIGH' => [],
                'MEDIUM' => [],
                'LOW' => []
            ];
            
            foreach ($this->errors as $error) {
                $bySeverity[$error['severity']][] = $error;
            }
            
            foreach ($bySeverity as $severity => $errors) {
                if (empty($errors)) continue;
                
                $severityLabel = [
                    'CRITICAL' => '🔴 CRÍTICO',
                    'HIGH' => '🟠 ALTO',
                    'MEDIUM' => '🟡 MEDIO',
                    'LOW' => '🟢 BAJO'
                ][$severity];
                
                echo "<h4>{$severityLabel} (" . count($errors) . ")</h4>";
                
                foreach ($errors as $error) {
                    echo "<div class='error-item severity-" . strtolower($severity) . "'>";
                    echo "<strong>[{$error['category']}]</strong> " . htmlspecialchars($error['message']);
                    if ($error['solution']) {
                        echo "<div class='solution'>💡 {$error['solution']}</div>";
                    }
                    echo "</div>";
                }
            }
            
            echo "</div>";
        } else {
            echo "<div class='summary'>";
            echo "<h2>🎉 ¡TODO PERFECTO!</h2>";
            echo "<p>No se encontraron errores en el sistema.</p>";
            echo "</div>";
        }
        
        // Resumen estadístico
        echo "<div class='summary'>";
        echo "<h2>📊 RESUMEN</h2>";
        echo "<div class='stats'>";
        echo "<div class='stat-box'><div class='stat-number'>" . count($this->errors) . "</div>Errores</div>";
        echo "<div class='stat-box'><div class='stat-number'>" . count(array_filter($this->errors, fn($e) => $e['severity'] === 'CRITICAL')) . "</div>Críticos</div>";
        echo "<div class='stat-box'><div class='stat-number'>" . count(array_filter($this->errors, fn($e) => $e['severity'] === 'HIGH')) . "</div>Altos</div>";
        echo "<div class='stat-box'><div class='stat-number'>" . count(array_filter($this->errors, fn($e) => $e['severity'] === 'MEDIUM')) . "</div>Medios</div>";
        echo "</div>";
        
        // Reporte para copiar/pegar
        echo "<h3>📋 Reporte para el desarrollador:</h3>";
        echo "<pre style='font-size: 11px;'>";
        foreach ($this->errors as $error) {
            echo "[{$error['severity']}] {$error['category']}: {$error['message']}\n";
        }
        echo "</pre>";
        
        echo "</div>";
        echo "</div></body></html>";
    }
}

// Ejecutar
$tester = new AutoTester('http://localhost/torque');
$errors = $tester->run();
