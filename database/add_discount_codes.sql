-- ================================================
-- Migración: Sistema de Códigos de Descuento
-- Descripción: Agrega tablas para gestionar códigos de descuento
--              en reservaciones de habitaciones
-- Fecha: 2025-10-12
-- ================================================

USE majorbot_db;

-- ================================================
-- Tabla: discount_codes
-- Descripción: Almacena los códigos de descuento disponibles
-- ================================================
CREATE TABLE IF NOT EXISTS discount_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    amount DECIMAL(10, 2) NOT NULL,
    hotel_id INT NOT NULL,
    active TINYINT(1) DEFAULT 1,
    valid_from DATE NOT NULL,
    valid_to DATE NOT NULL,
    usage_limit INT DEFAULT NULL COMMENT 'NULL = ilimitado',
    times_used INT DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_code (code),
    INDEX idx_hotel (hotel_id),
    INDEX idx_active (active),
    INDEX idx_valid_dates (valid_from, valid_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Tabla: discount_code_usages
-- Descripción: Registra el uso de códigos de descuento
-- ================================================
CREATE TABLE IF NOT EXISTS discount_code_usages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discount_code_id INT NOT NULL,
    reservation_id INT NOT NULL,
    reservation_type ENUM('room', 'table', 'amenity') NOT NULL DEFAULT 'room',
    discount_amount DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2) NOT NULL,
    final_price DECIMAL(10, 2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id) ON DELETE CASCADE,
    INDEX idx_discount_code (discount_code_id),
    INDEX idx_reservation (reservation_id, reservation_type),
    INDEX idx_used_at (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Agregar campos a room_reservations para descuentos
-- ================================================
ALTER TABLE room_reservations 
ADD COLUMN IF NOT EXISTS discount_code_id INT NULL AFTER total_price,
ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER discount_code_id,
ADD COLUMN IF NOT EXISTS original_price DECIMAL(10, 2) NULL AFTER discount_amount;

-- Si la columna discount_code_id ya existe, agregar la foreign key si no existe
-- (Esto es seguro ya que usamos IF NOT EXISTS arriba)
SET @fk_exists = (SELECT COUNT(*) 
                  FROM information_schema.TABLE_CONSTRAINTS 
                  WHERE TABLE_SCHEMA = 'majorbot_db' 
                    AND TABLE_NAME = 'room_reservations' 
                    AND CONSTRAINT_NAME = 'fk_room_reservation_discount'
                    AND CONSTRAINT_TYPE = 'FOREIGN KEY');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE room_reservations ADD CONSTRAINT fk_room_reservation_discount FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================
-- Datos de ejemplo: Códigos de descuento
-- ================================================
-- Nota: Estos son ejemplos. Ajustar hotel_id según necesidad.
-- Los códigos se pueden agregar manualmente desde la interfaz de admin.

-- Ejemplo 1: 10% de descuento (válido 30 días)
INSERT INTO discount_codes (code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('WELCOME10', 'percentage', 10.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), NULL, 'Código de bienvenida - 10% de descuento')
ON DUPLICATE KEY UPDATE code = code;

-- Ejemplo 2: $50 de descuento fijo (limitado a 100 usos)
INSERT INTO discount_codes (code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('PROMO50', 'fixed', 50.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 100, 'Promoción especial - $50 de descuento')
ON DUPLICATE KEY UPDATE code = code;

-- Ejemplo 3: 20% de descuento (válido 7 días)
INSERT INTO discount_codes (code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('FLASH20', 'percentage', 20.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 50, 'Flash Sale - 20% de descuento')
ON DUPLICATE KEY UPDATE code = code;

-- ================================================
-- Verificación de la migración
-- ================================================
SELECT 'Migración completada exitosamente' AS status;

-- Verificar tablas creadas
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' 
  AND TABLE_NAME IN ('discount_codes', 'discount_code_usages');

-- Verificar códigos de ejemplo insertados
SELECT id, code, discount_type, amount, active, valid_from, valid_to, usage_limit, times_used
FROM discount_codes;
