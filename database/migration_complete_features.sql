-- ========================================
-- MIGRATION COMPLETA: Nuevas Funcionalidades
-- Fecha: 2024
-- Descripción: Script SQL completo para actualizar la base de datos con todas las nuevas funcionalidades
-- ========================================

-- ========================================
-- 1. TABLA DE IMÁGENES PARA RECURSOS
-- ========================================

-- Crear tabla para almacenar imágenes de habitaciones, mesas y amenidades
CREATE TABLE IF NOT EXISTS resource_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type ENUM('room', 'table', 'amenity') NOT NULL,
    resource_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT = 'Almacena imágenes para habitaciones, mesas y amenidades. Múltiples imágenes por recurso permitidas.';

-- ========================================
-- 2. SISTEMA DE RESERVACIONES POR CHATBOT
-- ========================================

-- Crear tabla para reservaciones temporales/pendientes del chatbot
CREATE TABLE IF NOT EXISTS chatbot_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    resource_type ENUM('room', 'table', 'amenity') NOT NULL,
    resource_id INT NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    guest_email VARCHAR(255) NOT NULL,
    guest_phone VARCHAR(20) NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NULL,
    reservation_date DATETIME NULL COMMENT 'Para mesas y amenidades',
    reservation_time TIME NULL COMMENT 'Para mesas y amenidades',
    notes TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'expired') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    released_at TIMESTAMP NULL,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_hotel (hotel_id),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT = 'Almacena reservaciones públicas del chatbot con expiración automática';

-- ========================================
-- 3. PROCEDIMIENTO ALMACENADO: VERIFICAR DISPONIBILIDAD
-- ========================================

DELIMITER //

DROP PROCEDURE IF EXISTS check_resource_availability//

CREATE PROCEDURE check_resource_availability(
    IN p_resource_type VARCHAR(20),
    IN p_resource_id INT,
    IN p_check_in DATE,
    IN p_check_out DATE
)
BEGIN
    DECLARE conflicts INT DEFAULT 0;
    
    IF p_resource_type = 'room' THEN
        -- Verificar reservaciones de habitaciones
        SELECT COUNT(*) INTO conflicts
        FROM room_reservations
        WHERE room_id = p_resource_id
          AND status IN ('confirmed', 'checked_in')
          AND (
              (check_in_date <= p_check_in AND check_out_date > p_check_in)
              OR (check_in_date < p_check_out AND check_out_date >= p_check_out)
              OR (check_in_date >= p_check_in AND check_out_date <= p_check_out)
          );
    ELSEIF p_resource_type = 'table' THEN
        -- Verificar reservaciones de mesas (ventana de 2 horas)
        SELECT COUNT(*) INTO conflicts
        FROM table_reservations
        WHERE table_id = p_resource_id
          AND status IN ('confirmed', 'seated')
          AND reservation_date = p_check_in
          AND ABS(TIMESTAMPDIFF(MINUTE, reservation_time, TIME(p_check_out))) < 120;
    ELSEIF p_resource_type = 'amenity' THEN
        -- Verificar reservaciones de amenidades (ventana de 2 horas)
        SELECT COUNT(*) INTO conflicts
        FROM amenity_reservations
        WHERE amenity_id = p_resource_id
          AND status IN ('confirmed', 'in_use')
          AND reservation_date = p_check_in
          AND ABS(TIMESTAMPDIFF(MINUTE, reservation_time, TIME(p_check_out))) < 120;
    END IF;
    
    -- Retornar 0 si disponible, 1 si hay conflictos
    SELECT IF(conflicts > 0, 0, 1) as is_available;
END//

DELIMITER ;

-- ========================================
-- 4. EVENTOS AUTOMÁTICOS: LIBERACIÓN DE RECURSOS
-- ========================================

-- Habilitar el programador de eventos
SET GLOBAL event_scheduler = ON;

-- Evento para liberar mesas y amenidades después de 2 horas
DELIMITER //

