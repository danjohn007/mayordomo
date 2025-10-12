-- ================================================================
-- Migration: Add Daily Pricing to Rooms
-- Date: 2025-10-12
-- Description: Add price fields for each day of the week to rooms table
-- ================================================================

-- Add daily price columns to rooms table
ALTER TABLE rooms
ADD COLUMN price_monday DECIMAL(10, 2) DEFAULT NULL COMMENT 'Precio para lunes',
ADD COLUMN price_tuesday DECIMAL(10, 2) DEFAULT NULL COMMENT 'Precio para martes',
ADD COLUMN price_wednesday DECIMAL(10, 2) DEFAULT NULL COMMENT 'Precio para miércoles',
ADD COLUMN price_thursday DECIMAL(10, 2) DEFAULT NULL COMMENT 'Precio para jueves',
ADD COLUMN price_friday DECIMAL(10, 2) DEFAULT NULL COMMENT 'Precio para viernes',
ADD COLUMN price_saturday DECIMAL(10, 2) DEFAULT NULL COMMENT 'Precio para sábado',
ADD COLUMN price_sunday DECIMAL(10, 2) DEFAULT NULL COMMENT 'Precio para domingo';

-- Update existing rooms to use default price for all days
-- This ensures backward compatibility
UPDATE rooms 
SET 
    price_monday = price,
    price_tuesday = price,
    price_wednesday = price,
    price_thursday = price,
    price_friday = price,
    price_saturday = price,
    price_sunday = price
WHERE price IS NOT NULL;

-- Note: The 'price' column is kept for backward compatibility and as default/fallback price
-- When daily prices are not set, the system will use the 'price' column

-- Verification query
SELECT 
    'Daily pricing columns added successfully' AS status,
    COUNT(*) AS total_rooms,
    COUNT(CASE WHEN price_monday IS NOT NULL THEN 1 END) AS rooms_with_monday_price,
    COUNT(CASE WHEN price_sunday IS NOT NULL THEN 1 END) AS rooms_with_sunday_price
FROM rooms;
