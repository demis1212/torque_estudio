<?php
/**
 * VERIFICADOR DE SISTEMA - Torque Studio ERP
 * 
 * Script simplificado que verifica el estado del sistema sin necesidad
 * de base de datos. Ideal para pre-deployment checks.
 * 
 * Uso: php tests/verify-system.php
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║       TORQUE STUDIO ERP - VERIFICADOR DE SISTEMA              ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$baseDir = dirname(__DIR__);
$results = [];
$passed = 0;
$failed = 0;

echo "📁 Directorio base: {$baseDir}\n\n";

// ═══════════════════════════════════════════════════════════════
// VERIFICACIONES
// ═══════════════════════════════════════════════════════════════

// 1. Verificar estructura de directorios
$directories = [
    'app' => 'Aplicación',
    'app/controllers' => 'Controladores',
    'app/models' => 'Modelos',
    'app/views' => 'Vistas',
    'config' => 'Configuración',
    'database' => 'Base de datos',
    'public' => 'Público',
    'routes' => 'Rutas',
    'tests' => 'Pruebas',
];

echo "📂 VERIFICANDO DIRECTORIOS:\n";
echo "─────────────────────────────────────────────────────────────\n";

foreach ($directories as $dir => $name) {
    $path = $baseDir . '/' . $dir;
    if (is_dir($path)) {
        echo "  ✅ {$name}: {$dir}/\n";
        $passed++;
    } else {
        echo "  ❌ {$name}: {$dir}/ NO EXISTE\n";
        $results[] = ['fail', "Directorio faltante: {$dir}"];
        $failed++;
    }
}

// 2. Verificar archivos críticos
$criticalFiles = [
    'config/database.php' => 'Configuración BD',
    'public/index.php' => 'Entry Point',
    'routes/web.php' => 'Rutas Web',
    'app/helpers.php' => 'Helper functions',
    'app/models/Part.php' => 'Modelo Partes',
    'app/models/WarehouseTool.php' => 'Modelo Herramientas',
    'app/models/ToolRequest.php' => 'Modelo Solicitudes',
    'app/models/Notification.php' => 'Modelo Notificaciones',
    'app/controllers/PartController.php' => 'Controller Partes',
    'app/controllers/ToolsController.php' => 'Controller Herramientas',
    'app/views/parts/create.php' => 'Vista Crear Parte',
    'app/views/parts/edit.php' => 'Vista Editar Parte',
    'app/views/parts/index.php' => 'Vista Listar Partes',
    'app/views/tools/index.php' => 'Vista Herramientas',
    'app/views/tools/warehouse-tools.php' => 'Vista Bodega',
    'app/views/tools/purchase-request.php' => 'Vista Solicitud Compra',
    'app/views/components/sidebar.php' => 'Componente Sidebar',
    'app/views/components/toast.php' => 'Componente Toast',
    'tests/TestRunner.php' => 'Test Suite',
    'tests/full-test.php' => 'Test Completo',
];

echo "\n📄 VERIFICANDO ARCHIVOS CRÍTICOS:\n";
echo "─────────────────────────────────────────────────────────────\n";

foreach ($criticalFiles as $file => $name) {
    $path = $baseDir . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "  ✅ {$name}: " . basename($file) . " (" . number_format($size) . " bytes)\n";
        $passed++;
    } else {
        echo "  ❌ {$name}: " . basename($file) . " NO EXISTE\n";
        $results[] = ['fail', "Archivo faltante: {$file}"];
        $failed++;
    }
}

// 3. Verificar codificación UTF-8 en archivos PHP
echo "\n🔤 VERIFICANDO CODIFICACIÓN UTF-8:\n";
echo "─────────────────────────────────────────────────────────────\n";

$sampleFiles = [
    'config/database.php',
    'app/models/Part.php',
    'app/views/parts/create.php',
];

$utf8Ok = true;
foreach ($sampleFiles as $file) {
    $path = $baseDir . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (mb_check_encoding($content, 'UTF-8')) {
            echo "  ✅ {$file} es UTF-8 válido\n";
        } else {
            echo "  ❌ {$file} NO es UTF-8 válido\n";
            $utf8Ok = false;
            $failed++;
        }
    }
}

// 4. Verificar configuración UTF-8
echo "\n⚙️  VERIFICANDO CONFIGURACIÓN UTF-8:\n";
echo "─────────────────────────────────────────────────────────────\n";

// database.php
$dbConfig = @file_get_contents($baseDir . '/config/database.php');
if ($dbConfig) {
    if (strpos($dbConfig, 'utf8mb4') !== false) {
        echo "  ✅ config/database.php usa utf8mb4\n";
        $passed++;
    } else {
        echo "  ❌ config/database.php NO tiene utf8mb4\n";
        $failed++;
    }
    
    if (strpos($dbConfig, 'SET NAMES') !== false) {
        echo "  ✅ config/database.php tiene SET NAMES\n";
        $passed++;
    } else {
        echo "  ⚠️  config/database.php NO tiene SET NAMES explícito\n";
    }
}

// index.php
$indexContent = @file_get_contents($baseDir . '/public/index.php');
if ($indexContent) {
    if (strpos($indexContent, 'charset=utf-8') !== false) {
        echo "  ✅ public/index.php tiene header UTF-8\n";
        $passed++;
    } else {
        echo "  ⚠️  public/index.php NO tiene header UTF-8\n";
    }
}

// 5. Verificar schema SQL
echo "\n🗄️  VERIFICANDO SCHEMA SQL:\n";
echo "─────────────────────────────────────────────────────────────\n";

$sqlFiles = ['database/schema.sql', 'database/new_tables.sql'];
foreach ($sqlFiles as $sqlFile) {
    $path = $baseDir . '/' . $sqlFile;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $utf8Count = substr_count($content, 'utf8mb4');
        if ($utf8Count > 0) {
            echo "  ✅ {$sqlFile}: {$utf8Count} referencias utf8mb4\n";
            $passed++;
        } else {
            echo "  ❌ {$sqlFile}: NO tiene utf8mb4\n";
            $failed++;
        }
    } else {
        echo "  ⚠️  {$sqlFile} no encontrado\n";
    }
}

// 6. Verificar implementaciones específicas
echo "\n🔧 VERIFICANDO IMPLEMENTACIONES:\n";
echo "─────────────────────────────────────────────────────────────\n";

// Verificar unit_type en Part.php
$partModel = @file_get_contents($baseDir . '/app/models/Part.php');
if ($partModel) {
    if (strpos($partModel, 'unit_type') !== false) {
        echo "  ✅ Part.php tiene unit_type\n";
        $passed++;
    } else {
        echo "  ❌ Part.php NO tiene unit_type\n";
        $failed++;
    }
}

// Verificar category_new en PartController
$partController = @file_get_contents($baseDir . '/app/controllers/PartController.php');
if ($partController) {
    if (strpos($partController, 'category_new') !== false) {
        echo "  ✅ PartController maneja category_new\n";
        $passed++;
    } else {
        echo "  ❌ PartController NO maneja category_new\n";
        $failed++;
    }
    
    if (strpos($partController, 'isUsedInWorkOrders') !== false) {
        echo "  ✅ PartController verifica isUsedInWorkOrders\n";
        $passed++;
    } else {
        echo "  ⚠️  PartController puede no verificar isUsedInWorkOrders\n";
    }
}

// Verificar CLP y margen en create.php
$createView = @file_get_contents($baseDir . '/app/views/parts/create.php');
if ($createView) {
    if (strpos($createView, 'CLP') !== false || strpos($createView, 'number_format') !== false) {
        echo "  ✅ parts/create.php tiene formato CLP\n";
        $passed++;
    } else {
        echo "  ⚠️  parts/create.php puede no tener CLP\n";
    }
    
    if (strpos($createView, 'calculateMargin') !== false || strpos($createView, 'margen') !== false) {
        echo "  ✅ parts/create.php tiene cálculo de margen\n";
        $passed++;
    } else {
        echo "  ⚠️  parts/create.php puede no tener cálculo de margen\n";
    }
}

// Verificar purchaseRequest en ToolsController
$toolsController = @file_get_contents($baseDir . '/app/controllers/ToolsController.php');
if ($toolsController) {
    if (strpos($toolsController, 'purchaseRequest') !== false) {
        echo "  ✅ ToolsController tiene purchaseRequest\n";
        $passed++;
    } else {
        echo "  ❌ ToolsController NO tiene purchaseRequest\n";
        $failed++;
    }
    
    if (strpos($toolsController, 'sendToRepair') !== false) {
        echo "  ✅ ToolsController tiene sendToRepair\n";
        $passed++;
    } else {
        echo "  ❌ ToolsController NO tiene sendToRepair\n";
        $failed++;
    }
}

// 7. Verificar Font Awesome en vistas
echo "\n🎨 VERIFICANDO ICONOS (Font Awesome):\n";
echo "─────────────────────────────────────────────────────────────\n";

$faFiles = [
    'app/views/parts/index.php',
    'app/views/tools/index.php',
    'app/views/components/sidebar.php',
];

foreach ($faFiles as $file) {
    $path = $baseDir . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, 'font-awesome') !== false || strpos($content, 'fa-') !== false) {
            echo "  ✅ {$file} usa Font Awesome\n";
            $passed++;
        } else {
            echo "  ⚠️  {$file} puede no usar Font Awesome\n";
        }
    }
}

// 8. Resumen de archivos PHP
$phpFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir . '/app')
);
$phpCount = 0;
foreach ($phpFiles as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpCount++;
    }
}
echo "\n📊 Total archivos PHP en /app: {$phpCount}\n";

// ═══════════════════════════════════════════════════════════════
// RESUMEN FINAL
// ═══════════════════════════════════════════════════════════════

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100) : 0;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "                    RESUMEN FINAL                              \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "  ✅ Verificaciones pasadas: {$passed}\n";
echo "  ❌ Verificaciones fallidas: {$failed}\n";
echo "  📊 Total: {$total}\n";
echo "  🎯 Porcentaje: {$percentage}%\n";
echo "\n";

// Barra de progreso
$barWidth = 50;
$filled = round(($percentage / 100) * $barWidth);
$empty = $barWidth - $filled;

echo "  [";
echo str_repeat("█", $filled);
echo str_repeat("░", $empty);
echo "]\n";
echo "\n";

if ($failed === 0) {
    echo "  🎉 ¡TODAS LAS VERIFICACIONES PASARON!\n";
    echo "  El sistema está listo para deployment.\n";
    exit(0);
} elseif ($percentage >= 90) {
    echo "  ✅ EXCELENTE: El sistema está en muy buen estado.\n";
    echo "  Puedes deployear con confianza.\n";
    exit(0);
} elseif ($percentage >= 80) {
    echo "  ✅ BUENO: La mayoría de verificaciones pasaron.\n";
    echo "  Revisar los items fallidos antes de deployear.\n";
    exit(0);
} elseif ($percentage >= 60) {
    echo "  ⚠️  REGULAR: Hay varios problemas.\n";
    echo "  Corregir antes de deployear.\n";
    exit(1);
} else {
    echo "  ❌ CRÍTICO: Hay muchos problemas.\n";
    echo "  NO deployear hasta corregir todos los items.\n";
    exit(1);
}
