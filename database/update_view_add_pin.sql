-- ============================================================================
-- Actualizar vista v_all_reservations para incluir confirmation_code (PIN)
-- ============================================================================
-- INSTRUCCIONES:
-- 1. Abrir phpMyAdmin
-- 2. Seleccionar la base de datos: ranchopa_sistema
-- 3. Ir a pestaña "SQL"
-- 4. Copiar y pegar todo este contenido
-- 5. Click en "Continuar"
-- ============================================================================

-- Eliminar vista existente
DROP VIEW IF EXISTS v_all_reservations;

-- Recrear vista con columna confirmation_code incluida
CREATE VIEW v_all_reservations AS
SELECT 
    'room' as reservation_type,
    rr.id,
    rr.status,
    rr.created_at,
    r.hotel_id,
    r.room_number as resource_number,
    rr.check_in as reservation_date,
    NULL as reservation_time,
    rr.guest_id,
    COALESCE(rr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
    COALESCE(rr.guest_email, u.email) as guest_email,
    COALESCE(rr.guest_phone, u.phone) as guest_phone,
    rr.total_price,
    COALESCE(rr.special_requests, rr.notes) as notes,
    rr.notification_sent,
    rr.confirmation_code
FROM room_reservations rr
JOIN rooms r ON rr.room_id = r.id
LEFT JOIN users u ON rr.guest_id = u.id

UNION ALL

SELECT 
    'table' as reservation_type,
    tr.id,
    tr.status,
    tr.created_at,
    rt.hotel_id,
    rt.table_number as resource_number,
    tr.reservation_date,
    tr.reservation_time,
    tr.guest_id,
    COALESCE(tr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
    COALESCE(tr.guest_email, u.email) as guest_email,
    COALESCE(tr.guest_phone, u.phone) as guest_phone,
    NULL as total_price,
    tr.notes,
    tr.notification_sent,
    tr.confirmation_code
FROM table_reservations tr
JOIN restaurant_tables rt ON tr.table_id = rt.id
LEFT JOIN users u ON tr.guest_id = u.id

UNION ALL

SELECT 
    'amenity' as reservation_type,
    ar.id,
    ar.status,
    ar.created_at,
    a.hotel_id,
    a.name as resource_number,
    ar.reservation_date,
    ar.reservation_time,
    ar.user_id as guest_id,
    COALESCE(ar.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
    COALESCE(ar.guest_email, u.email) as guest_email,
    COALESCE(ar.guest_phone, u.phone) as guest_phone,
    NULL as total_price,
    COALESCE(ar.notes, ar.special_requests) as notes,
    ar.notification_sent,
    ar.confirmation_code
FROM amenity_reservations ar
JOIN amenities a ON ar.amenity_id = a.id
LEFT JOIN users u ON ar.user_id = u.id;

-- Verificar que la vista se creó correctamente
SELECT 'Vista v_all_reservations actualizada exitosamente con columna confirmation_code' as mensaje;

-- Probar la vista (mostrar primeras 5 reservaciones con sus PINs)
SELECT 
    reservation_type,
    id,
    guest_name,
    resource_number,
    status,
    confirmation_code,
    created_at
FROM v_all_reservations 
ORDER BY created_at DESC 
LIMIT 5;