DROP EVENT IF EXISTS auto_release_table_amenity_reservations//

CREATE EVENT auto_release_table_amenity_reservations
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    -- Liberar reservaciones de mesas después de 2 horas
    UPDATE table_reservations
    SET status = 'completed'
    WHERE status IN ('confirmed', 'seated')
      AND TIMESTAMPDIFF(HOUR, 
          CONCAT(reservation_date, ' ', reservation_time), 
          NOW()) >= 2;
    
    -- Liberar reservaciones de amenidades después de 2 horas
    UPDATE amenity_reservations
    SET status = 'completed'
    WHERE status IN ('confirmed', 'in_use')
      AND TIMESTAMPDIFF(HOUR, 
          CONCAT(reservation_date, ' ', reservation_time), 
          NOW()) >= 2;
END//

DELIMITER ;

-- Evento para liberar habitaciones a las 15:00 del día siguiente al checkout
DELIMITER //

DROP EVENT IF EXISTS auto_release_room_reservations//

CREATE EVENT auto_release_room_reservations
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    -- Liberar habitaciones a las 15:00 del día después del checkout
    UPDATE room_reservations
    SET status = 'checked_out'
    WHERE status = 'checked_in'
      AND check_out_date < CURDATE()
      AND HOUR(NOW()) >= 15;
      
    -- También actualizar el estado de las habitaciones
    UPDATE rooms r
    INNER JOIN room_reservations rr ON r.id = rr.room_id
    SET r.status = 'available'
    WHERE rr.status = 'checked_out'
      AND rr.check_out_date < CURDATE()
      AND r.status = 'occupied';
END//

DELIMITER ;

-- ========================================
-- 5. VERIFICACIÓN DE INSTALACIÓN
-- ========================================

-- Verificar que las tablas fueron creadas correctamente
SELECT 
    'resource_images' as tabla,
    COUNT(*) as registros
FROM resource_images
UNION ALL
SELECT 
    'chatbot_reservations' as tabla,
    COUNT(*) as registros
FROM chatbot_reservations;

-- Verificar que los eventos fueron creados
SHOW EVENTS WHERE Db = DATABASE();

-- Verificar que el procedimiento fue creado
SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'check_resource_availability';

-- ========================================
-- NOTAS IMPORTANTES
-- ========================================
-- 
-- 1. VALIDACIÓN DE TELÉFONO (10 dígitos):
--    - Implementada en la capa de aplicación (PHP)
--    - Afecta: registro público, nuevo usuario (admin), chatbot
--
-- 2. SOLICITUDES DE SERVICIO:
--    - Ahora incluyen iconos de editar y cancelar
--    - Los administradores pueden cambiar el estado
--
-- 3. CHATBOT PÚBLICO:
--    - Acceso: /chatbot/index/{hotel_id}
--    - Valida disponibilidad automáticamente
--    - Requiere teléfono de 10 dígitos
--    - Link disponible en "Mi Perfil" para admin/manager/hostess
--
-- 4. IMÁGENES DE RECURSOS:
--    - Soporta múltiples imágenes por habitación, mesa o amenidad
--    - Primera imagen se marca como principal
--    - Formatos: JPG, PNG, GIF
--    - Ubicación: /public/uploads/{rooms|tables|amenities}/
--
-- 5. LIBERACIÓN AUTOMÁTICA:
--    - Mesas: 2 horas después de la reservación
--    - Amenidades: 2 horas después de la reservación
--    - Habitaciones: 15:00 hrs del día siguiente al checkout
--
-- 6. PERMISOS REQUERIDOS:
--    - El usuario MySQL debe tener permisos para:
--      * CREATE TABLE
--      * CREATE PROCEDURE
--      * CREATE EVENT
--      * SET GLOBAL (para event_scheduler)
--
-- ========================================
-- FIN DE MIGRACIÓN
-- ========================================

SELECT '✓ Migración completada exitosamente' as resultado;
