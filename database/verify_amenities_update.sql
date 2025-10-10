-- =====================================================
-- VERIFICACIÓN DE ACTUALIZACIÓN DE AMENIDADES
-- =====================================================
-- Este script verifica que los cambios se aplicaron correctamente
-- =====================================================

-- 1. Verificar estructura de la tabla amenities
SELECT 
    'Verificación de campos en amenities' as verificacion,
    COLUMN_NAME as campo,
    DATA_TYPE as tipo,
    COLUMN_DEFAULT as valor_default,
    IS_NULLABLE as permite_null
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'amenities'
  AND COLUMN_NAME IN ('allow_overlap', 'max_reservations', 'block_duration_hours')
ORDER BY COLUMN_NAME;

-- 2. Verificar configuraciones de hoteles
SELECT 
    'Configuraciones de hoteles' as verificacion,
    h.id as hotel_id,
    h.name as hotel_name,
    hs.setting_key,
    hs.setting_value,
    hs.setting_type
FROM hotels h
LEFT JOIN hotel_settings hs ON h.id = hs.hotel_id
WHERE hs.setting_key IN ('allow_table_overlap', 'allow_room_overlap', 'allow_reservation_overlap')
ORDER BY h.id, hs.setting_key;

-- 3. Verificar que no existe la configuración antigua
SELECT 
    'Configuración antigua (debe estar vacío)' as verificacion,
    COUNT(*) as total
FROM hotel_settings
WHERE setting_key = 'allow_reservation_overlap';

-- 4. Contar amenidades por configuración de overlap
SELECT 
    'Distribución de amenidades por allow_overlap' as verificacion,
    allow_overlap,
    COUNT(*) as total_amenidades,
    AVG(block_duration_hours) as promedio_horas_bloqueo
FROM amenities
GROUP BY allow_overlap;

-- 5. Verificar amenidades con configuración específica
SELECT 
    'Amenidades con configuración de no-overlap' as verificacion,
    id,
    name,
    allow_overlap,
    max_reservations,
    block_duration_hours
FROM amenities
WHERE allow_overlap = 0
LIMIT 10;

-- =====================================================
-- FIN DE VERIFICACIÓN
-- =====================================================
