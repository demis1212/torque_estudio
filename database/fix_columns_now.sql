-- Fix para WorkOrderPartRequest - Verificar columnas
-- Ejecutar en MySQL

-- Verificar estructura de tabla parts
DESCRIBE parts;

-- Si las columnas son diferentes, estos son los ajustes necesarios
-- (El código ya usa 'quantity' y 'sale_price' correctamente)

-- Fix para purchase_alerts (columnas faltantes)
ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS part_name VARCHAR(150) NOT NULL DEFAULT '' AFTER part_id,
    ADD COLUMN IF NOT EXISTS part_code VARCHAR(50) NOT NULL DEFAULT '' AFTER part_name,
    ADD COLUMN IF NOT EXISTS current_quantity INT NOT NULL DEFAULT 0 AFTER part_code,
    ADD COLUMN IF NOT EXISTS min_stock INT NOT NULL DEFAULT 0 AFTER current_quantity,
    ADD COLUMN IF NOT EXISTS suggested_quantity INT NOT NULL DEFAULT 0 AFTER min_stock,
    ADD COLUMN IF NOT EXISTS status ENUM('pendiente','comprado','cancelado') NOT NULL DEFAULT 'pendiente' AFTER suggested_quantity,
    ADD COLUMN IF NOT EXISTS resolved_at TIMESTAMP NULL AFTER created_at,
    ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER resolved_at;

-- Verificar estructura final
DESCRIBE purchase_alerts;
