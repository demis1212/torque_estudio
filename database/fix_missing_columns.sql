-- =====================================================
-- AGREGAR COLUMNAS FALTANTES
-- Torque Studio ERP
-- =====================================================

SET NAMES utf8mb4;

-- Agregar columna 'diagnosis' a work_orders si no existe
ALTER TABLE work_orders 
ADD COLUMN IF NOT EXISTS diagnosis TEXT NULL 
COMMENT 'Diagnóstico técnico de la orden' 
AFTER description;

-- Agregar columna 'notes' a vehicles si no existe  
ALTER TABLE vehicles 
ADD COLUMN IF NOT EXISTS notes TEXT NULL 
COMMENT 'Notas adicionales del vehículo' 
AFTER engine;

-- Verificar que se crearon
SHOW COLUMNS FROM work_orders LIKE 'diagnosis';
SHOW COLUMNS FROM vehicles LIKE 'notes';
