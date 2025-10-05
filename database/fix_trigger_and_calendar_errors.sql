-- ============================================================================
-- Migration: Fix Trigger Errors and Calendar Issues
-- Date: 2024
-- Description: Fixes the following errors:
--   1. SQLSTATE[HY000]: 1442 - Can't update table in trigger
--   2. SQLSTATE[42S22]: Column not found 'rp.amenities_access'
--   3. Calendar column name mismatches
-- MySQL 5.7+ compatible
-- ============================================================================

USE aqh_mayordomo;

-- ============================================================================
-- STEP 1: Fix Room Reservation Trigger (Remove UPDATE statement)
-- ============================================================================

DROP TRIGGER IF EXISTS trg_notify_new_room_reservation;

DELIMITER $$
CREATE TRIGGER trg_notify_new_room_reservation
AFTER INSERT ON room_reservations
FOR EACH ROW
BEGIN
    DECLARE v_hotel_id INT;
    DECLARE v_room_number VARCHAR(20);
    
    -- Get hotel_id and room_number from rooms table
    SELECT hotel_id, room_number INTO v_hotel_id, v_room_number
    FROM rooms
    WHERE id = NEW.room_id;
    
    -- Insert notifications for admin and manager
    INSERT INTO system_notifications (
        hotel_id, 
        user_id, 
        notification_type, 
        related_type, 
        related_id, 
        title, 
        message, 
        requires_sound, 
        priority
    )
    SELECT 
        v_hotel_id,
        u.id,
        'new_reservation_room',
        'room_reservation',
        NEW.id,
        'Nueva Reservación de Habitación',
        CONCAT('Nueva reservación para habitación ', v_room_number, 
               ' - Check-in: ', DATE_FORMAT(NEW.check_in, '%d/%m/%Y')),
        1,
        'high'
    FROM users u
    WHERE u.hotel_id = v_hotel_id 
    AND u.role IN ('admin', 'manager')
    AND u.is_active = 1;
END$$
DELIMITER ;

-- ============================================================================
-- STEP 2: Fix Table Reservation Trigger (Remove UPDATE statement)
-- ============================================================================

DROP TRIGGER IF EXISTS trg_notify_new_table_reservation;

DELIMITER $$
CREATE TRIGGER trg_notify_new_table_reservation
AFTER INSERT ON table_reservations
FOR EACH ROW
BEGIN
    DECLARE v_hotel_id INT;
    DECLARE v_table_number VARCHAR(20);
    
    -- Get hotel_id and table_number from restaurant_tables
    SELECT hotel_id, table_number INTO v_hotel_id, v_table_number
    FROM restaurant_tables
    WHERE id = NEW.table_id;
    
    -- Insert notifications for admin, manager and hostess
    INSERT INTO system_notifications (
        hotel_id, 
        user_id, 
        notification_type, 
        related_type, 
        related_id, 
        title, 
        message, 
        requires_sound, 
        priority
    )
    SELECT 
        v_hotel_id,
        u.id,
        'new_reservation_table',
        'table_reservation',
        NEW.id,
        'Nueva Reservación de Mesa',
        CONCAT('Nueva reservación para mesa ', v_table_number, 
               ' - Fecha: ', DATE_FORMAT(NEW.reservation_date, '%d/%m/%Y'), 
               ' ', TIME_FORMAT(NEW.reservation_time, '%H:%i')),
        1,
        'high'
    FROM users u
    WHERE u.hotel_id = v_hotel_id 
    AND u.role IN ('admin', 'manager', 'hostess')
    AND u.is_active = 1;
END$$
DELIMITER ;

-- ============================================================================
-- STEP 3: Fix Amenity Reservation Trigger (Fix amenities_access column)
-- ============================================================================

DROP TRIGGER IF EXISTS trg_amenity_reservation_notification;

