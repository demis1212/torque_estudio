<?php
/**
 * Script de Instalación Rápida - Torque Studio ERP
 * 
 * Este script verifica los requisitos y guía en la instalación
 */

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     TORQUE STUDIO ERP - Instalación Rápida               ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Verificar PHP
$phpVersion = phpversion();
echo "✓ Versión de PHP: $phpVersion\n";
if (version_compare($phpVersion, '7.4.0', '<')) {
    echo "  ✗ ERROR: Se requiere PHP 7.4 o superior\n";
    exit(1);
}
echo "  ✓ PHP cumple con los requisitos\n\n";

// Verificar extensiones necesarias
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'session'];
echo "Verificando extensiones:\n";
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✓ $ext\n";
    } else {
        echo "  ✗ $ext (FALTA)\n";
    }
}
echo "\n";

// Verificar configuración de sesiones
echo "Configuración de sesiones:\n";
echo "  session.save_path: " . ini_get('session.save_path') . "\n";
echo "  session.cookie_httponly: " . (ini_get('session.cookie_httponly') ? 'On' : 'Off') . "\n\n";

// Verificar escritura en directorios
$writableDirs = ['storage', 'public/assets'];
echo "Permisos de directorios:\n";
foreach ($writableDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "  ✓ Creado: $dir\n";
    } else {
        echo "  ✓ Existe: $dir\n";
    }
}
echo "\n";

// Verificar configuración de base de datos
echo "Configuración de Base de Datos:\n";
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    echo "  ✓ Archivo de configuración encontrado\n";
    
    // Intentar conexión
    require_once $configFile;
    try {
        $db = \Config\Database::getConnection();
        echo "  ✓ Conexión exitosa a la base de datos\n";
        
        // Verificar tablas
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "  Tablas encontradas: " . count($tables) . "\n";
        foreach ($tables as $table) {
            echo "    - $table\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error de conexión: " . $e->getMessage() . "\n";
        echo "\n  Por favor verifique:\n";
        echo "  1. MySQL está corriendo\n";
        echo "  2. La base de datos 'torque_erp' existe\n";
        echo "  3. Credenciales en config/database.php son correctas\n";
    }
} else {
    echo "  ✗ No se encontró archivo de configuración\n";
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  Instrucciones de Instalación:                           ║\n";
echo "╠══════════════════════════════════════════════════════════╣\n";
echo "║  1. Crear base de datos 'torque_erp' en MySQL            ║\n";
echo "║  2. Importar database/schema.sql                         ║\n";
echo "║  3. Configurar config/database.php con sus credenciales  ║\n";
echo "║  4. Apuntar servidor web a /public                       ║\n";
echo "║                                                          ║\n";
echo "║  Credenciales por defecto:                               ║\n";
echo "║    Email: admin@torque.com                               ║\n";
echo "║    Password: admin123                                    ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
