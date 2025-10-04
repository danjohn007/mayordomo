-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-10-2025 a las 15:22:40
-- Versión del servidor: 5.7.23-23
-- Versión de PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `aqh_mayordomo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `hotel_id`, `action`, `entity_type`, `entity_id`, `description`, `ip_address`, `user_agent`, `data`, `created_at`) VALUES
(1, NULL, NULL, 'database_migration', NULL, NULL, 'Migración v1.0.0 a v1.1.0+ completada exitosamente', NULL, NULL, NULL, '2025-10-04 19:05:16'),
(2, 7, NULL, 'system_setup', 'system', NULL, 'Configuración inicial del sistema: Superadmin creado, planes de suscripción configurados, configuraciones globales establecidas', '127.0.0.1', NULL, NULL, '2025-10-04 19:59:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('wellness','fitness','entertainment','transport','business','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `capacity` int(11) DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `amenities`
--

INSERT INTO `amenities` (`id`, `hotel_id`, `name`, `category`, `price`, `capacity`, `opening_time`, `closing_time`, `description`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 'Spa & Wellness Center', 'wellness', 500.00, 10, '09:00:00', '20:00:00', 'Centro de spa con masajes, tratamientos faciales y corporales', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(2, 1, 'Gimnasio', 'fitness', 0.00, 20, '06:00:00', '22:00:00', 'Gimnasio equipado con máquinas de cardio y pesas', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(3, 1, 'Piscina', 'entertainment', 0.00, 50, '07:00:00', '21:00:00', 'Piscina al aire libre con área de camastros', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(4, 1, 'Sauna', 'wellness', 200.00, 8, '10:00:00', '20:00:00', 'Sauna finlandés con ducha fría', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(5, 1, 'Salón de Juegos', 'entertainment', 0.00, 15, '10:00:00', '23:00:00', 'Mesa de billar, futbolito y juegos de mesa', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(6, 1, 'Servicio de Transporte', 'transport', 300.00, 4, '00:00:00', '23:59:59', 'Transporte privado al aeropuerto o tours', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(7, 1, 'Sala de Negocios', 'business', 150.00, 12, '08:00:00', '18:00:00', 'Sala con proyector, pizarra y WiFi de alta velocidad', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(8, 1, 'Yoga en la Playa', 'wellness', 250.00, 15, '07:00:00', '08:00:00', 'Clase de yoga matutina frente al mar', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `availability_calendar`
--

CREATE TABLE `availability_calendar` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `resource_type` enum('room','table') COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  `available_slots` int(11) DEFAULT '0',
  `total_slots` int(11) DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dishes`
--

CREATE TABLE `dishes` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('appetizer','main_course','dessert','beverage','breakfast','lunch','dinner') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `service_time` enum('breakfast','lunch','dinner','all_day') COLLATE utf8mb4_unicode_ci DEFAULT 'all_day',
  `is_available` tinyint(1) DEFAULT '1',
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dishes`
--

INSERT INTO `dishes` (`id`, `hotel_id`, `name`, `category`, `price`, `description`, `service_time`, `is_available`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'Huevos Rancheros', 'breakfast', 120.00, 'Huevos con salsa ranchera, frijoles y tortillas', 'breakfast', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(2, 1, 'Omelette del Chef', 'breakfast', 150.00, 'Omelette con jamón, queso y vegetales', 'breakfast', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(3, 1, 'Pancakes', 'breakfast', 100.00, 'Hot cakes con miel de maple y frutas', 'breakfast', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(4, 1, 'Ensalada César', 'appetizer', 180.00, 'Lechuga romana, crutones, parmesano y aderezo César', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(5, 1, 'Sopa de Tortilla', 'appetizer', 90.00, 'Sopa tradicional con tiras de tortilla y aguacate', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(6, 1, 'Filete Mignon', 'main_course', 450.00, 'Filete de res con papas y vegetales', 'dinner', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(7, 1, 'Salmón a la Parrilla', 'main_course', 380.00, 'Salmón fresco con arroz y ensalada', 'lunch', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(8, 1, 'Tacos de Pescado', 'main_course', 220.00, 'Tres tacos de pescado empanizado con salsa especial', 'lunch', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(9, 1, 'Pasta Alfredo', 'main_course', 280.00, 'Fettuccine con salsa Alfredo y pollo', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(10, 1, 'Tiramisu', 'dessert', 120.00, 'Postre italiano tradicional', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(11, 1, 'Cheesecake', 'dessert', 130.00, 'Pastel de queso con frutos rojos', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(12, 1, 'Flan Napolitano', 'dessert', 80.00, 'Flan casero estilo mexicano', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(13, 1, 'Margarita Clásica', 'beverage', 150.00, 'Margarita con tequila premium', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(14, 1, 'Piña Colada', 'beverage', 130.00, 'Bebida tropical con ron y piña', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(15, 1, 'Agua Fresca', 'beverage', 50.00, 'Agua de frutas natural', 'all_day', 1, NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_notifications`
--

