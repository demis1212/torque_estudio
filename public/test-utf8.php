<?php
/**
 * PÁGINA DE PRUEBA UTF-8
 * Acceder vía: http://localhost/test-utf8.php
 */

// Forzar UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Conectar a BD
try {
    require_once dirname(__DIR__) . '/config/database.php';
    $db = Config\Database::getConnection();
} catch (Exception $e) {
    $db = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test UTF-8 - Torque Studio ERP</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 40px; background: #0a0c10; color: #e8eaf2; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #4d8eff; }
        h2 { color: #8ab4f8; border-bottom: 1px solid #333; padding-bottom: 10px; margin-top: 30px; }
        .test-box { background: #1a1d26; padding: 20px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #4d8eff; }
        .pass { border-left-color: #4ade80; }
        .fail { border-left-color: #f87171; }
        .warning { border-left-color: #fbbf24; }
        code { background: #0a0c10; padding: 2px 8px; border-radius: 4px; font-family: 'Consolas', monospace; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        th { color: #8ab4f8; }
        .status { font-weight: bold; }
        .status.ok { color: #4ade80; }
        .status.bad { color: #f87171; }
        .chars { font-size: 24px; margin: 10px 0; }
        .chars span { display: inline-block; margin: 5px 10px; padding: 5px 10px; background: #0a0c10; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Codificación UTF-8</h1>
        
        <div class="test-box <?= mb_internal_encoding() === 'UTF-8' ? 'pass' : 'fail' ?>">
            <h3>Configuración PHP</h3>
            <p><strong>mb_internal_encoding:</strong> <?= mb_internal_encoding() ?></p>
            <p><strong>default_charset:</strong> <?= ini_get('default_charset') ?></p>
            <p class="status <?= mb_internal_encoding() === 'UTF-8' ? 'ok' : 'bad' ?>">
                <?= mb_internal_encoding() === 'UTF-8' ? '✅ UTF-8 configurado correctamente' : '❌ UTF-8 NO configurado' ?>
            </p>
        </div>

        <h2>🔤 Caracteres Españoles</h2>
        <div class="test-box pass">
            <div class="chars">
                <span>á</span><span>é</span><span>í</span><span>ó</span><span>ú</span>
                <span>Á</span><span>É</span><span>Í</span><span>Ó</span><span>Ú</span>
                <span>ñ</span><span>Ñ</span><span>ü</span><span>Ü</span><span>¿</span><span>¡</span>
            </div>
            <p>Si ves signos de interrogación (??) arriba, hay problemas de codificación.</p>
        </div>

        <h2>🗄️ Base de Datos</h2>
        <?php if ($db): ?>
            <div class="test-box pass">
                <p><strong>✅ Conexión exitosa</strong></p>
                <?php
                $charset = $db->query("SELECT @@character_set_connection")->fetchColumn();
                $dbCharset = $db->query("SELECT @@character_set_database")->fetchColumn();
                ?>
                <p>Charset conexión: <code><?= $charset ?></code></p>
                <p>Charset BD: <code><?= $dbCharset ?></code></p>
            </div>

            <h3>Clientes (primeros 5)</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $clients = $db->query("SELECT id, name FROM clients LIMIT 5")->fetchAll();
                    foreach ($clients as $client):
                        $name = $client['name'];
                        $hasCorrupt = preg_match('/\?\?/', $name);
                        $isUtf8 = mb_check_encoding($name, 'UTF-8');
                    ?>
                    <tr>
                        <td><?= $client['id'] ?></td>
                        <td><?= htmlspecialchars($name) ?></td>
                        <td class="status <?= $hasCorrupt ? 'bad' : 'ok' ?>">
                            <?= $hasCorrupt ? '❌ Corrupto' : ($isUtf8 ? '✅ OK' : '⚠️ Dudoso') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Usuarios</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = $db->query("SELECT id, name FROM users LIMIT 5")->fetchAll();
                    foreach ($users as $user):
                        $name = $user['name'];
                        $hasCorrupt = preg_match('/\?\?/', $name);
                        $isUtf8 = mb_check_encoding($name, 'UTF-8');
                    ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($name) ?></td>
                        <td class="status <?= $hasCorrupt ? 'bad' : 'ok' ?>">
                            <?= $hasCorrupt ? '❌ Corrupto' : ($isUtf8 ? '✅ OK' : '⚠️ Dudoso') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="test-box fail">
                <p>❌ <strong>No se pudo conectar a la base de datos</strong></p>
                <p>Verifica que MySQL esté corriendo.</p>
            </div>
        <?php endif; ?>

        <h2>🔧 Solución</h2>
        <div class="test-box warning">
            <p><strong>Si ves caracteres corruptos (??):</strong></p>
            <ol>
                <li>Ejecuta en consola MySQL:
                    <pre><code>mysql -u root -p torque_erp &lt; database/regenerate_seeders_utf8.sql</code></pre>
                </li>
                <li>O recrea la base de datos completamente.</li>
                <li>Refresca esta página para verificar.</li>
            </ol>
        </div>

        <p style="margin-top: 40px; color: #666; text-align: center;">
            <a href="/" style="color: #4d8eff;">← Volver al Dashboard</a>
        </p>
    </div>
</body>
</html>
