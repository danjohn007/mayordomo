-- =====================================================
-- Verification Script for Subscription Fixes
-- =====================================================
-- This script helps verify that the subscription fixes were applied correctly
-- Run this AFTER executing create_bank_accounts_table.sql

SELECT '=====================================' as '';
SELECT 'VERIFICACIÓN DE CORRECCIONES DE SUSCRIPCIÓN' as '';
SELECT '=====================================' as '';
SELECT '' as '';

-- =====================================================
-- 1. Verify bank_accounts table exists
-- =====================================================
SELECT '1. Verificando tabla bank_accounts...' as '';

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ La tabla bank_accounts EXISTE'
        ELSE '✗ La tabla bank_accounts NO EXISTE - Ejecutar create_bank_accounts_table.sql'
    END as status
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'bank_accounts';

-- Show bank_accounts structure
SELECT '   Estructura de bank_accounts:' as '';
DESCRIBE bank_accounts;

-- Show bank_accounts records
SELECT '   Registros en bank_accounts:' as '';
SELECT 
    id,
    bank_name,
    account_holder,
    LEFT(account_number, 4) as account_prefix,
    is_active,
    created_at
FROM bank_accounts
ORDER BY id;

SELECT '' as '';

-- =====================================================
-- 2. Verify payment_transactions columns
-- =====================================================
SELECT '2. Verificando columnas de payment_transactions...' as '';

SELECT 
    CASE 
        WHEN COUNT(*) >= 3 THEN '✓ Todas las columnas necesarias EXISTEN'
        ELSE CONCAT('✗ Faltan ', 3 - COUNT(*), ' columnas - Ejecutar create_bank_accounts_table.sql')
    END as status
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payment_transactions'
    AND COLUMN_NAME IN ('subscription_id', 'payment_proof', 'transaction_reference');

-- Show added columns details
SELECT '   Columnas agregadas:' as '';
SELECT 
    COLUMN_NAME as columna,
    COLUMN_TYPE as tipo,
    IS_NULLABLE as nullable,
    COLUMN_DEFAULT as default_value
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payment_transactions'
    AND COLUMN_NAME IN ('subscription_id', 'payment_proof', 'transaction_reference')
ORDER BY ORDINAL_POSITION;

SELECT '' as '';

-- =====================================================
-- 3. Verify price synchronization
-- =====================================================
SELECT '3. Verificando sincronización de precios...' as '';

-- Check if global_settings exist
SELECT 
    CASE 
        WHEN COUNT(*) >= 2 THEN '✓ Configuraciones de precios EXISTEN en global_settings'
        ELSE '✗ Faltan configuraciones de precios en global_settings'
    END as status
FROM global_settings
WHERE setting_key IN ('plan_monthly_price', 'plan_annual_price');

-- Show price comparison
SELECT '   Comparación de precios:' as '';
SELECT 
    s.id,
    s.name as plan_name,
    s.type as tipo,
    s.price as precio_en_subscriptions,
    CASE 
        WHEN s.type = 'monthly' THEN (
            SELECT CAST(setting_value AS DECIMAL(10,2))
            FROM global_settings 
            WHERE setting_key = 'plan_monthly_price'
        )
        WHEN s.type = 'annual' THEN (
            SELECT CAST(setting_value AS DECIMAL(10,2))
            FROM global_settings 
            WHERE setting_key = 'plan_annual_price'
        )
        ELSE NULL
    END as precio_en_settings,
    CASE 
        WHEN s.type = 'trial' THEN '✓ Trial (sin precio)'
        WHEN s.type = 'monthly' AND s.price = (
            SELECT CAST(setting_value AS DECIMAL(10,2))
            FROM global_settings 
            WHERE setting_key = 'plan_monthly_price'
        ) THEN '✓ Sincronizado'
        WHEN s.type = 'annual' AND s.price = (
            SELECT CAST(setting_value AS DECIMAL(10,2))
            FROM global_settings 
            WHERE setting_key = 'plan_annual_price'
        ) THEN '✓ Sincronizado'
        ELSE '✗ No sincronizado - Ejecutar UPDATE manual'
    END as estado_sincronizacion
FROM subscriptions s
WHERE s.is_active = 1
ORDER BY s.type, s.id;

SELECT '' as '';

-- =====================================================
-- 4. Verify foreign keys and indexes
-- =====================================================
SELECT '4. Verificando foreign keys e índices...' as '';

-- Check foreign key
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ Foreign key fk_payment_subscription EXISTE'
        ELSE '✗ Foreign key fk_payment_subscription NO EXISTE'
    END as status
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payment_transactions'
    AND CONSTRAINT_NAME = 'fk_payment_subscription'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY';

-- Check index
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ Índice idx_subscription EXISTE'
        ELSE '✗ Índice idx_subscription NO EXISTE'
    END as status
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payment_transactions'
    AND INDEX_NAME = 'idx_subscription';

SELECT '' as '';

-- =====================================================
-- 5. Summary
-- =====================================================
SELECT '=====================================' as '';
SELECT 'RESUMEN DE VERIFICACIÓN' as '';
SELECT '=====================================' as '';

SELECT 
    CONCAT(
        'Tablas verificadas: ',
        CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bank_accounts') > 0 
            THEN '✓ bank_accounts ' 
            ELSE '✗ bank_accounts ' 
        END
    ) as resumen_tablas;

SELECT 
    CONCAT(
        'Columnas agregadas: ',
        (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_SCHEMA = DATABASE() 
         AND TABLE_NAME = 'payment_transactions' 
         AND COLUMN_NAME IN ('subscription_id', 'payment_proof', 'transaction_reference')),
        ' de 3'
    ) as resumen_columnas;

SELECT 
    CONCAT(
        'Precios sincronizados: ',
        COUNT(*),
        ' de 2 planes pagos'
    ) as resumen_precios
FROM subscriptions s
WHERE s.is_active = 1 
    AND s.type IN ('monthly', 'annual')
    AND (
        (s.type = 'monthly' AND s.price = (
            SELECT CAST(setting_value AS DECIMAL(10,2))
            FROM global_settings 
            WHERE setting_key = 'plan_monthly_price'
        ))
        OR
        (s.type = 'annual' AND s.price = (
            SELECT CAST(setting_value AS DECIMAL(10,2))
            FROM global_settings 
            WHERE setting_key = 'plan_annual_price'
        ))
    );

SELECT '' as '';
SELECT 'Verificación completada. Revise los resultados arriba.' as '';
SELECT 'Si hay algún ✗, ejecute create_bank_accounts_table.sql nuevamente.' as '';
