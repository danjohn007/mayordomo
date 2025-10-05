-- Migration: Add Calendar Support
-- Date: 2024
-- Description: Adds amenity_reservations table for complete calendar functionality

-- Create amenity_reservations table if it doesn't exist
CREATE TABLE IF NOT EXISTS amenity_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    amenity_id INT NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    guest_email VARCHAR(255) NULL,
    guest_phone VARCHAR(20) NULL,
    user_id INT NULL COMMENT 'If reserved by logged-in user',
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    duration INT DEFAULT 60 COMMENT 'Duration in minutes',
    party_size INT DEFAULT 1,
    status ENUM('pending', 'confirmed', 'in_use', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    notes TEXT,
    special_requests TEXT,
    confirmation_code VARCHAR(50),
    notification_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_hotel (hotel_id),
    INDEX idx_amenity (amenity_id),
    INDEX idx_reservation_date (reservation_date),
    INDEX idx_status (status),
    INDEX idx_confirmation (confirmation_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT = 'Stores reservations for hotel amenities (gym, pool, spa, etc)';

-- Add trigger to generate confirmation code for amenity reservations
DROP TRIGGER IF EXISTS trg_amenity_reservation_confirmation;

DELIMITER //
CREATE TRIGGER trg_amenity_reservation_confirmation
BEFORE INSERT ON amenity_reservations
FOR EACH ROW
BEGIN
    IF NEW.confirmation_code IS NULL OR NEW.confirmation_code = '' THEN
        SET NEW.confirmation_code = CONCAT('AMN-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 10000), 4, '0'));
    END IF;
END//
DELIMITER ;

-- Add trigger to notify staff when amenity reservation is created
DROP TRIGGER IF EXISTS trg_amenity_reservation_notification;

DELIMITER //
CREATE TRIGGER trg_amenity_reservation_notification
AFTER INSERT ON amenity_reservations
FOR EACH ROW
BEGIN
    -- Only send notification if system_notifications table exists
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'system_notifications') THEN
        -- Insert notification for users with amenity permissions
        INSERT INTO system_notifications (
            hotel_id,
            user_id,
            notification_type,
            title,
            message,
            priority,
            requires_sound,
            related_id
        )
        SELECT 
            NEW.hotel_id,
            rp.user_id,
            'amenity_reservation',
            'Nueva Reservación de Amenidad',
            CONCAT('Reservación de ', (SELECT name FROM amenities WHERE id = NEW.amenity_id), ' para ', NEW.guest_name, ' el ', DATE_FORMAT(NEW.reservation_date, '%d/%m/%Y'), ' a las ', TIME_FORMAT(NEW.reservation_time, '%H:%i')),
            'high',
            1,
            NEW.id
        FROM role_permissions rp
        WHERE rp.hotel_id = NEW.hotel_id
        AND (
            JSON_CONTAINS(rp.amenities_access, CAST(NEW.amenity_id AS JSON), '$')
            OR rp.amenities_access = 'all'
        );
        
        -- Update notification_sent flag
        UPDATE amenity_reservations SET notification_sent = 1 WHERE id = NEW.id;
    END IF;
END//
DELIMITER ;

-- Verify installation
SELECT 
    'amenity_reservations table created' as status,
    COUNT(*) as record_count 
FROM amenity_reservations;

SELECT 
    'Triggers created' as status,
    COUNT(*) as trigger_count
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND trigger_name IN ('trg_amenity_reservation_confirmation', 'trg_amenity_reservation_notification');
