-- ============================================================================
-- MIGRACIÓN v1.1.0+ para aqh_mayordomo.sql (adaptado a tu estructura actual)
-- ============================================================================

USE aqh_mayordomo;

-- ============================================================================
-- PHASE 1: RESERVATIONS MODULE
-- ============================================================================

-- room_reservations: Nuevos campos y índices
ALTER TABLE room_reservations
  ADD COLUMN confirmation_code VARCHAR(50) UNIQUE AFTER status,
  ADD COLUMN email_confirmed TINYINT(1) DEFAULT 0 AFTER confirmation_code,
  ADD COLUMN confirmed_at TIMESTAMP NULL AFTER email_confirmed,
  ADD COLUMN guest_name VARCHAR(200) AFTER guest_id,
  ADD COLUMN guest_email VARCHAR(255) AFTER guest_name,
  ADD COLUMN guest_phone VARCHAR(20) AFTER guest_email,
  ADD COLUMN special_requests TEXT AFTER notes,
  ADD COLUMN number_of_guests INT DEFAULT 1 AFTER special_requests,
  ADD INDEX idx_confirmation (confirmation_code),
  ADD INDEX idx_email_confirmed (email_confirmed);

-- table_reservations: Nuevos campos y índices
ALTER TABLE table_reservations
  ADD COLUMN confirmation_code VARCHAR(50) UNIQUE AFTER status,
  ADD COLUMN email_confirmed TINYINT(1) DEFAULT 0 AFTER confirmation_code,
  ADD COLUMN confirmed_at TIMESTAMP NULL AFTER email_confirmed,
  ADD COLUMN guest_name VARCHAR(200) AFTER guest_id,
  ADD COLUMN guest_email VARCHAR(255) AFTER guest_name,
  ADD COLUMN guest_phone VARCHAR(20) AFTER guest_email,
  ADD COLUMN special_requests TEXT AFTER notes,
  ADD INDEX idx_confirmation (confirmation_code),
  ADD INDEX idx_email_confirmed (email_confirmed);

-- email_notifications
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

-- availability_calendar
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

-- shopping_cart
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

-- cart_items
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

-- orders: Nuevos campos y índices
ALTER TABLE orders
  ADD COLUMN payment_method ENUM('cash', 'credit_card', 'debit_card', 'stripe', 'paypal', 'room_charge', 'complimentary') AFTER total_amount,
  ADD COLUMN payment_status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending' AFTER payment_method,
  ADD COLUMN paid_at TIMESTAMP NULL AFTER payment_status,
  ADD COLUMN tax_amount DECIMAL(10, 2) DEFAULT 0 AFTER total_amount,
  ADD COLUMN discount_amount DECIMAL(10, 2) DEFAULT 0 AFTER tax_amount,
  ADD COLUMN tip_amount DECIMAL(10, 2) DEFAULT 0 AFTER discount_amount,
  ADD COLUMN subtotal DECIMAL(10, 2) AFTER tip_amount,
  ADD INDEX idx_payment_status (payment_status);

-- payment_transactions
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

-- invoices
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

-- invoice_items
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

-- hotels: Nuevos campos y índices
ALTER TABLE hotels
  ADD COLUMN owner_id INT NULL AFTER id,
  ADD COLUMN subscription_plan_id INT NULL AFTER owner_id,
  ADD COLUMN subscription_status ENUM('trial', 'active', 'suspended', 'cancelled') DEFAULT 'trial' AFTER subscription_plan_id,
  ADD COLUMN subscription_start_date DATE NULL AFTER subscription_status,
  ADD COLUMN subscription_end_date DATE NULL AFTER subscription_start_date,
  ADD COLUMN max_rooms INT DEFAULT 50 AFTER description,
  ADD COLUMN max_tables INT DEFAULT 30 AFTER max_rooms,
  ADD COLUMN max_staff INT DEFAULT 20 AFTER max_tables,
  ADD COLUMN features JSON AFTER max_staff,
  ADD COLUMN timezone VARCHAR(50) DEFAULT 'America/Mexico_City' AFTER features,
  ADD COLUMN currency VARCHAR(3) DEFAULT 'MXN' AFTER timezone,
  ADD COLUMN logo_url VARCHAR(255) AFTER currency,
  ADD COLUMN website VARCHAR(255) AFTER logo_url,
  ADD FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
  ADD INDEX idx_owner (owner_id),
  ADD INDEX idx_subscription (subscription_status);

-- hotel_settings
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

-- subscription_plans
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

-- hotel_subscriptions
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

-- global_statistics
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

-- hotel_statistics
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

-- activity_log
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

-- notifications
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

-- notification_preferences
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

-- reports
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

-- report_generations
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

-- export_queue
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
-- INDICES ADICIONALES
-- ============================================================================

ALTER TABLE users ADD INDEX idx_created (created_at);
ALTER TABLE rooms ADD INDEX idx_type (type);
ALTER TABLE rooms ADD INDEX idx_price (price);
ALTER TABLE dishes ADD INDEX idx_price (price);
ALTER TABLE orders ADD INDEX idx_created (created_at);
ALTER TABLE service_requests ADD INDEX idx_created (requested_at);

-- ============================================================================
-- FINALIZACIÓN
-- ============================================================================
-- Puedes agregar los triggers y procedimientos si tu MySQL lo permite (no todos soportan IF NOT EXISTS en triggers, así que revisa antes de incluirlos).

-- Log de migración
INSERT INTO activity_log (action, description, created_at)
VALUES ('database_migration', 'Migración v1.0.0 a v1.1.0+ completada exitosamente', NOW());

-- ============================================================================
