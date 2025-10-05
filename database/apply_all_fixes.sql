-- ============================================================================
-- COMPREHENSIVE FIX SCRIPT - Apply All Changes
-- ============================================================================
-- This script applies all necessary fixes in the correct order
-- Safe to run multiple times (idempotent)
-- MySQL 5.7+ compatible
-- ============================================================================

USE aqh_mayordomo;

SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;

-- ============================================================================
-- SECTION 1: VERIFY REQUIRED TABLES EXIST
-- ============================================================================

SELECT 'Verificando tablas requeridas...' as status;

SELECT 
    CASE 
        WHEN COUNT(*) = 5 THEN '✓ Todas las tablas existen'
        ELSE CONCAT('✗ Faltan ', 5 - COUNT(*), ' tablas')
    END as resultado
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_name IN ('room_reservations', 'table_reservations', 'amenity_reservations', 'system_notifications', 'role_permissions');

-- ============================================================================
-- SECTION 2: ADD MISSING COLUMNS (Safe - checks before adding)
-- ============================================================================

SELECT 'Verificando y agregando columnas necesarias...' as status;

-- Add hotel_id to room_reservations if missing
SET @dbname = DATABASE();
SET @tablename = 'room_reservations';
SET @columnname = 'hotel_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  "SELECT '✓ hotel_id ya existe en room_reservations' as resultado",
  "ALTER TABLE room_reservations ADD COLUMN hotel_id INT NULL AFTER id; SELECT '✓ hotel_id agregado a room_reservations' as resultado"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add hotel_id to table_reservations if missing
SET @tablename = 'table_reservations';
SET @columnname = 'hotel_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  "SELECT '✓ hotel_id ya existe en table_reservations' as resultado",
  "ALTER TABLE table_reservations ADD COLUMN hotel_id INT NULL AFTER id; SELECT '✓ hotel_id agregado a table_reservations' as resultado"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Make guest_id nullable in room_reservations
ALTER TABLE room_reservations MODIFY COLUMN guest_id INT NULL;
SELECT '✓ guest_id nullable en room_reservations' as resultado;

-- Make guest_id nullable in table_reservations
ALTER TABLE table_reservations MODIFY COLUMN guest_id INT NULL;
SELECT '✓ guest_id nullable en table_reservations' as resultado;

-- Add notification_sent to room_reservations if missing
SET @tablename = 'room_reservations';
SET @columnname = 'notification_sent';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  "SELECT '✓ notification_sent ya existe en room_reservations' as resultado",
  "ALTER TABLE room_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 AFTER status; SELECT '✓ notification_sent agregado a room_reservations' as resultado"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add notification_sent to table_reservations if missing
SET @tablename = 'table_reservations';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  "SELECT '✓ notification_sent ya existe en table_reservations' as resultado",
  "ALTER TABLE table_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 AFTER status; SELECT '✓ notification_sent agregado a table_reservations' as resultado"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add notification_sent to amenity_reservations if missing
SET @tablename = 'amenity_reservations';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  "SELECT '✓ notification_sent ya existe en amenity_reservations' as resultado",
  "ALTER TABLE amenity_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 AFTER status; SELECT '✓ notification_sent agregado a amenity_reservations' as resultado"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- SECTION 3: UPDATE EXISTING RECORDS
-- ============================================================================

SELECT 'Actualizando registros existentes...' as status;

-- Update room_reservations with hotel_id from rooms
UPDATE room_reservations rr
INNER JOIN rooms r ON rr.room_id = r.id
SET rr.hotel_id = r.hotel_id
WHERE rr.hotel_id IS NULL;
SELECT CONCAT('✓ Actualizadas ', ROW_COUNT(), ' room_reservations con hotel_id') as resultado;

-- Update table_reservations with hotel_id from restaurant_tables
UPDATE table_reservations tr
INNER JOIN restaurant_tables t ON tr.table_id = t.id
SET tr.hotel_id = t.hotel_id
WHERE tr.hotel_id IS NULL;
SELECT CONCAT('✓ Actualizadas ', ROW_COUNT(), ' table_reservations con hotel_id') as resultado;

-- Mark existing reservations as notified to avoid duplicate notifications
UPDATE room_reservations SET notification_sent = 1 WHERE notification_sent = 0;
UPDATE table_reservations SET notification_sent = 1 WHERE notification_sent = 0;
UPDATE amenity_reservations SET notification_sent = 1 WHERE notification_sent = 0;
SELECT '✓ Reservaciones existentes marcadas como notificadas' as resultado;

