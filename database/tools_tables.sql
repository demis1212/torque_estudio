-- 🔧 HERRAMIENTAS DE MECÁNICO (Asignadas permanentemente)
CREATE TABLE IF NOT EXISTS mechanic_tools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    code VARCHAR(50) UNIQUE NULL,
    brand VARCHAR(50) NULL,
    model VARCHAR(50) NULL,
    purchase_date DATE NULL,
    cost DECIMAL(10,2) NULL,
    status ENUM('activa', 'danada', 'perdida', 'en_reparacion') DEFAULT 'activa',
    mechanic_id INT NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_mechanic (mechanic_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- 🏭 HERRAMIENTAS DE BODEGA (Alto valor, préstamo diario)
CREATE TABLE IF NOT EXISTS warehouse_tools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    code VARCHAR(50) UNIQUE NULL,
    brand VARCHAR(50) NULL,
    model VARCHAR(50) NULL,
    serial_number VARCHAR(100) NULL,
    purchase_date DATE NULL,
    cost DECIMAL(10,2) NULL,
    status ENUM('disponible', 'prestada', 'en_mantenimiento', 'danada') DEFAULT 'disponible',
    min_stock_alert INT DEFAULT 1,
    location VARCHAR(100) NULL,
    requires_auth BOOLEAN DEFAULT FALSE,
    auth_role_id INT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_code (code)
) ENGINE=InnoDB;

-- 📋 SOLICITUDES DE HERRAMIENTAS (Préstamos diarios)
CREATE TABLE IF NOT EXISTS tool_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warehouse_tool_id INT NOT NULL,
    mechanic_id INT NOT NULL,
    request_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('pendiente', 'aprobada', 'rechazada', 'entregada', 'devuelta', 'atrasada') DEFAULT 'pendiente',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    returned_at TIMESTAMP NULL,
    condition_notes TEXT NULL,
    notes TEXT NULL,
    FOREIGN KEY (warehouse_tool_id) REFERENCES warehouse_tools(id) ON DELETE RESTRICT,
    FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mechanic (mechanic_id),
    INDEX idx_status (status),
    INDEX idx_date (request_date)
) ENGINE=InnoDB;

-- 🔧 SEEDER HERRAMIENTAS DE MECÁNICO
INSERT IGNORE INTO mechanic_tools (name, description, code, brand, status, mechanic_id, notes) VALUES
('Juego de llaves mixtas', 'Llaves combinadas métricas 8-19mm', 'HM-001', 'Stanley', 'activa', 2, 'Asignadas el día de ingreso'),
('Carraca 1/2"', 'Carraca de 72 dientes con matraca', 'HM-002', 'Craftsman', 'activa', 2, 'Incluye extensiones'),
('Pistola de impacto neumática', 'Para uso con compresor de aire', 'HM-003', 'Ingersoll Rand', 'activa', 2, 'Revisar aceite mensualmente'),
('Juego de dados métricos', 'Dados 1/4", 3/8" y 1/2"', 'HM-004', 'Snap-on', 'activa', 2, 'Completo con estuche'),
('Multímetro digital', 'Para diagnóstico eléctrico', 'HM-005', 'Fluke', 'activa', 2, 'Calibración anual'),
('Scanner OBD2 básico', 'Para lectura de códigos', 'HM-006', 'Autel', 'activa', 2, 'Actualizar software trimestral'),
('Gato hidráulico 3 ton', 'Para levantar vehículos', 'HM-007', 'Torin', 'activa', 2, 'Revisar sellos cada 6 meses'),
('Llave dinamométrica', '3/8" 10-80 Nm', 'HM-008', 'Hazet', 'activa', 2, 'Calibración semestral obligatoria');

-- 🏭 SEEDER HERRAMIENTAS DE BODEGA (Alto Valor)
INSERT IGNORE INTO warehouse_tools (name, description, code, brand, serial_number, cost, status, location, requires_auth, auth_role_id, notes) VALUES
('Scanner OBD2 Profesional', 'Launch X431 V+ con todos los conectores', 'WB-001', 'Launch', 'SN123456789', 2500.00, 'disponible', 'Bodega A - Estante 1', TRUE, 1, 'Requiere autorización del admin'),
('Osciloscopio automotriz', 'Hantek 1008C 8 canales', 'WB-002', 'Hantek', 'SN987654321', 1800.00, 'disponible', 'Bodega A - Estante 2', TRUE, 1, 'Solo para diagnósticos complejos'),
('Pistola de impacto eléctrica 1/2"', 'Milwaukee M18 Fuel', 'WB-003', 'Milwaukee', 'SN456789123', 650.00, 'disponible', 'Bodega B - Herramientas eléctricas', FALSE, NULL, '2 baterías incluidas'),
('Compresor de aire portátil', 'DeWalt 6 galones', 'WB-004', 'DeWalt', 'SN789123456', 450.00, 'disponible', 'Bodega B - Rincón', FALSE, NULL, 'Revisar aceite antes de usar'),
('Soldadora MIG 140A', 'Lincoln Electric', 'WB-005', 'Lincoln', 'SN321654987', 1200.00, 'disponible', 'Bodega C - Área de soldadura', TRUE, 1, 'Solo personal autorizado'),
('Extractor de inyectores', 'Kit completo universal', 'WB-006', 'OTC', 'SN654987321', 850.00, 'disponible', 'Bodega A - Estante 3', FALSE, NULL, 'Revisar sellos después de uso'),
('Prensa hidráulica 20 ton', 'Para prensar rodamientos', 'WB-007', 'Omega', 'SN147258369', 950.00, 'disponible', 'Bodega C - Zona pesada', TRUE, 1, 'Requiere 2 personas para operar'),
('Máquina de limpieza de inyectores', 'Ulasonido con cuba', 'WB-008', 'Launch', 'SN369258147', 2200.00, 'disponible', 'Bodega A - Área limpia', TRUE, 1, 'Usar solo con supervisor'),
('Torreta para fresado', 'Mini torno/fresa', 'WB-009', 'Grizzly', 'SN852741963', 1500.00, 'disponible', 'Bodega C - Taller', TRUE, 1, 'Capacitación requerida'),
('Analizador de gases de escape', '4 gases con impresora', 'WB-010', 'Bosch', 'SN159753468', 3200.00, 'disponible', 'Bodega A - Estante 1', TRUE, 1, 'Calibración mensual');

-- 📋 SEEDER SOLICITUDES DE EJEMPLO
INSERT IGNORE INTO tool_requests (warehouse_tool_id, mechanic_id, request_date, status, approved_by, notes) VALUES
(1, 2, CURDATE(), 'entregada', 1, 'Para diagnóstico de Toyota Corolla'),
(3, 2, CURDATE(), 'pendiente', NULL, 'Necesito para cambio de amortiguadores');
