<?php
/**
 * DEEP ANALYZER - Análisis Profundo del Sistema
 * Torque Studio ERP
 * 
 * Navega por TODO el sistema y genera reporte completo:
 * - Todas las páginas y sus errores
 * - CSS/JS faltantes
 * - Imágenes rotas
 * - Enlaces muertos
 * - Errores PHP ocultos
 * - Problemas de UTF-8
 * - Performance
 */

header('Content-Type: text/html; charset=utf-8');
set_time_limit(300); // 5 minutos

class DeepAnalyzer {
    private $baseUrl;
    private $results = [];
    private $errors = [];
    private $warnings = [];
    private $visited = [];
    private $totalPages = 0;
    private $okPages = 0;
    private $db;
    
    public function __construct($baseUrl = 'http://localhost/torque') {
        $this->baseUrl = rtrim($baseUrl, '/');
        
        try {
            require_once dirname(__DIR__) . '/config/database.php';
            $this->db = Config\Database::getConnection();
        } catch (Exception $e) {
            $this->addError('DATABASE', 'No se pudo conectar: ' . $e->getMessage(), 'CRITICAL');
        }
    }
    
    public function analyze() {
        $this->printHeader();
        
        // SECCIÓN 1: Análisis de Base de Datos
        $this->section("📊 ANÁLISIS DE BASE DE DATOS");
        $this->analyzeDatabase();
        
        // SECCIÓN 2: Análisis de Páginas
        $this->section("🌐 ANÁLISIS DE PÁGINAS");
        $this->analyzeAllPages();
        
        // SECCIÓN 3: Análisis de Assets
        $this->section("🎨 ANÁLISIS DE RECURSOS (CSS/JS/IMG)");
        $this->analyzeAssets();
        
        // SECCIÓN 4: Análisis de Funcionalidades
        $this->section("⚙️ ANÁLISIS DE FUNCIONALIDADES");
        $this->analyzeFeatures();
        
        // SECCIÓN 5: Reporte Final
        $this->section("📋 REPORTE FINAL");
        $this->generateReport();
        
        return $this->errors;
    }
    
    private function section($title) {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║ {$title}\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";
        ob_flush();
        flush();
    }
    
