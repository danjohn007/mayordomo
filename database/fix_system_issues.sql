-- ============================================================================
-- Actualización del Sistema - Fix de Issues Reportados
-- ============================================================================
-- Este script corrige los siguientes problemas:
-- 1. Sincroniza precios de suscripciones con global_settings
-- 2. Crea tabla role_permissions para asignación de áreas por rol
-- 3. Crea tabla de notificaciones unificada (system_notifications)
-- 4. Agrega campo notification_sent a room_reservations y table_reservations
-- 5. Actualiza descripciones en features de los planes en subscriptions
-- 6. Crea triggers para notificaciones automáticas
-- 7. Inserta permisos por defecto para roles existentes
-- 8. Vista unificada de reservaciones
-- ============================================================================

USE aqh_mayordomo;

-- ============================================================================
-- 1. SINCRONIZAR PRECIOS DE SUSCRIPCIONES CON GLOBAL_SETTINGS
-- ============================================================================

-- Actualizar precios de planes según configuración global
UPDATE subscriptions s
SET s.price = (
    SELECT CAST(gs.setting_value AS DECIMAL(10,2))
    FROM global_settings gs
    WHERE gs.setting_key = 'plan_monthly_price'
)
WHERE s.type = 'monthly';

UPDATE subscriptions s
SET s.price = (
    SELECT CAST(gs.setting_value AS DECIMAL(10,2))
    FROM global_settings gs
    WHERE gs.setting_key = 'plan_annual_price'
)
WHERE s.type = 'annual';

-- Si no existen los valores, insertarlos (ajustado al esquema actual)
INSERT IGNORE INTO global_settings (setting_key, setting_value, setting_type, description, category)
VALUES
('plan_monthly_price', '499', 'number', 'Precio del plan mensual', 'subscription'),
('plan_annual_price', '4990', 'number', 'Precio del plan anual', 'subscription'),
('plan_trial_days', '7', 'number', 'Días de prueba gratuita', 'subscription');

-- ============================================================================
-- 2. CREAR TABLA DE PERMISOS POR ROL (para asignación de áreas)
-- ============================================================================

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    user_id INT NOT NULL,
    role_name VARCHAR(50) NOT NULL,
    can_manage_rooms TINYINT(1) DEFAULT 0,
    can_manage_tables TINYINT(1) DEFAULT 0,
    can_manage_menu TINYINT(1) DEFAULT 0,
    amenity_ids TEXT NULL COMMENT 'JSON array de IDs de amenidades asignadas',
    service_types TEXT NULL COMMENT 'JSON array de tipos de servicios asignados',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_hotel (hotel_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role_name),
    UNIQUE KEY unique_user_hotel (user_id, hotel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. CREAR TABLA DE NOTIFICACIONES DEL SISTEMA
-- ============================================================================

CREATE TABLE IF NOT EXISTS system_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'Usuario destinatario',
    notification_type ENUM(
        'new_reservation_room',
        'new_reservation_table', 
        'service_request',
        'amenity_request',
        'dish_order',
        'general'
    ) NOT NULL,
    related_type ENUM('room_reservation', 'table_reservation', 'service_request', 'amenity', 'order') NULL,
    related_id INT NULL COMMENT 'ID del registro relacionado',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    requires_sound TINYINT(1) DEFAULT 1 COMMENT 'Si debe reproducir sonido',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_hotel (hotel_id),
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_type (notification_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. AGREGAR CAMPOS ADICIONALES notification_sent A ROOM_RESERVATIONS Y TABLE_RESERVATIONS
-- ============================================================================

-- ROOM_RESERVATIONS
SET @dbname = DATABASE();
SET @columnname = 'notification_sent';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'room_reservations')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE room_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 AFTER status"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- TABLE_RESERVATIONS
SET @columnname = 'notification_sent';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'table_reservations')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE table_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 AFTER status"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- 5. ACTUALIZAR DESCRIPCIONES EN FEATURES DE LOS PLANES EXISTENTES
-- ============================================================================

-- Mensual
UPDATE subscriptions
SET features = CONCAT(features, '\nDescripción: Plan mensual con acceso completo a todas las funcionalidades.')
WHERE type = 'monthly' AND (features IS NOT NULL AND features NOT LIKE '%Descripción:%');

-- Anual
UPDATE subscriptions
SET features = CONCAT(features, '\nDescripción: Plan anual con descuento. Todas las funcionalidades incluidas más soporte prioritario.')
WHERE type = 'annual' AND (features IS NOT NULL AND features NOT LIKE '%Descripción:%');

-- Trial
UPDATE subscriptions
SET features = CONCAT(features, '\nDescripción: Acceso completo por 7 días. Ideal para probar todas las funcionalidades del sistema sin costo.')
WHERE type = 'trial' AND (features IS NOT NULL AND features NOT LIKE '%Descripción:%');

-- ============================================================================
-- 6. CREAR TRIGGERS PARA NOTIFICACIONES AUTOMÁTICAS
-- ============================================================================

