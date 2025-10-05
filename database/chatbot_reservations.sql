-- Migration: Chatbot Reservations System
-- Date: 2024
-- Description: Adds chatbot support for public hotel reservations with automatic release

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

ALTER TABLE chatbot_reservations 
COMMENT = 'Stores public chatbot reservations with automatic expiration';

DROP PROCEDURE IF EXISTS check_resource_availability;
DELIMITER //

CREATE PROCEDURE check_resource_availability(
    IN p_resource_type VARCHAR(20),
    IN p_resource_id INT,
    IN p_check_in DATE,
    IN p_check_out DATE
)
BEGIN
    DECLARE conflicts INT DEFAULT 0;
    
    IF p_resource_type = 'room' THEN
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
        SELECT COUNT(*) INTO conflicts
        FROM table_reservations
        WHERE table_id = p_resource_id
          AND status IN ('confirmed', 'seated')
          AND reservation_date = p_check_in
          AND ABS(TIMESTAMPDIFF(MINUTE, reservation_time, TIME(p_check_out))) < 120;
    ELSEIF p_resource_type = 'amenity' THEN
        SELECT COUNT(*) INTO conflicts
        FROM amenity_reservations
        WHERE amenity_id = p_resource_id
          AND status IN ('confirmed', 'in_use')
          AND reservation_date = p_check_in
          AND ABS(TIMESTAMPDIFF(MINUTE, reservation_time, TIME(p_check_out))) < 120;
    END IF;
    
    SELECT IF(conflicts > 0, 0, 1) as is_available;
END //

DELIMITER ;

DELIMITER //

CREATE EVENT IF NOT EXISTS auto_release_table_amenity_reservations
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    UPDATE table_reservations
    SET status = 'completed'
    WHERE status IN ('confirmed', 'seated')
      AND TIMESTAMPDIFF(HOUR, 
          CONCAT(reservation_date, ' ', reservation_time), 
          NOW()) >= 2;
    
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
    UPDATE room_reservations
    SET status = 'checked_out'
    WHERE status = 'checked_in'
      AND check_out_date < CURDATE()
      AND HOUR(NOW()) >= 15;
END //

DELIMITER ;
