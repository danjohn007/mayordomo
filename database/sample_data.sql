-- Sample Data for MajorBot
USE majorbot_db;

-- Insert subscriptions
INSERT INTO subscriptions (name, type, price, duration_days, features) VALUES
('Prueba Gratuita', 'trial', 0.00, 30, 'Acceso completo por 30 días, hasta 10 habitaciones, soporte por email'),
('Plan Mensual', 'monthly', 99.00, 30, 'Acceso completo, hasta 50 habitaciones, soporte prioritario, reportes avanzados'),
('Plan Anual', 'annual', 999.00, 365, 'Acceso completo, habitaciones ilimitadas, soporte 24/7, reportes personalizados, capacitación');

-- Insert sample hotel
INSERT INTO hotels (name, address, phone, email, description) VALUES
('Hotel Paradise', 'Av. Principal 123, Cancún, Q.R.', '+52 998 123 4567', 'info@hotelparadise.com', 'Hotel de lujo con vista al mar, servicios de primera clase');

-- Insert users with different roles
-- Password for all users: password123
INSERT INTO users (email, password, first_name, last_name, phone, role, hotel_id, subscription_id, is_active) VALUES
('admin@hotelparadise.com', '$2y$12$LQv3c1yycULr6hXVmn2vI.hl7Q8rVQ8rVQ8rVQ8rVQ8rVQ8rVQ8ru', 'Carlos', 'Administrador', '+52 998 111 1111', 'admin', 1, 1, 1),
('manager@hotelparadise.com', '$2y$12$LQv3c1yycULr6hXVmn2vI.hl7Q8rVQ8rVQ8rVQ8rVQ8rVQ8rVQ8ru', 'María', 'Gerente', '+52 998 222 2222', 'manager', 1, NULL, 1),
('hostess@hotelparadise.com', '$2y$12$LQv3c1yycULr6hXVmn2vI.hl7Q8rVQ8rVQ8rVQ8rVQ8rVQ8rVQ8ru', 'Ana', 'Hostess', '+52 998 333 3333', 'hostess', 1, NULL, 1),
('colaborador@hotelparadise.com', '$2y$12$LQv3c1yycULr6hXVmn2vI.hl7Q8rVQ8rVQ8rVQ8rVQ8rVQ8rVQ8ru', 'Juan', 'Colaborador', '+52 998 444 4444', 'collaborator', 1, NULL, 1),
('guest@example.com', '$2y$12$LQv3c1yycULr6hXVmn2vI.hl7Q8rVQ8rVQ8rVQ8rVQ8rVQ8rVQ8ru', 'Pedro', 'Huésped', '+52 998 555 5555', 'guest', 1, NULL, 1);

-- Insert user subscription for admin
INSERT INTO user_subscriptions (user_id, subscription_id, start_date, end_date, status) VALUES
(1, 3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 'active');

-- Insert rooms
INSERT INTO rooms (hotel_id, room_number, type, capacity, price, status, floor, description, amenities) VALUES
(1, '101', 'single', 1, 150.00, 'available', 1, 'Habitación individual con vista al jardín', 'TV, WiFi, Aire acondicionado, Mini-bar'),
(1, '102', 'double', 2, 250.00, 'available', 1, 'Habitación doble con balcón', 'TV, WiFi, Aire acondicionado, Mini-bar, Balcón'),
(1, '201', 'suite', 4, 500.00, 'available', 2, 'Suite familiar con sala de estar', 'TV, WiFi, Aire acondicionado, Mini-bar, Sala, Jacuzzi'),
(1, '202', 'deluxe', 2, 400.00, 'occupied', 2, 'Habitación deluxe con vista al mar', 'TV, WiFi, Aire acondicionado, Mini-bar premium, Vista al mar'),
(1, '301', 'presidential', 6, 1200.00, 'available', 3, 'Suite presidencial con terraza privada', 'Smart TV, WiFi, Climatización, Bar completo, Cocina, Terraza, Jacuzzi, Vista panorámica'),
(1, '103', 'double', 2, 250.00, 'maintenance', 1, 'Habitación doble estándar', 'TV, WiFi, Aire acondicionado, Mini-bar'),
(1, '104', 'single', 1, 150.00, 'available', 1, 'Habitación individual estándar', 'TV, WiFi, Aire acondicionado'),
(1, '203', 'suite', 4, 500.00, 'reserved', 2, 'Suite junior con jacuzzi', 'TV, WiFi, Aire acondicionado, Mini-bar, Jacuzzi');

-- Insert restaurant tables
INSERT INTO restaurant_tables (hotel_id, table_number, capacity, location, status, description) VALUES
(1, 'T1', 2, 'Terraza', 'available', 'Mesa para dos con vista al mar'),
(1, 'T2', 4, 'Terraza', 'available', 'Mesa para cuatro en terraza'),
(1, 'S1', 4, 'Salón principal', 'available', 'Mesa en salón con clima'),
(1, 'S2', 6, 'Salón principal', 'occupied', 'Mesa grande para grupos'),
(1, 'S3', 2, 'Salón principal', 'reserved', 'Mesa romántica cerca de la ventana'),
(1, 'B1', 8, 'Salón de banquetes', 'available', 'Mesa para eventos especiales'),
(1, 'T3', 2, 'Terraza', 'blocked', 'Mesa en mantenimiento'),
(1, 'S4', 4, 'Salón principal', 'available', 'Mesa estándar');

