-- ====================================================================
-- Script de Migración: Actualización de Reservaciones y Solicitudes de Servicio
-- Fecha: 2025-10-10
-- Descripción: 
--   - Asegura que service_type_id esté configurado en service_requests
--   - Asegura que assigned_to se establezca por defecto al creador
--   - Verifica que las tablas de reservaciones tengan hotel_id
--   - Mantiene la funcionalidad existente intacta
-- ====================================================================

-- ====================================================================
-- PASO 1: Verificar y crear tabla service_type_catalog si no existe
-- ====================================================================

CREATE TABLE IF NOT EXISTS service_type_catalog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'bi-wrench',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_hotel (hotel_id),
    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- PASO 2: Insertar tipos de servicio predeterminados si no existen
-- ====================================================================

-- Solo insertar si no hay tipos de servicio para el hotel
INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id as hotel_id,
    'Toallas' as name,
    'Solicitud de toallas adicionales' as description,
    'bi-droplet' as icon,
    1 as sort_order
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Toallas'
);

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT h.id, 'Menú / Room Service', 'Solicitud de servicio a la habitación', 'bi-egg-fried', 2
FROM hotels h
WHERE NOT EXISTS (SELECT 1 FROM service_type_catalog stc WHERE stc.hotel_id = h.id AND stc.name = 'Menú / Room Service');

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT h.id, 'Conserje', 'Solicitud de asistencia del conserje', 'bi-person-badge', 3
FROM hotels h
WHERE NOT EXISTS (SELECT 1 FROM service_type_catalog stc WHERE stc.hotel_id = h.id AND stc.name = 'Conserje');

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT h.id, 'Limpieza', 'Solicitud de servicio de limpieza', 'bi-brush', 4
FROM hotels h
WHERE NOT EXISTS (SELECT 1 FROM service_type_catalog stc WHERE stc.hotel_id = h.id AND stc.name = 'Limpieza');

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT h.id, 'Mantenimiento', 'Reporte de problema técnico o mantenimiento', 'bi-tools', 5
FROM hotels h
WHERE NOT EXISTS (SELECT 1 FROM service_type_catalog stc WHERE stc.hotel_id = h.id AND stc.name = 'Mantenimiento');

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT h.id, 'Amenidades', 'Solicitud relacionada con amenidades del hotel', 'bi-spa', 6
FROM hotels h
WHERE NOT EXISTS (SELECT 1 FROM service_type_catalog stc WHERE stc.hotel_id = h.id AND stc.name = 'Amenidades');

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT h.id, 'Transporte', 'Solicitud de servicio de transporte', 'bi-car-front', 7
FROM hotels h
WHERE NOT EXISTS (SELECT 1 FROM service_type_catalog stc WHERE stc.hotel_id = h.id AND stc.name = 'Transporte');

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT h.id, 'Otro', 'Otras solicitudes de servicio', 'bi-question-circle', 99
FROM hotels h
WHERE NOT EXISTS (SELECT 1 FROM service_type_catalog stc WHERE stc.hotel_id = h.id AND stc.name = 'Otro');

-- ====================================================================
-- PASO 3: Agregar columnas a service_requests si no existen
-- ====================================================================

-- Verificar y agregar service_type_id si no existe
SET @dbname = DATABASE();
SET @tablename = 'service_requests';
SET @columnname = 'service_type_id';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
    "SELECT '✓ service_type_id ya existe en service_requests' as resultado",
    CONCAT("ALTER TABLE service_requests ADD COLUMN service_type_id INT NULL AFTER title; ",
           "ALTER TABLE service_requests ADD CONSTRAINT fk_service_type FOREIGN KEY (service_type_id) REFERENCES service_type_catalog(id) ON DELETE SET NULL; ",
           "ALTER TABLE service_requests ADD INDEX idx_service_type (service_type_id); ",
           "SELECT '✓ service_type_id agregado a service_requests' as resultado")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ====================================================================
