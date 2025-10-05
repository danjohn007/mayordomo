-- ============================================================================
-- Migration: Fix Chatbot Errors
-- Date: 2024
-- Description: Fixes database issues for chatbot reservations
-- MySQL 5.7+ compatible
-- ============================================================================

-- ============================================================================
-- STEP 1: Make guest_id nullable for anonymous chatbot reservations
-- ============================================================================

-- Make guest_id nullable in room_reservations for chatbot reservations
ALTER TABLE room_reservations
MODIFY COLUMN guest_id INT NULL;

-- Make guest_id nullable in table_reservations for chatbot reservations
ALTER TABLE table_reservations
MODIFY COLUMN guest_id INT NULL;

-- ============================================================================
-- STEP 2: Add hotel_id columns (ignore error if already exists)
-- ============================================================================

-- Add hotel_id to room_reservations
ALTER TABLE room_reservations ADD COLUMN hotel_id INT NULL AFTER id;

-- Add hotel_id to table_reservations
ALTER TABLE table_reservations ADD COLUMN hotel_id INT NULL AFTER id;

-- ============================================================================
-- STEP 3: Update existing records with hotel_id
-- ============================================================================

-- Update room_reservations with hotel_id from rooms
UPDATE room_reservations rr
INNER JOIN rooms r ON rr.room_id = r.id
SET rr.hotel_id = r.hotel_id
WHERE rr.hotel_id IS NULL;

-- Update table_reservations with hotel_id from restaurant_tables
UPDATE table_reservations tr
INNER JOIN restaurant_tables t ON tr.table_id = t.id
SET tr.hotel_id = t.hotel_id
WHERE tr.hotel_id IS NULL;

-- ============================================================================
-- STEP 4: Make hotel_id NOT NULL and add indexes/foreign keys (ignore error if already exists)
-- ============================================================================

-- Make hotel_id NOT NULL in room_reservations
ALTER TABLE room_reservations
MODIFY COLUMN hotel_id INT NOT NULL;

-- Make hotel_id NOT NULL in table_reservations
ALTER TABLE table_reservations
MODIFY COLUMN hotel_id INT NOT NULL;

-- Add index for hotel_id in room_reservations
ALTER TABLE room_reservations ADD INDEX idx_hotel_room (hotel_id);

-- Add index for hotel_id in table_reservations
ALTER TABLE table_reservations ADD INDEX idx_hotel_table (hotel_id);

-- Add foreign key for hotel_id in room_reservations
ALTER TABLE room_reservations ADD CONSTRAINT fk_room_reservations_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE;

-- Add foreign key for hotel_id in table_reservations
ALTER TABLE table_reservations ADD CONSTRAINT fk_table_reservations_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE;

-- ============================================================================
-- VERIFICATION
-- ============================================================================

-- Show the updated structure
SHOW COLUMNS FROM room_reservations;
SHOW COLUMNS FROM table_reservations;

SELECT 'Migration completed successfully!' AS status;
