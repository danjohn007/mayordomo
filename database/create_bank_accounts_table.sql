-- =====================================================
-- SCRIPT DE MIGRACIÓN COMPLETO Y CORREGIDO (SIN ERRORES DE COLLATION)
-- Creación de bank_accounts y actualización dinámica de payment_transactions
-- Compatible con MySQL 5.7+, evita errores de collations
-- =====================================================

-- 1. Crear tabla bank_accounts si no existe
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

-- Insertar cuenta bancaria de ejemplo si no existe ninguna
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
-- 2. Actualizar payment_transactions (agregar columnas, FK, índice)
-- =====================================================

-- Solución definitiva: Forzar collation de variables a utf8_unicode_ci
DELIMITER $$

DROP PROCEDURE IF EXISTS AddColumnIfNotExists$$
CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(100),
    IN columnName VARCHAR(100),
    IN columnDefinition VARCHAR(500)
)
BEGIN
    DECLARE columnExists INT DEFAULT 0;
    -- Forzar collation de las variables a utf8_unicode_ci
    SELECT COUNT(*) INTO columnExists 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME COLLATE utf8_unicode_ci = tableName COLLATE utf8_unicode_ci
        AND COLUMN_NAME COLLATE utf8_unicode_ci = columnName COLLATE utf8_unicode_ci;
    IF columnExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD COLUMN ', columnName, ' ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Agregar columnas faltantes
CALL AddColumnIfNotExists('payment_transactions', 'subscription_id', 'INT NULL AFTER user_id');
CALL AddColumnIfNotExists('payment_transactions', 'payment_proof', 'VARCHAR(255) NULL COMMENT "Nombre del archivo de comprobante de pago"');
CALL AddColumnIfNotExists('payment_transactions', 'transaction_reference', 'VARCHAR(255) NULL COMMENT "Referencia o folio de transacción"');

-- Agregar clave foránea a subscription_id si no existe (sin COLLATE)
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

-- Agregar índice en subscription_id si no existe (sin COLLATE)
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

-- Limpieza: eliminar el procedimiento
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;

-- =====================================================
-- 3. Sincronizar precios de suscripciones con global_settings
-- =====================================================

UPDATE subscriptions s
SET s.price = (
    SELECT CAST(setting_value AS DECIMAL(10,2))
    FROM global_settings 
    WHERE setting_key = 'plan_monthly_price'
    LIMIT 1
)
WHERE s.type = 'monthly' 
AND EXISTS (SELECT 1 FROM global_settings WHERE setting_key = 'plan_monthly_price');

UPDATE subscriptions s
SET s.price = (
    SELECT CAST(setting_value AS DECIMAL(10,2))
    FROM global_settings 
    WHERE setting_key = 'plan_annual_price'
    LIMIT 1
)
WHERE s.type = 'annual'
AND EXISTS (SELECT 1 FROM global_settings WHERE setting_key = 'plan_annual_price');

-- Si quieres usar precios promocionales, descomenta el siguiente bloque:
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
-- 4. Registrar actividad de la migración
-- =====================================================

INSERT INTO activity_log (user_id, action, description, created_at)
VALUES (
    NULL,
    'database_migration',
    'Tabla bank_accounts creada, payment_transactions actualizada (columnas, índice, clave foránea) y precios de suscripciones sincronizados',
    NOW()
);

-- =====================================================
-- FIN DEL SCRIPT DE MIGRACIÓN
-- =====================================================
