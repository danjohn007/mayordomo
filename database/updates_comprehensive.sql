-- ============================================================================
-- COMPREHENSIVE SYSTEM UPDATES
-- Sistema MajorBot - Nuevas funcionalidades completas
-- ============================================================================
USE aqh_mayordomo;

-- ============================================================================
-- 1. PASSWORD RESET FUNCTIONALITY
-- ============================================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. LOYALTY PROGRAM & REFERRAL SYSTEM
-- ============================================================================
CREATE TABLE IF NOT EXISTS loyalty_program (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    referral_code VARCHAR(50) NOT NULL UNIQUE,
    total_referrals INT DEFAULT 0,
    total_earnings DECIMAL(10, 2) DEFAULT 0.00,
    available_balance DECIMAL(10, 2) DEFAULT 0.00,
    withdrawn_balance DECIMAL(10, 2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_referral_code (referral_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referred_user_id INT NOT NULL,
    referral_code VARCHAR(50) NOT NULL,
    status ENUM('pending', 'completed', 'paid') DEFAULT 'pending',
    commission_percentage DECIMAL(5, 2) DEFAULT 10.00,
    commission_amount DECIMAL(10, 2) DEFAULT 0.00,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_referrer (referrer_id),
    INDEX idx_referred (referred_user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. EXPAND GLOBAL SETTINGS WITH ALL CONFIGURATIONS
-- ============================================================================
INSERT INTO global_settings (setting_key, setting_value, setting_type, description, category)
VALUES
    -- PayPal Configuration
    ('paypal_enabled', '0', 'boolean', 'Habilitar pagos con PayPal', 'payment'),
    ('paypal_client_id', '', 'string', 'PayPal Client ID', 'payment'),
    ('paypal_secret', '', 'string', 'PayPal Secret Key', 'payment'),
    ('paypal_mode', 'sandbox', 'string', 'PayPal Mode (sandbox/live)', 'payment'),
    
    -- SMTP/Email Configuration
    ('smtp_enabled', '0', 'boolean', 'Habilitar envío de emails', 'email'),
    ('smtp_host', 'smtp.gmail.com', 'string', 'Servidor SMTP', 'email'),
    ('smtp_port', '587', 'number', 'Puerto SMTP', 'email'),
    ('smtp_username', '', 'string', 'Usuario SMTP', 'email'),
    ('smtp_password', '', 'string', 'Contraseña SMTP', 'email'),
    ('smtp_from_email', '', 'string', 'Email remitente del sistema', 'email'),
    ('smtp_from_name', 'MajorBot', 'string', 'Nombre remitente del sistema', 'email'),
    
    -- Loyalty Program
    ('loyalty_enabled', '1', 'boolean', 'Habilitar programa de lealtad', 'loyalty'),
    ('loyalty_default_percentage', '10', 'number', 'Porcentaje por defecto de comisión (%)', 'loyalty'),
    ('loyalty_min_withdrawal', '500', 'number', 'Monto mínimo para retiro', 'loyalty'),
    
    -- Currency and Tax
    ('currency_symbol', 'MXN', 'string', 'Símbolo de la moneda', 'financial'),
    ('currency_code', 'MXN', 'string', 'Código de la moneda', 'financial'),
    ('tax_rate', '16', 'number', 'Porcentaje de tasa de impuesto (%)', 'financial'),
    ('tax_enabled', '1', 'boolean', 'Aplicar impuestos', 'financial'),
    
    -- Site Information
    ('site_name', 'MajorBot', 'string', 'Nombre del Sitio Público', 'site'),
    ('site_logo', '', 'string', 'URL del Logo del Sitio', 'site'),
    ('site_description', 'Sistema de Mayordomía Online', 'string', 'Descripción del Sitio', 'site'),
    ('site_url', '', 'string', 'URL del sitio web', 'site'),
    
    -- Trial Period
    ('trial_days', '30', 'number', 'Días del Periodo Gratuito', 'subscription'),
    
    -- Pricing Plans
    ('plan_monthly_price', '499', 'number', 'Precio del plan mensual', 'subscription'),
    ('plan_annual_price', '4990', 'number', 'Precio del plan anual', 'subscription'),
    ('promo_enabled', '0', 'boolean', 'Activar precios promocionales', 'subscription'),
    ('promo_monthly_price', '399', 'number', 'Precio promocional mensual', 'subscription'),
    ('promo_annual_price', '3990', 'number', 'Precio promocional anual', 'subscription'),
    ('promo_start_date', NULL, 'string', 'Fecha inicio promoción', 'subscription'),
    ('promo_end_date', NULL, 'string', 'Fecha fin promoción', 'subscription'),
    
    -- WhatsApp Chatbot
    ('whatsapp_enabled', '0', 'boolean', 'Habilitar chatbot de WhatsApp', 'whatsapp'),
    ('whatsapp_number', '', 'string', 'Número de WhatsApp del sistema', 'whatsapp'),
    ('whatsapp_api_key', '', 'string', 'API Key de WhatsApp Business', 'whatsapp'),
    
    -- Bank Accounts
    ('bank_accounts', '[]', 'json', 'Datos de cuentas bancarias para depósitos', 'payment')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description);

-- ============================================================================
-- 4. PAYMENT TRANSACTIONS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NULL,
    subscription_id INT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'MXN',
    payment_method ENUM('paypal', 'stripe', 'bank_transfer', 'cash', 'other') NOT NULL,
    transaction_id VARCHAR(255) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_data JSON,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_hotel (hotel_id),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. ACTIVITY LOG FOR TRACKING
-- ============================================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. USER PROFILE ENHANCEMENTS
-- ============================================================================
ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER phone,
    ADD COLUMN IF NOT EXISTS timezone VARCHAR(50) DEFAULT 'America/Mexico_City' AFTER avatar,
    ADD COLUMN IF NOT EXISTS language VARCHAR(10) DEFAULT 'es' AFTER timezone,
    ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL AFTER language;

-- ============================================================================
-- 7. LOG INSTALLATION
-- ============================================================================
INSERT INTO activity_log (user_id, action, description, created_at)
VALUES (
    NULL,
    'system_update',
    'Comprehensive system updates installed: Password Reset, Loyalty Program, Global Settings, Payment Transactions',
    NOW()
);

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================
SELECT 'Password Resets Table' AS 'Feature', 
       IF(COUNT(*) > 0, '✓ Created', '✗ Failed') AS 'Status'
FROM information_schema.tables 
WHERE table_schema = 'aqh_mayordomo' AND table_name = 'password_resets'
UNION ALL
SELECT 'Loyalty Program Table',
       IF(COUNT(*) > 0, '✓ Created', '✗ Failed')
FROM information_schema.tables 
WHERE table_schema = 'aqh_mayordomo' AND table_name = 'loyalty_program'
UNION ALL
SELECT 'Referrals Table',
       IF(COUNT(*) > 0, '✓ Created', '✗ Failed')
FROM information_schema.tables 
WHERE table_schema = 'aqh_mayordomo' AND table_name = 'referrals'
UNION ALL
SELECT 'Payment Transactions Table',
       IF(COUNT(*) > 0, '✓ Created', '✗ Failed')
FROM information_schema.tables 
WHERE table_schema = 'aqh_mayordomo' AND table_name = 'payment_transactions'
UNION ALL
SELECT 'Global Settings Expanded',
       CONCAT('✓ ', COUNT(*), ' settings') 
FROM global_settings
WHERE category IN ('payment', 'email', 'loyalty', 'financial', 'site', 'whatsapp');