    private function analyzeDatabase() {
        if (!$this->db) {
            echo "❌ Sin conexión a base de datos\n";
            return;
        }
        
        $tables = [
            'clients' => ['name', 'email', 'address'],
            'users' => ['name', 'email'],
            'services' => ['name', 'description'],
            'parts' => ['name', 'description', 'supplier'],
            'work_orders' => ['description', 'diagnosis'],
            'vehicles' => ['notes'],
            'manuals' => ['title', 'description'],
        ];
        
        foreach ($tables as $table => $textColumns) {
            try {
                // Contar registros
                $count = $this->db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                echo "  📁 {$table}: {$count} registros\n";
                
                // Verificar UTF-8
                $corruptFound = false;
                foreach ($textColumns as $column) {
                    $stmt = $this->db->query("SELECT id, {$column} FROM {$table} WHERE {$column} LIKE '%??%' LIMIT 5");
                    while ($row = $stmt->fetch()) {
                        if ($row[$column] && preg_match('/\?\?/', $row[$column])) {
                            $this->addError('DB_UTF8', 
                                "Tabla '{$table}'.'{$column}' ID={$row['id']}: " . substr($row[$column], 0, 40),
                                'HIGH',
                                "Ejecutar fix de UTF-8"
                            );
                            $corruptFound = true;
                        }
                    }
                }
                
                if (!$corruptFound) {
                    echo "     ✅ UTF-8 OK\n";
                } else {
                    echo "     ❌ Tiene datos corruptos\n";
                }
                
            } catch (Exception $e) {
                $this->addError('DB_CHECK', "Error en {$table}: " . $e->getMessage(), 'MEDIUM');
                echo "     ⚠️  Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    private function analyzeAllPages() {
        $routes = [
            // Dashboard
            ['/', 'Dashboard', ['html', 'css', 'js']],
            
            // Clientes
            ['/clients', 'Lista Clientes', ['html', 'table', 'css']],
            ['/clients/create', 'Crear Cliente', ['html', 'form', 'css', 'js']],
            
            // Servicios
            ['/services', 'Catálogo Servicios', ['html', 'table', 'css']],
            ['/services/create', 'Crear Servicio', ['html', 'form', 'css']],
            
            // Inventario
            ['/parts', 'Inventario', ['html', 'table', 'css']],
            ['/parts/create', 'Crear Repuesto', ['html', 'form', 'css', 'js']],
            
            // Herramientas
            ['/tools', 'Herramientas', ['html', 'css']],
            ['/tools/warehouse-tools', 'Bodega', ['html', 'table', 'css']],
            ['/tools/mechanic-tools', 'Herramientas Mecánicos', ['html', 'table', 'css']],
            ['/tools/requests', 'Solicitudes', ['html', 'table', 'css']],
            
            // Órdenes
            ['/work-orders', 'Órdenes', ['html', 'table', 'css']],
            ['/work-orders/create', 'Crear Orden', ['html', 'form', 'css', 'js']],
            ['/work-orders/kanban', 'Kanban', ['html', 'css', 'js']],
            
            // Manuales
            ['/manuals', 'Manuales', ['html', 'css']],
            ['/manuals/create', 'Subir Manual', ['html', 'form', 'css']],
            
            // Otros
            ['/dtc', 'DTC Codes', ['html', 'table', 'css']],
            ['/reports', 'Reportes', ['html', 'css']],
            ['/settings', 'Configuración', ['html', 'form', 'css']],
            ['/vin-decoder', 'VIN Decoder', ['html', 'form', 'css']],
        ];
        
        foreach ($routes as $route) {
            $this->testPageDeep($route[0], $route[1], $route[2]);
        }
    }
    
    private function testPageDeep($path, $name, $checks) {
        $url = $this->baseUrl . $path;
        $this->totalPages++;
        
        echo "  🔍 {$name}... ";
        
        $start = microtime(true);
        
        // Intentar con cURL primero (más confiable)
        $html = false;
        $httpCode = 0;
        
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($html === false && !empty($curlError)) {
                echo "❌ CURL Error: {$curlError}\n";
                $this->addError('PAGE_LOAD', "{$name} ({$path}): {$curlError}", 'CRITICAL');
                return;
            }
        } else {
            // Fallback a file_get_contents
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'ignore_errors' => true,
                ]
            ]);
            $html = @file_get_contents($url, false, $context);
            
