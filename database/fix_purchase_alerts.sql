-- Fix para tabla purchase_alerts - Agregar columnas faltantes

-- Primero verificar si la tabla existe
SHOW COLUMNS FROM purchase_alerts;

-- Si falta alguna columna, ejecutar estos ALTER TABLE:

-- Agregar columnas faltantes una por una (ignorar errores si ya existen)
ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS part_name VARCHAR(150) NOT NULL DEFAULT '' AFTER part_id;

ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS part_code VARCHAR(50) NOT NULL DEFAULT '' AFTER part_name;

ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS current_quantity INT NOT NULL DEFAULT 0 AFTER part_code;

ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS min_stock INT NOT NULL DEFAULT 0 AFTER current_quantity;

ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS suggested_quantity INT NOT NULL DEFAULT 0 AFTER min_stock;

ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS status ENUM('pendiente','comprado','cancelado') NOT NULL DEFAULT 'pendiente' AFTER suggested_quantity;

ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS resolved_at TIMESTAMP NULL AFTER created_at;

ALTER TABLE purchase_alerts 
    ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER resolved_at;

-- Crear índices si no existen
CREATE INDEX IF NOT EXISTS idx_part_status ON purchase_alerts(part_id, status);
CREATE INDEX IF NOT EXISTS idx_status ON purchase_alerts(status);

-- Verificar estructura final
DESCRIBE purchase_alerts;