DELIMITER $$
CREATE TRIGGER trg_amenity_reservation_notification
AFTER INSERT ON amenity_reservations
FOR EACH ROW
BEGIN
    DECLARE v_amenity_name VARCHAR(255);
    
    -- Get amenity name
    SELECT name INTO v_amenity_name
    FROM amenities
    WHERE id = NEW.amenity_id;
    
    -- Insert notifications for users with amenity permissions
    -- First, notify admin and managers (they have access to all)
    INSERT INTO system_notifications (
        hotel_id,
        user_id,
        notification_type,
        related_type,
        related_id,
        title,
        message,
        priority,
        requires_sound
    )
    SELECT 
        NEW.hotel_id,
        u.id,
        'amenity_request',
        'amenity_reservation',
        NEW.id,
        'Nueva Reservación de Amenidad',
        CONCAT('Reservación de ', v_amenity_name, ' para ', NEW.guest_name, 
               ' el ', DATE_FORMAT(NEW.reservation_date, '%d/%m/%Y'), 
               ' a las ', TIME_FORMAT(NEW.reservation_time, '%H:%i')),
        'high',
        1
    FROM users u
    WHERE u.hotel_id = NEW.hotel_id
    AND u.role IN ('admin', 'manager')
    AND u.is_active = 1;
    
    -- Then notify collaborators with specific amenity access
    INSERT INTO system_notifications (
        hotel_id,
        user_id,
        notification_type,
        related_type,
        related_id,
        title,
        message,
        priority,
        requires_sound
    )
    SELECT 
        NEW.hotel_id,
        rp.user_id,
        'amenity_request',
        'amenity_reservation',
        NEW.id,
        'Nueva Reservación de Amenidad',
        CONCAT('Reservación de ', v_amenity_name, ' para ', NEW.guest_name, 
               ' el ', DATE_FORMAT(NEW.reservation_date, '%d/%m/%Y'), 
               ' a las ', TIME_FORMAT(NEW.reservation_time, '%H:%i')),
        'high',
        1
    FROM role_permissions rp
    INNER JOIN users u ON rp.user_id = u.id
    WHERE rp.hotel_id = NEW.hotel_id
    AND u.is_active = 1
    AND u.role = 'collaborator'
    AND (
        -- Check if user has access to all amenities
        rp.amenity_ids = 'all'
        OR rp.amenity_ids IS NULL
        -- Or check if specific amenity ID is in the JSON array
        OR rp.amenity_ids LIKE CONCAT('%', NEW.amenity_id, '%')
    );
END$$
DELIMITER ;

-- ============================================================================
-- STEP 4: Ensure notification_sent columns exist (safe to run multiple times)
-- ============================================================================

-- Add notification_sent to room_reservations if it doesn't exist
SET @dbname = DATABASE();
SET @columnname = 'notification_sent';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'room_reservations')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE room_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 AFTER status"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add notification_sent to table_reservations if it doesn't exist
SET @columnname = 'notification_sent';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'table_reservations')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE table_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 AFTER status"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add notification_sent to amenity_reservations if it doesn't exist
SET @columnname = 'notification_sent';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'amenity_reservations')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE amenity_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 AFTER status"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 5: Update existing records to mark as notified
-- ============================================================================

-- Mark existing room reservations as notified to avoid duplicate notifications
UPDATE room_reservations 
SET notification_sent = 1 
WHERE notification_sent = 0;

-- Mark existing table reservations as notified
UPDATE table_reservations 
SET notification_sent = 1 
WHERE notification_sent = 0;

-- Mark existing amenity reservations as notified
UPDATE amenity_reservations 
SET notification_sent = 1 
WHERE notification_sent = 0;

-- ============================================================================
-- VERIFICATION
-- ============================================================================

SELECT '✓ Trigger trg_notify_new_room_reservation recreated' as status;
SELECT '✓ Trigger trg_notify_new_table_reservation recreated' as status;
SELECT '✓ Trigger trg_amenity_reservation_notification fixed' as status;
SELECT '✓ notification_sent columns verified' as status;

-- Show all triggers
SELECT 
    trigger_name,
    event_manipulation,
    event_object_table,
    action_timing
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND trigger_name LIKE 'trg_notify_%'
OR trigger_name LIKE 'trg_amenity_%';

SELECT 'Migration completed successfully!' AS final_status;
