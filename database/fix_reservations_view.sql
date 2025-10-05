-- ============================================================================
-- Fix Reservations View and Add Auto-blocking for Tables/Amenities
-- ============================================================================

-- Drop existing view if exists
DROP VIEW IF EXISTS v_all_reservations;

-- Create updated view including amenity reservations
CREATE OR REPLACE VIEW v_all_reservations AS
SELECT 
    'room' as reservation_type,
    rr.id,
    rr.status,
    rr.created_at,
    r.hotel_id,
    r.room_number as resource_number,
    rr.check_in as reservation_date,
    NULL as reservation_time,
    rr.guest_id,
    COALESCE(rr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
    COALESCE(rr.guest_email, u.email) as guest_email,
    COALESCE(rr.guest_phone, u.phone) as guest_phone,
    rr.total_price,
    COALESCE(rr.special_requests, rr.notes) as notes,
    rr.notification_sent
FROM room_reservations rr
JOIN rooms r ON rr.room_id = r.id
LEFT JOIN users u ON rr.guest_id = u.id

UNION ALL

SELECT 
    'table' as reservation_type,
    tr.id,
    tr.status,
    tr.created_at,
    rt.hotel_id,
    rt.table_number as resource_number,
    tr.reservation_date,
    tr.reservation_time,
    tr.guest_id,
    COALESCE(tr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
    COALESCE(tr.guest_email, u.email) as guest_email,
    COALESCE(tr.guest_phone, u.phone) as guest_phone,
    NULL as total_price,
    tr.notes,
    tr.notification_sent
FROM table_reservations tr
JOIN restaurant_tables rt ON tr.table_id = rt.id
LEFT JOIN users u ON tr.guest_id = u.id

UNION ALL

SELECT 
    'amenity' as reservation_type,
    ar.id,
    ar.status,
    ar.created_at,
    a.hotel_id,
    a.name as resource_number,
    ar.reservation_date,
    ar.reservation_time,
    ar.user_id as guest_id,
    COALESCE(ar.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
    COALESCE(ar.guest_email, u.email) as guest_email,
    COALESCE(ar.guest_phone, u.phone) as guest_phone,
    NULL as total_price,
    COALESCE(ar.notes, ar.special_requests) as notes,
    ar.notification_sent
FROM amenity_reservations ar
JOIN amenities a ON ar.amenity_id = a.id
LEFT JOIN users u ON ar.user_id = u.id;

-- ============================================================================
-- Add trigger to auto-block tables for 2 hours when confirmed
-- ============================================================================

DROP TRIGGER IF EXISTS trg_block_table_on_confirm;

DELIMITER //
CREATE TRIGGER trg_block_table_on_confirm
AFTER UPDATE ON table_reservations
FOR EACH ROW
BEGIN
    -- If status changed to confirmed, create a 2-hour block
    IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
        -- Calculate end time (2 hours after reservation time)
        SET @end_time = ADDTIME(NEW.reservation_time, '02:00:00');
        
        -- Insert block record if not already exists
        INSERT IGNORE INTO resource_blocks (
            resource_type,
            resource_id,
            blocked_by,
            reason,
            start_date,
            end_date,
            status
        ) VALUES (
            'table',
            NEW.table_id,
            COALESCE(NEW.guest_id, 1), -- Use guest_id or system user
            CONCAT('Reservación confirmada - ', NEW.guest_name),
            TIMESTAMP(NEW.reservation_date, NEW.reservation_time),
            TIMESTAMP(NEW.reservation_date, @end_time),
            'active'
        );
    END IF;
END//
DELIMITER ;

-- ============================================================================
-- Add trigger to auto-block amenities for 2 hours when confirmed
-- ============================================================================

DROP TRIGGER IF EXISTS trg_block_amenity_on_confirm;

DELIMITER //
CREATE TRIGGER trg_block_amenity_on_confirm
AFTER UPDATE ON amenity_reservations
FOR EACH ROW
BEGIN
    -- If status changed to confirmed, create a 2-hour block
    IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
        -- Calculate end time (2 hours after reservation time)
        SET @end_time = ADDTIME(NEW.reservation_time, '02:00:00');
        
        -- Insert block record if not already exists
        INSERT IGNORE INTO resource_blocks (
            resource_type,
            resource_id,
            blocked_by,
            reason,
            start_date,
            end_date,
            status
        ) VALUES (
            'amenity',
            NEW.amenity_id,
            COALESCE(NEW.user_id, 1), -- Use user_id or system user
            CONCAT('Reservación confirmada - ', NEW.guest_name),
            TIMESTAMP(NEW.reservation_date, NEW.reservation_time),
            TIMESTAMP(NEW.reservation_date, @end_time),
            'active'
        );
    END IF;
END//
DELIMITER ;

-- ============================================================================
-- Verification
-- ============================================================================

SELECT 'View v_all_reservations updated successfully' as status;
SELECT 'Triggers for auto-blocking created successfully' as status;
