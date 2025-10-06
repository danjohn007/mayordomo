-- ============================================================================
-- ADD UNLIMITED PLAN SUPPORT
-- Sistema MajorBot - Soporte para Planes Ilimitados sin Vigencia
-- ============================================================================
-- Este script agrega la funcionalidad de planes ilimitados (sin vencimiento)
-- en la gestión de usuarios del superadmin
-- ============================================================================

USE aqh_mayordomo;

-- Agregar columna is_unlimited a user_subscriptions si no existe
ALTER TABLE user_subscriptions 
ADD COLUMN IF NOT EXISTS is_unlimited TINYINT(1) DEFAULT 0 COMMENT 'Plan sin vigencia/vencimiento' AFTER end_date;

-- Agregar columna updated_at si no existe
ALTER TABLE user_subscriptions 
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Crear índice para búsquedas de planes ilimitados
ALTER TABLE user_subscriptions 
ADD INDEX IF NOT EXISTS idx_unlimited (is_unlimited);

-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================

-- Verificar la estructura actualizada
DESCRIBE user_subscriptions;

-- Mensaje de confirmación
SELECT 'Soporte para planes ilimitados agregado exitosamente' as Status;
