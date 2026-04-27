CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    vin VARCHAR(50) NOT NULL UNIQUE,
    plate VARCHAR(20) NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year YEAR NOT NULL,
    engine VARCHAR(50) NULL,
    transmission VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE work_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    user_id INT NOT NULL, -- quien crea la orden
    status ENUM('recepcion', 'diagnostico', 'reparacion', 'terminado') NOT NULL DEFAULT 'recepcion',
    description TEXT NULL,
    total_cost DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE work_order_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 🔧 INVENTARIO DE REPUESTOS
CREATE TABLE parts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    category VARCHAR(50) NULL,
    quantity INT NOT NULL DEFAULT 0,
    min_stock INT NOT NULL DEFAULT 5,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    supplier VARCHAR(100) NULL,
    location VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 🔗 REPUESTOS USADOS EN ÓRDENES
CREATE TABLE work_order_parts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    part_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 📊 ASIGNACIÓN DE MECÁNICOS
CREATE TABLE work_order_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    mechanic_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (work_order_id, mechanic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 🔔 NOTIFICACIONES
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 📝 LOGS DE ACTIVIDAD
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ⚙️ CONFIGURACIONES DEL SISTEMA
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NOT NULL,
    `group` VARCHAR(50) DEFAULT 'general',
    description TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEEDER ROLES
INSERT INTO roles (name) VALUES ('Admin'), ('Mecánico'), ('Recepcionista');

-- SEEDER USUARIOS (Password: admin123)
INSERT INTO users (name, email, password, role_id) VALUES 
('Administrador', 'admin@torque.com', '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O', 1),
('Juan Mecánico', 'juan@torque.com', '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O', 2),
('María Recepción', 'maria@torque.com', '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O', 3);

-- SEEDER SERVICIOS
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

-- SEEDER CLIENTES
INSERT INTO clients (name, phone, email, address) VALUES
('Carlos Rodríguez', '809-555-0101', 'carlos@email.com', 'Av. Principal #123, Santo Domingo'),
('Ana María Gómez', '809-555-0102', 'ana@email.com', 'Calle 27 #45, Santiago'),
('Pedro Martínez', '809-555-0103', 'pedro@email.com', 'Carrera 8 #12, La Romana'),
('Laura Fernández', '809-555-0104', 'laura@email.com', 'Av. Lincoln #89, Santo Domingo'),
('Roberto Sánchez', '809-555-0105', 'roberto@email.com', 'Calle Principal #56, Puerto Plata');

-- SEEDER VEHÍCULOS
INSERT INTO vehicles (client_id, vin, plate, brand, model, year, engine, transmission) VALUES
(1, '3HGBH41JXMN109186', 'A123456', 'Toyota', 'Corolla', 2019, '1.8L', 'Automática'),
(1, '1FAFP53U31A217971', 'B234567', 'Honda', 'Civic', 2020, '2.0L', 'Automática'),
(2, '2G1WG5EK3C1122334', 'C345678', 'Hyundai', 'Elantra', 2018, '1.6L', 'Manual'),
(3, '3N1AB7AP7KY234567', 'D456789', 'Nissan', 'Sentra', 2021, '1.8L', 'Automática'),
(4, '5XYKU3A12CG456789', 'E567890', 'Kia', 'Sportage', 2020, '2.4L', 'Automática'),
(5, 'JM1BL1H5A1A567890', 'F678901', 'Mazda', '3', 2019, '2.0L', 'Automática');

-- SEEDER ÓRDENES DE TRABAJO
INSERT INTO work_orders (client_id, vehicle_id, user_id, status, description, total_cost) VALUES
(1, 1, 1, 'recepcion', 'Ruido extraño en el motor al encender', 0.00),
(2, 3, 3, 'diagnostico', 'Frenos chirriantes, necesita revisión', 0.00),
(3, 4, 1, 'reparacion', 'Cambio de aceite y revisión general', 0.00),
(4, 5, 3, 'terminado', 'Alineación y balanceo completados', 115.00),
(1, 2, 1, 'recepcion', 'Aire acondicionado no enfría', 0.00),
(5, 6, 3, 'diagnostico', 'Vibración en volante a alta velocidad', 0.00);

-- SEEDER SERVICIOS EN ÓRDENES
INSERT INTO work_order_services (work_order_id, service_id, quantity, price) VALUES
(1, 5, 1, 80.00),  -- Diagnóstico para orden 1
(2, 3, 1, 35.00),  -- Revisión de frenos para orden 2
(3, 1, 1, 45.00),  -- Cambio de aceite para orden 3
(3, 6, 1, 20.00),  -- Filtro de aire para orden 3
(4, 2, 1, 60.00),  -- Alineación para orden 4
(4, 2, 1, 55.00),  -- Balanceo (usando mismo servicio) para orden 4
(5, 8, 1, 55.00),  -- Aire acondicionado para orden 5
(6, 2, 1, 60.00);  -- Alineación para orden 6

-- Actualizar totales de órdenes
UPDATE work_orders SET total_cost = 80.00 WHERE id = 1;
UPDATE work_orders SET total_cost = 35.00 WHERE id = 2;
UPDATE work_orders SET total_cost = 65.00 WHERE id = 3;
UPDATE work_orders SET total_cost = 115.00 WHERE id = 4;
UPDATE work_orders SET total_cost = 55.00 WHERE id = 5;
UPDATE work_orders SET total_cost = 60.00 WHERE id = 6;

-- 🔧 SEEDER REPUESTOS (INVENTARIO)
INSERT INTO parts (code, name, description, category, quantity, min_stock, cost_price, sale_price, supplier, location) VALUES
('ACEITE-5W30', 'Aceite Motor 5W-30', 'Aceite sintético 5W-30 4L', 'Lubricantes', 50, 10, 25.00, 45.00, 'Shell Dominicana', 'Estante A1'),
('FILTRO-ACEITE', 'Filtro de Aceite', 'Filtro de aceite universal', 'Filtros', 30, 5, 8.00, 20.00, 'Bosch', 'Estante A2'),
('FILTRO-AIRE', 'Filtro de Aire', 'Filtro de aire estándar', 'Filtros', 25, 5, 10.00, 25.00, 'Mann Filter', 'Estante A2'),
('BUJIA-NGK', 'Bujía NGK', 'Bujía de encendido NGK', 'Encendido', 40, 8, 6.00, 15.00, 'NGK', 'Estante B1'),
('PASTILLAS-FRENO', 'Pastillas de Freno', 'Pastillas de freno delanteras', 'Frenos', 20, 5, 35.00, 75.00, 'Brembo', 'Estante C1'),
('DISCOS-FRENO', 'Discos de Freno', 'Discos de freno ventilados', 'Frenos', 15, 3, 45.00, 95.00, 'Brembo', 'Estante C1'),
('AMORTIGUADOR', 'Amortiguador Trasero', 'Amortiguador gas', 'Suspensión', 12, 4, 55.00, 120.00, 'Monroe', 'Estante D1'),
('BATERIA-55AH', 'Batería 55AH', 'Batería 12V 55AH', 'Eléctrico', 8, 3, 85.00, 150.00, 'MAC', 'Estante E1'),
('CORREA-DIST', 'Correa Distribución', 'Kit correa de distribución', 'Motor', 6, 2, 120.00, 250.00, 'Continental', 'Estante F1'),
('REFRIGERANTE', 'Refrigerante 1L', 'Refrigerante anticongelante', 'Líquidos', 40, 10, 8.00, 18.00, 'Prestone', 'Estante A3');

-- 🔗 SEEDER REPUESTOS EN ÓRDENES
INSERT INTO work_order_parts (work_order_id, part_id, quantity, price) VALUES
(3, 1, 1, 45.00),  -- Aceite en orden 3
(3, 2, 1, 20.00),  -- Filtro de aceite en orden 3
(6, 5, 1, 75.00);  -- Pastillas en orden 6

-- 📊 SEEDER ASIGNACIONES DE MECÁNICOS
INSERT INTO work_order_assignments (work_order_id, mechanic_id, notes) VALUES
(1, 2, 'Diagnóstico de motor'),
(2, 2, 'Revisión de frenos'),
(3, 2, 'Cambio de aceite y filtros'),
(6, 2, 'Vibración en suspensión');

-- 🔔 SEEDER NOTIFICACIONES
INSERT INTO notifications (user_id, title, message, type, link) VALUES
(1, 'Orden #1 en diagnóstico', 'La orden de Carlos Rodríguez está en fase de diagnóstico', 'info', '/work-orders/edit/1'),
(1, 'Bajo stock: Filtro de Aire', 'Quedan solo 3 unidades en inventario', 'warning', '/parts'),
(2, 'Nueva orden asignada', 'Se te ha asignado la orden #6', 'info', '/work-orders/edit/6'),
(3, 'Orden #4 terminada', 'La orden de Laura Fernández está lista para entrega', 'success', '/work-orders/edit/4');

-- ⚙️ SEEDER CONFIGURACIONES
INSERT INTO settings (`key`, `value`, `group`, description) VALUES
('company_name', 'Torque Studio', 'general', 'Nombre del taller'),
('company_phone', '809-555-0000', 'general', 'Teléfono de contacto'),
('company_address', 'Av. Principal #123, Santo Domingo', 'general', 'Dirección del taller'),
('currency', 'DOP', 'general', 'Moneda utilizada'),
('tax_rate', '18', 'billing', 'Porcentaje de impuestos'),
('work_order_prefix', 'WO-', 'work_orders', 'Prefijo para números de orden'),
('low_stock_alert', 'true', 'inventory', 'Alertar cuando stock es bajo'),
('default_mechanic', '2', 'work_orders', 'Mecánico por defecto para asignación');
