-- =====================================================
-- SCRIPT PARA CORREGIR DATOS CORRUPTOS EN UTF-8
-- Torque Studio ERP
-- =====================================================
-- Este script corrige caracteres corruptos tipo:
--   ?? -> ó, í, é, á, ñ, etc.
-- Ejecutar en MySQL:
--   mysql -u root -p torque_erp < database/fix_corrupted_data.sql
-- =====================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- =====================================================
-- TABLA: clients
-- =====================================================
UPDATE clients SET 
    name = REPLACE(name, '??', 'á'),
    name = REPLACE(name, '??', 'é'),
    name = REPLACE(name, '??', 'í'),
    name = REPLACE(name, '??', 'ó'),
    name = REPLACE(name, '??', 'ú'),
    name = REPLACE(name, '??', 'ñ'),
    name = REPLACE(name, '??', 'Á'),
    name = REPLACE(name, '??', 'É'),
    name = REPLACE(name, '??', 'Í'),
    name = REPLACE(name, '??', 'Ó'),
    name = REPLACE(name, '??', 'Ú'),
    name = REPLACE(name, '??', 'Ñ');

UPDATE clients SET 
    address = REPLACE(address, '??', 'á'),
    address = REPLACE(address, '??', 'é'),
    address = REPLACE(address, '??', 'í'),
    address = REPLACE(address, '??', 'ó'),
    address = REPLACE(address, '??', 'ú'),
    address = REPLACE(address, '??', 'ñ'),
    address = REPLACE(address, '??', 'Á'),
    address = REPLACE(address, '??', 'É'),
    address = REPLACE(address, '??', 'Í'),
    address = REPLACE(address, '??', 'Ó'),
    address = REPLACE(address, '??', 'Ú'),
    address = REPLACE(address, '??', 'Ñ');

-- =====================================================
-- TABLA: users
-- =====================================================
UPDATE users SET 
    name = REPLACE(name, '??', 'á'),
    name = REPLACE(name, '??', 'é'),
    name = REPLACE(name, '??', 'í'),
    name = REPLACE(name, '??', 'ó'),
    name = REPLACE(name, '??', 'ú'),
    name = REPLACE(name, '??', 'ñ'),
    name = REPLACE(name, '??', 'Á'),
    name = REPLACE(name, '??', 'É'),
    name = REPLACE(name, '??', 'Í'),
    name = REPLACE(name, '??', 'Ó'),
    name = REPLACE(name, '??', 'Ú'),
    name = REPLACE(name, '??', 'Ñ');

-- =====================================================
-- TABLA: work_orders
-- =====================================================
UPDATE work_orders SET 
    description = REPLACE(description, '??', 'á'),
    description = REPLACE(description, '??', 'é'),
    description = REPLACE(description, '??', 'í'),
    description = REPLACE(description, '??', 'ó'),
    description = REPLACE(description, '??', 'ú'),
    description = REPLACE(description, '??', 'ñ'),
    description = REPLACE(description, '??', 'Á'),
    description = REPLACE(description, '??', 'É'),
    description = REPLACE(description, '??', 'Í'),
    description = REPLACE(description, '??', 'Ó'),
    description = REPLACE(description, '??', 'Ú'),
    description = REPLACE(description, '??', 'Ñ');

UPDATE work_orders SET 
    diagnosis = REPLACE(diagnosis, '??', 'á'),
    diagnosis = REPLACE(diagnosis, '??', 'é'),
    diagnosis = REPLACE(diagnosis, '??', 'í'),
    diagnosis = REPLACE(diagnosis, '??', 'ó'),
    diagnosis = REPLACE(diagnosis, '??', 'ú'),
    diagnosis = REPLACE(diagnosis, '??', 'ñ'),
    diagnosis = REPLACE(diagnosis, '??', 'Á'),
    diagnosis = REPLACE(diagnosis, '??', 'É'),
    diagnosis = REPLACE(diagnosis, '??', 'Í'),
    diagnosis = REPLACE(diagnosis, '??', 'Ó'),
    diagnosis = REPLACE(diagnosis, '??', 'Ú'),
    diagnosis = REPLACE(diagnosis, '??', 'Ñ');

-- =====================================================
-- TABLA: parts
-- =====================================================
UPDATE parts SET 
    name = REPLACE(name, '??', 'á'),
    name = REPLACE(name, '??', 'é'),
    name = REPLACE(name, '??', 'í'),
    name = REPLACE(name, '??', 'ó'),
    name = REPLACE(name, '??', 'ú'),
    name = REPLACE(name, '??', 'ñ'),
    name = REPLACE(name, '??', 'Á'),
    name = REPLACE(name, '??', 'É'),
    name = REPLACE(name, '??', 'Í'),
    name = REPLACE(name, '??', 'Ó'),
    name = REPLACE(name, '??', 'Ú'),
    name = REPLACE(name, '??', 'Ñ');

