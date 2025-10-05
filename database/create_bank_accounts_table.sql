-- =====================================================
-- Fix for Subscription Controller Issues
-- =====================================================
-- This migration fixes two critical issues:
-- 1. Creates missing bank_accounts table (Error in line 48 of SubscriptionController.php)
-- 2. Adds missing columns to payment_transactions for subscription payments
-- 
-- Execute this script to fix the fatal error:
-- SQLSTATE[42S02]: Base table or view not found: 1146 Table 'aqh_mayordomo.bank_accounts' doesn't exist
-- =====================================================

-- =====================================================
-- 1. Create bank_accounts table
-- =====================================================

CREATE TABLE IF NOT EXISTS bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(100) NOT NULL,
    account_holder VARCHAR(200) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    clabe VARCHAR(18) NULL COMMENT 'CLABE interbancaria (México)',
    swift VARCHAR(11) NULL COMMENT 'Código SWIFT/BIC para transferencias internacionales',
    account_type ENUM('checking', 'savings', 'other') DEFAULT 'checking',
    currency VARCHAR(3) DEFAULT 'MXN',
    is_active TINYINT(1) DEFAULT 1,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_bank_name (bank_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cuentas bancarias para recibir pagos de suscripciones y servicios';

-- Insert default bank account (placeholder that can be updated by superadmin)
INSERT INTO bank_accounts (bank_name, account_holder, account_number, notes, is_active)
SELECT 
    'Banco Por Configurar' as bank_name,
    'Nombre del Titular' as account_holder,
    'XXXXXXXXXXXXX' as account_number,
    'Configure las cuentas bancarias desde el panel de Superadmin o actualice esta entrada con información real' as notes,
    1 as is_active
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM bank_accounts LIMIT 1);

-- =====================================================
-- 2. Update payment_transactions table
-- =====================================================

-- Create procedure to safely add columns if they don't exist
DELIMITER $$

DROP PROCEDURE IF EXISTS AddColumnIfNotExists$$
CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(100),
    IN columnName VARCHAR(100),
    IN columnDefinition VARCHAR(500)
)
BEGIN
    DECLARE columnExists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO columnExists 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = tableName 
        AND COLUMN_NAME = columnName;
    
    IF columnExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD COLUMN ', columnName, ' ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Add subscription_id column for subscription payments
CALL AddColumnIfNotExists('payment_transactions', 'subscription_id', 'INT NULL AFTER user_id');

-- Add payment_proof column to store uploaded payment proof file names
CALL AddColumnIfNotExists('payment_transactions', 'payment_proof', 'VARCHAR(255) NULL COMMENT "Nombre del archivo de comprobante de pago"');

-- Add transaction_reference column for manual payment references
CALL AddColumnIfNotExists('payment_transactions', 'transaction_reference', 'VARCHAR(255) NULL COMMENT "Referencia o folio de transacción"');

-- Add foreign key constraint for subscription_id (check if it exists first)
SET @fk_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'payment_transactions'
        AND CONSTRAINT_NAME = 'fk_payment_subscription'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE payment_transactions ADD CONSTRAINT fk_payment_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL',
    'SELECT "Foreign key fk_payment_subscription already exists" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for better query performance (check if it exists first)
SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'payment_transactions'
        AND INDEX_NAME = 'idx_subscription'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE payment_transactions ADD INDEX idx_subscription (subscription_id)',
    'SELECT "Index idx_subscription already exists" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Clean up procedure
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;

-- =====================================================
-- 3. Synchronize subscription prices with global_settings
-- =====================================================
-- This ensures prices displayed match those configured in Global Configuration

-- Update monthly plan price from global_settings if the setting exists
UPDATE subscriptions s
SET s.price = (
    SELECT CAST(setting_value AS DECIMAL(10,2))
    FROM global_settings 
    WHERE setting_key = 'plan_monthly_price'
    LIMIT 1
)
WHERE s.type = 'monthly' 
AND EXISTS (SELECT 1 FROM global_settings WHERE setting_key = 'plan_monthly_price');

-- Update annual plan price from global_settings if the setting exists
UPDATE subscriptions s
SET s.price = (
    SELECT CAST(setting_value AS DECIMAL(10,2))
    FROM global_settings 
    WHERE setting_key = 'plan_annual_price'
    LIMIT 1
)
WHERE s.type = 'annual'
AND EXISTS (SELECT 1 FROM global_settings WHERE setting_key = 'plan_annual_price');

-- Note: If promotional prices are enabled, you may want to update accordingly
-- Uncomment the following if you want to use promotional prices when enabled:
/*
UPDATE subscriptions s
SET s.price = (
    SELECT CAST(setting_value AS DECIMAL(10,2))
    FROM global_settings 
    WHERE setting_key = 'promo_monthly_price'
    LIMIT 1
)
WHERE s.type = 'monthly' 
AND EXISTS (
    SELECT 1 FROM global_settings 
    WHERE setting_key = 'promo_enabled' AND setting_value = '1'
);

UPDATE subscriptions s
SET s.price = (
    SELECT CAST(setting_value AS DECIMAL(10,2))
    FROM global_settings 
    WHERE setting_key = 'promo_annual_price'
    LIMIT 1
)
WHERE s.type = 'annual'
AND EXISTS (
    SELECT 1 FROM global_settings 
    WHERE setting_key = 'promo_enabled' AND setting_value = '1'
);
*/

-- =====================================================
-- 4. Activity log entry
-- =====================================================

INSERT INTO activity_log (user_id, action, description, created_at)
VALUES (
    NULL,
    'database_migration',
    'Tabla bank_accounts creada, payment_transactions actualizada y precios de suscripciones sincronizados',
    NOW()
);
