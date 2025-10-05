-- Migration: Add Image Support for Resources
-- Date: 2024
-- Description: Adds support for multiple images for rooms, tables, and amenities

-- Create resource_images table
CREATE TABLE IF NOT EXISTS resource_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type ENUM('room', 'table', 'amenity') NOT NULL,
    resource_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment for documentation
ALTER TABLE resource_images 
COMMENT = 'Stores images for rooms, tables, and amenities. Multiple images per resource allowed.';
