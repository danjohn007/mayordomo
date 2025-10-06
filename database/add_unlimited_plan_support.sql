-- ============================================================================
-- ADD UNLIMITED PLAN SUPPORT
-- Sistema MajorBot - Soporte para Planes Ilimitados sin Vigencia
-- ============================================================================
-- Este script agrega la funcionalidad de planes ilimitados (sin vencimiento)
-- en la gestión de usuarios del superadmin
-- ============================================================================

USE aqh_mayordomo;

-- Agregar columna is_unlimited a user_subscriptions si no existe
SET @col_exists := (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'user_subscriptions' 
      AND COLUMN_NAME = 'is_unlimited'
);

SET @sql := IF(@col_exists = 0, 
    'ALTER TABLE user_subscriptions ADD COLUMN is_unlimited TINYINT(1) DEFAULT 0 COMMENT ''Plan sin vigencia/vencimiento'' AFTER end_date;', 
    'SELECT ''Columna is_unlimited ya existe'';'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna updated_at si no existe
SET @col_exists := (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'user_subscriptions' 
      AND COLUMN_NAME = 'updated_at'
);

SET @sql := IF(@col_exists = 0, 
    'ALTER TABLE user_subscriptions ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;', 
    'SELECT ''Columna updated_at ya existe'';'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índice para búsquedas de planes ilimitados si no existe
SET @idx_exists := (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'user_subscriptions' 
      AND INDEX_NAME = 'idx_unlimited'
);

SET @sql := IF(@idx_exists = 0, 
    'ALTER TABLE user_subscriptions ADD INDEX idx_unlimited (is_unlimited);', 
    'SELECT ''Índice idx_unlimited ya existe'';'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar la estructura actualizada
DESCRIBE user_subscriptions;

-- Mensaje de confirmación
SELECT 'Soporte para planes ilimitados agregado exitosamente' as Status;