-- ============================================================================
-- SECTION 4: ADD INDEXES (Safe - checks before adding)
-- ============================================================================

SELECT 'Verificando y agregando índices...' as status;

-- Add index for hotel_id in room_reservations if not exists
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'room_reservations' 
               AND index_name = 'idx_hotel_room');
SET @sqlstmt := IF(@exist > 0, 
    'SELECT "✓ idx_hotel_room ya existe" as resultado', 
    'CREATE INDEX idx_hotel_room ON room_reservations(hotel_id); SELECT "✓ idx_hotel_room creado" as resultado');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for hotel_id in table_reservations if not exists
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'table_reservations' 
               AND index_name = 'idx_hotel_table');
SET @sqlstmt := IF(@exist > 0, 
    'SELECT "✓ idx_hotel_table ya existe" as resultado', 
    'CREATE INDEX idx_hotel_table ON table_reservations(hotel_id); SELECT "✓ idx_hotel_table creado" as resultado');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SECTION 5: RECREATE TRIGGERS (Always drop and recreate)
-- ============================================================================

SELECT 'Recreando triggers corregidos...' as status;

-- Drop existing triggers
DROP TRIGGER IF EXISTS trg_notify_new_room_reservation;
DROP TRIGGER IF EXISTS trg_notify_new_table_reservation;
DROP TRIGGER IF EXISTS trg_amenity_reservation_notification;

SELECT '✓ Triggers anteriores eliminados' as resultado;

-- Create trigger for room reservations (WITHOUT UPDATE statement)
DELIMITER $$
CREATE TRIGGER trg_notify_new_room_reservation
AFTER INSERT ON room_reservations
FOR EACH ROW
BEGIN
    DECLARE v_hotel_id INT;
    DECLARE v_room_number VARCHAR(20);
    
    SELECT hotel_id, room_number INTO v_hotel_id, v_room_number
    FROM rooms
    WHERE id = NEW.room_id;
    
    INSERT INTO system_notifications (
        hotel_id, user_id, notification_type, related_type, related_id, 
        title, message, requires_sound, priority
    )
    SELECT 
        v_hotel_id, u.id, 'new_reservation_room', 'room_reservation', NEW.id,
        'Nueva Reservación de Habitación',
        CONCAT('Nueva reservación para habitación ', v_room_number, 
               ' - Check-in: ', DATE_FORMAT(NEW.check_in, '%d/%m/%Y')),
        1, 'high'
    FROM users u
    WHERE u.hotel_id = v_hotel_id 
    AND u.role IN ('admin', 'manager')
    AND u.is_active = 1;
END$$
DELIMITER ;

SELECT '✓ trg_notify_new_room_reservation creado' as resultado;

-- Create trigger for table reservations (WITHOUT UPDATE statement)
DELIMITER $$
CREATE TRIGGER trg_notify_new_table_reservation
AFTER INSERT ON table_reservations
FOR EACH ROW
BEGIN
    DECLARE v_hotel_id INT;
    DECLARE v_table_number VARCHAR(20);
    
    SELECT hotel_id, table_number INTO v_hotel_id, v_table_number
    FROM restaurant_tables
    WHERE id = NEW.table_id;
    
    INSERT INTO system_notifications (
        hotel_id, user_id, notification_type, related_type, related_id, 
        title, message, requires_sound, priority
    )
    SELECT 
        v_hotel_id, u.id, 'new_reservation_table', 'table_reservation', NEW.id,
        'Nueva Reservación de Mesa',
        CONCAT('Nueva reservación para mesa ', v_table_number, 
               ' - Fecha: ', DATE_FORMAT(NEW.reservation_date, '%d/%m/%Y'), 
               ' ', TIME_FORMAT(NEW.reservation_time, '%H:%i')),
        1, 'high'
    FROM users u
    WHERE u.hotel_id = v_hotel_id 
    AND u.role IN ('admin', 'manager', 'hostess')
    AND u.is_active = 1;
END$$
DELIMITER ;

SELECT '✓ trg_notify_new_table_reservation creado' as resultado;

