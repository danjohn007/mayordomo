-- Script SQL para agregar columna confirmation_code (PIN) a todas las tablas de reservaciones
-- Si ya existe, no hará nada

-- Para room_reservations
ALTER TABLE `room_reservations` 
ADD COLUMN IF NOT EXISTS `confirmation_code` VARCHAR(50) DEFAULT NULL AFTER `status`;

-- Para table_reservations
ALTER TABLE `table_reservations` 
ADD COLUMN IF NOT EXISTS `confirmation_code` VARCHAR(50) DEFAULT NULL AFTER `status`;

-- Para amenity_reservations
ALTER TABLE `amenity_reservations` 
ADD COLUMN IF NOT EXISTS `confirmation_code` VARCHAR(50) DEFAULT NULL AFTER `status`;

-- Crear índices para búsqueda rápida por PIN
CREATE INDEX IF NOT EXISTS `idx_confirmation_code` ON `room_reservations` (`confirmation_code`);
CREATE INDEX IF NOT EXISTS `idx_confirmation_code` ON `table_reservations` (`confirmation_code`);
CREATE INDEX IF NOT EXISTS `idx_confirmation_code` ON `amenity_reservations` (`confirmation_code`);
