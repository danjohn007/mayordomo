-- =====================================================
-- ACTUALIZACIÓN DE AMENIDADES Y CONFIGURACIONES
-- =====================================================
-- Este script agrega los nuevos campos y configuraciones
-- para el sistema de reservaciones mejorado
-- =====================================================

-- 1. Agregar nuevos campos a la tabla amenities
ALTER TABLE amenities 
ADD COLUMN allow_overlap TINYINT(1) DEFAULT 1 COMMENT 'Permitir empalmar con mismo horario y fecha',
ADD COLUMN max_reservations INT DEFAULT NULL COMMENT 'Capacidad máxima de reservaciones cuando allow_overlap=0',
ADD COLUMN block_duration_hours DECIMAL(4,2) DEFAULT 2.00 COMMENT 'Horas de bloqueo por reservación';

-- 2. Actualizar la configuración existente de 'allow_reservation_overlap' 
-- para que sea específica de mesas
-- Primero, obtenemos los hoteles y sus configuraciones actuales
UPDATE hotel_settings 
SET setting_key = 'allow_table_overlap'
WHERE setting_key = 'allow_reservation_overlap';

-- 3. Insertar la nueva configuración para mesas con valor por defecto activado
INSERT INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'allow_table_overlap', '1', 'boolean', 'reservations'
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM hotel_settings hs 
    WHERE hs.hotel_id = h.id 
    AND hs.setting_key = 'allow_table_overlap'
);

-- 4. Insertar la nueva configuración para habitaciones con valor por defecto desactivado
INSERT INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
SELECT h.id, 'allow_room_overlap', '0', 'boolean', 'reservations'
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM hotel_settings hs 
    WHERE hs.hotel_id = h.id 
    AND hs.setting_key = 'allow_room_overlap'
);

-- 5. Verificación de los cambios
SELECT 
    'Campos agregados a amenities' as verificacion,
    COUNT(*) as total_amenities
FROM amenities;

SELECT 
    'Configuraciones de hoteles' as verificacion,
    setting_key,
    COUNT(*) as total_hoteles,
    GROUP_CONCAT(DISTINCT setting_value) as valores
FROM hotel_settings
WHERE setting_key IN ('allow_table_overlap', 'allow_room_overlap')
GROUP BY setting_key;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
