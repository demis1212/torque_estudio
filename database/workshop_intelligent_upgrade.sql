SET NAMES utf8mb4;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS hourly_rate DECIMAL(12,2) NULL AFTER role_id;

UPDATE users
SET hourly_rate = 25000
WHERE role_id = 2 AND (hourly_rate IS NULL OR hourly_rate = 0);

ALTER TABLE clients
    ADD COLUMN IF NOT EXISTS rut VARCHAR(20) NULL AFTER name,
    ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(20) NULL AFTER phone,
    ADD COLUMN IF NOT EXISTS whatsapp_opt_in TINYINT(1) NOT NULL DEFAULT 0 AFTER whatsapp;

ALTER TABLE vehicles
    ADD COLUMN IF NOT EXISTS color VARCHAR(50) NULL AFTER model,
    ADD COLUMN IF NOT EXISTS mileage INT NULL AFTER engine,
    ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER mileage;

ALTER TABLE work_orders
    ADD COLUMN IF NOT EXISTS problem_reported TEXT NULL AFTER description,
    ADD COLUMN IF NOT EXISTS diagnosis TEXT NULL AFTER problem_reported,
    ADD COLUMN IF NOT EXISTS priority ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media' AFTER diagnosis,
    ADD COLUMN IF NOT EXISTS estimated_delivery_at DATETIME NULL AFTER created_at,
    ADD COLUMN IF NOT EXISTS delivered_at DATETIME NULL AFTER estimated_delivery_at,
    ADD COLUMN IF NOT EXISTS dead_minutes INT NOT NULL DEFAULT 0 AFTER delivered_at,
    ADD COLUMN IF NOT EXISTS billable_minutes INT NOT NULL DEFAULT 0 AFTER dead_minutes,
    ADD COLUMN IF NOT EXISTS non_billable_minutes INT NOT NULL DEFAULT 0 AFTER billable_minutes,
    ADD COLUMN IF NOT EXISTS labor_cost DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER non_billable_minutes,
    ADD COLUMN IF NOT EXISTS parts_cost DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER labor_cost,
    ADD COLUMN IF NOT EXISTS supplies_cost DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER parts_cost,
    ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER supplies_cost,
    ADD COLUMN IF NOT EXISTS tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER discount_amount;

CREATE TABLE IF NOT EXISTS workshop_hourly_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    billable TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_order_time_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    mechanic_id INT NOT NULL,
    rate_code VARCHAR(50) NOT NULL,
    hourly_rate DECIMAL(12,2) NOT NULL,
    billable TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('running','paused','finished') NOT NULL DEFAULT 'running',
    started_at DATETIME NOT NULL,
    paused_started_at DATETIME NULL,
    ended_at DATETIME NULL,
    paused_minutes INT NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (rate_code) REFERENCES workshop_hourly_rates(code) ON DELETE RESTRICT,
    INDEX idx_work_order_status (work_order_id, status),
    INDEX idx_mechanic_status (mechanic_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_order_pause_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time_entry_id INT NOT NULL,
    reason ENUM('espera_repuestos','cambio_mecanico','almuerzo','cliente_autoriza','espera_diagnostico','falta_herramienta','otro') NOT NULL,
    notes VARCHAR(255) NULL,
    started_at DATETIME NOT NULL,
    ended_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_entry_id) REFERENCES work_order_time_entries(id) ON DELETE CASCADE,
    INDEX idx_time_entry (time_entry_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_order_quality_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL UNIQUE,
    work_done_ok TINYINT(1) NOT NULL DEFAULT 0,
    torque_applied_ok TINYINT(1) NOT NULL DEFAULT 0,
    no_leaks_ok TINYINT(1) NOT NULL DEFAULT 0,
    no_dashboard_lights_ok TINYINT(1) NOT NULL DEFAULT 0,
    road_test_ok TINYINT(1) NOT NULL DEFAULT 0,
    cleaning_ok TINYINT(1) NOT NULL DEFAULT 0,
    client_informed_ok TINYINT(1) NOT NULL DEFAULT 0,
    signed_by_user_id INT NULL,
    signed_name VARCHAR(120) NULL,
    signed_at DATETIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (signed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS billing_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    document_type ENUM('boleta','factura','cotizacion','presupuesto') NOT NULL,
    document_number VARCHAR(50) NOT NULL,
    issued_at DATETIME NOT NULL,
    payment_method VARCHAR(50) NULL,
    payment_status ENUM('pendiente','pagado','abono') NOT NULL DEFAULT 'pendiente',
    pending_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
    labor_subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    services_subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    parts_subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    supplies_subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_doc_number (document_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    reminder_type ENUM('cambio_aceite','mantencion_mensual','revision_frenos','revision_neumaticos','soap_permiso','vencimiento_bateria') NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('pendiente','enviado','cancelado') NOT NULL DEFAULT 'pendiente',
    sent_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_due_status (due_date, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_order_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    media_type ENUM('foto_antes','foto_despues','documento','firma_cliente') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de alertas automáticas de compra de repuestos
CREATE TABLE IF NOT EXISTS purchase_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    part_id INT NOT NULL,
    part_name VARCHAR(150) NOT NULL,
    part_code VARCHAR(50) NOT NULL,
    current_quantity INT NOT NULL,
    min_stock INT NOT NULL,
    suggested_quantity INT NOT NULL,
    status ENUM('pendiente','comprado','cancelado') NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    notes TEXT NULL,
    INDEX idx_part_status (part_id, status),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de recordatorios WhatsApp
CREATE TABLE IF NOT EXISTS whatsapp_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    work_order_id INT NULL,
    reminder_type ENUM('cita','entrega_ot','mantenimiento','promocion','personalizado') NOT NULL,
    message TEXT NOT NULL,
    scheduled_at TIMESTAMP NOT NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('programado','enviado','fallido','cancelado') NOT NULL DEFAULT 'programado',
    whatsapp_number VARCHAR(20) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_work_order (work_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO workshop_hourly_rates (code, label, amount, billable)
VALUES
    ('mecanica_general', 'Hora Mecánica General', 25000, 1),
    ('diagnostico_especializado', 'Diagnóstico Especializado', 35000, 1),
    ('electricidad_automotriz', 'Electricidad Automotriz', 45000, 1),
    ('espera_repuestos', 'Espera Repuestos', 0, 0),
    ('tiempo_no_cobrable', 'Tiempo No Cobrable', 0, 0)
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    amount = VALUES(amount),
    billable = VALUES(billable);
