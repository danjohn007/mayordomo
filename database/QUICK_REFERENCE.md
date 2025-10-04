# Referencia Rápida - Nuevas Funcionalidades de Base de Datos

## 🎯 Casos de Uso Comunes

### 1. Reservaciones de Habitaciones

#### Crear una nueva reservación
```sql
INSERT INTO room_reservations (
    room_id, guest_id, check_in, check_out, 
    total_price, status, guest_name, guest_email, guest_phone,
    number_of_guests, special_requests
) VALUES (
    1, 5, '2024-01-15', '2024-01-20',
    2500.00, 'pending', 'Juan Pérez', 'juan@email.com', '555-1234',
    2, 'Cama extra por favor'
);
-- El trigger automáticamente genera un confirmation_code
```

#### Confirmar reservación por email
```sql
UPDATE room_reservations 
SET email_confirmed = 1, 
    confirmed_at = NOW(), 
    status = 'confirmed'
WHERE confirmation_code = 'RR20240115XXXXX';
```

#### Verificar disponibilidad
```sql
CALL sp_check_room_availability(1, '2024-01-15', '2024-01-20');
```

### 2. Carrito de Compras y Pedidos

#### Crear carrito para usuario
```sql
INSERT INTO shopping_cart (user_id, hotel_id) 
VALUES (5, 1);
```

#### Agregar items al carrito
```sql
INSERT INTO cart_items (cart_id, dish_id, quantity, unit_price, special_instructions)
VALUES (1, 10, 2, 150.00, 'Sin cebolla');
```

#### Convertir carrito en orden
```sql
-- 1. Crear orden
INSERT INTO orders (
    hotel_id, guest_id, order_type, 
    total_amount, subtotal, tax_amount, 
    payment_method, payment_status
)
SELECT 
    c.hotel_id, c.user_id, 'dine_in',
    SUM(ci.quantity * ci.unit_price) * 1.16,  -- con IVA
    SUM(ci.quantity * ci.unit_price),
    SUM(ci.quantity * ci.unit_price) * 0.16,
    'cash', 'pending'
FROM shopping_cart c
JOIN cart_items ci ON c.id = ci.cart_id
WHERE c.id = 1
GROUP BY c.hotel_id, c.user_id;

-- 2. Copiar items a order_items
INSERT INTO order_items (order_id, dish_id, quantity, unit_price, subtotal, special_instructions)
SELECT 
    LAST_INSERT_ID(), ci.dish_id, ci.quantity, ci.unit_price,
    ci.quantity * ci.unit_price, ci.special_instructions
FROM cart_items ci
WHERE ci.cart_id = 1;

-- 3. Limpiar carrito
DELETE FROM cart_items WHERE cart_id = 1;
```

### 3. Pagos y Facturación

#### Registrar pago
```sql
INSERT INTO payment_transactions (
    order_id, user_id, amount, currency,
    payment_method, payment_gateway, transaction_id,
    status, processed_at
) VALUES (
    1, 5, 348.00, 'MXN',
    'stripe', 'stripe', 'ch_1234567890',
    'completed', NOW()
);
```

#### Generar factura
```sql
INSERT INTO invoices (
    hotel_id, user_id, order_id,
    invoice_date, subtotal, tax_rate, tax_amount,
    total_amount, status
) VALUES (
    1, 5, 1,
    CURDATE(), 300.00, 16.00, 48.00,
    348.00, 'sent'
);
-- El trigger genera automáticamente el invoice_number
```

#### Agregar líneas a la factura
```sql
INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, subtotal, tax_rate, tax_amount, total)
SELECT 
    LAST_INSERT_ID(),
    d.name,
    oi.quantity,
    oi.unit_price,
    oi.subtotal,
    16.00,
    oi.subtotal * 0.16,
    oi.subtotal * 1.16
FROM order_items oi
JOIN dishes d ON oi.dish_id = d.id
WHERE oi.order_id = 1;
```

### 4. Notificaciones

