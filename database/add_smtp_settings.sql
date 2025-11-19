-- ============================================================================
-- Add SMTP Configuration Settings
-- This migration adds SMTP email configuration fields to hotel_settings
-- ============================================================================

USE aqh_mayordomo;

-- Add SMTP settings to hotel_settings for each hotel
-- These settings will be used to configure email notifications

-- Insert default SMTP settings for existing hotels
INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'smtp_enabled', '1', 'boolean', 'email'
FROM hotels h
WHERE h.is_active = 1;

INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'smtp_host', 'ranchoparaisoreal.com', 'string', 'email'
FROM hotels h
WHERE h.is_active = 1;

INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'smtp_port', '465', 'number', 'email'
FROM hotels h
WHERE h.is_active = 1;

INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'smtp_username', 'reservaciones@ranchoparaisoreal.com', 'string', 'email'
FROM hotels h
WHERE h.is_active = 1;

INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'smtp_password', 'Danjohn007!', 'string', 'email'
FROM hotels h
WHERE h.is_active = 1;

INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'smtp_encryption', 'ssl', 'string', 'email'
FROM hotels h
WHERE h.is_active = 1;

INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'smtp_from_email', 'reservaciones@ranchoparaisoreal.com', 'string', 'email'
FROM hotels h
WHERE h.is_active = 1;

INSERT IGNORE INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'smtp_from_name', 'Rancho Paraíso Real - Reservaciones', 'string', 'email'
FROM hotels h
WHERE h.is_active = 1;

-- Verification query
SELECT 
    h.id as hotel_id,
    h.name as hotel_name,
    hs.setting_key,
    CASE 
        WHEN hs.setting_key = 'smtp_password' THEN '********'
        ELSE hs.setting_value
    END as setting_value,
    hs.setting_type,
    hs.category
FROM hotels h
LEFT JOIN hotel_settings hs ON h.id = hs.hotel_id AND hs.category = 'email'
WHERE h.is_active = 1
ORDER BY h.id, hs.setting_key;
