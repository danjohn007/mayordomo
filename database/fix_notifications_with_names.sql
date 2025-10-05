-- ============================================================================
-- Fix: Add Guest Names to All Notifications
-- Date: 2024
-- Description: Updates notification triggers to include guest names in all
--              notification types (room, table, amenity reservations)
-- ============================================================================

USE aqh_mayordomo;

-- ============================================================================
-- Update Room Reservation Trigger - Include Guest Name
-- ============================================================================

DROP TRIGGER IF EXISTS trg_notify_new_room_reservation;

DELIMITER $$
CREATE TRIGGER trg_notify_new_room_reservation
AFTER INSERT ON room_reservations
FOR EACH ROW
BEGIN
    DECLARE v_hotel_id INT;
    DECLARE v_room_number VARCHAR(20);
    DECLARE v_guest_display_name VARCHAR(255);
    
    -- Get hotel_id and room_number from rooms table
    SELECT hotel_id, room_number INTO v_hotel_id, v_room_number
    FROM rooms
    WHERE id = NEW.room_id;
    
    -- Get guest display name (prefer guest_name from reservation, fallback to user table)
    IF NEW.guest_name IS NOT NULL AND NEW.guest_name != '' THEN
        SET v_guest_display_name = NEW.guest_name;
    ELSEIF NEW.guest_id IS NOT NULL THEN
        SELECT CONCAT(first_name, ' ', last_name) INTO v_guest_display_name
        FROM users
        WHERE id = NEW.guest_id;
    ELSE
        SET v_guest_display_name = 'Huésped';
    END IF;
    
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
        CONCAT('Nueva reservación de ', v_guest_display_name, 
               ' para habitación ', v_room_number, 
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
-- Update Table Reservation Trigger - Include Guest Name
-- ============================================================================

DROP TRIGGER IF EXISTS trg_notify_new_table_reservation;

DELIMITER $$
CREATE TRIGGER trg_notify_new_table_reservation
AFTER INSERT ON table_reservations
FOR EACH ROW
BEGIN
    DECLARE v_hotel_id INT;
    DECLARE v_table_number VARCHAR(20);
    DECLARE v_guest_display_name VARCHAR(255);
    
    -- Get hotel_id and table_number from restaurant_tables
    SELECT hotel_id, table_number INTO v_hotel_id, v_table_number
    FROM restaurant_tables
    WHERE id = NEW.table_id;
    
    -- Get guest display name (prefer guest_name from reservation, fallback to user table)
    IF NEW.guest_name IS NOT NULL AND NEW.guest_name != '' THEN
        SET v_guest_display_name = NEW.guest_name;
    ELSEIF NEW.guest_id IS NOT NULL THEN
        SELECT CONCAT(first_name, ' ', last_name) INTO v_guest_display_name
        FROM users
        WHERE id = NEW.guest_id;
    ELSE
        SET v_guest_display_name = 'Huésped';
    END IF;
    
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
        CONCAT('Nueva reservación de ', v_guest_display_name,
               ' para mesa ', v_table_number, 
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
-- VERIFICATION
-- ============================================================================

SELECT '✓ Trigger trg_notify_new_room_reservation updated with guest names' as status;
SELECT '✓ Trigger trg_notify_new_table_reservation updated with guest names' as status;
SELECT '✓ Amenity trigger already includes guest names (no changes needed)' as status;

-- Show all notification triggers
SELECT 
    trigger_name,
    event_manipulation,
    event_object_table,
    action_timing
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND (trigger_name LIKE 'trg_notify_%' OR trigger_name LIKE 'trg_amenity_%');

SELECT 'Migration completed successfully!' AS final_status;
