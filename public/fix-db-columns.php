<?php
/**
 * FIX DB COLUMNS - Agregar columnas faltantes
 * Torque Studio ERP
 */

header('Content-Type: text/html; charset=utf-8');

try {
    require_once dirname(__DIR__) . '/config/database.php';
    $db = Config\Database::getConnection();
    
    echo "<h2>🔧 Arreglando Base de Datos</h2>";
    
    // 1. Verificar si existe columna diagnosis en work_orders
    $check = $db->query("SHOW COLUMNS FROM work_orders LIKE 'diagnosis'")->fetch();
    if (!$check) {
        $db->exec("ALTER TABLE work_orders ADD COLUMN diagnosis TEXT NULL AFTER description");
        echo "✅ Columna 'diagnosis' agregada a work_orders<br>";
    } else {
        echo "✓ Columna 'diagnosis' ya existe<br>";
    }
    
    // 2. Verificar si existe columna notes en vehicles
    $check = $db->query("SHOW COLUMNS FROM vehicles LIKE 'notes'")->fetch();
    if (!$check) {
        $db->exec("ALTER TABLE vehicles ADD COLUMN notes TEXT NULL AFTER engine");
        echo "✅ Columna 'notes' agregada a vehicles<br>";
    } else {
        echo "✓ Columna 'notes' ya existe<br>";
    }
    
    echo "<br><strong>✅ Base de datos actualizada correctamente!</strong><br><br>";
    
    echo '<a href="/torque/deep-analyzer.php" style="padding: 10px 20px; background: #4d8eff; color: white; text-decoration: none; border-radius: 8px;">🔄 Volver a Analizar →</a>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    echo "<br><br>Si el error persiste, ejecuta esto en phpMyAdmin:<br>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>
ALTER TABLE work_orders ADD COLUMN diagnosis TEXT NULL AFTER description;
ALTER TABLE vehicles ADD COLUMN notes TEXT NULL AFTER engine;
    </pre>";
}
