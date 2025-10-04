-- ============================================================================
-- Verification Script for Migration v1.1.0
-- Run this AFTER applying migration_v1.1.0.sql to verify success
-- ============================================================================

USE majorbot_db;

-- ============================================================================
-- 1. VERIFY NEW TABLES EXIST
-- ============================================================================

SELECT 'Checking new tables...' AS Status;

SELECT 
    'email_notifications' AS table_name,
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END AS status
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'email_notifications'
UNION ALL
SELECT 
    'availability_calendar',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'availability_calendar'
UNION ALL
SELECT 
    'shopping_cart',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'shopping_cart'
UNION ALL
SELECT 
    'cart_items',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'cart_items'
UNION ALL
SELECT 
    'payment_transactions',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'payment_transactions'
UNION ALL
SELECT 
    'invoices',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'invoices'
UNION ALL
SELECT 
    'invoice_items',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'invoice_items'
UNION ALL
SELECT 
    'subscription_plans',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'subscription_plans'
UNION ALL
SELECT 
    'hotel_subscriptions',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'hotel_subscriptions'
UNION ALL
SELECT 
    'hotel_settings',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'hotel_settings'
UNION ALL
SELECT 
    'global_statistics',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'global_statistics'
UNION ALL
SELECT 
    'hotel_statistics',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'hotel_statistics'
UNION ALL
SELECT 
    'activity_log',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'activity_log'
UNION ALL
SELECT 
    'notifications',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'notifications'
UNION ALL
SELECT 
    'notification_preferences',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'notification_preferences'
UNION ALL
SELECT 
    'reports',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'reports'
UNION ALL
SELECT 
    'report_generations',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'report_generations'
UNION ALL
SELECT 
    'export_queue',
    CASE WHEN COUNT(*) > 0 THEN '✓ EXISTS' ELSE '✗ MISSING' END
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'majorbot_db' AND TABLE_NAME = 'export_queue';

-- ============================================================================
-- 2. VERIFY NEW COLUMNS IN EXISTING TABLES
-- ============================================================================

SELECT '' AS '';
SELECT 'Checking new columns in room_reservations...' AS Status;

SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    '✓ EXISTS' AS status
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'majorbot_db' 
    AND TABLE_NAME = 'room_reservations'
    AND COLUMN_NAME IN ('confirmation_code', 'email_confirmed', 'confirmed_at', 'guest_name', 'guest_email');

SELECT '' AS '';
SELECT 'Checking new columns in orders...' AS Status;

SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    '✓ EXISTS' AS status
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'majorbot_db' 
    AND TABLE_NAME = 'orders'
    AND COLUMN_NAME IN ('payment_method', 'payment_status', 'paid_at', 'tax_amount', 'discount_amount');

SELECT '' AS '';
SELECT 'Checking new columns in hotels...' AS Status;

SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    '✓ EXISTS' AS status
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'majorbot_db' 
    AND TABLE_NAME = 'hotels'
    AND COLUMN_NAME IN ('owner_id', 'subscription_plan_id', 'subscription_status', 'max_rooms', 'timezone');

-- ============================================================================
-- 3. VERIFY VIEWS
-- ============================================================================

SELECT '' AS '';
SELECT 'Checking views...' AS Status;

SELECT 
    TABLE_NAME,
    '✓ EXISTS' AS status
FROM information_schema.VIEWS
WHERE TABLE_SCHEMA = 'majorbot_db'
    AND TABLE_NAME IN ('v_room_availability', 'v_daily_revenue', 'v_occupancy_rate');

-- ============================================================================
-- 4. VERIFY TRIGGERS
-- ============================================================================

SELECT '' AS '';
SELECT 'Checking triggers...' AS Status;

SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    EVENT_OBJECT_TABLE,
    '✓ EXISTS' AS status
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = 'majorbot_db'
    AND TRIGGER_NAME IN (
        'trg_room_reservation_confirmation',
        'trg_table_reservation_confirmation',
        'trg_invoice_number',
        'trg_order_subtotal'
    );

-- ============================================================================
-- 5. VERIFY STORED PROCEDURES
-- ============================================================================

SELECT '' AS '';
SELECT 'Checking stored procedures...' AS Status;

SELECT 
    ROUTINE_NAME,
    ROUTINE_TYPE,
    '✓ EXISTS' AS status
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'majorbot_db'
    AND ROUTINE_NAME IN (
        'sp_check_room_availability',
        'sp_calculate_occupancy'
    );

-- ============================================================================
-- 6. VERIFY SUBSCRIPTION PLANS DATA
-- ============================================================================

SELECT '' AS '';
SELECT 'Checking subscription plans data...' AS Status;

SELECT 
    id,
    name,
    slug,
    price,
    billing_cycle,
    max_hotels,
    is_active
FROM subscription_plans
ORDER BY sort_order;

-- ============================================================================
-- 7. COUNT RECORDS IN ALL TABLES
-- ============================================================================

SELECT '' AS '';
SELECT 'Record counts in all tables...' AS Status;

SELECT 'users' AS table_name, COUNT(*) AS record_count FROM users
UNION ALL
SELECT 'hotels', COUNT(*) FROM hotels
UNION ALL
SELECT 'rooms', COUNT(*) FROM rooms
UNION ALL
SELECT 'restaurant_tables', COUNT(*) FROM restaurant_tables
UNION ALL
SELECT 'dishes', COUNT(*) FROM dishes
UNION ALL
SELECT 'amenities', COUNT(*) FROM amenities
UNION ALL
SELECT 'room_reservations', COUNT(*) FROM room_reservations
UNION ALL
SELECT 'table_reservations', COUNT(*) FROM table_reservations
UNION ALL
SELECT 'orders', COUNT(*) FROM orders
UNION ALL
SELECT 'subscription_plans', COUNT(*) FROM subscription_plans
UNION ALL
SELECT 'notifications', COUNT(*) FROM notifications
UNION ALL
SELECT 'activity_log', COUNT(*) FROM activity_log;

-- ============================================================================
-- 8. CHECK FOR ANY FOREIGN KEY CONSTRAINT ISSUES
-- ============================================================================

SELECT '' AS '';
SELECT 'Checking foreign key constraints...' AS Status;

SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    CONSTRAINT_TYPE,
    '✓ VALID' AS status
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = 'majorbot_db'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND TABLE_NAME IN (
        'email_notifications',
        'payment_transactions',
        'invoices',
        'hotel_subscriptions',
        'hotel_statistics',
        'notifications'
    );

-- ============================================================================
-- 9. MIGRATION LOG
-- ============================================================================

SELECT '' AS '';
SELECT 'Migration log entry...' AS Status;

SELECT * FROM activity_log 
WHERE action = 'database_migration'
ORDER BY created_at DESC
LIMIT 1;

-- ============================================================================
-- 10. SUMMARY
-- ============================================================================

SELECT '' AS '';
SELECT 'MIGRATION VERIFICATION COMPLETE!' AS Status;
SELECT 'If all items show ✓ EXISTS or ✓ VALID, the migration was successful.' AS Note;
