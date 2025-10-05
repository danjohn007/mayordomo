-- ============================================================================
-- Verification Script for Trigger and Calendar Fixes
-- Run this after applying fix_trigger_and_calendar_errors.sql
-- ============================================================================

USE aqh_mayordomo;

-- ============================================================================
-- 1. Verify Triggers Exist and Are Correct
-- ============================================================================

SELECT '=' as separator;
SELECT '1. VERIFICANDO TRIGGERS' as test;
SELECT '=' as separator;

SELECT 
    trigger_name as 'Trigger Name',
    event_manipulation as 'Event',
    event_object_table as 'Table',
    action_timing as 'Timing'
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND (
    trigger_name = 'trg_notify_new_room_reservation'
    OR trigger_name = 'trg_notify_new_table_reservation' 
    OR trigger_name = 'trg_amenity_reservation_notification'
    OR trigger_name = 'trg_amenity_reservation_confirmation'
)
ORDER BY trigger_name;

-- Expected: 4 triggers
-- - trg_amenity_reservation_confirmation
-- - trg_amenity_reservation_notification
-- - trg_notify_new_room_reservation
-- - trg_notify_new_table_reservation

-- ============================================================================
-- 2. Verify notification_sent Column Exists
-- ============================================================================

SELECT '=' as separator;
SELECT '2. VERIFICANDO COLUMNA notification_sent' as test;
SELECT '=' as separator;

SELECT 
    'room_reservations' as tabla,
    IF(COUNT(*) > 0, '✓ Existe', '✗ No existe') as estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'room_reservations'
AND column_name = 'notification_sent'

UNION ALL

SELECT 
    'table_reservations' as tabla,
    IF(COUNT(*) > 0, '✓ Existe', '✗ No existe') as estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'table_reservations'
AND column_name = 'notification_sent'

UNION ALL

SELECT 
    'amenity_reservations' as tabla,
    IF(COUNT(*) > 0, '✓ Existe', '✗ No existe') as estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'amenity_reservations'
AND column_name = 'notification_sent';

-- Expected: All should show ✓ Existe

-- ============================================================================
-- 3. Verify hotel_id Columns Exist
-- ============================================================================

SELECT '=' as separator;
SELECT '3. VERIFICANDO COLUMNA hotel_id' as test;
SELECT '=' as separator;

SELECT 
    'room_reservations' as tabla,
    IF(COUNT(*) > 0, '✓ Existe', '✗ No existe') as estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'room_reservations'
AND column_name = 'hotel_id'

UNION ALL

SELECT 
    'table_reservations' as tabla,
    IF(COUNT(*) > 0, '✓ Existe', '✗ No existe') as estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'table_reservations'
AND column_name = 'hotel_id';

-- Expected: Both should show ✓ Existe

-- ============================================================================
-- 4. Verify Column Names in room_reservations
-- ============================================================================

SELECT '=' as separator;
SELECT '4. VERIFICANDO COLUMNAS DE FECHAS' as test;
SELECT '=' as separator;

SELECT 
    column_name as 'Column Name',
    data_type as 'Type',
    is_nullable as 'Nullable'
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'room_reservations'
AND column_name IN ('check_in', 'check_out', 'check_in_date', 'check_out_date')
ORDER BY column_name;

-- Expected: Only check_in and check_out should exist
-- check_in_date and check_out_date should NOT exist

-- ============================================================================
-- 5. Verify role_permissions Has amenity_ids (not amenities_access)
-- ============================================================================

SELECT '=' as separator;
SELECT '5. VERIFICANDO CAMPO amenity_ids EN role_permissions' as test;
SELECT '=' as separator;

SELECT 
    IF(COUNT(*) > 0, '✓ amenity_ids existe', '✗ amenity_ids NO existe') as estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'role_permissions'
AND column_name = 'amenity_ids'

UNION ALL

SELECT 
    IF(COUNT(*) > 0, '✗ amenities_access existe (INCORRECTO)', '✓ amenities_access NO existe') as estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'role_permissions'
AND column_name = 'amenities_access';

-- Expected: 
-- ✓ amenity_ids existe
-- ✓ amenities_access NO existe

-- ============================================================================
-- 6. Check Trigger Definitions for UPDATE Statements
-- ============================================================================

SELECT '=' as separator;
SELECT '6. VERIFICANDO QUE TRIGGERS NO TIENEN UPDATE' as test;
SELECT '=' as separator;

SELECT 
    trigger_name as 'Trigger',
    IF(action_statement LIKE '%UPDATE room_reservations%', '✗ Tiene UPDATE', '✓ Sin UPDATE') as 'room_reservations'
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND trigger_name = 'trg_notify_new_room_reservation'

UNION ALL

SELECT 
    trigger_name as 'Trigger',
    IF(action_statement LIKE '%UPDATE table_reservations%', '✗ Tiene UPDATE', '✓ Sin UPDATE') as 'table_reservations'
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND trigger_name = 'trg_notify_new_table_reservation'

UNION ALL

SELECT 
    trigger_name as 'Trigger',
    IF(action_statement LIKE '%UPDATE amenity_reservations%', '✗ Tiene UPDATE', '✓ Sin UPDATE') as 'amenity_reservations'
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND trigger_name = 'trg_amenity_reservation_notification';

-- Expected: All should show ✓ Sin UPDATE

-- ============================================================================
-- 7. Sample Data Counts
-- ============================================================================

SELECT '=' as separator;
SELECT '7. CONTEO DE REGISTROS' as test;
SELECT '=' as separator;

SELECT 
    'room_reservations' as tabla,
    COUNT(*) as total,
    SUM(notification_sent) as notificados
FROM room_reservations

UNION ALL

SELECT 
    'table_reservations' as tabla,
    COUNT(*) as total,
    SUM(notification_sent) as notificados
FROM table_reservations

UNION ALL

SELECT 
    'amenity_reservations' as tabla,
    COUNT(*) as total,
    SUM(IFNULL(notification_sent, 0)) as notificados
FROM amenity_reservations

UNION ALL

SELECT 
    'system_notifications' as tabla,
    COUNT(*) as total,
    SUM(is_read) as leidas
FROM system_notifications;

-- ============================================================================
-- FINAL SUMMARY
-- ============================================================================

SELECT '=' as separator;
SELECT '✅ VERIFICACIÓN COMPLETADA' as resultado;
SELECT '=' as separator;
SELECT 'Si todos los resultados anteriores son correctos,' as mensaje;
SELECT 'el sistema está listo para funcionar sin errores.' as mensaje;
