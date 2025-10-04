# Guía de Migración de Base de Datos - MajorBot v1.1.0+

## 📋 Descripción General

Esta migración actualiza la base de datos de MajorBot de la versión 1.0.0 a la 1.1.0+, agregando soporte completo para:

- **Fase 1**: Sistema de Reservaciones con confirmación por email
- **Fase 2**: Pedidos, Facturación y Pagos (Stripe/PayPal)
- **Fase 3**: Panel de Superadministrador y gestión multi-hotel
- **Fase 4**: Notificaciones en tiempo real y sistema de reportes

## ⚠️ Requisitos Previos

1. **Backup de la base de datos actual**
   ```bash
   mysqldump -u root -p majorbot_db > backup_majorbot_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **MySQL 5.7 o superior**
3. **Permisos suficientes para crear tablas, vistas, triggers y procedimientos**

## 🚀 Pasos de Instalación

### Opción 1: Instalación Nueva (Recomendada para desarrollo)

Si estás instalando desde cero:

```bash
# 1. Crear la base de datos
mysql -u root -p -e "CREATE DATABASE majorbot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Importar el schema base
mysql -u root -p majorbot_db < database/schema.sql

# 3. Aplicar la migración
mysql -u root -p majorbot_db < database/migration_v1.1.0.sql

# 4. (Opcional) Importar datos de ejemplo
mysql -u root -p majorbot_db < database/sample_data.sql
```

### Opción 2: Migración desde v1.0.0 (Producción)

Si ya tienes datos en producción:

```bash
# 1. Hacer backup
mysqldump -u root -p majorbot_db > backup_majorbot_$(date +%Y%m%d_%H%M%S).sql

# 2. Aplicar SOLO la migración (preserva datos existentes)
mysql -u root -p majorbot_db < database/migration_v1.1.0.sql
```

## 📊 Nuevas Tablas Creadas

### Fase 1 - Reservaciones
- `email_notifications` - Registro de emails enviados
- `availability_calendar` - Cache de disponibilidad (optimización)

### Fase 2 - Pedidos y Facturación
- `shopping_cart` - Carritos de compra
- `cart_items` - Items en carritos
- `payment_transactions` - Transacciones de pago
- `invoices` - Facturas generadas
- `invoice_items` - Líneas de factura

### Fase 3 - Superadmin
- `subscription_plans` - Planes de suscripción
- `hotel_subscriptions` - Suscripciones por hotel
- `hotel_settings` - Configuraciones por hotel
- `global_statistics` - Estadísticas globales
- `hotel_statistics` - Estadísticas por hotel
- `activity_log` - Registro de actividad del sistema

### Fase 4 - Notificaciones y Reportes
- `notifications` - Notificaciones en tiempo real
- `notification_preferences` - Preferencias de notificaciones
- `reports` - Reportes guardados/programados
- `report_generations` - Historial de reportes generados
- `export_queue` - Cola de exportaciones

## 🔧 Campos Agregados a Tablas Existentes

### `room_reservations`
- `confirmation_code` - Código único de confirmación
- `email_confirmed` - Estado de confirmación por email
- `confirmed_at` - Fecha de confirmación
- `guest_name`, `guest_email`, `guest_phone` - Información del huésped
- `special_requests` - Solicitudes especiales
- `number_of_guests` - Número de huéspedes

### `table_reservations`
- Los mismos campos que room_reservations

### `orders`
- `payment_method` - Método de pago usado
- `payment_status` - Estado del pago
- `paid_at` - Fecha de pago
- `tax_amount` - Monto de impuestos
- `discount_amount` - Descuentos aplicados
- `tip_amount` - Propina
- `subtotal` - Subtotal calculado

### `hotels`
- `owner_id` - Propietario del hotel
- `subscription_plan_id` - Plan de suscripción
- `subscription_status` - Estado de la suscripción
- `subscription_start_date`, `subscription_end_date` - Fechas de suscripción
- `max_rooms`, `max_tables`, `max_staff` - Límites del plan
- `features` - Características JSON
- `timezone`, `currency` - Configuración regional
- `logo_url`, `website` - Información adicional

## 📈 Vistas Creadas

1. `v_room_availability` - Disponibilidad actual de habitaciones
2. `v_daily_revenue` - Ingresos diarios por hotel
3. `v_occupancy_rate` - Tasa de ocupación por hotel

### Ejemplo de uso:
```sql
-- Ver disponibilidad de habitaciones
SELECT * FROM v_room_availability WHERE hotel_id = 1;

-- Ver ingresos del día
SELECT * FROM v_daily_revenue WHERE revenue_date = CURDATE();

-- Ver ocupación actual
SELECT * FROM v_occupancy_rate;
```

## 🔄 Triggers Creados

1. `trg_room_reservation_confirmation` - Genera código de confirmación automáticamente
2. `trg_table_reservation_confirmation` - Genera código para reservas de mesa
3. `trg_invoice_number` - Genera número de factura único
4. `trg_order_subtotal` - Calcula subtotal automáticamente

## 📝 Procedimientos Almacenados

1. `sp_check_room_availability(hotel_id, check_in, check_out)` - Verifica disponibilidad
2. `sp_calculate_occupancy(hotel_id, date)` - Calcula tasa de ocupación

### Ejemplo de uso:
```sql
-- Verificar disponibilidad de habitaciones
CALL sp_check_room_availability(1, '2024-01-15', '2024-01-20');

