-- ================================================
-- Migración: Agregar campo de fecha de cumpleaños
-- ================================================

-- Agregar campo birthday a room_reservations
ALTER TABLE room_reservations 
ADD COLUMN IF NOT EXISTS guest_birthday DATE NULL 
AFTER guest_phone;

-- Agregar campo birthday a table_reservations
ALTER TABLE table_reservations 
ADD COLUMN IF NOT EXISTS guest_birthday DATE NULL 
AFTER guest_phone;

-- Agregar campo birthday a amenity_reservations si la tabla existe
SET @table_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'amenity_reservations'
);

SET @sql = IF(@table_exists > 0,
    'ALTER TABLE amenity_reservations ADD COLUMN IF NOT EXISTS guest_birthday DATE NULL AFTER guest_phone;',
    'SELECT "Table amenity_reservations does not exist, skipping" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration completed: birthday field added to reservations tables' AS status;
