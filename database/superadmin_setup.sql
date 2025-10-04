-- ============================================================================
-- SUPERADMIN SETUP SCRIPT
-- Sistema MajorBot - Configuración Inicial de Superadministrador
-- ============================================================================
-- Este script configura:
-- 1. Usuario Superadmin con acceso completo
-- 2. Planes de suscripción (Trial, Mensual, Anual)
-- 3. Configuraciones globales iniciales
-- ============================================================================

USE aqh_mayordomo;

-- ============================================================================
-- 1. INSERTAR PLANES DE SUSCRIPCIÓN
-- ============================================================================

-- Verificar si la tabla subscription_plans existe, si no existe usar la tabla subscriptions heredada
-- Nota: Este script es compatible con ambas estructuras de base de datos

-- Limpiar planes existentes si es necesario (comentar si deseas mantener datos existentes)
-- DELETE FROM subscription_plans;
-- DELETE FROM subscriptions;

-- Insertar planes en subscription_plans (nueva estructura v1.1.0+)
INSERT INTO subscription_plans (name, slug, description, price, billing_cycle, trial_days, max_hotels, max_rooms_per_hotel, max_tables_per_hotel, max_staff_per_hotel, features, is_active, sort_order)
VALUES 
(
    'Plan Trial - Prueba Gratuita',
    'trial',
    'Plan de prueba gratuito configurable por Superadmin. Incluye acceso completo con límites básicos.',
    0.00,
    'monthly',
    30, -- Días de prueba (configurable)
    1,  -- Máximo 1 hotel
    10, -- Máximo 10 habitaciones
    10, -- Máximo 10 mesas
    5,  -- Máximo 5 miembros del personal
    JSON_OBJECT(
        'descripcion', 'Prueba gratuita con todas las funcionalidades',
        'habitaciones_max', 10,
        'mesas_max', 10,
        'personal_max', 5,
        'soporte', 'Email básico',
        'reportes', 'Básicos',
        'integraciones', false,
        'multi_hotel', false
    ),
    1,
    1
),
(
    'Plan Mensual - Básico',
    'monthly',
    'Plan mensual con pago recurrente. Ideal para hoteles pequeños y medianos.',
    99.00,
    'monthly',
    0, -- Sin periodo de prueba adicional
    1,  -- Máximo 1 hotel
    50, -- Máximo 50 habitaciones
    30, -- Máximo 30 mesas
    20, -- Máximo 20 miembros del personal
    JSON_OBJECT(
        'descripcion', 'Plan mensual con acceso completo',
        'habitaciones_max', 50,
        'mesas_max', 30,
        'personal_max', 20,
        'soporte', 'Email prioritario',
        'reportes', 'Avanzados',
        'integraciones', 'Stripe, PayPal',
        'multi_hotel', false,
        'notificaciones_email', true,
        'notificaciones_sms', false
    ),
    1,
    2
),
(
    'Plan Anual - Profesional',
    'annual',
    'Plan anual con descuento significativo. Pago único anual con todas las funcionalidades premium.',
    999.00,
    'annual',
    0, -- Sin periodo de prueba adicional
    3,  -- Máximo 3 hoteles
    150, -- Máximo 150 habitaciones por hotel
    80, -- Máximo 80 mesas por hotel
    50, -- Máximo 50 miembros del personal por hotel
    JSON_OBJECT(
        'descripcion', 'Plan anual con máximo ahorro',
        'habitaciones_max', 150,
        'mesas_max', 80,
        'personal_max', 50,
        'soporte', '24/7 prioritario',
        'reportes', 'Personalizados y exportables',
        'integraciones', 'Stripe, PayPal, MercadoPago',
        'multi_hotel', true,
        'notificaciones_email', true,
        'notificaciones_sms', true,
        'capacitacion', true,
        'descuento_anual', '16% vs mensual'
    ),
    1,
    3
),
(
    'Plan Enterprise - Ilimitado',
    'enterprise',
    'Plan corporativo sin límites. Para cadenas hoteleras grandes con necesidades especiales.',
    2999.00,
    'annual',
    0,
    999, -- Hoteles ilimitados
    999, -- Habitaciones ilimitadas por hotel
    999, -- Mesas ilimitadas por hotel
    999, -- Personal ilimitado por hotel
    JSON_OBJECT(
        'descripcion', 'Plan corporativo sin límites',
        'habitaciones_max', 'ilimitadas',
        'mesas_max', 'ilimitadas',
        'personal_max', 'ilimitado',
        'soporte', 'Dedicado 24/7 con gestor de cuenta',
        'reportes', 'Personalizados con BI',
        'integraciones', 'Todas las pasarelas disponibles',
        'multi_hotel', true,
        'notificaciones_email', true,
        'notificaciones_sms', true,
        'notificaciones_push', true,
        'capacitacion', 'Ilimitada',
        'api_acceso', true,
        'customizacion', true,
        'white_label', true
    ),
    1,
    4
);