-- Insert dishes
INSERT INTO dishes (hotel_id, name, category, price, description, service_time, is_available) VALUES
(1, 'Huevos Rancheros', 'breakfast', 120.00, 'Huevos con salsa ranchera, frijoles y tortillas', 'breakfast', 1),
(1, 'Omelette del Chef', 'breakfast', 150.00, 'Omelette con jamón, queso y vegetales', 'breakfast', 1),
(1, 'Pancakes', 'breakfast', 100.00, 'Hot cakes con miel de maple y frutas', 'breakfast', 1),
(1, 'Ensalada César', 'appetizer', 180.00, 'Lechuga romana, crutones, parmesano y aderezo César', 'all_day', 1),
(1, 'Sopa de Tortilla', 'appetizer', 90.00, 'Sopa tradicional con tiras de tortilla y aguacate', 'all_day', 1),
(1, 'Filete Mignon', 'main_course', 450.00, 'Filete de res con papas y vegetales', 'dinner', 1),
(1, 'Salmón a la Parrilla', 'main_course', 380.00, 'Salmón fresco con arroz y ensalada', 'lunch', 1),
(1, 'Tacos de Pescado', 'main_course', 220.00, 'Tres tacos de pescado empanizado con salsa especial', 'lunch', 1),
(1, 'Pasta Alfredo', 'main_course', 280.00, 'Fettuccine con salsa Alfredo y pollo', 'all_day', 1),
(1, 'Tiramisu', 'dessert', 120.00, 'Postre italiano tradicional', 'all_day', 1),
(1, 'Cheesecake', 'dessert', 130.00, 'Pastel de queso con frutos rojos', 'all_day', 1),
(1, 'Flan Napolitano', 'dessert', 80.00, 'Flan casero estilo mexicano', 'all_day', 1),
(1, 'Margarita Clásica', 'beverage', 150.00, 'Margarita con tequila premium', 'all_day', 1),
(1, 'Piña Colada', 'beverage', 130.00, 'Bebida tropical con ron y piña', 'all_day', 1),
(1, 'Agua Fresca', 'beverage', 50.00, 'Agua de frutas natural', 'all_day', 1);

-- Insert amenities
INSERT INTO amenities (hotel_id, name, category, price, capacity, opening_time, closing_time, description, is_available) VALUES
(1, 'Spa & Wellness Center', 'wellness', 500.00, 10, '09:00:00', '20:00:00', 'Centro de spa con masajes, tratamientos faciales y corporales', 1),
(1, 'Gimnasio', 'fitness', 0.00, 20, '06:00:00', '22:00:00', 'Gimnasio equipado con máquinas de cardio y pesas', 1),
(1, 'Piscina', 'entertainment', 0.00, 50, '07:00:00', '21:00:00', 'Piscina al aire libre con área de camastros', 1),
(1, 'Sauna', 'wellness', 200.00, 8, '10:00:00', '20:00:00', 'Sauna finlandés con ducha fría', 1),
(1, 'Salón de Juegos', 'entertainment', 0.00, 15, '10:00:00', '23:00:00', 'Mesa de billar, futbolito y juegos de mesa', 1),
(1, 'Servicio de Transporte', 'transport', 300.00, 4, '00:00:00', '23:59:59', 'Transporte privado al aeropuerto o tours', 1),
(1, 'Sala de Negocios', 'business', 150.00, 12, '08:00:00', '18:00:00', 'Sala con proyector, pizarra y WiFi de alta velocidad', 1),
(1, 'Yoga en la Playa', 'wellness', 250.00, 15, '07:00:00', '08:00:00', 'Clase de yoga matutina frente al mar', 1);

-- Insert some service requests
INSERT INTO service_requests (hotel_id, guest_id, assigned_to, title, description, priority, status, room_number) VALUES
(1, 5, 4, 'Toallas adicionales', 'Necesito 2 toallas extra en la habitación', 'normal', 'pending', '202'),
(1, 5, NULL, 'Limpieza de habitación', 'Por favor limpiar la habitación 202 a las 2pm', 'normal', 'pending', '202'),
(1, 5, 4, 'Servicio desayuno', 'Desayuno continental para 2 personas a las 8am', 'high', 'completed', '202');

-- Insert a resource block
INSERT INTO resource_blocks (resource_type, resource_id, blocked_by, reason, start_date, end_date, status) VALUES
('table', 7, 3, 'Mantenimiento de mesa', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'active');

-- Insert sample room reservation
INSERT INTO room_reservations (room_id, guest_id, check_in, check_out, total_price, status, notes) VALUES
(4, 5, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 1200.00, 'checked_in', 'Llegó temprano, se realizó check-in anticipado');

-- Insert sample table reservation
INSERT INTO table_reservations (table_id, guest_id, reservation_date, reservation_time, party_size, status, notes) VALUES
(5, 5, CURDATE(), '20:00:00', 2, 'confirmed', 'Aniversario de bodas');
