-- ============================================================================
-- MajorBot Database Migration v1.0.0 to v1.1.0+
-- Migration for Phases 1-4: Reservations, Orders & Billing, Superadmin, Notifications & Reports
-- ============================================================================
-- IMPORTANT: This migration preserves all existing data and functionality
-- Execute this file after backing up your database
-- ============================================================================

USE majorbot_db;

-- ============================================================================
-- PHASE 1: RESERVATIONS MODULE
-- ============================================================================

-- Enhance room_reservations table with email confirmation and additional fields
ALTER TABLE room_reservations 
    ADD COLUMN IF NOT EXISTS confirmation_code VARCHAR(50) UNIQUE AFTER status,
    ADD COLUMN IF NOT EXISTS email_confirmed TINYINT(1) DEFAULT 0 AFTER confirmation_code,
    ADD COLUMN IF NOT EXISTS confirmed_at TIMESTAMP NULL AFTER email_confirmed,
    ADD COLUMN IF NOT EXISTS guest_name VARCHAR(200) AFTER guest_id,
    ADD COLUMN IF NOT EXISTS guest_email VARCHAR(255) AFTER guest_name,
    ADD COLUMN IF NOT EXISTS guest_phone VARCHAR(20) AFTER guest_email,
    ADD COLUMN IF NOT EXISTS special_requests TEXT AFTER notes,
    ADD COLUMN IF NOT EXISTS number_of_guests INT DEFAULT 1 AFTER special_requests,
    ADD INDEX idx_confirmation (confirmation_code),
    ADD INDEX idx_email_confirmed (email_confirmed);

-- Enhance table_reservations with email confirmation
ALTER TABLE table_reservations
    ADD COLUMN IF NOT EXISTS confirmation_code VARCHAR(50) UNIQUE AFTER status,
    ADD COLUMN IF NOT EXISTS email_confirmed TINYINT(1) DEFAULT 0 AFTER confirmation_code,
    ADD COLUMN IF NOT EXISTS confirmed_at TIMESTAMP NULL AFTER email_confirmed,
    ADD COLUMN IF NOT EXISTS guest_name VARCHAR(200) AFTER guest_id,
    ADD COLUMN IF NOT EXISTS guest_email VARCHAR(255) AFTER guest_name,
    ADD COLUMN IF NOT EXISTS guest_phone VARCHAR(20) AFTER guest_email,
    ADD COLUMN IF NOT EXISTS special_requests TEXT AFTER notes,
    ADD INDEX idx_confirmation (confirmation_code),
    ADD INDEX idx_email_confirmed (email_confirmed);

