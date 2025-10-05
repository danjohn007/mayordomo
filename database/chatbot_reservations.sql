-- Migration: Chatbot Reservations System
-- Date: 2024
-- Description: Adds chatbot support for public hotel reservations with automatic release

-- Create chatbot_reservations table for temporary/pending reservations from chatbot
CREATE TABLE IF NOT EXISTS chatbot_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    resource_type ENUM('room', 'table', 'amenity') NOT NULL,
    resource_id INT NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    guest_email VARCHAR(255) NOT NULL,
    guest_phone VARCHAR(20) NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NULL,
    reservation_date DATETIME NULL COMMENT 'For tables and amenities',
    reservation_time TIME NULL COMMENT 'For tables and amenities',
    notes TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'expired') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    released_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_hotel (hotel_id),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment for documentation
ALTER TABLE chatbot_reservations 
COMMENT = 'Stores public chatbot reservations with automatic expiration';

-- Create stored procedure to check availability
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS check_resource_availability(
    IN p_resource_type VARCHAR(20),
    IN p_resource_id INT,
    IN p_check_in DATE,
    IN p_check_out DATE
)
BEGIN
    DECLARE conflicts INT DEFAULT 0;
    
    IF p_resource_type = 'room' THEN
        -- Check room reservations
        SELECT COUNT(*) INTO conflicts
        FROM room_reservations
        WHERE room_id = p_resource_id
          AND status IN ('confirmed', 'checked_in')
          AND (
              (check_in_date <= p_check_in AND check_out_date > p_check_in)
              OR (check_in_date < p_check_out AND check_out_date >= p_check_out)
              OR (check_in_date >= p_check_in AND check_out_date <= p_check_out)
          );
    ELSEIF p_resource_type = 'table' THEN
        -- Check table reservations (2 hour window)
        SELECT COUNT(*) INTO conflicts
        FROM table_reservations
        WHERE table_id = p_resource_id
          AND status IN ('confirmed', 'seated')
          AND reservation_date = p_check_in
          AND ABS(TIMESTAMPDIFF(MINUTE, reservation_time, TIME(p_check_out))) < 120;
    ELSEIF p_resource_type = 'amenity' THEN
        -- Check amenity reservations (2 hour window)
        SELECT COUNT(*) INTO conflicts
        FROM amenity_reservations
        WHERE amenity_id = p_resource_id
          AND status IN ('confirmed', 'in_use')
          AND reservation_date = p_check_in
          AND ABS(TIMESTAMPDIFF(MINUTE, reservation_time, TIME(p_check_out))) < 120;
    END IF;
    
    -- Return 0 if available, 1 if conflicts exist
    SELECT IF(conflicts > 0, 0, 1) as is_available;
END //

DELIMITER ;

-- Create event to auto-release expired reservations
-- Tables and amenities: release after 2 hours
-- Rooms: release at 15:00 (3 PM) the day after checkout

DELIMITER //

CREATE EVENT IF NOT EXISTS auto_release_table_amenity_reservations
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    -- Release table reservations after 2 hours
    UPDATE table_reservations
    SET status = 'completed'
    WHERE status IN ('confirmed', 'seated')
      AND TIMESTAMPDIFF(HOUR, 
          CONCAT(reservation_date, ' ', reservation_time), 
          NOW()) >= 2;
    
    -- Release amenity reservations after 2 hours
    UPDATE amenity_reservations
    SET status = 'completed'
    WHERE status IN ('confirmed', 'in_use')
      AND TIMESTAMPDIFF(HOUR, 
          CONCAT(reservation_date, ' ', reservation_time), 
          NOW()) >= 2;
END //

DELIMITER ;

DELIMITER //

CREATE EVENT IF NOT EXISTS auto_release_room_reservations
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    -- Release room reservations at 15:00 the day after checkout
    UPDATE room_reservations
    SET status = 'checked_out'
    WHERE status = 'checked_in'
      AND check_out_date < CURDATE()
      AND HOUR(NOW()) >= 15;
END //

DELIMITER ;

-- Enable event scheduler if not already enabled
SET GLOBAL event_scheduler = ON;