#### Crear notificación
```sql
INSERT INTO notifications (
    user_id, hotel_id, type, title, message,
    action_url, related_type, related_id, priority
) VALUES (
    5, 1, 'reservation', 'Reservación Confirmada',
    'Su reservación ha sido confirmada exitosamente',
    '/reservations/view/1', 'room_reservation', 1, 'high'
);
```

#### Marcar como leída
```sql
UPDATE notifications 
SET is_read = 1, read_at = NOW()
WHERE id = 1 AND user_id = 5;
```

#### Obtener notificaciones no leídas
```sql
SELECT * FROM notifications
WHERE user_id = 5 AND is_read = 0
ORDER BY created_at DESC
LIMIT 10;
```

### 5. Reportes y Estadísticas

#### Calcular ocupación del día
```sql
CALL sp_calculate_occupancy(1, CURDATE());
```

#### Ver ocupación de todos los hoteles
```sql
SELECT * FROM v_occupancy_rate;
```

#### Ver ingresos del mes
```sql
SELECT 
    hotel_id,
    SUM(total_revenue) as monthly_revenue,
    SUM(total_orders) as total_orders,
    AVG(total_revenue) as avg_daily_revenue
FROM v_daily_revenue
WHERE MONTH(revenue_date) = MONTH(CURDATE())
    AND YEAR(revenue_date) = YEAR(CURDATE())
GROUP BY hotel_id;
```

#### Crear reporte programado
```sql
INSERT INTO reports (
    hotel_id, created_by, report_type, name,
    schedule, format, recipients
) VALUES (
    1, 2, 'occupancy', 'Reporte Diario de Ocupación',
    'daily', 'pdf', 'admin@hotel.com,manager@hotel.com'
);
```

### 6. Gestión Multi-Hotel (Superadmin)

#### Listar todos los hoteles con su estado
```sql
SELECT 
    h.id,
    h.name,
    h.subscription_status,
    h.subscription_end_date,
    sp.name as plan_name,
    u.email as owner_email,
    (SELECT COUNT(*) FROM rooms WHERE hotel_id = h.id) as total_rooms,
    (SELECT COUNT(*) FROM users WHERE hotel_id = h.id) as total_staff
FROM hotels h
LEFT JOIN subscription_plans sp ON h.subscription_plan_id = sp.id
LEFT JOIN users u ON h.owner_id = u.id
ORDER BY h.created_at DESC;
```

#### Estadísticas globales del día
```sql
SELECT 
    COUNT(DISTINCT h.id) as total_hotels,
    SUM(CASE WHEN h.is_active = 1 THEN 1 ELSE 0 END) as active_hotels,
    (SELECT COUNT(*) FROM rooms) as total_rooms,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE()) as today_revenue
FROM hotels h;
```

#### Verificar límites del plan
```sql
SELECT 
    h.name,
    h.max_rooms,
    (SELECT COUNT(*) FROM rooms WHERE hotel_id = h.id) as current_rooms,
    h.max_rooms - (SELECT COUNT(*) FROM rooms WHERE hotel_id = h.id) as available_rooms,
    h.max_staff,
    (SELECT COUNT(*) FROM users WHERE hotel_id = h.id) as current_staff
FROM hotels h
WHERE h.id = 1;
```

### 7. Log de Actividad

#### Registrar acción importante
```sql
INSERT INTO activity_log (
    user_id, hotel_id, action, entity_type, entity_id,
    description, ip_address
) VALUES (
    2, 1, 'reservation_created', 'room_reservation', 123,
    'Nueva reservación creada para habitación 101',
    '192.168.1.1'
);
```

#### Ver actividad reciente de un hotel
```sql
SELECT 
    al.*,
    u.email as user_email,
    u.first_name,
    u.last_name
FROM activity_log al
LEFT JOIN users u ON al.user_id = u.id
WHERE al.hotel_id = 1
ORDER BY al.created_at DESC
LIMIT 50;
```

## 📊 Consultas Útiles para Dashboards

