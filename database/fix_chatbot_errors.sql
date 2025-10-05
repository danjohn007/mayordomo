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
-- STEP 2: Add hotel_id columns
-- ============================================================================

-- Check if hotel_id exists in room_reservations, add if not
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'room_reservations' 
  AND column_name = 'hotel_id';

SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE room_reservations ADD COLUMN hotel_id INT NULL AFTER id',
  'SELECT ''hotel_id already exists in room_reservations'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if hotel_id exists in table_reservations, add if not
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'table_reservations' 
  AND column_name = 'hotel_id';

SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE table_reservations ADD COLUMN hotel_id INT NULL AFTER id',
  'SELECT ''hotel_id already exists in table_reservations'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

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
-- STEP 4: Make hotel_id NOT NULL and add indexes/foreign keys
-- ============================================================================

-- Make hotel_id NOT NULL in room_reservations
ALTER TABLE room_reservations
MODIFY COLUMN hotel_id INT NOT NULL;

-- Make hotel_id NOT NULL in table_reservations
ALTER TABLE table_reservations
MODIFY COLUMN hotel_id INT NOT NULL;

-- Add indexes if they don't exist
SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND table_name = 'room_reservations'
  AND index_name = 'idx_hotel_room';

SET @sql = IF(@index_exists = 0,
  'ALTER TABLE room_reservations ADD INDEX idx_hotel_room (hotel_id)',
  'SELECT ''Index idx_hotel_room already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND table_name = 'table_reservations'
  AND index_name = 'idx_hotel_table';

SET @sql = IF(@index_exists = 0,
  'ALTER TABLE table_reservations ADD INDEX idx_hotel_table (hotel_id)',
  'SELECT ''Index idx_hotel_table already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign keys if they don't exist
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM information_schema.table_constraints
WHERE table_schema = DATABASE()
  AND table_name = 'room_reservations'
  AND constraint_name = 'fk_room_reservations_hotel';

SET @sql = IF(@fk_exists = 0,
  'ALTER TABLE room_reservations ADD CONSTRAINT fk_room_reservations_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE',
  'SELECT ''Foreign key fk_room_reservations_hotel already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM information_schema.table_constraints
WHERE table_schema = DATABASE()
  AND table_name = 'table_reservations'
  AND constraint_name = 'fk_table_reservations_hotel';

SET @sql = IF(@fk_exists = 0,
  'ALTER TABLE table_reservations ADD CONSTRAINT fk_table_reservations_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE',
  'SELECT ''Foreign key fk_table_reservations_hotel already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- VERIFICATION
-- ============================================================================

-- Show the updated structure
SHOW COLUMNS FROM room_reservations;
SHOW COLUMNS FROM table_reservations;

SELECT 'Migration completed successfully!' AS status;