-- Email notifications log
CREATE TABLE IF NOT EXISTS email_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    notification_type ENUM('reservation_confirmation', 'reservation_reminder', 'order_confirmation', 'payment_receipt', 'service_request', 'general') NOT NULL,
    related_type ENUM('room_reservation', 'table_reservation', 'order', 'service_request', 'other') NULL,
    related_id INT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient (recipient_email),
    INDEX idx_status (status),
    INDEX idx_type (notification_type),
    INDEX idx_related (related_type, related_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calendar availability cache (for performance)
CREATE TABLE IF NOT EXISTS availability_calendar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    resource_type ENUM('room', 'table') NOT NULL,
    resource_id INT NOT NULL,
    date DATE NOT NULL,
    is_available TINYINT(1) DEFAULT 1,
    available_slots INT DEFAULT 0,
    total_slots INT DEFAULT 0,
    notes TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_availability (hotel_id, resource_type, resource_id, date),
    INDEX idx_hotel_date (hotel_id, date),
    INDEX idx_resource (resource_type, resource_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PHASE 2: ORDERS & BILLING MODULE
-- ============================================================================

-- Shopping cart table
CREATE TABLE IF NOT EXISTS shopping_cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    session_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cart items
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES shopping_cart(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    INDEX idx_cart (cart_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhance orders table with payment information
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS payment_method ENUM('cash', 'credit_card', 'debit_card', 'stripe', 'paypal', 'room_charge', 'complimentary') AFTER total_amount,
    ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending' AFTER payment_method,
    ADD COLUMN IF NOT EXISTS paid_at TIMESTAMP NULL AFTER payment_status,
    ADD COLUMN IF NOT EXISTS tax_amount DECIMAL(10, 2) DEFAULT 0 AFTER total_amount,
    ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10, 2) DEFAULT 0 AFTER tax_amount,
    ADD COLUMN IF NOT EXISTS tip_amount DECIMAL(10, 2) DEFAULT 0 AFTER discount_amount,
    ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10, 2) AFTER tip_amount,
    ADD INDEX idx_payment_status (payment_status);

-- Payment transactions
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NULL,
    reservation_id INT NULL,
    reservation_type ENUM('room', 'table') NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'MXN',
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'stripe', 'paypal', 'bank_transfer') NOT NULL,
    payment_gateway ENUM('stripe', 'paypal', 'manual') NULL,
    transaction_id VARCHAR(255) NULL,
    gateway_response TEXT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
    error_message TEXT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_order (order_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    hotel_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NULL,
    reservation_id INT NULL,
    reservation_type ENUM('room', 'table') NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_rate DECIMAL(5, 2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'MXN',
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    payment_terms TEXT,
    notes TEXT,
    pdf_path VARCHAR(255) NULL,
    sent_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_hotel (hotel_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_dates (invoice_date, due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice line items
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_rate DECIMAL(5, 2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX idx_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PHASE 3: SUPERADMIN & MULTI-HOTEL MODULE
-- ============================================================================

-- Enhance hotels table for multi-hotel management
ALTER TABLE hotels
    ADD COLUMN IF NOT EXISTS owner_id INT NULL AFTER id,
    ADD COLUMN IF NOT EXISTS subscription_plan_id INT NULL AFTER owner_id,
    ADD COLUMN IF NOT EXISTS subscription_status ENUM('trial', 'active', 'suspended', 'cancelled') DEFAULT 'trial' AFTER subscription_plan_id,
    ADD COLUMN IF NOT EXISTS subscription_start_date DATE NULL AFTER subscription_status,
    ADD COLUMN IF NOT EXISTS subscription_end_date DATE NULL AFTER subscription_start_date,
    ADD COLUMN IF NOT EXISTS max_rooms INT DEFAULT 50 AFTER description,
    ADD COLUMN IF NOT EXISTS max_tables INT DEFAULT 30 AFTER max_rooms,
    ADD COLUMN IF NOT EXISTS max_staff INT DEFAULT 20 AFTER max_tables,
    ADD COLUMN IF NOT EXISTS features JSON AFTER max_staff,
    ADD COLUMN IF NOT EXISTS timezone VARCHAR(50) DEFAULT 'America/Mexico_City' AFTER features,
    ADD COLUMN IF NOT EXISTS currency VARCHAR(3) DEFAULT 'MXN' AFTER timezone,
    ADD COLUMN IF NOT EXISTS logo_url VARCHAR(255) AFTER currency,
    ADD COLUMN IF NOT EXISTS website VARCHAR(255) AFTER logo_url,
    ADD FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD INDEX idx_owner (owner_id),
    ADD INDEX idx_subscription (subscription_status);

-- Hotel settings (additional configurations)
CREATE TABLE IF NOT EXISTS hotel_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    category VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_hotel_setting (hotel_id, setting_key),
    INDEX idx_hotel (hotel_id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscription plans (enhanced)
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    billing_cycle ENUM('monthly', 'annual', 'lifetime') NOT NULL,
    trial_days INT DEFAULT 0,
    max_hotels INT DEFAULT 1,
    max_rooms_per_hotel INT DEFAULT 50,
    max_tables_per_hotel INT DEFAULT 30,
    max_staff_per_hotel INT DEFAULT 20,
    features JSON,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hotel subscriptions (tracking)
CREATE TABLE IF NOT EXISTS hotel_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('trial', 'active', 'expired', 'cancelled', 'suspended') DEFAULT 'trial',
    auto_renew TINYINT(1) DEFAULT 1,
    payment_method VARCHAR(50),
    last_payment_date DATE NULL,
    next_payment_date DATE NULL,
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    INDEX idx_hotel (hotel_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Global statistics (cached aggregations)
CREATE TABLE IF NOT EXISTS global_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE NOT NULL,
    stat_type ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
    total_hotels INT DEFAULT 0,
    active_hotels INT DEFAULT 0,
    total_rooms INT DEFAULT 0,
    occupied_rooms INT DEFAULT 0,
    total_tables INT DEFAULT 0,
    occupied_tables INT DEFAULT 0,
    total_reservations INT DEFAULT 0,
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(12, 2) DEFAULT 0,
    total_users INT DEFAULT 0,
    active_subscriptions INT DEFAULT 0,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_stat (stat_date, stat_type),
    INDEX idx_date (stat_date),
    INDEX idx_type (stat_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hotel statistics (per hotel metrics)
CREATE TABLE IF NOT EXISTS hotel_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    stat_date DATE NOT NULL,
    stat_type ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
    total_rooms INT DEFAULT 0,
    occupied_rooms INT DEFAULT 0,
    occupancy_rate DECIMAL(5, 2) DEFAULT 0,
    total_reservations INT DEFAULT 0,
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(12, 2) DEFAULT 0,
    room_revenue DECIMAL(12, 2) DEFAULT 0,
    food_revenue DECIMAL(12, 2) DEFAULT 0,
    service_revenue DECIMAL(12, 2) DEFAULT 0,
    average_daily_rate DECIMAL(10, 2) DEFAULT 0,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_hotel_stat (hotel_id, stat_date, stat_type),
    INDEX idx_hotel (hotel_id),
    INDEX idx_date (stat_date),
    INDEX idx_type (stat_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System activity log (for superadmin monitoring)
CREATE TABLE IF NOT EXISTS activity_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    hotel_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_hotel (hotel_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PHASE 4: NOTIFICATIONS & REPORTS MODULE
-- ============================================================================

-- Real-time notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'reservation', 'order', 'service', 'payment', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(255) NULL,
    related_type VARCHAR(50) NULL,
    related_id INT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_hotel (hotel_id),
    INDEX idx_read (is_read),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification preferences
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    enabled TINYINT(1) DEFAULT 1,
    email_enabled TINYINT(1) DEFAULT 1,
    push_enabled TINYINT(1) DEFAULT 1,
    sms_enabled TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_type (user_id, notification_type),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports (saved and scheduled)
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NULL,
    created_by INT NOT NULL,
    report_type ENUM('occupancy', 'revenue', 'reservations', 'orders', 'staff_performance', 'customer_satisfaction', 'custom') NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    parameters JSON,
    schedule ENUM('once', 'daily', 'weekly', 'monthly') DEFAULT 'once',
    format ENUM('pdf', 'excel', 'csv', 'html') DEFAULT 'pdf',
    recipients TEXT,
    last_generated_at TIMESTAMP NULL,
    next_generation_at TIMESTAMP NULL,
    status ENUM('active', 'paused', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_hotel (hotel_id),
    INDEX idx_type (report_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report generations (history)
CREATE TABLE IF NOT EXISTS report_generations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    file_path VARCHAR(255) NULL,
    file_size INT NULL,
    status ENUM('generating', 'completed', 'failed') DEFAULT 'generating',
    error_message TEXT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    INDEX idx_report (report_id),
    INDEX idx_generated (generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Export queue (for async report generation)
CREATE TABLE IF NOT EXISTS export_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NULL,
    export_type VARCHAR(50) NOT NULL,
    export_format ENUM('pdf', 'excel', 'csv') NOT NULL,
    parameters JSON,
    status ENUM('queued', 'processing', 'completed', 'failed') DEFAULT 'queued',
    file_path VARCHAR(255) NULL,
    file_size INT NULL,
    error_message TEXT NULL,
    download_count INT DEFAULT 0,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ADDITIONAL ENHANCEMENTS & INDEXES
-- ============================================================================

-- Add indexes for better query performance on existing tables if not present
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_created (created_at);
ALTER TABLE rooms ADD INDEX IF NOT EXISTS idx_type (type);
ALTER TABLE rooms ADD INDEX IF NOT EXISTS idx_price (price);
ALTER TABLE dishes ADD INDEX IF NOT EXISTS idx_price (price);
ALTER TABLE orders ADD INDEX IF NOT EXISTS idx_created (created_at);
ALTER TABLE service_requests ADD INDEX IF NOT EXISTS idx_created (requested_at);

-- ============================================================================
-- SAMPLE DATA INSERTS FOR NEW FEATURES
-- ============================================================================

-- Insert default subscription plans
INSERT INTO subscription_plans (name, slug, description, price, billing_cycle, trial_days, max_hotels, max_rooms_per_hotel, max_tables_per_hotel, max_staff_per_hotel, features, is_active, sort_order) 
VALUES 
    ('Trial', 'trial', 'Free trial plan for 30 days', 0.00, 'monthly', 30, 1, 10, 10, 5, '["Basic features", "Email support", "1 hotel"]', 1, 1),
    ('Básico', 'basic', 'Perfect for small hotels', 499.00, 'monthly', 14, 1, 50, 30, 20, '["Up to 50 rooms", "Up to 30 tables", "Email support", "Basic reports"]', 1, 2),
    ('Profesional', 'professional', 'For growing businesses', 999.00, 'monthly', 14, 3, 100, 50, 50, '["Up to 3 hotels", "Up to 100 rooms each", "Priority support", "Advanced reports", "Payment integrations"]', 1, 3),
    ('Enterprise', 'enterprise', 'Unlimited hotels and features', 2499.00, 'monthly', 14, 999, 500, 200, 200, '["Unlimited hotels", "Up to 500 rooms each", "24/7 support", "Custom reports", "API access", "White label"]', 1, 4)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Insert default notification preferences for existing users
INSERT INTO notification_preferences (user_id, notification_type, enabled, email_enabled, push_enabled, sms_enabled)
SELECT id, 'reservation', 1, 1, 1, 0 FROM users
WHERE NOT EXISTS (SELECT 1 FROM notification_preferences WHERE user_id = users.id AND notification_type = 'reservation');

INSERT INTO notification_preferences (user_id, notification_type, enabled, email_enabled, push_enabled, sms_enabled)
SELECT id, 'order', 1, 1, 1, 0 FROM users
WHERE NOT EXISTS (SELECT 1 FROM notification_preferences WHERE user_id = users.id AND notification_type = 'order');

INSERT INTO notification_preferences (user_id, notification_type, enabled, email_enabled, push_enabled, sms_enabled)
SELECT id, 'service', 1, 1, 1, 0 FROM users
WHERE NOT EXISTS (SELECT 1 FROM notification_preferences WHERE user_id = users.id AND notification_type = 'service');

-- ============================================================================
-- VIEWS FOR COMMON QUERIES (Optional but recommended for performance)
-- ============================================================================

-- View for current room availability
CREATE OR REPLACE VIEW v_room_availability AS
SELECT 
    r.id AS room_id,
    r.hotel_id,
    r.room_number,
    r.type,
    r.capacity,
    r.price,
    r.status,
    r.floor,
    COUNT(DISTINCT rr.id) AS active_reservations,
    CASE 
        WHEN r.status = 'maintenance' THEN 0
        WHEN r.status = 'occupied' THEN 0
        WHEN EXISTS (
            SELECT 1 FROM room_reservations rr2 
            WHERE rr2.room_id = r.id 
            AND rr2.status IN ('confirmed', 'checked_in')
            AND CURDATE() BETWEEN rr2.check_in AND rr2.check_out
        ) THEN 0
        ELSE 1
    END AS is_available
FROM rooms r
LEFT JOIN room_reservations rr ON r.id = rr.room_id 
    AND rr.status IN ('confirmed', 'checked_in')
    AND CURDATE() BETWEEN rr.check_in AND rr.check_out
GROUP BY r.id;

-- View for daily revenue
CREATE OR REPLACE VIEW v_daily_revenue AS
SELECT 
    hotel_id,
    DATE(created_at) AS revenue_date,
    SUM(CASE WHEN status IN ('completed', 'delivered') THEN total_amount ELSE 0 END) AS total_revenue,
    COUNT(*) AS total_orders,
    COUNT(CASE WHEN status IN ('completed', 'delivered') THEN 1 END) AS completed_orders
FROM orders
GROUP BY hotel_id, DATE(created_at);

-- View for occupancy rate
CREATE OR REPLACE VIEW v_occupancy_rate AS
SELECT 
    h.id AS hotel_id,
    h.name AS hotel_name,
    COUNT(DISTINCT r.id) AS total_rooms,
    COUNT(DISTINCT CASE WHEN r.status = 'occupied' THEN r.id END) AS occupied_rooms,
    COUNT(DISTINCT CASE WHEN r.status = 'available' THEN r.id END) AS available_rooms,
    ROUND((COUNT(DISTINCT CASE WHEN r.status = 'occupied' THEN r.id END) / COUNT(DISTINCT r.id) * 100), 2) AS occupancy_percentage
FROM hotels h
LEFT JOIN rooms r ON h.id = r.hotel_id
GROUP BY h.id;

-- ============================================================================
-- TRIGGERS FOR AUTOMATIC UPDATES (Optional but recommended)
-- ============================================================================

DELIMITER $$

-- Trigger to generate confirmation code for room reservations
CREATE TRIGGER IF NOT EXISTS trg_room_reservation_confirmation
BEFORE INSERT ON room_reservations
FOR EACH ROW
BEGIN
    IF NEW.confirmation_code IS NULL OR NEW.confirmation_code = '' THEN
        SET NEW.confirmation_code = CONCAT('RR', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 99999), 5, '0'));
    END IF;
END$$

-- Trigger to generate confirmation code for table reservations
CREATE TRIGGER IF NOT EXISTS trg_table_reservation_confirmation
BEFORE INSERT ON table_reservations
FOR EACH ROW
BEGIN
    IF NEW.confirmation_code IS NULL OR NEW.confirmation_code = '' THEN
        SET NEW.confirmation_code = CONCAT('TR', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 99999), 5, '0'));
    END IF;
END$$

-- Trigger to generate invoice number
CREATE TRIGGER IF NOT EXISTS trg_invoice_number
BEFORE INSERT ON invoices
FOR EACH ROW
BEGIN
    IF NEW.invoice_number IS NULL OR NEW.invoice_number = '' THEN
        SET NEW.invoice_number = CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m'), '-', LPAD(FLOOR(RAND() * 9999), 4, '0'));
    END IF;
END$$

-- Trigger to update order subtotal when payment info is added
CREATE TRIGGER IF NOT EXISTS trg_order_subtotal
BEFORE UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.tax_amount IS NOT NULL OR NEW.discount_amount IS NOT NULL OR NEW.tip_amount IS NOT NULL THEN
        SET NEW.subtotal = NEW.total_amount - COALESCE(NEW.tax_amount, 0) - COALESCE(NEW.tip_amount, 0) + COALESCE(NEW.discount_amount, 0);
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS (Optional)
-- ============================================================================

DELIMITER $$

-- Procedure to check room availability for a date range
CREATE PROCEDURE IF NOT EXISTS sp_check_room_availability(
    IN p_hotel_id INT,
    IN p_check_in DATE,
    IN p_check_out DATE
)
BEGIN
    SELECT 
        r.id,
        r.room_number,
        r.type,
        r.capacity,
        r.price,
        r.status,
        CASE 
            WHEN r.status NOT IN ('available', 'reserved') THEN 0
            WHEN EXISTS (
                SELECT 1 FROM room_reservations rr
                WHERE rr.room_id = r.id
                AND rr.status IN ('confirmed', 'checked_in')
                AND (
                    (p_check_in BETWEEN rr.check_in AND rr.check_out) OR
                    (p_check_out BETWEEN rr.check_in AND rr.check_out) OR
                    (rr.check_in BETWEEN p_check_in AND p_check_out)
                )
            ) THEN 0
            ELSE 1
        END AS is_available
    FROM rooms r
    WHERE r.hotel_id = p_hotel_id
    ORDER BY r.room_number;
END$$

-- Procedure to calculate hotel occupancy rate
CREATE PROCEDURE IF NOT EXISTS sp_calculate_occupancy(
    IN p_hotel_id INT,
    IN p_date DATE
)
BEGIN
    SELECT 
        COUNT(DISTINCT r.id) AS total_rooms,
        COUNT(DISTINCT CASE 
            WHEN r.status = 'occupied' OR EXISTS (
                SELECT 1 FROM room_reservations rr
                WHERE rr.room_id = r.id
                AND rr.status IN ('confirmed', 'checked_in')
                AND p_date BETWEEN rr.check_in AND rr.check_out
            ) THEN r.id 
        END) AS occupied_rooms,
        ROUND((COUNT(DISTINCT CASE 
            WHEN r.status = 'occupied' OR EXISTS (
                SELECT 1 FROM room_reservations rr
                WHERE rr.room_id = r.id
                AND rr.status IN ('confirmed', 'checked_in')
                AND p_date BETWEEN rr.check_in AND rr.check_out
            ) THEN r.id 
        END) / COUNT(DISTINCT r.id) * 100), 2) AS occupancy_rate
    FROM rooms r
    WHERE r.hotel_id = p_hotel_id;
END$$

DELIMITER ;

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================

-- Log migration completion
INSERT INTO activity_log (action, description, created_at)
VALUES ('database_migration', 'Migration v1.0.0 to v1.1.0+ completed successfully', NOW());

-- ============================================================================
-- POST-MIGRATION NOTES
-- ============================================================================
-- 1. Update your application code to use the new tables and fields
-- 2. Configure email settings for notification system
-- 3. Set up payment gateway credentials (Stripe/PayPal)
-- 4. Review and adjust subscription plans pricing
-- 5. Test all new features in a staging environment first
-- 6. Create scheduled jobs for report generation and statistics updates
-- 7. Implement proper backup strategy for the enhanced database
-- ============================================================================
