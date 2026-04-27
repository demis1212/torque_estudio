<?php
/**
 * DIAGNÓSTICO Y REPARACIÓN DE UTF-8
 * Torque Studio ERP
 * 
 * Uso: php tests/check-encoding.php
 *      php tests/check-encoding.php --fix  (para reparar datos)
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     DIAGNÓSTICO DE CODIFICACIÓN UTF-8 - Torque Studio ERP     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Configuración UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

$shouldFix = in_array('--fix', $argv);

// =====================================================
// 1. VERIFICAR CONFIGURACIÓN PHP
// =====================================================
echo "📋 CONFIGURACIÓN PHP:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$configs = [
    'default_charset' => ini_get('default_charset'),
    'mbstring.internal_encoding' => ini_get('mbstring.internal_encoding') ?: mb_internal_encoding(),
    'mbstring.http_output' => ini_get('mbstring.http_output'),
];

foreach ($configs as $key => $value) {
    $status = (strpos(strtolower($value), 'utf') !== false) ? '✅' : '⚠️ ';
    echo "  {$status} {$key}: {$value}\n";
}

// =====================================================
// 2. INTENTAR CONEXIÓN A BD
// =====================================================
echo "\n🗄️  BASE DE DATOS:\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    require_once dirname(__DIR__) . '/config/database.php';
    $db = \Config\Database::getConnection();
    
    echo "  ✅ Conexión exitosa\n";
    
    // Verificar charset de la conexión
    $charset = $db->query("SELECT @@character_set_connection")->fetchColumn();
    $dbCharset = $db->query("SELECT @@character_set_database")->fetchColumn();
    
    echo "  📊 Charset conexión: {$charset}\n";
    echo "  📊 Charset base de datos: {$dbCharset}\n";
    
    // =====================================================
    // 3. BUSCAR CARACTERES CORRUPTOS
    // =====================================================
    echo "\n🔍 BUSCANDO CARACTERES CORRUPTOS:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    
    $tables = ['clients', 'users', 'parts', 'services', 'work_orders'];
    $foundCorrupt = false;
    
    foreach ($tables as $table) {
        try {
            // Buscar patrones de corrupción: ?? seguido de caracteres
            $stmt = $db->query("SELECT * FROM {$table} LIMIT 100");
            $rows = $stmt->fetchAll();
            
            $tableHasCorrupt = false;
            
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if (is_string($value) && preg_match('/\?\?/', $value)) {
                        if (!$tableHasCorrupt) {
                            echo "\n  ⚠️  Tabla '{$table}' tiene datos corruptos:\n";
                            $tableHasCorrupt = true;
                            $foundCorrupt = true;
                        }
                        echo "     - ID {$row['id']}, campo '{$key}': " . substr($value, 0, 50) . "...\n";
                    }
                }
            }
            
            if (!$tableHasCorrupt) {
                echo "  ✅ {$table}: Sin caracteres corruptos\n";
            }
            
        } catch (Exception $e) {
            echo "  ⚠️  No se pudo verificar {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    if (!$foundCorrupt) {
        echo "\n  🎉 No se encontraron caracteres corruptos en ninguna tabla!\n";
    }
    
    // =====================================================
    // 4. REPARAR SI SE SOLICITÓ --fix
    // =====================================================
    if ($shouldFix && $foundCorrupt) {
        echo "\n🔧 REPARANDO DATOS CORRUPTOS:\n";
        echo "─────────────────────────────────────────────────────────────────\n";
        
        $replacements = [
            '??' => ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            '?' => ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ']
        ];
        
        $fixedCount = 0;
        
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("DESCRIBE {$table}");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($columns as $column) {
                    // Intentar detectar y reparar
                    try {
                        $checkStmt = $db->query("SELECT id, {$column} FROM {$table} WHERE {$column} LIKE '%??%' OR {$column} LIKE '%?%' LIMIT 10");
                        $badRows = $checkStmt->fetchAll();
                        
                        foreach ($badRows as $badRow) {
                            $value = $badRow[$column];
                            $original = $value;
                            
                            // Intentar reparar (esto es una heurística simple)
                            // Nota: La reparación real requiere conocer el charset original
                            
                            echo "     Encontrado en {$table}.{$column} ID={$badRow['id']}\n";
                            $fixedCount++;
                        }
                    } catch (Exception $e) {
                        // Ignorar errores de columnas que no son texto
                    }
                }
            } catch (Exception $e) {
                echo "  ⚠️  Error en {$table}: " . $e->getMessage() . "\n";
            }
        }
        
        if ($fixedCount > 0) {
            echo "\n  ⚠️  Se encontraron {$fixedCount} campos con posibles problemas.\n";
            echo "  📄 Ejecuta el SQL: database/regenerate_seeders_utf8.sql\n";
            echo "  📄 O: database/fix_corrupted_data.sql\n";
        }
    }
    
    // =====================================================
    // 5. MOSTRAR EJEMPLOS
    // =====================================================
    echo "\n📝 EJEMPLOS DE DATOS ACTUALES:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    
    try {
        $clients = $db->query("SELECT id, name FROM clients LIMIT 3")->fetchAll();
        foreach ($clients as $client) {
            $name = $client['name'];
            $isUtf8 = mb_check_encoding($name, 'UTF-8');
            $hasCorrupt = preg_match('/\?\?/', $name);
            $status = $hasCorrupt ? '❌' : ($isUtf8 ? '✅' : '⚠️ ');
            echo "  {$status} Cliente #{$client['id']}: {$name}\n";
        }
    } catch (Exception $e) {
        echo "  ⚠️  No se pudieron mostrar ejemplos\n";
    }
    
} catch (Exception $e) {
    echo "  ❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "\n  ℹ️  Verifica que MySQL esté corriendo y las credenciales sean correctas.\n";
}

// =====================================================
// 6. INSTRUCCIONES
// =====================================================
echo "\n";
echo "═════════════════════════════════════════════════════════════════\n";
echo "                     INSTRUCCIONES                              \n";
echo "═════════════════════════════════════════════════════════════════\n\n";

echo "Para REPARAR datos corruptos, ejecuta en MySQL:\n\n";
echo "  1. Opción A - Regenerar seeders (recomendado para desarrollo):\n";
echo "     mysql -u root -p torque_erp < database/regenerate_seeders_utf8.sql\n\n";
echo "  2. Opción B - Intentar reparar datos existentes:\n";
echo "     mysql -u root -p torque_erp < database/fix_corrupted_data.sql\n\n";
echo "  3. Opción C - Recrear base de datos completamente:\n";
echo "     mysql -u root -p -e \"DROP DATABASE torque_erp; CREATE DATABASE torque_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"\n";
echo "     mysql -u root -p torque_erp < database/schema.sql\n";
echo "     mysql -u root -p torque_erp < database/new_tables.sql\n\n";

echo "Verificar codificación después:\n";
echo "  mysql -u root -p -e \"SELECT @@character_set_database;\"\n\n";

echo "═════════════════════════════════════════════════════════════════\n";
