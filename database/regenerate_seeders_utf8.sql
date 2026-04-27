-- =====================================================
-- REGENERAR SEEDERS CON UTF-8 CORRECTO
-- Torque Studio ERP
-- =====================================================
-- Este script borra y regenera los datos de prueba
-- con codificación UTF-8 correcta
-- =====================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- =====================================================
-- LIMPIAR DATOS EXISTENTES (mantener estructura)
-- =====================================================
DELETE FROM clients WHERE id <= 5;
DELETE FROM users WHERE id <= 3;

-- =====================================================
-- INSERTAR CLIENTES CON UTF-8 CORRECTO
-- =====================================================
INSERT INTO clients (name, phone, email, address) VALUES
('Carlos Rodríguez', '809-555-0101', 'carlos@email.com', 'Av. Principal #123, Santo Domingo'),
('Ana María Gómez', '809-555-0102', 'ana@email.com', 'Calle 27 #45, Santiago'),
('Pedro Martínez', '809-555-0103', 'pedro@email.com', 'Carrera 8 #12, La Romana'),
('Laura Fernández', '809-555-0104', 'laura@email.com', 'Av. Las Palmas #78, Puerto Plata'),
('Miguel Ángel Sánchez', '809-555-0105', 'miguel@email.com', 'Calle del Sol #34, San Francisco');

-- =====================================================
-- INSERTAR USUARIOS CON UTF-8 CORRECTO  
-- =====================================================
-- Password: admin123
INSERT INTO users (name, email, password, role_id) VALUES 
('Administrador', 'admin@torque.com', '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O', 1),
('Juan Mecánico', 'juan@torque.com', '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O', 2),
('María Recepción', 'maria@torque.com', '$2y$10$tZ2.QZ/Z//Dq2MOMp/1m..U8G/wS42F5rG7XG9c/R5I2q3c/S9j0O', 3);

-- =====================================================
-- VERIFICAR RESULTADO
-- =====================================================
SELECT 'Clientes insertados:' AS verificacion;
SELECT id, name, email FROM clients ORDER BY id;

SELECT 'Usuarios insertados:' AS verificacion;
SELECT id, name, email FROM users ORDER BY id;