CREATE TABLE `email_notifications` (
  `id` int(11) NOT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_type` enum('reservation_confirmation','reservation_reminder','order_confirmation','payment_receipt','service_request','general') COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_type` enum('room_reservation','table_reservation','order','service_request','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `export_queue`
--

CREATE TABLE `export_queue` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `export_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `export_format` enum('pdf','excel','csv') COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` json DEFAULT NULL,
  `status` enum('queued','processing','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'queued',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `download_count` int(11) DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `global_settings`
--

CREATE TABLE `global_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('string','number','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `global_settings`
--

INSERT INTO `global_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `category`, `updated_at`, `updated_by`) VALUES
(1, 'trial_period_days', '30', 'number', 'Días de prueba gratuita para nuevos registros', 'subscription', '2025-10-04 19:59:09', NULL),
(2, 'trial_auto_activate', '1', 'boolean', 'Activar automáticamente periodo de prueba en registro', 'subscription', '2025-10-04 19:59:09', NULL),
(3, 'default_subscription_plan', '1', 'number', 'ID del plan de suscripción por defecto (Trial)', 'subscription', '2025-10-04 19:59:09', NULL),
(4, 'require_hotel_name_registration', '1', 'boolean', 'Requerir nombre del hotel en registro público', 'registration', '2025-10-04 19:59:09', NULL),
(5, 'public_registration_role', 'admin', 'string', 'Rol asignado en registro público (admin para propietarios)', 'registration', '2025-10-04 19:59:09', NULL),
(6, 'payment_gateway_stripe_enabled', '0', 'boolean', 'Habilitar Stripe como pasarela de pago', 'payment', '2025-10-04 19:59:09', NULL),
(7, 'payment_gateway_paypal_enabled', '0', 'boolean', 'Habilitar PayPal como pasarela de pago', 'payment', '2025-10-04 19:59:09', NULL),
(8, 'payment_gateway_mercadopago_enabled', '0', 'boolean', 'Habilitar MercadoPago como pasarela de pago', 'payment', '2025-10-04 19:59:09', NULL),
(9, 'subscription_block_on_expire', '1', 'boolean', 'Bloquear acceso al vencer suscripción', 'subscription', '2025-10-04 19:59:09', NULL),
(10, 'subscription_notification_days_before', '7', 'number', 'Días antes de vencimiento para enviar notificación', 'notification', '2025-10-04 19:59:09', NULL),
(11, 'subscription_auto_renew_default', '1', 'boolean', 'Activar renovación automática por defecto', 'subscription', '2025-10-04 19:59:09', NULL),
(12, 'invoice_auto_generate', '1', 'boolean', 'Generar facturas automáticamente', 'billing', '2025-10-04 19:59:09', NULL),
(13, 'system_currency_default', 'MXN', 'string', 'Moneda por defecto del sistema', 'general', '2025-10-04 19:59:09', NULL),
(14, 'system_timezone_default', 'America/Mexico_City', 'string', 'Zona horaria por defecto', 'general', '2025-10-04 19:59:09', NULL),
(15, 'superadmin_email', 'superadmin@mayorbot.com', 'string', 'Email del superadministrador principal', 'system', '2025-10-04 19:59:09', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `global_statistics`
--

CREATE TABLE `global_statistics` (
  `id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `stat_type` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_hotels` int(11) DEFAULT '0',
  `active_hotels` int(11) DEFAULT '0',
  `total_rooms` int(11) DEFAULT '0',
  `occupied_rooms` int(11) DEFAULT '0',
  `total_tables` int(11) DEFAULT '0',
  `occupied_tables` int(11) DEFAULT '0',
  `total_reservations` int(11) DEFAULT '0',
  `total_orders` int(11) DEFAULT '0',
  `total_revenue` decimal(12,2) DEFAULT '0.00',
  `total_users` int(11) DEFAULT '0',
  `active_subscriptions` int(11) DEFAULT '0',
  `data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `subscription_plan_id` int(11) DEFAULT NULL,
  `subscription_status` enum('trial','active','suspended','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'trial',
  `subscription_start_date` date DEFAULT NULL,
  `subscription_end_date` date DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `max_rooms` int(11) DEFAULT '50',
  `max_tables` int(11) DEFAULT '30',
  `max_staff` int(11) DEFAULT '20',
  `features` json DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'America/Mexico_City',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'MXN',
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `hotels`
--

INSERT INTO `hotels` (`id`, `owner_id`, `subscription_plan_id`, `subscription_status`, `subscription_start_date`, `subscription_end_date`, `name`, `address`, `phone`, `email`, `description`, `max_rooms`, `max_tables`, `max_staff`, `features`, `timezone`, `currency`, `logo_url`, `website`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'trial', NULL, NULL, 'Hotel Paradise', 'Av. Principal 123, Cancún, Q.R.', '+52 998 123 4567', 'info@hotelparadise.com', 'Hotel de lujo con vista al mar, servicios de primera clase', 50, 30, 20, NULL, 'America/Mexico_City', 'MXN', NULL, NULL, 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(2, 8, NULL, 'active', '2025-10-04', '2026-10-04', 'Domun Hotel', NULL, NULL, 'nath@domunhotel.com', NULL, 50, 30, 20, NULL, 'America/Mexico_City', 'MXN', NULL, NULL, 1, '2025-10-04 20:08:52', '2025-10-04 20:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hotel_settings`
--

CREATE TABLE `hotel_settings` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('string','number','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hotel_statistics`
--

CREATE TABLE `hotel_statistics` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `stat_type` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_rooms` int(11) DEFAULT '0',
  `occupied_rooms` int(11) DEFAULT '0',
  `occupancy_rate` decimal(5,2) DEFAULT '0.00',
  `total_reservations` int(11) DEFAULT '0',
  `total_orders` int(11) DEFAULT '0',
  `total_revenue` decimal(12,2) DEFAULT '0.00',
  `room_revenue` decimal(12,2) DEFAULT '0.00',
  `food_revenue` decimal(12,2) DEFAULT '0.00',
  `service_revenue` decimal(12,2) DEFAULT '0.00',
  `average_daily_rate` decimal(10,2) DEFAULT '0.00',
  `data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hotel_subscriptions`
--

CREATE TABLE `hotel_subscriptions` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('trial','active','expired','cancelled','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'trial',
  `auto_renew` tinyint(1) DEFAULT '1',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_payment_date` date DEFAULT NULL,
  `next_payment_date` date DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `reservation_type` enum('room','table') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT '0.00',
  `tax_amount` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'MXN',
  `status` enum('draft','sent','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `payment_terms` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT '0.00',
  `tax_amount` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `type` enum('info','success','warning','error','reservation','order','service','payment','system') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `email_enabled` tinyint(1) DEFAULT '1',
  `push_enabled` tinyint(1) DEFAULT '1',
  `sms_enabled` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `order_type` enum('dine_in','room_service','takeout') COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `tip_amount` decimal(10,2) DEFAULT '0.00',
  `subtotal` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('cash','credit_card','debit_card','stripe','paypal','room_charge','complimentary') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('pending','processing','completed','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','preparing','ready','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `reservation_type` enum('room','table') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'MXN',
  `payment_method` enum('cash','credit_card','debit_card','stripe','paypal','bank_transfer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_gateway` enum('stripe','paypal','manual') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_response` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','processing','completed','failed','refunded','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `report_type` enum('occupancy','revenue','reservations','orders','staff_performance','customer_satisfaction','custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parameters` json DEFAULT NULL,
  `schedule` enum('once','daily','weekly','monthly') COLLATE utf8mb4_unicode_ci DEFAULT 'once',
  `format` enum('pdf','excel','csv','html') COLLATE utf8mb4_unicode_ci DEFAULT 'pdf',
  `recipients` text COLLATE utf8mb4_unicode_ci,
  `last_generated_at` timestamp NULL DEFAULT NULL,
  `next_generation_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','paused','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `report_generations`
--

CREATE TABLE `report_generations` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `status` enum('generating','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'generating',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resource_blocks`
--

CREATE TABLE `resource_blocks` (
  `id` int(11) NOT NULL,
  `resource_type` enum('room','table','amenity') COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` int(11) NOT NULL,
  `blocked_by` int(11) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','released') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `resource_blocks`
--

INSERT INTO `resource_blocks` (`id`, `resource_type`, `resource_id`, `blocked_by`, `reason`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'table', 7, 3, 'Mantenimiento de mesa', '2025-10-04', '2025-10-07', 'active', '2025-10-04 18:02:35', '2025-10-04 18:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_tables`
--

CREATE TABLE `restaurant_tables` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `table_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('available','occupied','reserved','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restaurant_tables`
--

INSERT INTO `restaurant_tables` (`id`, `hotel_id`, `table_number`, `capacity`, `location`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'T1', 2, 'Terraza', 'available', 'Mesa para dos con vista al mar', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(2, 1, 'T2', 4, 'Terraza', 'available', 'Mesa para cuatro en terraza', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(3, 1, 'S1', 4, 'Salón principal', 'available', 'Mesa en salón con clima', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(4, 1, 'S2', 6, 'Salón principal', 'occupied', 'Mesa grande para grupos', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(5, 1, 'S3', 2, 'Salón principal', 'reserved', 'Mesa romántica cerca de la ventana', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(6, 1, 'B1', 8, 'Salón de banquetes', 'available', 'Mesa para eventos especiales', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(7, 1, 'T3', 2, 'Terraza', 'blocked', 'Mesa en mantenimiento', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(8, 1, 'S4', 4, 'Salón principal', 'available', 'Mesa estándar', '2025-10-04 18:02:35', '2025-10-04 18:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `room_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('single','double','suite','deluxe','presidential') COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('available','occupied','maintenance','reserved') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `floor` int(11) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amenities` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rooms`
--

INSERT INTO `rooms` (`id`, `hotel_id`, `room_number`, `type`, `capacity`, `price`, `status`, `floor`, `description`, `amenities`, `created_at`, `updated_at`) VALUES
(1, 1, '101', 'single', 1, 150.00, 'available', 1, 'Habitación individual con vista al jardín', 'TV, WiFi, Aire acondicionado, Mini-bar', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(2, 1, '102', 'double', 2, 250.00, 'available', 1, 'Habitación doble con balcón', 'TV, WiFi, Aire acondicionado, Mini-bar, Balcón', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(3, 1, '201', 'suite', 4, 500.00, 'available', 2, 'Suite familiar con sala de estar', 'TV, WiFi, Aire acondicionado, Mini-bar, Sala, Jacuzzi', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(4, 1, '202', 'deluxe', 2, 400.00, 'occupied', 2, 'Habitación deluxe con vista al mar', 'TV, WiFi, Aire acondicionado, Mini-bar premium, Vista al mar', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(5, 1, '301', 'presidential', 6, 1200.00, 'available', 3, 'Suite presidencial con terraza privada', 'Smart TV, WiFi, Climatización, Bar completo, Cocina, Terraza, Jacuzzi, Vista panorámica', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(6, 1, '103', 'double', 2, 250.00, 'maintenance', 1, 'Habitación doble estándar', 'TV, WiFi, Aire acondicionado, Mini-bar', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(7, 1, '104', 'single', 1, 150.00, 'available', 1, 'Habitación individual estándar', 'TV, WiFi, Aire acondicionado', '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(8, 1, '203', 'suite', 4, 500.00, 'reserved', 2, 'Suite junior con jacuzzi', 'TV, WiFi, Aire acondicionado, Mini-bar, Jacuzzi', '2025-10-04 18:02:35', '2025-10-04 18:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `room_reservations`
--

CREATE TABLE `room_reservations` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `guest_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','checked_in','checked_out','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `confirmation_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_confirmed` tinyint(1) DEFAULT '0',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `number_of_guests` int(11) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `room_reservations`
--

INSERT INTO `room_reservations` (`id`, `room_id`, `guest_id`, `guest_name`, `guest_email`, `guest_phone`, `check_in`, `check_out`, `total_price`, `status`, `confirmation_code`, `email_confirmed`, `confirmed_at`, `notes`, `special_requests`, `number_of_guests`, `created_at`, `updated_at`) VALUES
(1, 4, 5, NULL, NULL, NULL, '2025-10-04', '2025-10-07', 1200.00, 'checked_in', NULL, 0, NULL, 'Llegó temprano, se realizó check-in anticipado', NULL, 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `status` enum('pending','assigned','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `room_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `service_requests`
--

INSERT INTO `service_requests` (`id`, `hotel_id`, `guest_id`, `assigned_to`, `title`, `description`, `priority`, `status`, `room_number`, `requested_at`, `completed_at`) VALUES
(1, 1, 5, 4, 'Toallas adicionales', 'Necesito 2 toallas extra en la habitación', 'normal', 'pending', '202', '2025-10-04 18:02:35', NULL),
(2, 1, 5, NULL, 'Limpieza de habitación', 'Por favor limpiar la habitación 202 a las 2pm', 'normal', 'pending', '202', '2025-10-04 18:02:35', NULL),
(3, 1, 5, 4, 'Servicio desayuno', 'Desayuno continental para 2 personas a las 8am', 'high', 'completed', '202', '2025-10-04 18:02:35', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('trial','monthly','annual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `features` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `name`, `type`, `price`, `duration_days`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Plan Mensual', 'monthly', 99.00, 30, 'Acceso completo, hasta 50 habitaciones, soporte prioritario, reportes avanzados', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(3, 'Plan Anual', 'annual', 999.00, 365, 'Acceso completo, habitaciones ilimitadas, soporte 24/7, reportes personalizados, capacitación', 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `billing_cycle` enum('monthly','annual','lifetime') COLLATE utf8mb4_unicode_ci NOT NULL,
  `trial_days` int(11) DEFAULT '0',
  `max_hotels` int(11) DEFAULT '1',
  `max_rooms_per_hotel` int(11) DEFAULT '50',
  `max_tables_per_hotel` int(11) DEFAULT '30',
  `max_staff_per_hotel` int(11) DEFAULT '20',
  `features` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `description`, `price`, `billing_cycle`, `trial_days`, `max_hotels`, `max_rooms_per_hotel`, `max_tables_per_hotel`, `max_staff_per_hotel`, `features`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Plan Trial - Prueba Gratuita', 'trial', 'Plan de prueba gratuito configurable por Superadmin. Incluye acceso completo con límites básicos.', 0.00, 'monthly', 30, 1, 10, 10, 5, '{\"soporte\": \"Email básico\", \"reportes\": \"Básicos\", \"mesas_max\": 10, \"descripcion\": \"Prueba gratuita con todas las funcionalidades\", \"multi_hotel\": false, \"personal_max\": 5, \"integraciones\": false, \"habitaciones_max\": 10}', 1, 1, '2025-10-04 19:59:09', '2025-10-04 19:59:09'),
(2, 'Plan Mensual - Básico', 'monthly', 'Plan mensual con pago recurrente. Ideal para hoteles pequeños y medianos.', 99.00, 'monthly', 0, 1, 50, 30, 20, '{\"soporte\": \"Email prioritario\", \"reportes\": \"Avanzados\", \"mesas_max\": 30, \"descripcion\": \"Plan mensual con acceso completo\", \"multi_hotel\": false, \"personal_max\": 20, \"integraciones\": \"Stripe, PayPal\", \"habitaciones_max\": 50, \"notificaciones_sms\": false, \"notificaciones_email\": true}', 1, 2, '2025-10-04 19:59:09', '2025-10-04 19:59:09'),
(3, 'Plan Anual - Profesional', 'annual', 'Plan anual con descuento significativo. Pago único anual con todas las funcionalidades premium.', 999.00, 'annual', 0, 3, 150, 80, 50, '{\"soporte\": \"24/7 prioritario\", \"reportes\": \"Personalizados y exportables\", \"mesas_max\": 80, \"descripcion\": \"Plan anual con máximo ahorro\", \"multi_hotel\": true, \"capacitacion\": true, \"personal_max\": 50, \"integraciones\": \"Stripe, PayPal, MercadoPago\", \"descuento_anual\": \"16% vs mensual\", \"habitaciones_max\": 150, \"notificaciones_sms\": true, \"notificaciones_email\": true}', 1, 3, '2025-10-04 19:59:09', '2025-10-04 19:59:09'),
(4, 'Plan Enterprise - Ilimitado', 'enterprise', 'Plan corporativo sin límites. Para cadenas hoteleras grandes con necesidades especiales.', 2999.00, 'annual', 0, 999, 999, 999, 999, '{\"soporte\": \"Dedicado 24/7 con gestor de cuenta\", \"reportes\": \"Personalizados con BI\", \"mesas_max\": \"ilimitadas\", \"api_acceso\": true, \"descripcion\": \"Plan corporativo sin límites\", \"multi_hotel\": true, \"white_label\": true, \"capacitacion\": \"Ilimitada\", \"personal_max\": \"ilimitado\", \"customizacion\": true, \"integraciones\": \"Todas las pasarelas disponibles\", \"habitaciones_max\": \"ilimitadas\", \"notificaciones_sms\": true, \"notificaciones_push\": true, \"notificaciones_email\": true}', 1, 4, '2025-10-04 19:59:09', '2025-10-04 19:59:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `table_reservations`
--

CREATE TABLE `table_reservations` (
  `id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `guest_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `party_size` int(11) NOT NULL,
  `status` enum('pending','confirmed','seated','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `confirmation_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_confirmed` tinyint(1) DEFAULT '0',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `table_reservations`
--

INSERT INTO `table_reservations` (`id`, `table_id`, `guest_id`, `guest_name`, `guest_email`, `guest_phone`, `reservation_date`, `reservation_time`, `party_size`, `status`, `confirmation_code`, `email_confirmed`, `confirmed_at`, `notes`, `special_requests`, `created_at`, `updated_at`) VALUES
(1, 5, 5, NULL, NULL, NULL, '2025-10-04', '20:00:00', 2, 'confirmed', NULL, 0, NULL, 'Aniversario de bodas', NULL, '2025-10-04 18:02:35', '2025-10-04 18:02:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('superadmin','admin','manager','hostess','collaborator','guest') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guest',
  `hotel_id` int(11) DEFAULT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `phone`, `role`, `hotel_id`, `subscription_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin@hotelparadise.com', '$2y$10$u/Y2ma/LLTna/Yt6sX5SieIQzAlEgrK5.WkYWydtfOZgjuc0kJWTu', 'Carlos', 'Administrador', '+52 998 111 1111', 'admin', 1, 1, 1, '2025-10-04 18:02:35', '2025-10-04 18:09:18'),
(2, 'manager@hotelparadise.com', '$2y$10$GZkOls6kp6W3TZPeShslEebo6jWFysvY5oeGuvf3TCYPr7nyZT2P2', 'María', 'Gerente', '+52 998 222 2222', 'manager', 1, NULL, 1, '2025-10-04 18:02:35', '2025-10-04 19:29:07'),
(3, 'hostess@hotelparadise.com', '$2y$12$LQv3c1yycULr6hXVmn2vI.hl7Q8rVQ8rVQ8rVQ8rVQ8rVQ8rVQ8ru', 'Ana', 'Hostess', '+52 998 333 3333', 'hostess', 1, NULL, 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(4, 'colaborador@hotelparadise.com', '$2y$12$LQv3c1yycULr6hXVmn2vI.hl7Q8rVQ8rVQ8rVQ8rVQ8rVQ8rVQ8ru', 'Juan', 'Colaborador', '+52 998 444 4444', 'collaborator', 1, NULL, 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(5, 'guest@example.com', '$2y$12$LQv3c1yycULr6hXVmn2vI.hl7Q8rVQ8rVQ8rVQ8rVQ8rVQ8rVQ8ru', 'Pedro', 'Huésped', '+52 998 555 5555', 'guest', 1, NULL, 1, '2025-10-04 18:02:35', '2025-10-04 18:02:35'),
(6, 'dan@impactosdigitales.com', '$2y$12$d.645qjwg3i0jNYVR11xIOBw.WE7ck46jlshwigYp7Ycr25G1mQJq', 'Dan', 'Raso', '4425986318', 'guest', NULL, 1, 1, '2025-10-04 18:10:59', '2025-10-04 18:10:59'),
(7, 'superadmin@mayorbot.com', '$2y$10$Ht7lH82J8HEdZ7Uw.n4JPOBb2ZIj3YZCL.HWssjI9F2XSHEMfppCS', 'Super', 'Administrador', '+52 999 999 9999', 'superadmin', NULL, NULL, 1, '2025-10-04 19:59:09', '2025-10-04 20:00:35'),
(8, 'nath@domunhotel.com', '$2y$12$Cv3xN//9BeTNZmKPZEFD1OO6DnXAJ3/ylQgkACIpeDdzhOFyb6GWa', 'Nath', 'Justo', '4425986320', 'admin', 2, 3, 1, '2025-10-04 20:08:52', '2025-10-04 20:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_subscriptions`
--

INSERT INTO `user_subscriptions` (`id`, `user_id`, `subscription_id`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 1, 3, '2025-10-04', '2026-10-04', 'active', '2025-10-04 18:02:35'),
(2, 8, 3, '2025-10-04', '2026-10-04', 'active', '2025-10-04 20:08:52');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_category` (`category`);

--
-- Indices de la tabla `availability_calendar`
--
ALTER TABLE `availability_calendar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_availability` (`hotel_id`,`resource_type`,`resource_id`,`date`),
  ADD KEY `idx_hotel_date` (`hotel_id`,`date`),
  ADD KEY `idx_resource` (`resource_type`,`resource_id`,`date`);

--
-- Indices de la tabla `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_id` (`dish_id`),
  ADD KEY `idx_cart` (`cart_id`);

--
-- Indices de la tabla `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_available` (`is_available`),
  ADD KEY `idx_price` (`price`);

--
-- Indices de la tabla `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipient` (`recipient_email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`notification_type`),
  ADD KEY `idx_related` (`related_type`,`related_id`);

--
-- Indices de la tabla `export_queue`
--
ALTER TABLE `export_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `global_settings`
--
ALTER TABLE `global_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indices de la tabla `global_statistics`
--
ALTER TABLE `global_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stat` (`stat_date`,`stat_type`),
  ADD KEY `idx_date` (`stat_date`),
  ADD KEY `idx_type` (`stat_type`);

--
-- Indices de la tabla `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_owner` (`owner_id`),
  ADD KEY `idx_subscription` (`subscription_status`);

--
-- Indices de la tabla `hotel_settings`
--
ALTER TABLE `hotel_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hotel_setting` (`hotel_id`,`setting_key`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_category` (`category`);

--
-- Indices de la tabla `hotel_statistics`
--
ALTER TABLE `hotel_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hotel_stat` (`hotel_id`,`stat_date`,`stat_type`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_date` (`stat_date`),
  ADD KEY `idx_type` (`stat_type`);

--
-- Indices de la tabla `hotel_subscriptions`
--
ALTER TABLE `hotel_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indices de la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_invoice_number` (`invoice_number`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`invoice_date`,`due_date`);

--
-- Indices de la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_type` (`user_id`,`notification_type`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_id` (`dish_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indices de la tabla `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_transaction` (`transaction_id`);

--
-- Indices de la tabla `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_type` (`report_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `report_generations`
--
ALTER TABLE `report_generations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report` (`report_id`),
  ADD KEY `idx_generated` (`generated_at`);

--
-- Indices de la tabla `resource_blocks`
--
ALTER TABLE `resource_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blocked_by` (`blocked_by`),
  ADD KEY `idx_resource` (`resource_type`,`resource_id`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indices de la tabla `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_table` (`hotel_id`,`table_number`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_room` (`hotel_id`,`room_number`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_price` (`price`);

--
-- Indices de la tabla `room_reservations`
--
ALTER TABLE `room_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `confirmation_code` (`confirmation_code`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `idx_room` (`room_id`),
  ADD KEY `idx_dates` (`check_in`,`check_out`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_confirmation` (`confirmation_code`),
  ADD KEY `idx_email_confirmed` (`email_confirmed`);

--
-- Indices de la tabla `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created` (`requested_at`);

--
-- Indices de la tabla `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_session` (`session_id`);

--
-- Indices de la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indices de la tabla `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `confirmation_code` (`confirmation_code`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `idx_table` (`table_id`),
  ADD KEY `idx_date` (`reservation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_confirmation` (`confirmation_code`),
  ADD KEY `idx_email_confirmed` (`email_confirmed`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_hotel` (`hotel_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `availability_calendar`
--
ALTER TABLE `availability_calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `email_notifications`
--
ALTER TABLE `email_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `export_queue`
--
ALTER TABLE `export_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `global_settings`
--
ALTER TABLE `global_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `global_statistics`
--
ALTER TABLE `global_statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `hotel_settings`
--
ALTER TABLE `hotel_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hotel_statistics`
--
ALTER TABLE `hotel_statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hotel_subscriptions`
--
ALTER TABLE `hotel_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `report_generations`
--
ALTER TABLE `report_generations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resource_blocks`
--
ALTER TABLE `resource_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `room_reservations`
--
ALTER TABLE `room_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `table_reservations`
--
ALTER TABLE `table_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_log_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `amenities`
--
ALTER TABLE `amenities`
  ADD CONSTRAINT `amenities_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `availability_calendar`
--
ALTER TABLE `availability_calendar`
  ADD CONSTRAINT `availability_calendar_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dishes`
--
ALTER TABLE `dishes`
  ADD CONSTRAINT `dishes_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `export_queue`
--
ALTER TABLE `export_queue`
  ADD CONSTRAINT `export_queue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `export_queue_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `global_settings`
--
ALTER TABLE `global_settings`
  ADD CONSTRAINT `global_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `hotels`
--
ALTER TABLE `hotels`
  ADD CONSTRAINT `hotels_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `hotel_settings`
--
ALTER TABLE `hotel_settings`
  ADD CONSTRAINT `hotel_settings_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `hotel_statistics`
--
ALTER TABLE `hotel_statistics`
  ADD CONSTRAINT `hotel_statistics_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `hotel_subscriptions`
--
ALTER TABLE `hotel_subscriptions`
  ADD CONSTRAINT `hotel_subscriptions_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hotel_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Filtros para la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`);

--
-- Filtros para la tabla `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payment_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `report_generations`
--
ALTER TABLE `report_generations`
  ADD CONSTRAINT `report_generations_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resource_blocks`
--
ALTER TABLE `resource_blocks`
  ADD CONSTRAINT `resource_blocks_ibfk_1` FOREIGN KEY (`blocked_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD CONSTRAINT `restaurant_tables_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `room_reservations`
--
ALTER TABLE `room_reservations`
  ADD CONSTRAINT `room_reservations_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_reservations_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `service_requests_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD CONSTRAINT `table_reservations_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `table_reservations_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
