-- ====================================================================
-- Script de Migración: Catálogo de Solicitudes de Servicio y Mejoras
-- Fecha: 2025-10-10
-- Descripción: 
--   - Crea catálogo de tipos de servicio
--   - Modifica service_requests para usar el catálogo
--   - Mantiene funcionalidad existente intacta
-- ====================================================================

-- ====================================================================
-- PASO 1: Crear tabla de catálogo de tipos de servicio
-- ====================================================================

CREATE TABLE IF NOT EXISTS service_type_catalog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'bi-wrench',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_hotel (hotel_id),
    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- PASO 2: Insertar tipos de servicio predeterminados para cada hotel
-- ====================================================================

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id as hotel_id,
    'Toallas' as name,
    'Solicitud de toallas adicionales' as description,
    'bi-droplet' as icon,
    1 as sort_order
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Toallas'
);

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id,
    'Menú / Room Service',
    'Solicitud de servicio a la habitación',
    'bi-egg-fried',
    2
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Menú / Room Service'
);

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id,
    'Conserje',
    'Solicitud de asistencia del conserje',
    'bi-person-badge',
    3
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Conserje'
);

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id,
    'Limpieza',
    'Solicitud de servicio de limpieza',
    'bi-brush',
    4
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Limpieza'
);

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id,
    'Mantenimiento',
    'Reporte de problema técnico o mantenimiento',
    'bi-tools',
    5
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Mantenimiento'
);

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id,
    'Amenidades',
    'Solicitud relacionada con amenidades del hotel',
    'bi-spa',
    6
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Amenidades'
);

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id,
    'Transporte',
    'Solicitud de servicio de transporte',
    'bi-car-front',
    7
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Transporte'
);

INSERT INTO service_type_catalog (hotel_id, name, description, icon, sort_order)
SELECT 
    h.id,
    'Otro',
    'Otras solicitudes de servicio',
    'bi-question-circle',
    99
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM service_type_catalog stc 
    WHERE stc.hotel_id = h.id AND stc.name = 'Otro'
);

-- ====================================================================
-- PASO 3: Modificar tabla service_requests
-- ====================================================================

-- Agregar nueva columna service_type_id (puede ser NULL para compatibilidad)
ALTER TABLE service_requests 
ADD COLUMN service_type_id INT NULL AFTER title;

-- Agregar la llave foránea
ALTER TABLE service_requests
ADD CONSTRAINT fk_service_type 
    FOREIGN KEY (service_type_id) 
    REFERENCES service_type_catalog(id) 
    ON DELETE SET NULL;

-- Crear índice para mejor rendimiento
ALTER TABLE service_requests
ADD INDEX idx_service_type (service_type_id);

-- ====================================================================
-- PASO 4: Migrar datos existentes (si hay)
-- ====================================================================

-- Intentar mapear títulos existentes a tipos de servicio
UPDATE service_requests sr
JOIN service_type_catalog stc ON sr.hotel_id = stc.hotel_id
SET sr.service_type_id = stc.id
WHERE sr.service_type_id IS NULL
AND (
    (LOWER(sr.title) LIKE '%toalla%' AND stc.name = 'Toallas')
    OR (LOWER(sr.title) LIKE '%comida%' AND stc.name = 'Menú / Room Service')
    OR (LOWER(sr.title) LIKE '%menu%' AND stc.name = 'Menú / Room Service')
    OR (LOWER(sr.title) LIKE '%room service%' AND stc.name = 'Menú / Room Service')
    OR (LOWER(sr.title) LIKE '%conserje%' AND stc.name = 'Conserje')
    OR (LOWER(sr.title) LIKE '%limpieza%' AND stc.name = 'Limpieza')
    OR (LOWER(sr.title) LIKE '%mantenimiento%' AND stc.name = 'Mantenimiento')
    OR (LOWER(sr.title) LIKE '%tecnico%' AND stc.name = 'Mantenimiento')
    OR (LOWER(sr.title) LIKE '%reparar%' AND stc.name = 'Mantenimiento')
    OR (LOWER(sr.title) LIKE '%amenidad%' AND stc.name = 'Amenidades')
    OR (LOWER(sr.title) LIKE '%transporte%' AND stc.name = 'Transporte')
    OR (LOWER(sr.title) LIKE '%taxi%' AND stc.name = 'Transporte')
);

-- Asignar "Otro" a solicitudes sin tipo específico
UPDATE service_requests sr
JOIN service_type_catalog stc ON sr.hotel_id = stc.hotel_id
SET sr.service_type_id = stc.id
WHERE sr.service_type_id IS NULL
AND stc.name = 'Otro';

-- ====================================================================
-- PASO 5: Verificación
-- ====================================================================

-- Mostrar resumen de cambios
SELECT 
    'service_type_catalog' as tabla,
    COUNT(*) as total_registros
FROM service_type_catalog
UNION ALL
SELECT 
    'service_requests con tipo' as tabla,
    COUNT(*) as total_registros
FROM service_requests
WHERE service_type_id IS NOT NULL
UNION ALL
SELECT 
    'service_requests sin tipo' as tabla,
    COUNT(*) as total_registros
FROM service_requests
WHERE service_type_id IS NULL;

-- Verificar estructura de tablas
SHOW COLUMNS FROM service_type_catalog;
SHOW COLUMNS FROM service_requests LIKE 'service_type_id';

-- ====================================================================
-- NOTAS IMPORTANTES:
-- ====================================================================
-- 1. El campo 'title' se mantiene en service_requests para descripción adicional
-- 2. El campo 'service_type_id' puede ser NULL para mantener compatibilidad
-- 3. Los datos existentes se migran automáticamente cuando es posible
-- 4. Cada hotel tiene su propio catálogo de tipos de servicio
-- 5. Los administradores pueden agregar/editar tipos desde la interfaz
-- ====================================================================