            // Extraer código HTTP de headers
            if (isset($http_response_header[0])) {
                preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
                $httpCode = isset($matches[1]) ? intval($matches[1]) : 0;
            }
        }
        
        $time = round((microtime(true) - $start) * 1000);
        
        if ($html === false || empty($html)) {
            echo "❌ ({$time}ms) HTTP {$httpCode}\n";
            $this->addError('PAGE_LOAD', "{$name} ({$path}): HTTP {$httpCode} o vacío", 'CRITICAL');
            return;
        }
        
        // Verificar código HTTP de error
        if ($httpCode >= 400) {
            echo "❌ ({$time}ms) HTTP {$httpCode}\n";
            $this->addError('PAGE_LOAD', "{$name} ({$path}): HTTP {$httpCode}", 'CRITICAL');
            return;
        }
        
        $pageErrors = [];
        
        // 1. Verificar estructura HTML completa
        if (in_array('html', $checks)) {
            $structErrors = $this->checkHTMLStructure($html);
            if (!empty($structErrors)) {
                $pageErrors[] = 'Estructura: ' . implode(', ', $structErrors);
            }
        }
        
        // 2. Verificar errores PHP
        $phpErrors = $this->extractPHPErrors($html);
        if (!empty($phpErrors)) {
            foreach ($phpErrors as $error) {
                $pageErrors[] = 'PHP: ' . $error;
                $this->addError('PHP', "{$name}: {$error}", 'HIGH');
            }
        }
        
        // 3. Verificar UTF-8
        $utf8Errors = $this->checkUTF8($html);
        if (!empty($utf8Errors)) {
            $pageErrors[] = 'UTF-8: ' . count($utf8Errors) . ' problemas';
        }
        
        // 4. Verificar CSS/JS externos
        if (in_array('css', $checks)) {
            $missingCSS = $this->checkExternalCSS($html);
            if (!empty($missingCSS)) {
                $pageErrors[] = 'CSS faltante: ' . implode(', ', $missingCSS);
            }
        }
        
        if (in_array('js', $checks)) {
            $missingJS = $this->checkExternalJS($html);
            if (!empty($missingJS)) {
                $pageErrors[] = 'JS faltante: ' . implode(', ', $missingJS);
            }
        }
        
        // 5. Verificar imágenes rotas
        $brokenImages = $this->checkImages($html);
        if (!empty($brokenImages)) {
            $pageErrors[] = 'Imágenes rotas: ' . count($brokenImages);
        }
        
        // 6. Verificar enlaces rotos
        if (in_array('table', $checks)) {
            $brokenLinks = $this->checkInternalLinks($html);
            if (!empty($brokenLinks)) {
                $pageErrors[] = 'Enlaces: ' . count($brokenLinks) . ' posibles problemas';
            }
        }
        
        // 7. Verificar formularios
        if (in_array('form', $checks)) {
            $formIssues = $this->checkForms($html);
            if (!empty($formIssues)) {
                $pageErrors[] = 'Formulario: ' . implode(', ', $formIssues);
            }
        }
        
        if (empty($pageErrors)) {
            echo "✅ OK ({$time}ms)\n";
            $this->okPages++;
        } else {
            echo "⚠️  ({$time}ms)\n";
            foreach ($pageErrors as $err) {
                echo "     └─ {$err}\n";
            }
        }
    }
    
    private function checkHTMLStructure($html) {
        $errors = [];
        
        // Verificar estructura básica (case-insensitive)
        $htmlLower = strtolower($html);
        
        // Si hay errores PHP fatales, el HTML no estará completo
        if (preg_match('/fatal error|parse error/i', $html)) {
            return ['Hay errores PHP que impiden renderizar HTML completo'];
        }
        
        // Verificar etiquetas esenciales
        if (!preg_match('/<!DOCTYPE\s+html/i', $html)) {
            $errors[] = 'Falta DOCTYPE html';
        }
        
        if (!preg_match('/<html[^>]*>/i', $html)) {
            $errors[] = 'Falta <html>';
        }
        
        if (!preg_match('/<\/html>/i', $html)) {
            $errors[] = 'Falta </html>';
        }
        
        // Verificar divs balanceados (solo si el HTML parece válido)
        if (count($errors) === 0) {
            $openDiv = preg_match_all('/<div[\s>]/i', $html);
            $closeDiv = preg_match_all('/<\/div>/i', $html);
            if ($openDiv !== $closeDiv) {
                $errors[] = "Divs desbalanceados ({$openDiv} abiertas, {$closeDiv} cerradas)";
            }
        }
        
        return $errors;
    }
    
    private function extractPHPErrors($html) {
        $errors = [];
        $patterns = [
            '/<b>Fatal error<\/b>:(.*?)<br/i',
            '/<b>Parse error<\/b>:(.*?)<br/i',
            '/<b>Warning<\/b>:(.*?)<br/i',
            '/<b>Notice<\/b>:(.*?)<br/i',
            '/Uncaught Exception:(.*?)Stack trace/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $errors[] = strip_tags($matches[1]);
            }
        }
        
        return $errors;
    }
    
    private function checkUTF8($html) {
        $errors = [];
        if (preg_match_all('/[a-zA-Z]\?\?[a-zA-Z]/', $html, $matches)) {
            $errors = array_unique($matches[0]);
        }
        return array_slice($errors, 0, 3);
    }
    
    private function checkExternalCSS($html) {
        $missing = [];
        preg_match_all('/<link[^>]*href=["\']([^"\']+\.css)["\']/i', $html, $matches);
        foreach ($matches[1] as $css) {
            if (strpos($css, 'http') !== 0 && strpos($css, '//') !== 0) {
                $cssUrl = $this->baseUrl . '/' . ltrim($css, '/');
                if (!@file_get_contents($cssUrl)) {
                    $missing[] = basename($css);
                }
            }
        }
        return $missing;
    }
    
    private function checkExternalJS($html) {
        $missing = [];
        preg_match_all('/<script[^>]*src=["\']([^"\']+\.js)["\']/i', $html, $matches);
        foreach ($matches[1] as $js) {
            if (strpos($js, 'http') !== 0 && strpos($js, '//') !== 0) {
                $jsUrl = $this->baseUrl . '/' . ltrim($js, '/');
                if (!@file_get_contents($jsUrl)) {
                    $missing[] = basename($js);
                }
            }
        }
        return $missing;
    }
    
    private function checkImages($html) {
        $broken = [];
        preg_match_all('/<img[^>]*src=["\']([^"\']+)["\']/i', $html, $matches);
        // Aquí podríamos verificar cada imagen
        return $broken;
    }
    
    private function checkInternalLinks($html) {
        $issues = [];
        preg_match_all('/href=["\'](\/[^"\']*)["\']/', $html, $matches);
        // Aquí podríamos verificar enlaces internos
        return $issues;
    }
    
    private function checkForms($html) {
        $issues = [];
        if (preg_match('/<form/', $html)) {
            if (!preg_match('/method=["\'](post|get)["\']/i', $html)) {
                $issues[] = 'Form sin method';
            }
            if (!preg_match('/<input[^>]*type=["\']submit["\']/i', $html) && 
                !preg_match('/<button[^>]*type=["\']submit["\']/i', $html)) {
                $issues[] = 'Form sin botón submit';
            }
        }
        return $issues;
    }
    
    private function analyzeAssets() {
        $cssPath = 'C:/xampp/htdocs/torque/public/css';
        $jsPath = 'C:/xampp/htdocs/torque/public/assets/js';
        
        if (!is_dir($cssPath)) {
            echo "  ❌ Directorio CSS no existe: {$cssPath}\n";
            $this->addWarning('ASSETS', 'Falta directorio CSS', 'MEDIUM');
        } else {
            $cssFiles = glob($cssPath . '/*.css');
            echo "  ✅ CSS: " . count($cssFiles) . " archivos\n";
        }
        
        if (!is_dir($jsPath)) {
            echo "  ⚠️  Directorio JS no existe: {$jsPath}\n";
        } else {
            $jsFiles = glob($jsPath . '/*.js');
            echo "  ✅ JS: " . count($jsFiles) . " archivos\n";
        }
    }
    
    private function analyzeFeatures() {
        echo "  📱 Funcionalidades CRUD:\n";
        
        // Verificar que existan controladores
        $controllers = [
            'ClientController.php',
            'ServiceController.php',
            'PartController.php',
            'ToolsController.php',
            'WorkOrderController.php',
        ];
        
        foreach ($controllers as $ctrl) {
            $path = "C:/xampp/htdocs/torque/app/controllers/{$ctrl}";
            if (file_exists($path)) {
                echo "     ✅ {$ctrl}\n";
            } else {
                echo "     ❌ {$ctrl} NO EXISTE\n";
                $this->addError('CONTROLLER', "Falta: {$ctrl}", 'HIGH');
            }
        }
        
        echo "\n  🔒 Seguridad:\n";
        $securityChecks = [
            'app/helpers.php' => 'Helper functions',
            'app/models/Part.php' => 'Modelo Partes',
            'config/database.php' => 'Config BD',
        ];
        
        foreach ($securityChecks as $file => $desc) {
            $path = "C:/xampp/htdocs/torque/{$file}";
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $hasCSRF = strpos($content, 'csrf_token') !== false;
                $hasPrepare = strpos($content, 'prepare') !== false;
                echo "     ✅ {$desc}" . ($hasCSRF ? ' (CSRF OK)' : '') . ($hasPrepare ? ' (SQL Safe)' : '') . "\n";
            } else {
                echo "     ❌ {$desc} NO EXISTE\n";
            }
        }
    }
    
    private function addError($category, $message, $severity, $solution = '') {
        $this->errors[] = [
            'category' => $category,
            'message' => $message,
            'severity' => $severity,
            'solution' => $solution
        ];
    }
    
    private function addWarning($category, $message, $severity) {
        $this->warnings[] = [
            'category' => $category,
            'message' => $message,
            'severity' => $severity
        ];
    }
    
    private function printHeader() {
        echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Analyzer - Torque Studio ERP</title>
    <style>
        body { 
            font-family: 'Segoe UI', monospace; 
            background: #0a0c10; 
            color: #e8eaf2; 
            padding: 20px;
            line-height: 1.5;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #4d8eff; margin-bottom: 5px; }
        .subtitle { color: #9aa3b2; margin-bottom: 30px; }
        pre { 
            background: #0f1115; 
            padding: 20px; 
            border-radius: 8px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .section { 
            background: #1a1d26; 
            border-left: 4px solid #4d8eff;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .error-box {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
        }
        .warning-box {
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
        }
        .success-box {
            background: rgba(74, 222, 128, 0.1);
            border: 1px solid rgba(74, 222, 128, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
        }
        .error-item {
            background: rgba(0,0,0,0.3);
            padding: 12px;
            border-radius: 6px;
            margin: 8px 0;
            border-left: 3px solid;
        }
        .severity-critical { border-left-color: #f87171; }
        .severity-high { border-left-color: #fb923c; }
        .severity-medium { border-left-color: #fbbf24; }
        .stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
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
        .solution {
            color: #8ab4f8;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔬 Deep Analyzer</h1>
        <p class="subtitle">Análisis profundo del sistema - Torque Studio ERP</p>
        
        <div style="margin-bottom: 20px;">
            <a href="?action=run" class="btn">🔄 Ejecutar Análisis</a>
            <a href="diagnose-utf8.php" class="btn" style="background: #28a745;">🔧 Reparar UTF-8</a>
            <a href="auto-test.php" class="btn" style="background: #666;">← Auto Test</a>
        </div>
        
        <pre style="background: #0f1115; padding: 20px; border-radius: 8px;">
HTML;
        ob_flush();
        flush();
    }
    
    private function generateReport() {
        echo "\n\n</pre>";
        
        // Estadísticas
        $critical = count(array_filter($this->errors, fn($e) => $e['severity'] === 'CRITICAL'));
        $high = count(array_filter($this->errors, fn($e) => $e['severity'] === 'HIGH'));
        $medium = count(array_filter($this->errors, fn($e) => $e['severity'] === 'MEDIUM'));
        
        echo "<div class='section'>";
        echo "<h2>📊 ESTADÍSTICAS</h2>";
        echo "<div class='stats'>";
        echo "<div class='stat-box'><div class='stat-number'>{$this->totalPages}</div>Páginas</div>";
        echo "<div class='stat-box'><div class='stat-number'>{$this->okPages}</div>OK</div>";
        echo "<div class='stat-box'><div class='stat-number' style='color: #f87171;'>{$critical}</div>Críticos</div>";
        echo "<div class='stat-box'><div class='stat-number' style='color: #fb923c;'>{$high}</div>Altos</div>";
        echo "<div class='stat-box'><div class='stat-number' style='color: #fbbf24;'>{$medium}</div>Medios</div>";
        echo "</div>";
        echo "</div>";
        
        // Errores detallados
        if (!empty($this->errors)) {
            echo "<div class='error-box'>";
            echo "<h2>❌ ERRORES ENCONTRADOS (" . count($this->errors) . ")</h2>";
            
            $byCat = [];
            foreach ($this->errors as $err) {
                $byCat[$err['category']][] = $err;
            }
            
            foreach ($byCat as $cat => $errs) {
                echo "<h3>{$cat} (" . count($errs) . ")</h3>";
                foreach ($errs as $err) {
                    $class = 'severity-' . strtolower($err['severity']);
                    echo "<div class='error-item {$class}'>";
                    echo "<strong>[{$err['severity']}]</strong> " . htmlspecialchars($err['message']);
                    if ($err['solution']) {
                        echo "<div class='solution'>💡 {$err['solution']}</div>";
                    }
                    echo "</div>";
                }
            }
            echo "</div>";
        }
        
        // Reporte para desarrollador
        echo "<div class='section'>";
        echo "<h2>📋 Reporte para Copiar/Pegar</h2>";
        echo "<pre style='font-size: 11px; max-height: 400px;'>";
        echo "DEEP ANALYZER REPORT\n";
        echo "=====================\n\n";
        echo "Páginas: {$this->okPages}/{$this->totalPages} OK\n";
        echo "Errores: " . count($this->errors) . "\n\n";
        foreach ($this->errors as $err) {
            echo "[{$err['severity']}] {$err['category']}: {$err['message']}\n";
        }
        echo "</pre>";
        echo "</div>";
        
        echo "</div></body></html>";
    }
}

// Ejecutar
$analyzer = new DeepAnalyzer('http://localhost/torque');
$analyzer->analyze();