-- PASO 4: Migrar datos existentes sin service_type_id
-- ====================================================================

-- Asignar tipo "Otro" a solicitudes sin tipo
UPDATE service_requests sr
JOIN service_type_catalog stc ON sr.hotel_id = stc.hotel_id
SET sr.service_type_id = stc.id
WHERE sr.service_type_id IS NULL
AND stc.name = 'Otro';

-- ====================================================================
-- PASO 5: Verificar tablas de reservaciones tienen hotel_id
-- ====================================================================

-- Agregar hotel_id a room_reservations si no existe
SET @tablename = 'room_reservations';
SET @columnname = 'hotel_id';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
    "SELECT '✓ hotel_id ya existe en room_reservations' as resultado",
    "ALTER TABLE room_reservations ADD COLUMN hotel_id INT NULL AFTER id, ADD INDEX idx_hotel_id (hotel_id); SELECT '✓ hotel_id agregado a room_reservations' as resultado"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Actualizar hotel_id en room_reservations si es NULL
UPDATE room_reservations rr
JOIN rooms r ON rr.room_id = r.id
SET rr.hotel_id = r.hotel_id
WHERE rr.hotel_id IS NULL;

-- Agregar hotel_id a table_reservations si no existe
SET @tablename = 'table_reservations';
SET @columnname = 'hotel_id';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
    "SELECT '✓ hotel_id ya existe en table_reservations' as resultado",
    "ALTER TABLE table_reservations ADD COLUMN hotel_id INT NULL AFTER id, ADD INDEX idx_hotel_id (hotel_id); SELECT '✓ hotel_id agregado a table_reservations' as resultado"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Actualizar hotel_id en table_reservations si es NULL
UPDATE table_reservations tr
JOIN restaurant_tables rt ON tr.table_id = rt.id
SET tr.hotel_id = rt.hotel_id
WHERE tr.hotel_id IS NULL;

-- Agregar hotel_id a amenity_reservations si no existe y la tabla existe
SET @tablename = 'amenity_reservations';
SET @columnname = 'hotel_id';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
     WHERE table_schema = @dbname AND table_name = @tablename) = 0,
    "SELECT '⚠ amenity_reservations no existe, se omite' as resultado",
    IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
       "SELECT '✓ hotel_id ya existe en amenity_reservations' as resultado",
       "ALTER TABLE amenity_reservations ADD INDEX idx_hotel_id (hotel_id); SELECT '✓ índice agregado a amenity_reservations' as resultado"
    )
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ====================================================================
-- PASO 6: Verificación de cambios
-- ====================================================================

-- Mostrar resumen
SELECT 'Resumen de Migración' as '============================';

SELECT 
    'service_type_catalog' as tabla,
    COUNT(*) as total_registros
FROM service_type_catalog
UNION ALL
SELECT 
    'service_requests con tipo' as tabla,
    COUNT(*) as total_registros
FROM service_requests
WHERE service_type_id IS NOT NULL
UNION ALL
SELECT 
    'service_requests sin tipo' as tabla,
    COUNT(*) as total_registros
FROM service_requests
WHERE service_type_id IS NULL;

-- ====================================================================
-- NOTAS IMPORTANTES:
-- ====================================================================
-- 1. La columna 'title' en service_requests se mantiene para descripción adicional
-- 2. El campo 'service_type_id' puede ser NULL para mantener compatibilidad
-- 3. El campo 'assigned_to' debe establecerse automáticamente al usuario creador
--    (esto se maneja en la capa de aplicación PHP)
-- 4. Admin, Manager y Hostess pueden crear reservaciones
-- 5. Las reservaciones bloquean automáticamente el recurso según las reglas:
--    - Habitaciones: bloqueo por rango de fechas (check-in a check-out)
--    - Mesas: bloqueo de 2 horas desde la hora de reservación
--    - Amenidades: bloqueo de 2 horas desde la hora de reservación
-- ====================================================================

SELECT '✓ Migración completada exitosamente' as resultado;
