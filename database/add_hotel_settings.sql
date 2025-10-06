-- ============================================================================
-- Add hotel_settings table if not exists
-- This table stores hotel-specific settings like reservation overlap permission
-- ============================================================================

USE aqh_mayordomo;

-- Create hotel_settings table if not exists
CREATE TABLE IF NOT EXISTS hotel_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    category VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_hotel_setting (hotel_id, setting_key),
    INDEX idx_hotel (hotel_id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default setting for allow_reservation_overlap (disabled by default)
-- This will only insert if the hotel doesn't have this setting yet
INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT id, 'allow_reservation_overlap', '0', 'boolean', 'reservations'
FROM hotels
WHERE is_active = 1;

-- Verification query
SELECT 
    h.id as hotel_id,
    h.name as hotel_name,
    hs.setting_key,
    hs.setting_value,
    hs.setting_type
FROM hotels h
LEFT JOIN hotel_settings hs ON h.id = hs.hotel_id AND hs.setting_key = 'allow_reservation_overlap'
WHERE h.is_active = 1;
