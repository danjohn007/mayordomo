-- ================================================
-- Migración: Agregar campo de fecha de cumpleaños
-- ================================================

-- Agregar campo guest_birthday a room_reservations si no existe
SET @room_has_birthday = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'room_reservations'
      AND COLUMN_NAME = 'guest_birthday'
);

SET @sql = IF(@room_has_birthday = 0,
    'ALTER TABLE room_reservations ADD COLUMN guest_birthday DATE NULL;',
    'SELECT "Column guest_birthday already exists in room_reservations, skipping" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar campo guest_birthday a table_reservations si no existe
SET @table_has_birthday = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'table_reservations'
      AND COLUMN_NAME = 'guest_birthday'
);

SET @sql = IF(@table_has_birthday = 0,
    'ALTER TABLE table_reservations ADD COLUMN guest_birthday DATE NULL;',
    'SELECT "Column guest_birthday already exists in table_reservations, skipping" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar campo guest_birthday a amenity_reservations si la tabla y columna no existen
SET @amenity_table_exists = (
    SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'amenity_reservations'
);

SET @amenity_has_birthday = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'amenity_reservations'
      AND COLUMN_NAME = 'guest_birthday'
);

SET @sql = IF(@amenity_table_exists > 0 AND @amenity_has_birthday = 0,
    'ALTER TABLE amenity_reservations ADD COLUMN guest_birthday DATE NULL;',
    'SELECT "Table amenity_reservations does not exist or column guest_birthday already exists, skipping" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration completed: birthday field added to reservations tables' AS status;