-- Create trigger for amenity reservations (FIXED: amenity_ids instead of amenities_access)
DELIMITER $$
CREATE TRIGGER trg_amenity_reservation_notification
AFTER INSERT ON amenity_reservations
FOR EACH ROW
BEGIN
    DECLARE v_amenity_name VARCHAR(255);
    
    SELECT name INTO v_amenity_name FROM amenities WHERE id = NEW.amenity_id;
    
    -- Notify admin and managers (full access)
    INSERT INTO system_notifications (
        hotel_id, user_id, notification_type, related_type, related_id,
        title, message, priority, requires_sound
    )
    SELECT 
        NEW.hotel_id, u.id, 'amenity_request', 'amenity_reservation', NEW.id,
        'Nueva Reservación de Amenidad',
        CONCAT('Reservación de ', v_amenity_name, ' para ', NEW.guest_name, 
               ' el ', DATE_FORMAT(NEW.reservation_date, '%d/%m/%Y'), 
               ' a las ', TIME_FORMAT(NEW.reservation_time, '%H:%i')),
        'high', 1
    FROM users u
    WHERE u.hotel_id = NEW.hotel_id
    AND u.role IN ('admin', 'manager')
    AND u.is_active = 1;
    
    -- Notify collaborators with specific amenity access
    INSERT INTO system_notifications (
        hotel_id, user_id, notification_type, related_type, related_id,
        title, message, priority, requires_sound
    )
    SELECT 
        NEW.hotel_id, rp.user_id, 'amenity_request', 'amenity_reservation', NEW.id,
        'Nueva Reservación de Amenidad',
        CONCAT('Reservación de ', v_amenity_name, ' para ', NEW.guest_name, 
               ' el ', DATE_FORMAT(NEW.reservation_date, '%d/%m/%Y'), 
               ' a las ', TIME_FORMAT(NEW.reservation_time, '%H:%i')),
        'high', 1
    FROM role_permissions rp
    INNER JOIN users u ON rp.user_id = u.id
    WHERE rp.hotel_id = NEW.hotel_id
    AND u.is_active = 1
    AND u.role = 'collaborator'
    AND (
        rp.amenity_ids = 'all'
        OR rp.amenity_ids IS NULL
        OR rp.amenity_ids LIKE CONCAT('%', NEW.amenity_id, '%')
    );
END$$
DELIMITER ;

SELECT '✓ trg_amenity_reservation_notification creado' as resultado;

-- ============================================================================
-- SECTION 6: FINAL VERIFICATION
-- ============================================================================

SELECT '=' as separator;
SELECT 'VERIFICACIÓN FINAL' as titulo;
SELECT '=' as separator;

SELECT 
    trigger_name as 'Trigger',
    event_object_table as 'Tabla',
    action_timing as 'Timing'
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND trigger_name IN (
    'trg_notify_new_room_reservation',
    'trg_notify_new_table_reservation',
    'trg_amenity_reservation_notification'
)
ORDER BY trigger_name;

SELECT 
    'room_reservations' as tabla,
    COUNT(*) as total,
    SUM(notification_sent) as notificados
FROM room_reservations
UNION ALL
SELECT 
    'table_reservations' as tabla,
    COUNT(*) as total,
    SUM(notification_sent) as notificados
FROM table_reservations
UNION ALL
SELECT 
    'amenity_reservations' as tabla,
    COUNT(*) as total,
    SUM(IFNULL(notification_sent, 0)) as notificados
FROM amenity_reservations;

-- Restore settings
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================

SELECT '=' as separator;
SELECT '✅ TODOS LOS CAMBIOS APLICADOS EXITOSAMENTE' as resultado;
SELECT '=' as separator;
SELECT 'Los siguientes problemas han sido resueltos:' as mensaje
UNION ALL SELECT '1. Error 1442 en room_reservations - CORREGIDO'
UNION ALL SELECT '2. Error 1442 en table_reservations - CORREGIDO'
UNION ALL SELECT '3. Error amenities_access en amenity trigger - CORREGIDO'
UNION ALL SELECT '4. Columnas necesarias agregadas - COMPLETADO'
UNION ALL SELECT '5. Índices optimizados - COMPLETADO'
UNION ALL SELECT '6. Triggers recreados correctamente - COMPLETADO';

SELECT '=' as separator;
SELECT 'Próximos pasos:' as mensaje
UNION ALL SELECT '1. Actualizar CalendarController.php'
UNION ALL SELECT '2. Probar reservaciones desde chatbot'
UNION ALL SELECT '3. Verificar notificaciones con sonido'
UNION ALL SELECT '4. Verificar calendario muestra eventos';
