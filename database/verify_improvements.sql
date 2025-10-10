-- ====================================================================
-- Script de Verificación: Mejoras Implementadas
-- Fecha: 2025-10-10
-- Descripción: Verifica que todas las mejoras se aplicaron correctamente
-- ====================================================================

-- ====================================================================
-- PASO 1: Verificar tabla service_type_catalog
-- ====================================================================

-- Verificar que la tabla existe
SELECT 'Verificando tabla service_type_catalog...' as paso;

SHOW TABLES LIKE 'service_type_catalog';

-- Verificar estructura de la tabla
DESCRIBE service_type_catalog;

-- Contar tipos de servicio por hotel
SELECT 
    h.id as hotel_id,
    h.name as hotel_name,
    COUNT(stc.id) as tipos_servicio
FROM hotels h
LEFT JOIN service_type_catalog stc ON h.id = stc.hotel_id
GROUP BY h.id, h.name
ORDER BY h.id;

-- Listar todos los tipos de servicio
SELECT 
    h.name as hotel,
    stc.name as tipo_servicio,
    stc.icon,
    stc.sort_order,
    CASE WHEN stc.is_active = 1 THEN 'Activo' ELSE 'Inactivo' END as estado
FROM service_type_catalog stc
JOIN hotels h ON stc.hotel_id = h.id
ORDER BY h.id, stc.sort_order;

-- ====================================================================
-- PASO 2: Verificar columna service_type_id en service_requests
-- ====================================================================

SELECT 'Verificando columna service_type_id en service_requests...' as paso;

-- Verificar estructura
DESCRIBE service_requests;

-- Verificar que la columna existe
SHOW COLUMNS FROM service_requests LIKE 'service_type_id';

-- Verificar constraint foreign key
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_NAME = 'service_requests'
AND COLUMN_NAME = 'service_type_id';

-- ====================================================================
-- PASO 3: Verificar datos migrados
-- ====================================================================

SELECT 'Verificando datos migrados en service_requests...' as paso;

-- Contar solicitudes con y sin tipo asignado
SELECT 
    CASE 
        WHEN service_type_id IS NOT NULL THEN 'Con tipo asignado'
        ELSE 'Sin tipo asignado'
    END as estado,
    COUNT(*) as cantidad
FROM service_requests
GROUP BY estado;

-- Ver distribución de solicitudes por tipo de servicio
SELECT 
    stc.name as tipo_servicio,
    COUNT(sr.id) as cantidad_solicitudes,
    stc.icon
FROM service_type_catalog stc
LEFT JOIN service_requests sr ON stc.id = sr.service_type_id
GROUP BY stc.id, stc.name, stc.icon
ORDER BY cantidad_solicitudes DESC;

-- ====================================================================
-- PASO 4: Verificar integridad de datos
-- ====================================================================

SELECT 'Verificando integridad de datos...' as paso;

-- Verificar que no hay huérfanos (service_type_id que no existe)
SELECT COUNT(*) as solicitudes_huerfanas
FROM service_requests sr
WHERE sr.service_type_id IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.id = sr.service_type_id
);

-- Verificar que todas las solicitudes tienen hotel_id válido
SELECT COUNT(*) as solicitudes_sin_hotel
FROM service_requests sr
WHERE NOT EXISTS (
    SELECT 1 FROM hotels h 
    WHERE h.id = sr.hotel_id
);

-- ====================================================================
-- PASO 5: Verificar vista v_all_reservations (para gráficas)
-- ====================================================================

SELECT 'Verificando vista v_all_reservations...' as paso;

-- Verificar que la vista existe
SHOW FULL TABLES WHERE Table_Type = 'VIEW' AND Tables_in_majorbot_db LIKE 'v_all_reservations';

-- Contar reservaciones por tipo (datos para gráfica 1)
SELECT 
    reservation_type,
    CASE 
        WHEN reservation_type = 'room' THEN 'Habitaciones'
        WHEN reservation_type = 'table' THEN 'Mesas'
        WHEN reservation_type = 'amenity' THEN 'Amenidades'
        ELSE reservation_type
    END as tipo_traducido,
    COUNT(*) as cantidad