-- Insertar planes en subscriptions (estructura heredada para compatibilidad)
-- Esto asegura que el sistema funcione con la estructura anterior
INSERT INTO subscriptions (name, type, price, duration_days, features, is_active)
VALUES 
(
    'Plan Trial - Prueba Gratuita',
    'trial',
    0.00,
    30,
    'Acceso completo por 30 días configurables, hasta 10 habitaciones, 10 mesas, 5 personal, soporte por email',
    1
),
(
    'Plan Mensual - Básico',
    'monthly',
    99.00,
    30,
    'Acceso completo, hasta 50 habitaciones, 30 mesas, 20 personal, soporte prioritario, reportes avanzados, integraciones Stripe/PayPal',
    1
),
(
    'Plan Anual - Profesional',
    'annual',
    999.00,
    365,
    'Acceso completo, hasta 150 habitaciones por hotel, multi-hotel (3), soporte 24/7, reportes personalizados, todas las integraciones, capacitación, SMS',
    1
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    type = VALUES(type),
    price = VALUES(price),
    duration_days = VALUES(duration_days),
    features = VALUES(features),
    is_active = VALUES(is_active);

-- ============================================================================
-- 2. CREAR USUARIO SUPERADMIN
-- ============================================================================

-- Insertar usuario Superadmin
-- Contraseña: Admin@2024! (deberá cambiarse al primer inicio de sesión)
-- Hash generado con bcrypt cost 12
INSERT INTO users (
    email,
    password,
    first_name,
    last_name,
    phone,
    role,
    hotel_id,
    subscription_id,
    is_active,
    created_at,
    updated_at
)
VALUES (
    'superadmin@mayorbot.com',
    '$2y$12$LQv3c1yycULr6hXVmn2vI.iILcRdLB1xI0qdHvvL4F8QHhEWtXivy', -- Admin@2024!
    'Super',
    'Administrador',
    '+52 999 999 9999',
    'superadmin',
    NULL, -- No está asociado a ningún hotel específico
    NULL, -- No tiene suscripción (acceso ilimitado)
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    password = VALUES(password),
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    phone = VALUES(phone),
    role = 'superadmin',
    is_active = 1,
    updated_at = NOW();

-- ============================================================================
-- 3. CONFIGURACIONES GLOBALES DEL SISTEMA
-- ============================================================================

-- Configuración del periodo de prueba gratuita (configurable por Superadmin)
-- Estas configuraciones se pueden agregar a una tabla de configuración global
-- o usar la tabla hotel_settings con hotel_id = NULL para configuraciones globales

-- Si existe la tabla hotel_settings, podemos agregar configuraciones globales
-- Nota: Algunas de estas configuraciones podrían requerir una tabla separada para configs globales

-- Crear tabla de configuración global si no existe
CREATE TABLE IF NOT EXISTS global_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    category VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones globales del sistema
INSERT INTO global_settings (setting_key, setting_value, setting_type, description, category)
VALUES
    ('trial_period_days', '30', 'number', 'Días de prueba gratuita para nuevos registros', 'subscription'),
    ('trial_auto_activate', '1', 'boolean', 'Activar automáticamente periodo de prueba en registro', 'subscription'),
    ('default_subscription_plan', '1', 'number', 'ID del plan de suscripción por defecto (Trial)', 'subscription'),
    ('require_hotel_name_registration', '1', 'boolean', 'Requerir nombre del hotel en registro público', 'registration'),
    ('public_registration_role', 'admin', 'string', 'Rol asignado en registro público (admin para propietarios)', 'registration'),
    ('payment_gateway_stripe_enabled', '0', 'boolean', 'Habilitar Stripe como pasarela de pago', 'payment'),
    ('payment_gateway_paypal_enabled', '0', 'boolean', 'Habilitar PayPal como pasarela de pago', 'payment'),
    ('payment_gateway_mercadopago_enabled', '0', 'boolean', 'Habilitar MercadoPago como pasarela de pago', 'payment'),
    ('subscription_block_on_expire', '1', 'boolean', 'Bloquear acceso al vencer suscripción', 'subscription'),
    ('subscription_notification_days_before', '7', 'number', 'Días antes de vencimiento para enviar notificación', 'notification'),
    ('subscription_auto_renew_default', '1', 'boolean', 'Activar renovación automática por defecto', 'subscription'),
    ('invoice_auto_generate', '1', 'boolean', 'Generar facturas automáticamente', 'billing'),
    ('system_currency_default', 'MXN', 'string', 'Moneda por defecto del sistema', 'general'),
    ('system_timezone_default', 'America/Mexico_City', 'string', 'Zona horaria por defecto', 'general'),
    ('superadmin_email', 'superadmin@mayorbot.com', 'string', 'Email del superadministrador principal', 'system')
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    description = VALUES(description),
    updated_at = NOW();

-- ============================================================================
-- 4. REGISTRAR ACTIVIDAD EN LOG
-- ============================================================================

-- Registrar en el log de actividad la configuración inicial
INSERT INTO activity_log (
    user_id,
    hotel_id,
    action,
    entity_type,
    entity_id,
    description,
    ip_address,
    created_at
)
VALUES (
    (SELECT id FROM users WHERE email = 'superadmin@mayorbot.com' LIMIT 1),
    NULL,
    'system_setup',
    'system',
    NULL,
    'Configuración inicial del sistema: Superadmin creado, planes de suscripción configurados, configuraciones globales establecidas',
    '127.0.0.1',
    NOW()
);

-- ============================================================================
-- 5. VERIFICACIÓN Y CONSULTAS ÚTILES
-- ============================================================================

-- Consultar el usuario Superadmin creado
SELECT 
    id,
    email,
    CONCAT(first_name, ' ', last_name) as nombre_completo,
    role,
    is_active,
    created_at
FROM users 
WHERE role = 'superadmin';

-- Consultar planes de suscripción configurados
SELECT 
    id,
    name,
    slug,
    price,
    billing_cycle,
    trial_days,
    max_hotels,
    max_rooms_per_hotel,
    is_active
FROM subscription_plans
ORDER BY sort_order;

-- Consultar configuraciones globales
SELECT 
    setting_key,
    setting_value,
    setting_type,
    description,
    category
FROM global_settings
ORDER BY category, setting_key;

-- ============================================================================
-- INFORMACIÓN DE ACCESO
-- ============================================================================

/*
╔════════════════════════════════════════════════════════════════════════════╗
║                    CREDENCIALES DE SUPERADMIN                               ║
╚════════════════════════════════════════════════════════════════════════════╝

Email:      superadmin@mayorbot.com
Contraseña: Admin@2024!

⚠️  IMPORTANTE: Cambiar la contraseña inmediatamente después del primer inicio de sesión.

═══════════════════════════════════════════════════════════════════════════════

PLANES DE SUSCRIPCIÓN CONFIGURADOS:

1. Plan Trial - Prueba Gratuita ($0.00)
   - 30 días configurables
   - 10 habitaciones, 10 mesas, 5 personal
   - Soporte por email

2. Plan Mensual - Básico ($99.00/mes)
   - 50 habitaciones, 30 mesas, 20 personal
   - Soporte prioritario
   - Integraciones Stripe/PayPal

3. Plan Anual - Profesional ($999.00/año)
   - 150 habitaciones, 80 mesas, 50 personal por hotel
   - Multi-hotel (hasta 3)
   - Soporte 24/7
   - Todas las integraciones

4. Plan Enterprise - Ilimitado ($2999.00/año)
   - Sin límites
   - Soporte dedicado con gestor de cuenta
   - White label y personalización

═══════════════════════════════════════════════════════════════════════════════

FUNCIONALIDADES DEL SUPERADMIN:

✓ Gestión completa de hoteles (alta, baja, configuración)
✓ Administración de planes de suscripción
✓ Configuración de periodo de prueba gratuita
✓ Panel de métricas globales (ocupación, ingresos, usuarios activos)
✓ Control de límites por plan
✓ Configuración de parámetros del sistema
✓ Gestión de pasarelas de pago
✓ Auditoría completa de actividad
✓ Acceso ilimitado a todos los módulos

═══════════════════════════════════════════════════════════════════════════════
*/

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
