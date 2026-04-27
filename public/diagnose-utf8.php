<?php
/**
 * DIAGNÓSTICO DETALLADO UTF-8 - Torque Studio ERP
 * 
 * Muestra exactamente qué está pasando con la codificación
 * y permite reparar los datos paso a paso.
 */

header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

$action = $_GET['action'] ?? 'diagnose';
$message = '';

try {
    require_once dirname(__DIR__) . '/config/database.php';
    $db = Config\Database::getConnection();
} catch (Exception $e) {
    die("Error BD: " . $e->getMessage());
}

// Reparar datos si se solicita
if ($action === 'fix') {
    try {
        // Limpiar e insertar correctamente
        $db->exec("DELETE FROM clients");
        $db->exec("DELETE FROM users WHERE id > 1");
        
        $stmt = $db->prepare("INSERT INTO clients (name, phone, email, address) VALUES (?, ?, ?, ?)");
        
        $clientes = [
            ['Carlos Rodríguez', '809-555-0101', 'carlos@email.com', 'Av. Principal #123'],
            ['Ana María Gómez', '809-555-0102', 'ana@email.com', 'Calle 27 #45'],
            ['Pedro Martínez', '809-555-0103', 'pedro@email.com', 'Carrera 8 #12'],
            ['Laura Fernández', '809-555-0104', 'laura@email.com', 'Av. Las Palmas #78'],
            ['Miguel Ángel Sánchez', '809-555-0105', 'miguel@email.com', 'Calle del Sol #34'],
        ];
        
        foreach ($clientes as $c) {
            $stmt->execute($c);
        }
        
        // Usuarios
        $password = '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O';
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Juan Mecánico', 'juan@torque.com', $password, 2]);
        $stmt->execute(['María Recepción', 'maria@torque.com', $password, 3]);
        
        // Servicios (CATÁLOGO)
        $db->exec("DELETE FROM services");
        $stmt = $db->prepare("INSERT INTO services (name, description, price) VALUES (?, ?, ?)");
        
        $servicios = [
            ['Cambio de Aceite', 'Cambio de aceite de motor y filtro', 45.00],
            ['Alineación y Balanceo', 'Alineación de dirección y balanceo de neumáticos', 60.00],
            ['Revisión de Frenos', 'Inspección completa del sistema de frenos', 35.00],
            ['Cambio de Bujías', 'Reemplazo de bujías de encendido', 25.00],
            ['Diagnóstico Computarizado', 'Escaneo de fallas con equipo diagnóstico', 80.00],
            ['Cambio de Filtro de Aire', 'Reemplazo de filtro de aire del motor', 20.00],
            ['Revisión de Suspensión', 'Inspección de amortiguadores y suspensión', 40.00],
            ['Carga de Aire Acondicionado', 'Recarga de gas refrigerante', 55.00],
            ['Cambio de Batería', 'Reemplazo de batería del vehículo', 120.00],
            ['Limpieza de Inyectores', 'Limpieza ultrasonido de inyectores de combustible', 90.00],
        ];
        
        foreach ($servicios as $s) {
            $stmt->execute($s);
        }
        
        $message = "✅ Datos reparados correctamente! (Clientes + Usuarios + Servicios)";
        
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}

// Obtener datos actuales
$clients = $db->query("SELECT * FROM clients ORDER BY id");
$users = $db->query("SELECT * FROM users ORDER BY id");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico UTF-8</title>
    <style>
        body { 
            font-family: 'Segoe UI', monospace; 
            background: #0a0c10; 
            color: #e8eaf2; 
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #4d8eff; }
        h2 { color: #8ab4f8; margin-top: 30px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .box { 
            background: #1a1d26; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 15px 0;
            border-left: 4px solid #4d8eff;
        }
        .box.error { border-left-color: #f87171; }
        .box.success { border-left-color: #4ade80; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        th { color: #8ab4f8; font-size: 12px; text-transform: uppercase; }
        .hex { font-family: monospace; font-size: 11px; color: #666; }
        .char-display { font-size: 18px; }
        .corrupt { background: rgba(248, 113, 113, 0.2); color: #f87171; }
        .ok { background: rgba(74, 222, 128, 0.1); color: #4ade80; }
        .btn { 
            display: inline-block; 
            padding: 12px 24px; 
            background: #4d8eff; 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            margin: 5px;
        }
        .btn:hover { background: #3b7de8; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        pre { background: #0a0c10; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .byte-view { font-family: monospace; font-size: 10px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Detallado UTF-8</h1>
        
        <?php if ($message): ?>
        <div class="box <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <strong><?= $message ?></strong>
        </div>
        <?php endif; ?>
        
        <div class="box">
            <h3>⚙️ Configuración Actual</h3>
            <p><strong>PHP mb_internal_encoding:</strong> <?= mb_internal_encoding() ?></p>
            <p><strong>default_charset:</strong> <?= ini_get('default_charset') ?></p>
            <p><strong>BD Charset:</strong> <?= $db->query("SELECT @@character_set_database")->fetchColumn() ?></p>
            <p><strong>BD Collation:</strong> <?= $db->query("SELECT @@collation_database")->fetchColumn() ?></p>
        </div>
        
        <div class="box">
            <a href="?action=diagnose" class="btn">🔄 Refrescar</a>
            <a href="?action=fix" class="btn" style="background: #28a745;">🔧 Reparar Datos</a>
            <a href="/torque/clients" class="btn" style="background: #666;">👥 Ver Clientes</a>
        </div>
        
        <h2>📋 Tabla CLIENTES (Análisis Byte por Byte)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre (Visual)</th>
                    <th>Nombre (Hex)</th>
                    <th>¿Corrupto?</th>
                    <th>Bytes</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $clients->fetch()): 
                    $name = $row['name'];
                    $hex = bin2hex($name);
                    $hasQuestion = strpos($name, '?') !== false || strpos($name, '??') !== false;
                    $isUtf8 = mb_check_encoding($name, 'UTF-8');
                    
                    // Análisis de bytes
                    $bytes = [];
                    for ($i = 0; $i < min(strlen($name), 20); $i++) {
                        $bytes[] = sprintf('%02X', ord($name[$i]));
                    }
                ?>
                <tr class="<?= $hasQuestion ? 'corrupt' : ($isUtf8 ? 'ok' : '') ?>">
                    <td><?= $row['id'] ?></td>
                    <td class="char-display"><?= htmlspecialchars($name) ?></td>
                    <td class="hex"><?= substr($hex, 0, 40) ?>...</td>
                    <td>
                        <?php if ($hasQuestion): ?>
                            ❌ SÍ - Tiene '?'
                        <?php elseif (!$isUtf8): ?>
                            ⚠️ No es UTF-8 válido
                        <?php else: ?>
                            ✅ OK
                        <?php endif; ?>
                    </td>
                    <td class="byte-view"><?= implode(' ', $bytes) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h2>👤 Tabla USUARIOS</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch()): 
                    $name = $row['name'];
                    $hasQuestion = strpos($name, '?') !== false;
                ?>
                <tr class="<?= $hasQuestion ? 'corrupt' : 'ok' ?>">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($name) ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $hasQuestion ? '❌ Corrupto' : '✅ OK' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h2>🔧 Tabla SERVICIOS (Catálogo)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $services = $db->query("SELECT * FROM services ORDER BY id");
                while ($row = $services->fetch()): 
                    $name = $row['name'];
                    $desc = $row['description'];
                    $hasQuestion = strpos($name, '?') !== false || strpos($desc, '?') !== false;
                ?>
                <tr class="<?= $hasQuestion ? 'corrupt' : 'ok' ?>">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($name) ?></td>
                    <td><?= htmlspecialchars(substr($desc, 0, 50)) ?>...</td>
                    <td><?= $hasQuestion ? '❌ Corrupto' : '✅ OK' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h2>🧪 Test de Caracteres</h2>
        <div class="box">
            <p style="font-size: 24px; margin: 10px 0;">
                á é í ó ú ñ Á É Í Ó Ú Ñ ü
            </p>
            <p style="color: #666;">Si ves signos de interrogación arriba, hay problemas de encoding.</p>
        </div>
        
        <h2>🔧 Solución Manual (SQL)</h2>
        <div class="box">
            <p>Si el botón "Reparar Datos" no funciona, ejecuta esto en phpMyAdmin:</p>
            <pre>
SET NAMES utf8mb4;
DELETE FROM clients;
DELETE FROM users WHERE id > 1;

INSERT INTO clients (name, phone, email, address) VALUES
('Carlos Rodríguez', '809-555-0101', 'carlos@email.com', 'Av. Principal #123'),
('Ana María Gómez', '809-555-0102', 'ana@email.com', 'Calle 27 #45'),
('Pedro Martínez', '809-555-0103', 'pedro@email.com', 'Carrera 8 #12'),
('Laura Fernández', '809-555-0104', 'laura@email.com', 'Av. Las Palmas #78'),
('Miguel Ángel Sánchez', '809-555-0105', 'miguel@email.com', 'Calle del Sol #34');

INSERT INTO users (name, email, password, role_id) VALUES
('Juan Mecánico', 'juan@torque.com', '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O', 2),
('María Recepción', 'maria@torque.com', '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O', 3);

DELETE FROM services;

INSERT INTO services (name, description, price) VALUES
('Cambio de Aceite', 'Cambio de aceite de motor y filtro', 45.00),
('Alineación y Balanceo', 'Alineación de dirección y balanceo de neumáticos', 60.00),
('Revisión de Frenos', 'Inspección completa del sistema de frenos', 35.00),
('Cambio de Bujías', 'Reemplazo de bujías de encendido', 25.00),
('Diagnóstico Computarizado', 'Escaneo de fallas con equipo diagnóstico', 80.00),
('Cambio de Filtro de Aire', 'Reemplazo de filtro de aire del motor', 20.00),
('Revisión de Suspensión', 'Inspección de amortiguadores y suspensión', 40.00),
('Carga de Aire Acondicionado', 'Recarga de gas refrigerante', 55.00),
('Cambio de Batería', 'Reemplazo de batería del vehículo', 120.00),
('Limpieza de Inyectores', 'Limpieza ultrasonido de inyectores de combustible', 90.00);
            </pre>
        </div>
    </div>
</body>
</html>
