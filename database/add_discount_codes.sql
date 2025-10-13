-- ================================================
-- Migración: Sistema de Códigos de Descuento
-- Script listo para importar sin errores de dependencias
-- ================================================

-- USE majorbot_db;

-- ================================================
-- Crear tabla hotels si no existe (mínima estructura)
-- ================================================
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY
) ENGINE=InnoDB;

-- ================================================
-- Tabla: discount_codes
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
-- Agregar columnas y Foreign Key a room_reservations solo si no existen
-- ================================================

-- discount_code_id
SET @column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'room_reservations'
      AND COLUMN_NAME = 'discount_code_id'
);
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE room_reservations ADD COLUMN discount_code_id INT NULL AFTER total_price;',
    'SELECT "Column discount_code_id already exists" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- discount_amount
SET @column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'room_reservations'
      AND COLUMN_NAME = 'discount_amount'
);
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE room_reservations ADD COLUMN discount_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER discount_code_id;',
    'SELECT "Column discount_amount already exists" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- original_price
SET @column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'room_reservations'
      AND COLUMN_NAME = 'original_price'
);
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE room_reservations ADD COLUMN original_price DECIMAL(10, 2) NULL AFTER discount_amount;',
    'SELECT "Column original_price already exists" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Foreign Key para discount_code_id
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'room_reservations' 
      AND CONSTRAINT_NAME = 'fk_room_reservation_discount'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE room_reservations ADD CONSTRAINT fk_room_reservation_discount FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id) ON DELETE SET NULL;',
    'SELECT "Foreign key already exists" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================
-- Datos de ejemplo: Códigos de descuento
-- ================================================
INSERT INTO discount_codes (code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('WELCOME10', 'percentage', 10.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), NULL, 'Código de bienvenida - 10% de descuento')
ON DUPLICATE KEY UPDATE code = code;

INSERT INTO discount_codes (code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('PROMO50', 'fixed', 50.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 100, 'Promoción especial - $50 de descuento')
ON DUPLICATE KEY UPDATE code = code;

INSERT INTO discount_codes (code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('FLASH20', 'percentage', 20.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 50, 'Flash Sale - 20% de descuento')
ON DUPLICATE KEY UPDATE code = code;

-- ================================================
-- Verificación de la migración
-- ================================================

SELECT 'Migración completada exitosamente' AS status;

-- Verificar tablas creadas
SHOW TABLES LIKE 'discount_codes';
SHOW TABLES LIKE 'discount_code_usages';

-- Verificar códigos de ejemplo insertados
SELECT id, code, discount_type, amount, active, valid_from, valid_to, usage_limit, times_used
FROM discount_codes LIMIT 0, 25;
