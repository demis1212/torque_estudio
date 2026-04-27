<?php
/**
 * REPARADOR DE DATOS UTF-8 - Torque Studio ERP
 * 
 * Ejecutar vía web: http://localhost/fix-data.php
 * Este script regenera los datos de prueba con UTF-8 correcto
 */

// Forzar UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

$message = '';
$error = '';
$success = false;

try {
    // Conectar a BD
    require_once dirname(__DIR__) . '/config/database.php';
    $db = Config\Database::getConnection();
    
    // Iniciar transacción
    $db->beginTransaction();
    
    // =====================================================
    // 1. BORRAR DATOS CORRUPTOS EXISTENTES
    // =====================================================
    $db->exec("DELETE FROM clients WHERE id <= 5");
    $deletedClients = $db->rowCount();
    
    $db->exec("DELETE FROM users WHERE id > 1 AND id <= 3");
    $deletedUsers = $db->rowCount();
    
    // =====================================================
    // 2. INSERTAR CLIENTES CON UTF-8 CORRECTO
    // =====================================================
    $clients = [
        ['Carlos Rodríguez', '809-555-0101', 'carlos@email.com', 'Av. Principal #123, Santo Domingo'],
        ['Ana María Gómez', '809-555-0102', 'ana@email.com', 'Calle 27 #45, Santiago'],
        ['Pedro Martínez', '809-555-0103', 'pedro@email.com', 'Carrera 8 #12, La Romana'],
        ['Laura Fernández', '809-555-0104', 'laura@email.com', 'Av. Las Palmas #78, Puerto Plata'],
        ['Miguel Ángel Sánchez', '809-555-0105', 'miguel@email.com', 'Calle del Sol #34, San Francisco'],
    ];
    
    $stmt = $db->prepare("INSERT INTO clients (name, phone, email, address) VALUES (?, ?, ?, ?)");
    $insertedClients = 0;
    foreach ($clients as $client) {
        $stmt->execute($client);
        $insertedClients++;
    }
    
    // =====================================================
    // 3. INSERTAR USUARIOS CON UTF-8 CORRECTO
    // =====================================================
    // Password hash para 'admin123'
    $passwordHash = '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O';
    
    $users = [
        ['Juan Mecánico', 'juan@torque.com', $passwordHash, 2],
        ['María Recepción', 'maria@torque.com', $passwordHash, 3],
    ];
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
    $insertedUsers = 0;
    foreach ($users as $user) {
        $stmt->execute($user);
        $insertedUsers++;
    }
    
    // Confirmar transacción
    $db->commit();
    $success = true;
    
    $message = "✅ Datos regenerados exitosamente!<br>";
    $message .= "• Clientes borrados: {$deletedClients}<br>";
    $message .= "• Clientes insertados: {$insertedClients}<br>";
    $message .= "• Usuarios borrados: {$deletedUsers}<br>";
    $message .= "• Usuarios insertados: {$insertedUsers}";
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    $error = "❌ Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reparar Datos UTF-8 - Torque Studio ERP</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            background: linear-gradient(135deg, #0a0c10 0%, #1a1d26 100%); 
            color: #e8eaf2; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container { 
            max-width: 600px; 
            width: 100%;
            background: rgba(26, 29, 38, 0.9);
            border-radius: 16px;
            padding: 40px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        h1 { 
            color: #4d8eff; 
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #9aa3b2;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .result {
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            font-size: 15px;
            line-height: 1.6;
        }
        .result.success {
            background: rgba(74, 222, 128, 0.1);
            border: 1px solid rgba(74, 222, 128, 0.3);
            color: #4ade80;
        }
        .result.error {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.3);
            color: #f87171;
        }
        .chars-test {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .chars-test span {
            display: inline-block;
            margin: 5px;
            padding: 8px 12px;
            background: rgba(77, 142, 255, 0.2);
            border-radius: 6px;
            font-size: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #4d8eff 0%, #3b7de8 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .data-preview {
            margin-top: 20px;
        }
        .data-preview h3 {
            color: #8ab4f8;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .client-item {
            padding: 10px;
            background: rgba(0,0,0,0.2);
            border-radius: 6px;
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .client-name {
            font-weight: 500;
        }
        .status-ok {
            color: #4ade80;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Reparador de Datos UTF-8</h1>
        <p class="subtitle">Torque Studio ERP - Regeneración de datos con codificación correcta</p>
        
        <?php if ($message): ?>
            <div class="result success">
                <?= $message ?>
            </div>
            
            <div class="chars-test">
                <p style="margin-bottom: 10px; color: #9aa3b2; font-size: 12px;">Caracteres UTF-8:</p>
                <span>á</span><span>é</span><span>í</span><span>ó</span><span>ú</span>
                <span>Á</span><span>É</span><span>Í</span><span>Ó</span><span>Ú</span>
                <span>ñ</span><span>Ñ</span><span>ü</span>
            </div>
            
            <?php if (isset($db)): ?>
            <div class="data-preview">
                <h3>✅ Clientes insertados:</h3>
                <?php
                $newClients = $db->query("SELECT name FROM clients ORDER BY id DESC LIMIT 5")->fetchAll();
                foreach ($newClients as $client):
                    $name = $client['name'];
                    $hasCorrupt = preg_match('/\?\?/', $name);
                ?>
                <div class="client-item">
                    <span class="client-name"><?= htmlspecialchars($name) ?></span>
                    <span class="status-ok"><?= $hasCorrupt ? '❌' : '✅ UTF-8 OK' ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <a href="/clients" class="btn">Ver Clientes →</a>
            <a href="/test-utf8.php" class="btn" style="margin-left: 10px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">Test UTF-8 →</a>
            
        <?php elseif ($error): ?>
            <div class="result error">
                <?= $error ?>
            </div>
            <p style="margin-top: 20px; color: #9aa3b2;">
                Posibles soluciones:<br>
                • Verifica que MySQL esté corriendo en XAMPP<br>
                • Verifica las credenciales en config/database.php<br>
                • Recarga la página para intentar de nuevo
            </p>
        <?php else: ?>
            <p>Cargando...</p>
        <?php endif; ?>
    </div>
</body>
</html>
