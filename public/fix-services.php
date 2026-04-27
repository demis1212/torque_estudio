<?php
/**
 * REPARADOR RÁPIDO - Tabla Services
 * Torque Studio ERP
 */

header('Content-Type: text/html; charset=utf-8');

try {
    require_once dirname(__DIR__) . '/config/database.php';
    $db = Config\Database::getConnection();
    
    // Borrar servicios corruptos
    $db->exec("DELETE FROM services");
    
    // Insertar servicios con UTF-8 correcto
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
    
    echo "✅ Servicios reparados correctamente!<br><br>";
    
    // Mostrar los servicios insertados
    $result = $db->query("SELECT name, description FROM services ORDER BY id");
    echo "<strong>Servicios insertados:</strong><br><ul>";
    while ($row = $result->fetch()) {
        echo "<li><strong>" . htmlspecialchars($row['name']) . "</strong><br>";
        echo "<small>" . htmlspecialchars($row['description']) . "</small></li>";
    }
    echo "</ul>";
    
    echo '<br><a href="/torque/services" style="padding: 10px 20px; background: #4d8eff; color: white; text-decoration: none; border-radius: 8px;">Ver Catálogo de Servicios →</a>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