UPDATE parts SET 
    description = REPLACE(description, '??', 'á'),
    description = REPLACE(description, '??', 'é'),
    description = REPLACE(description, '??', 'í'),
    description = REPLACE(description, '??', 'ó'),
    description = REPLACE(description, '??', 'ú'),
    description = REPLACE(description, '??', 'ñ'),
    description = REPLACE(description, '??', 'Á'),
    description = REPLACE(description, '??', 'É'),
    description = REPLACE(description, '??', 'Í'),
    description = REPLACE(description, '??', 'Ó'),
    description = REPLACE(description, '??', 'Ú'),
    description = REPLACE(description, '??', 'Ñ');

UPDATE parts SET 
    supplier = REPLACE(supplier, '??', 'á'),
    supplier = REPLACE(supplier, '??', 'é'),
    supplier = REPLACE(supplier, '??', 'í'),
    supplier = REPLACE(supplier, '??', 'ó'),
    supplier = REPLACE(supplier, '??', 'ú'),
    supplier = REPLACE(supplier, '??', 'ñ'),
    supplier = REPLACE(supplier, '??', 'Á'),
    supplier = REPLACE(supplier, '??', 'É'),
    supplier = REPLACE(supplier, '??', 'Í'),
    supplier = REPLACE(supplier, '??', 'Ó'),
    supplier = REPLACE(supplier, '??', 'Ú'),
    supplier = REPLACE(supplier, '??', 'Ñ');

-- =====================================================
-- TABLA: services
-- =====================================================
UPDATE services SET 
    name = REPLACE(name, '??', 'á'),
    name = REPLACE(name, '??', 'é'),
    name = REPLACE(name, '??', 'í'),
    name = REPLACE(name, '??', 'ó'),
    name = REPLACE(name, '??', 'ú'),
    name = REPLACE(name, '??', 'ñ'),
    name = REPLACE(name, '??', 'Á'),
    name = REPLACE(name, '??', 'É'),
    name = REPLACE(name, '??', 'Í'),
    name = REPLACE(name, '??', 'Ó'),
    name = REPLACE(name, '??', 'Ú'),
    name = REPLACE(name, '??', 'Ñ');

UPDATE services SET 
    description = REPLACE(description, '??', 'á'),
    description = REPLACE(description, '??', 'é'),
    description = REPLACE(description, '??', 'í'),
    description = REPLACE(description, '??', 'ó'),
    description = REPLACE(description, '??', 'ú'),
    description = REPLACE(description, '??', 'ñ'),
    description = REPLACE(description, '??', 'Á'),
    description = REPLACE(description, '??', 'É'),
    description = REPLACE(description, '??', 'Í'),
    description = REPLACE(description, '??', 'Ó'),
    description = REPLACE(description, '??', 'Ú'),
    description = REPLACE(description, '??', 'Ñ');

-- =====================================================
-- TABLA: vehicles
-- =====================================================
UPDATE vehicles SET 
    notes = REPLACE(notes, '??', 'á'),
    notes = REPLACE(notes, '??', 'é'),
    notes = REPLACE(notes, '??', 'í'),
    notes = REPLACE(notes, '??', 'ó'),
    notes = REPLACE(notes, '??', 'ú'),
    notes = REPLACE(notes, '??', 'ñ'),
    notes = REPLACE(notes, '??', 'Á'),
    notes = REPLACE(notes, '??', 'É'),
    notes = REPLACE(notes, '??', 'Í'),
    notes = REPLACE(notes, '??', 'Ó'),
    notes = REPLACE(notes, '??', 'Ú'),
    notes = REPLACE(notes, '??', 'Ñ');

-- =====================================================
-- TABLA: manuals
-- =====================================================
UPDATE manuals SET 
    title = REPLACE(title, '??', 'á'),
    title = REPLACE(title, '??', 'é'),
    title = REPLACE(title, '??', 'í'),
    title = REPLACE(title, '??', 'ó'),
    title = REPLACE(title, '??', 'ú'),
    title = REPLACE(title, '??', 'ñ'),
    title = REPLACE(title, '??', 'Á'),
    title = REPLACE(title, '??', 'É'),
    title = REPLACE(title, '??', 'Í'),
    title = REPLACE(title, '??', 'Ó'),
    title = REPLACE(title, '??', 'Ú'),
    title = REPLACE(title, '??', 'Ñ');

UPDATE manuals SET 
    description = REPLACE(description, '??', 'á'),
    description = REPLACE(description, '??', 'é'),
    description = REPLACE(description, '??', 'í'),
    description = REPLACE(description, '??', 'ó'),
    description = REPLACE(description, '??', 'ú'),
    description = REPLACE(description, '??', 'ñ'),
    description = REPLACE(description, '??', 'Á'),
    description = REPLACE(description, '??', 'É'),
    description = REPLACE(description, '??', 'Í'),
    description = REPLACE(description, '??', 'Ó'),
    description = REPLACE(description, '??', 'Ú'),
    description = REPLACE(description, '??', 'Ñ');

-- =====================================================
-- VERIFICACIÓN
-- =====================================================
SELECT 'Corrección de datos completada' AS mensaje;
SELECT 'Clientes con nombres actualizados:' AS verificacion;
SELECT COUNT(*) FROM clients WHERE name LIKE '%á%' OR name LIKE '%é%' OR name LIKE '%í%' OR name LIKE '%ó%' OR name LIKE '%ú%' OR name LIKE '%ñ%';