FROM v_all_reservations
GROUP BY reservation_type
ORDER BY cantidad DESC;

-- Contar reservaciones por estado (datos para gráfica 2)
SELECT 
    status,
    COUNT(*) as cantidad
FROM v_all_reservations
GROUP BY status
ORDER BY 
    CASE status
        WHEN 'pending' THEN 1
        WHEN 'confirmed' THEN 2
        WHEN 'checked_in' THEN 3
        WHEN 'seated' THEN 3
        WHEN 'completed' THEN 4
        WHEN 'checked_out' THEN 4
        WHEN 'cancelled' THEN 5
        ELSE 6
    END;

-- ====================================================================
-- PASO 6: Verificar datos para gráfica de solicitudes asignadas
-- ====================================================================

SELECT 'Verificando datos para gráfica de solicitudes...' as paso;

-- Solicitudes asignadas vs sin asignar (datos para gráfica 3)
SELECT 
    CASE 
        WHEN assigned_to IS NOT NULL THEN 'Asignadas'
        ELSE 'Sin Asignar'
    END as estado_asignacion,
    COUNT(*) as cantidad
FROM service_requests
WHERE status NOT IN ('completed', 'cancelled')
GROUP BY estado_asignacion;

-- Detalle de solicitudes por colaborador
SELECT 
    u.first_name,
    u.last_name,
    COUNT(sr.id) as solicitudes_asignadas,
    SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) as completadas,
    SUM(CASE WHEN sr.status IN ('pending', 'assigned', 'in_progress') THEN 1 ELSE 0 END) as pendientes
FROM users u
LEFT JOIN service_requests sr ON u.id = sr.assigned_to
WHERE u.role = 'collaborator'
GROUP BY u.id, u.first_name, u.last_name
ORDER BY solicitudes_asignadas DESC;

-- ====================================================================
-- PASO 7: Resumen General
-- ====================================================================

SELECT 'RESUMEN GENERAL' as '═══════════════════════════════════════';

SELECT 
    'Hoteles Registrados' as metrica,
    COUNT(*) as valor
FROM hotels
UNION ALL
SELECT 
    'Tipos de Servicio (Total)' as metrica,
    COUNT(*) as valor
FROM service_type_catalog
UNION ALL
SELECT 
    'Tipos de Servicio (Activos)' as metrica,
    COUNT(*) as valor
FROM service_type_catalog
WHERE is_active = 1
UNION ALL
SELECT 
    'Solicitudes de Servicio (Total)' as metrica,
    COUNT(*) as valor
FROM service_requests
UNION ALL
SELECT 
    'Solicitudes con Tipo Asignado' as metrica,
    COUNT(*) as valor
FROM service_requests
WHERE service_type_id IS NOT NULL
UNION ALL
SELECT 
    'Reservaciones (Total)' as metrica,
    COUNT(*) as valor
FROM v_all_reservations
UNION ALL
SELECT 
    'Reservaciones - Habitaciones' as metrica,
    COUNT(*) as valor
FROM v_all_reservations
WHERE reservation_type = 'room'
UNION ALL
SELECT 
    'Reservaciones - Mesas' as metrica,
    COUNT(*) as valor
FROM v_all_reservations
WHERE reservation_type = 'table'
UNION ALL
SELECT 
    'Reservaciones - Amenidades' as metrica,
    COUNT(*) as valor
FROM v_all_reservations
WHERE reservation_type = 'amenity';

-- ====================================================================
-- PASO 8: Verificar índices
-- ====================================================================

SELECT 'Verificando índices...' as paso;

-- Índices en service_type_catalog
SHOW INDEX FROM service_type_catalog;

-- Índices en service_requests
SHOW INDEX FROM service_requests;

-- ====================================================================
-- FIN DE VERIFICACIÓN
-- ====================================================================

SELECT '✅ Verificación completada exitosamente' as resultado;
SELECT 'Si todos los queries anteriores retornaron datos correctos,' as nota1;
SELECT 'la implementación se realizó correctamente.' as nota2;