### Dashboard del Hotel
```sql
-- Estadísticas del día
SELECT 
    (SELECT COUNT(*) FROM rooms WHERE hotel_id = 1 AND status = 'occupied') as occupied_rooms,
    (SELECT COUNT(*) FROM rooms WHERE hotel_id = 1 AND status = 'available') as available_rooms,
    (SELECT COUNT(*) FROM room_reservations WHERE check_in = CURDATE()) as arrivals_today,
    (SELECT COUNT(*) FROM room_reservations WHERE check_out = CURDATE()) as departures_today,
    (SELECT COUNT(*) FROM orders WHERE hotel_id = 1 AND DATE(created_at) = CURDATE()) as orders_today,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE hotel_id = 1 AND DATE(created_at) = CURDATE()) as revenue_today;
```

### Dashboard del Superadmin
```sql
-- Resumen global
SELECT 
    (SELECT COUNT(*) FROM hotels WHERE is_active = 1) as active_hotels,
    (SELECT COUNT(*) FROM hotels WHERE subscription_status = 'trial') as trial_hotels,
    (SELECT COUNT(*) FROM users WHERE role IN ('admin', 'manager')) as total_managers,
    (SELECT COUNT(*) FROM room_reservations WHERE status = 'confirmed') as active_reservations,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE()) as global_revenue_today,
    (SELECT COUNT(*) FROM notifications WHERE is_read = 0) as unread_notifications;
```

### Reservaciones próximas a vencer (recordatorios)
```sql
SELECT 
    rr.*,
    r.room_number,
    u.email,
    u.first_name,
    u.last_name
FROM room_reservations rr
JOIN rooms r ON rr.room_id = r.id
JOIN users u ON rr.guest_id = u.id
WHERE rr.check_in = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    AND rr.status = 'confirmed'
    AND rr.email_confirmed = 1;
```

### Órdenes pendientes de pago
```sql
SELECT 
    o.*,
    u.email,
    u.first_name,
    u.last_name,
    h.name as hotel_name
FROM orders o
JOIN users u ON o.guest_id = u.id
JOIN hotels h ON o.hotel_id = h.id
WHERE o.payment_status = 'pending'
ORDER BY o.created_at;
```

## 🔍 Índices para Mejorar Performance

Los siguientes índices ya están creados por la migración:

```sql
-- Búsquedas por código de confirmación (muy común)
INDEX idx_confirmation ON room_reservations(confirmation_code)
INDEX idx_confirmation ON table_reservations(confirmation_code)

-- Búsquedas por estado de pago
INDEX idx_payment_status ON orders(payment_status)

-- Búsquedas por fecha para reportes
INDEX idx_created ON orders(created_at)
INDEX idx_date ON room_reservations(check_in, check_out)

-- Filtros comunes en vistas
INDEX idx_status ON various_tables(status)
INDEX idx_type ON various_tables(type)
```

## 💡 Tips de Optimización

1. **Usa las vistas** para consultas frecuentes en lugar de JOINs complejos
2. **Los triggers** manejan automáticamente códigos de confirmación y números de factura
3. **Los procedimientos almacenados** son más rápidos para operaciones complejas
4. **Cachea la disponibilidad** usando la tabla `availability_calendar`
5. **Usa transacciones** para operaciones que modifican múltiples tablas

### Ejemplo de transacción para crear orden completa:
```sql
START TRANSACTION;

-- Crear orden
INSERT INTO orders (...) VALUES (...);
SET @order_id = LAST_INSERT_ID();

-- Agregar items
INSERT INTO order_items (...) SELECT ... WHERE cart_id = 1;

-- Registrar pago
INSERT INTO payment_transactions (...) VALUES (...);

-- Actualizar estado
UPDATE orders SET payment_status = 'completed' WHERE id = @order_id;

-- Limpiar carrito
DELETE FROM cart_items WHERE cart_id = 1;

COMMIT;
```

---

**Nota**: Esta guía asume que ya ejecutaste la migración `migration_v1.1.0.sql`
