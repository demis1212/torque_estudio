-- 🔧 INVENTARIO DE REPUESTOS
CREATE TABLE IF NOT EXISTS parts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    category VARCHAR(50) NULL,
    unit_type ENUM('unidad', 'litros', 'kilos', 'metros', 'pares') DEFAULT 'unidad',
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

-- Migración: Agregar columna unit_type si no existe (para BD existentes)
ALTER TABLE parts ADD COLUMN IF NOT EXISTS unit_type ENUM('unidad', 'litros', 'kilos', 'metros', 'pares') DEFAULT 'unidad' AFTER category;

-- 🔗 REPUESTOS USADOS EN ÓRDENES
CREATE TABLE IF NOT EXISTS work_order_parts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    part_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 📊 ASIGNACIÓN DE MECÁNICOS
CREATE TABLE IF NOT EXISTS work_order_assignments (
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
CREATE TABLE IF NOT EXISTS notifications (
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

-- 📋 LOGS DE ACTIVIDAD
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    description TEXT,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ⚙️ CONFIGURACIONES DEL SISTEMA
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NOT NULL,
    `group` VARCHAR(50) DEFAULT 'general',
    description TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 🔧 SEEDER REPUESTOS (INVENTARIO)
INSERT IGNORE INTO parts (code, name, description, category, quantity, min_stock, cost_price, sale_price, supplier, location) VALUES
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
INSERT IGNORE INTO work_order_parts (work_order_id, part_id, quantity, price) VALUES
(3, 1, 1, 45.00),
(3, 2, 1, 20.00),
(6, 5, 1, 75.00);

-- 📊 SEEDER ASIGNACIONES DE MECÁNICOS
INSERT IGNORE INTO work_order_assignments (work_order_id, mechanic_id, notes) VALUES
(1, 2, 'Diagnóstico de motor'),
(2, 2, 'Revisión de frenos'),
(3, 2, 'Cambio de aceite y filtros'),
(6, 2, 'Vibración en suspensión');

-- 🔔 SEEDER NOTIFICACIONES
INSERT IGNORE INTO notifications (user_id, title, message, type, link) VALUES
(1, 'Orden #1 en diagnóstico', 'La orden de Carlos Rodríguez está en fase de diagnóstico', 'info', '/work-orders/edit/1'),
(1, 'Bajo stock: Filtro de Aire', 'Quedan solo 3 unidades en inventario', 'warning', '/parts'),
(2, 'Nueva orden asignada', 'Se te ha asignado la orden #6', 'info', '/work-orders/edit/6'),
(3, 'Orden #4 terminada', 'La orden de Laura Fernández está lista para entrega', 'success', '/work-orders/edit/4');

-- ⚙️ SEEDER CONFIGURACIONES
INSERT IGNORE INTO settings (`key`, `value`, `group`, description) VALUES
('company_name', 'Torque Studio', 'general', 'Nombre del taller'),
('company_phone', '809-555-0000', 'general', 'Teléfono de contacto'),
('company_address', 'Av. Principal #123, Santo Domingo', 'general', 'Dirección del taller'),
('currency', 'DOP', 'general', 'Moneda utilizada'),
('tax_rate', '18', 'billing', 'Porcentaje de impuestos'),
('work_order_prefix', 'WO-', 'work_orders', 'Prefijo para números de orden'),
('low_stock_alert', 'true', 'inventory', 'Alertar cuando stock es bajo'),
('default_mechanic', '2', 'work_orders', 'Mecánico por defecto para asignación');