DROP TRIGGER IF EXISTS trg_notify_new_room_reservation;
DELIMITER $$
CREATE TRIGGER trg_notify_new_room_reservation
AFTER INSERT ON room_reservations
FOR EACH ROW
BEGIN
    DECLARE v_hotel_id INT;
    DECLARE v_room_number VARCHAR(20);
    SELECT hotel_id, room_number INTO v_hotel_id, v_room_number
    FROM rooms
    WHERE id = NEW.room_id;
    INSERT INTO system_notifications (hotel_id, user_id, notification_type, related_type, related_id, title, message, requires_sound, priority)
    SELECT 
        v_hotel_id,
        u.id,
        'new_reservation_room',
        'room_reservation',
        NEW.id,
        'Nueva Reservación de Habitación',
        CONCAT('Nueva reservación para habitación ', v_room_number, ' - Check-in: ', DATE_FORMAT(NEW.check_in, '%d/%m/%Y')),
        1,
        'high'
    FROM users u
    WHERE u.hotel_id = v_hotel_id 
    AND u.role IN ('admin', 'manager')
    AND u.is_active = 1;
    UPDATE room_reservations SET notification_sent = 1 WHERE id = NEW.id;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_notify_new_table_reservation;
DELIMITER $$
CREATE TRIGGER trg_notify_new_table_reservation
AFTER INSERT ON table_reservations
FOR EACH ROW
BEGIN
    DECLARE v_hotel_id INT;
    DECLARE v_table_number VARCHAR(20);
    SELECT hotel_id, table_number INTO v_hotel_id, v_table_number
    FROM restaurant_tables
    WHERE id = NEW.table_id;
    INSERT INTO system_notifications (hotel_id, user_id, notification_type, related_type, related_id, title, message, requires_sound, priority)
    SELECT 
        v_hotel_id,
        u.id,
        'new_reservation_table',
        'table_reservation',
        NEW.id,
        'Nueva Reservación de Mesa',
        CONCAT('Nueva reservación para mesa ', v_table_number, ' - Fecha: ', DATE_FORMAT(NEW.reservation_date, '%d/%m/%Y'), ' ', TIME_FORMAT(NEW.reservation_time, '%H:%i')),
        1,
        'high'
    FROM users u
    WHERE u.hotel_id = v_hotel_id 
    AND u.role IN ('admin', 'manager', 'hostess')
    AND u.is_active = 1;
    UPDATE table_reservations SET notification_sent = 1 WHERE id = NEW.id;
END$$
DELIMITER ;

-- ============================================================================
-- 7. INSERTAR PERMISOS POR DEFECTO PARA ROLES EXISTENTES
-- ============================================================================

INSERT INTO role_permissions (hotel_id, user_id, role_name, can_manage_rooms, can_manage_tables, can_manage_menu, created_at)
SELECT 
    u.hotel_id,
    u.id,
    u.role,
    CASE WHEN u.role IN ('admin', 'manager') THEN 1 ELSE 0 END,
    CASE WHEN u.role IN ('admin', 'manager', 'hostess') THEN 1 ELSE 0 END,
    CASE WHEN u.role IN ('admin', 'manager') THEN 1 ELSE 0 END,
    NOW()
FROM users u
WHERE u.hotel_id IS NOT NULL 
AND u.role IN ('admin', 'manager', 'hostess', 'collaborator')
ON DUPLICATE KEY UPDATE 
    role_name = VALUES(role_name),
    updated_at = NOW();

-- ============================================================================
-- 8. VISTA UNIFICADA DE RESERVACIONES
-- ============================================================================

CREATE OR REPLACE VIEW v_all_reservations AS
SELECT 
    'room' as reservation_type,
    rr.id,
    rr.status,
    rr.created_at,
    r.hotel_id,
    r.room_number as resource_number,
    rr.check_in as reservation_date,
    NULL as reservation_time,
    rr.guest_id,
    COALESCE(rr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
    COALESCE(rr.guest_email, u.email) as guest_email,
    COALESCE(rr.guest_phone, u.phone) as guest_phone,
    rr.total_price,
    rr.notes,
    rr.notification_sent
FROM room_reservations rr
JOIN rooms r ON rr.room_id = r.id
LEFT JOIN users u ON rr.guest_id = u.id

UNION ALL

SELECT 
    'table' as reservation_type,
    tr.id,
    tr.status,
    tr.created_at,
    rt.hotel_id,
    rt.table_number as resource_number,
    tr.reservation_date,
    tr.reservation_time,
    tr.guest_id,
    COALESCE(tr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
    COALESCE(tr.guest_email, u.email) as guest_email,
    COALESCE(tr.guest_phone, u.phone) as guest_phone,
    NULL as total_price,
    tr.notes,
    tr.notification_sent
FROM table_reservations tr
JOIN restaurant_tables rt ON tr.table_id = rt.id
LEFT JOIN users u ON tr.guest_id = u.id;

-- ============================================================================
-- VERIFICACIÓN FINAL
-- ============================================================================

SELECT 'Verificando precios de suscripciones...' as verificacion;
SELECT s.name, s.type, s.price, gs.setting_value as precio_configurado
FROM subscriptions s
LEFT JOIN global_settings gs ON (
    (s.type = 'monthly' AND gs.setting_key = 'plan_monthly_price') OR
    (s.type = 'annual' AND gs.setting_key = 'plan_annual_price')
)
WHERE s.type IN ('monthly', 'annual');

SELECT 'Verificando nuevas tablas...' as verificacion;
SELECT table_name
FROM INFORMATION_SCHEMA.TABLES
WHERE table_schema = DATABASE()
AND table_name IN ('role_permissions', 'system_notifications');

SELECT 'Verificando triggers...' as verificacion;
SELECT trigger_name
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE trigger_schema = DATABASE()
AND trigger_name LIKE 'trg_notify_%';

SELECT 'Migración completada exitosamente!' as resultado;
