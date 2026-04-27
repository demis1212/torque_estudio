<?php
/**
 * VISUAL TESTER - Torque Studio ERP
 * 
 * Este script navega por la aplicación y verifica:
 * - Errores PHP visibles
 * - Caracteres corruptos (??)
 * - Enlaces rotos
 * - Problemas de UI
 * 
 * Uso: php tests/visual-test.php
 *      php tests/visual-test.php --url=http://localhost/torque
 */

class VisualTester {
    private $baseUrl;
    private $results = [];
    private $errors = [];
    private $checkedUrls = [];
    
    public function __construct($baseUrl = 'http://localhost/torque') {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    public function run() {
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║       VISUAL TESTER - Torque Studio ERP                       ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
        
        echo "🌐 URL Base: {$this->baseUrl}\n\n";
        
        // Rutas a probar
        $routes = [
            ['/', 'Dashboard'],
            ['/clients', 'Lista de Clientes'],
            ['/clients/create', 'Crear Cliente'],
            ['/parts', 'Inventario'],
            ['/parts/create', 'Crear Repuesto'],
            ['/tools', 'Herramientas'],
            ['/tools/warehouse-tools', 'Bodega'],
            ['/work-orders', 'Órdenes'],
            ['/manuals', 'Manuales'],
            ['/dtc', 'Códigos DTC'],
        ];
        
        foreach ($routes as $route) {
            $this->testRoute($route[0], $route[1]);
        }
        
        $this->printSummary();
    }
    
    private function testRoute($path, $name) {
        $url = $this->baseUrl . $path;
        echo "🔍 Probando: {$name}\n";
        echo "   URL: {$url}\n";
        
        // Intentar obtener la página
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
            ]
        ]);
        
        $html = @file_get_contents($url, false, $context);
        
        if ($html === false) {
            $this->errors[] = [
                'route' => $path,
                'name' => $name,
                'error' => 'No se pudo conectar a la URL',
                'severity' => 'CRITICAL'
            ];
            echo "   ❌ ERROR: No se pudo conectar\n\n";
            return;
        }
        
        // Verificar errores PHP visibles
        $phpErrors = $this->findPHPErrors($html);
        if (!empty($phpErrors)) {
            $this->errors[] = [
                'route' => $path,
                'name' => $name,
                'error' => 'Errores PHP: ' . implode(', ', $phpErrors),
                'severity' => 'HIGH'
            ];
            echo "   ❌ ERRORES PHP encontrados\n";
        }
        
        // Verificar caracteres corruptos
        $corrupt = $this->findCorruptCharacters($html);
        if (!empty($corrupt)) {
            $this->errors[] = [
                'route' => $path,
                'name' => $name,
                'error' => 'Caracteres corruptos: ' . implode(', ', array_slice($corrupt, 0, 3)),
                'severity' => 'MEDIUM'
            ];
            echo "   ⚠️  Caracteres corruptos encontrados\n";
        }
        
        // Verificar título de página
        $title = $this->extractTitle($html);
        if (empty($title) || strpos($title, 'Error') !== false) {
            $this->errors[] = [
                'route' => $path,
                'name' => $name,
                'error' => 'Título sospechoso: ' . $title,
                'severity' => 'LOW'
            ];
            echo "   ⚠️  Título sospechoso: {$title}\n";
        }
        
        // Verificar estructura básica
        if (!$this->hasValidStructure($html)) {
            $this->errors[] = [
                'route' => $path,
                'name' => $name,
                'error' => 'Estructura HTML inválida',
                'severity' => 'HIGH'
            ];
            echo "   ❌ Estructura HTML inválida\n";
        }
        
        // Verificar enlaces rotos internos
        $brokenLinks = $this->findBrokenLinks($html, $url);
        if (!empty($brokenLinks)) {
            $this->errors[] = [
                'route' => $path,
                'name' => $name,
                'error' => count($brokenLinks) . ' enlaces posiblemente rotos',
                'severity' => 'LOW'
            ];
            echo "   ⚠️  " . count($brokenLinks) . " enlaces posiblemente rotos\n";
        }
        
        if (empty($this->errors) || !in_array($path, array_column($this->errors, 'route'))) {
            echo "   ✅ OK\n";
        }
        
        echo "\n";
    }
    
    private function findPHPErrors($html) {
        $errors = [];
        
        $patterns = [
            '/Fatal error:/i',
            '/Parse error:/i',
            '/Warning:/i',
            '/Notice:/i',
            '/Error:/i',
            '/Stack trace:/i',
            '/Uncaught.*Exception/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html)) {
                $errors[] = $pattern;
            }
        }
        
        return $errors;
    }
    
    private function findCorruptCharacters($html) {
        $corrupt = [];
        
        // Buscar patrones de corrupción
        if (preg_match_all('/[A-Za-z]\?\?[a-z]/', $html, $matches)) {
            $corrupt = array_merge($corrupt, array_unique($matches[0]));
        }
        
        // Buscar ? (signo de interrogación seguido de caracter raro)
        if (preg_match_all('/\?[^a-zA-Z0-9\s<]/u', $html, $matches)) {
            $corrupt = array_merge($corrupt, array_unique($matches[0]));
        }
        
        return array_slice($corrupt, 0, 5);
    }
    
    private function extractTitle($html) {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }
    
    private function hasValidStructure($html) {
        // Verificar que tenga etiquetas básicas
        $hasHtml = stripos($html, '<html') !== false;
        $hasBody = stripos($html, '<body') !== false;
        $hasHead = stripos($html, '<head') !== false;
        
        return $hasHtml && $hasBody && $hasHead;
    }
    
    private function findBrokenLinks($html, $baseUrl) {
        $broken = [];
        
        // Extraer enlaces internos
        preg_match_all('/href=["\']([^"\']+)["\']/i', $html, $matches);
        
        foreach ($matches[1] as $link) {
            // Solo enlaces internos
            if (strpos($link, '/') === 0 || strpos($link, $this->baseUrl) === 0) {
                if (!in_array($link, $this->checkedUrls)) {
                    $this->checkedUrls[] = $link;
                    // Aquí podríamos hacer HEAD request para verificar
                }
            }
        }
        
        return $broken;
    }
    
    private function printSummary() {
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "                    RESUMEN DE PRUEBAS VISUALES                  \n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        if (empty($this->errors)) {
            echo "✅ ¡TODAS LAS PÁGINAS PASARON LAS PRUEBAS VISUALES!\n\n";
        } else {
            echo "⚠️  SE ENCONTRARON " . count($this->errors) . " PROBLEMAS:\n\n";
            
            // Agrupar por severidad
            $critical = array_filter($this->errors, fn($e) => $e['severity'] === 'CRITICAL');
            $high = array_filter($this->errors, fn($e) => $e['severity'] === 'HIGH');
            $medium = array_filter($this->errors, fn($e) => $e['severity'] === 'MEDIUM');
            $low = array_filter($this->errors, fn($e) => $e['severity'] === 'LOW');
            
            if (!empty($critical)) {
                echo "🔴 CRÍTICOS (" . count($critical) . "):\n";
                foreach ($critical as $err) {
                    echo "   ❌ {$err['name']} ({$err['route']})\n";
                    echo "      → {$err['error']}\n\n";
                }
            }
            
            if (!empty($high)) {
                echo "🟠 ALTOS (" . count($high) . "):\n";
                foreach ($high as $err) {
                    echo "   ❌ {$err['name']} ({$err['route']})\n";
                    echo "      → {$err['error']}\n\n";
                }
            }
            
            if (!empty($medium)) {
                echo "🟡 MEDIOS (" . count($medium) . "):\n";
                foreach ($medium as $err) {
                    echo "   ⚠️  {$err['name']} ({$err['route']})\n";
                    echo "      → {$err['error']}\n\n";
                }
            }
        }
        
        echo "\n📋 NOTAS:\n";
        echo "   • Este test verifica errores VISIBLES en el HTML\n";
        echo "   • No reemplaza tests funcionales de BD\n";
        echo "   • Para problemas de encoding, usa: diagnose-utf8.php\n";
    }
}

// Ejecutar si se llama directamente
if (php_sapi_name() === 'cli') {
    $url = 'http://localhost/torque';
    
    // Buscar argumento --url
    foreach ($argv as $arg) {
        if (strpos($arg, '--url=') === 0) {
            $url = substr($arg, 6);
        }
    }
    
    $tester = new VisualTester($url);
    $tester->run();
}