-- Calcular ocupación
CALL sp_calculate_occupancy(1, CURDATE());
```

## 🎯 Datos de Ejemplo Insertados

La migración inserta automáticamente:

1. **4 Planes de Suscripción**:
   - Trial (Gratis - 30 días)
   - Básico ($499 MXN/mes)
   - Profesional ($999 MXN/mes)
   - Enterprise ($2,499 MXN/mes)

2. **Preferencias de Notificación** para todos los usuarios existentes

## ✅ Validación Post-Migración

Ejecuta estas consultas para verificar que la migración fue exitosa:

```sql
-- 1. Verificar nuevas tablas
SHOW TABLES;

-- 2. Verificar vistas
SHOW FULL TABLES WHERE Table_type = 'VIEW';

-- 3. Verificar triggers
SHOW TRIGGERS;

-- 4. Verificar procedimientos
SHOW PROCEDURE STATUS WHERE Db = 'majorbot_db';

-- 5. Verificar planes de suscripción
SELECT * FROM subscription_plans;

-- 6. Verificar que no hay errores en actividad
SELECT * FROM activity_log WHERE action = 'database_migration';

-- 7. Contar registros en tablas principales
SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM hotels) as total_hotels,
    (SELECT COUNT(*) FROM rooms) as total_rooms,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT COUNT(*) FROM subscription_plans) as total_plans;
```

## 🔐 Seguridad y Permisos

Asegúrate de actualizar los permisos del usuario de la base de datos:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON majorbot_db.* TO 'tu_usuario'@'localhost';
FLUSH PRIVILEGES;
```

## 📋 Tareas Post-Migración

### 1. Configuración de Email
Actualiza `config/email.php` con tus credenciales SMTP:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu_email@gmail.com');
define('SMTP_PASS', 'tu_contraseña');
```

### 2. Configuración de Pagos
Configura las API keys de Stripe/PayPal en `config/payment.php`:
```php
define('STRIPE_PUBLIC_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('PAYPAL_CLIENT_ID', 'tu_client_id');
define('PAYPAL_SECRET', 'tu_secret');
```

### 3. Asignar Suscripciones a Hoteles Existentes
```sql
-- Asignar plan Trial a hoteles existentes
INSERT INTO hotel_subscriptions (hotel_id, plan_id, start_date, end_date, status)
SELECT 
    h.id, 
    (SELECT id FROM subscription_plans WHERE slug = 'trial'),
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 30 DAY),
    'trial'
FROM hotels h
WHERE NOT EXISTS (
    SELECT 1 FROM hotel_subscriptions WHERE hotel_id = h.id
);
```

### 4. Configurar Cronjobs para Reportes
```bash
# Agregar a crontab para generar estadísticas diarias
0 1 * * * php /path/to/mayordomo/scripts/generate_statistics.php

# Enviar reportes programados
0 8 * * * php /path/to/mayordomo/scripts/send_scheduled_reports.php

# Limpiar notificaciones antiguas
0 2 * * 0 php /path/to/mayordomo/scripts/cleanup_old_notifications.php
```

## 🐛 Troubleshooting

### Error: "Table already exists"
Esto es normal si algunas tablas ya existían. La migración usa `IF NOT EXISTS` y `ADD COLUMN IF NOT EXISTS` para evitar errores.

### Error: "Trigger already exists"
```sql
DROP TRIGGER IF EXISTS nombre_del_trigger;
-- Luego vuelve a ejecutar la migración
```

### Error: "Cannot add foreign key constraint"
Verifica que las tablas referenciadas existan y tengan los índices correctos:
```sql
SHOW CREATE TABLE nombre_tabla;
```

### Verificar Integridad
```sql
-- Verificar constraints
SELECT 
    TABLE_NAME, 
    CONSTRAINT_NAME, 
    CONSTRAINT_TYPE 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_SCHEMA = 'majorbot_db';
```

## 🔄 Rollback (En caso de problemas)

Si necesitas revertir la migración:

```bash
# Restaurar desde el backup
mysql -u root -p majorbot_db < backup_majorbot_YYYYMMDD_HHMMSS.sql
```

## 📚 Documentación Adicional

- [README.md](../README.md) - Documentación principal
- [SYSTEM_OVERVIEW.md](../SYSTEM_OVERVIEW.md) - Resumen del sistema
- [CHANGELOG.md](../CHANGELOG.md) - Historial de cambios

## 🆘 Soporte

Si encuentras problemas durante la migración:

1. Revisa el archivo de log de MySQL: `/var/log/mysql/error.log`
2. Verifica que tienes permisos suficientes
3. Asegúrate de que la versión de MySQL es compatible (5.7+)
4. Abre un issue en GitHub con el error específico

## ✨ Próximos Pasos

Después de completar la migración:

1. ✅ Actualizar el código PHP para usar las nuevas tablas
2. ✅ Implementar los controladores para reservaciones
3. ✅ Configurar el sistema de pagos
4. ✅ Crear las vistas del panel de superadmin
5. ✅ Implementar el sistema de notificaciones
6. ✅ Desarrollar el módulo de reportes

---

**Versión de Migración**: 1.1.0  
**Fecha**: Diciembre 2024  
**Compatibilidad**: MySQL 5.7+ / MariaDB 10.2+
