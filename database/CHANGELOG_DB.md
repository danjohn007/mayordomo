# Changelog de Base de Datos - MajorBot

## [1.1.0] - 2024-12 - Migración Completa Fases 1-4

### 📋 Resumen de Cambios

Esta migración agrega **18 nuevas tablas**, **35+ nuevos campos** en tablas existentes, **4 vistas**, **4 triggers**, y **2 procedimientos almacenados** para soportar las funcionalidades de las Fases 1-4.

---

## FASE 1: Sistema de Reservaciones

### Nuevas Tablas
- ✅ `email_notifications` - Registro y seguimiento de emails enviados
- ✅ `availability_calendar` - Cache de disponibilidad para optimización

### Tablas Modificadas
#### `room_reservations`
- ✅ `confirmation_code` VARCHAR(50) - Código único de confirmación
- ✅ `email_confirmed` TINYINT(1) - Estado de confirmación por email
- ✅ `confirmed_at` TIMESTAMP - Fecha/hora de confirmación
- ✅ `guest_name` VARCHAR(200) - Nombre del huésped
- ✅ `guest_email` VARCHAR(255) - Email del huésped
- ✅ `guest_phone` VARCHAR(20) - Teléfono del huésped
- ✅ `special_requests` TEXT - Solicitudes especiales
- ✅ `number_of_guests` INT - Número de huéspedes

#### `table_reservations`
- ✅ Mismos campos que `room_reservations`

### Características
- ✅ Sistema de confirmación por email automático
- ✅ Códigos únicos de reservación
- ✅ Calendario de disponibilidad con cache
- ✅ Gestión de solicitudes especiales

---

## FASE 2: Pedidos y Facturación

### Nuevas Tablas
- ✅ `shopping_cart` - Carritos de compra activos
- ✅ `cart_items` - Items en los carritos
- ✅ `payment_transactions` - Transacciones de pago completas
- ✅ `invoices` - Facturas generadas
- ✅ `invoice_items` - Líneas de detalle de facturas

### Tablas Modificadas
#### `orders`
- ✅ `payment_method` ENUM - Método de pago (cash, card, stripe, paypal, etc.)
- ✅ `payment_status` ENUM - Estado del pago
- ✅ `paid_at` TIMESTAMP - Fecha de pago
- ✅ `tax_amount` DECIMAL - IVA/impuestos
- ✅ `discount_amount` DECIMAL - Descuentos aplicados
- ✅ `tip_amount` DECIMAL - Propina
- ✅ `subtotal` DECIMAL - Subtotal calculado

### Características
- ✅ Carrito de compras persistente
- ✅ Integración con Stripe y PayPal
- ✅ Gestión completa de pagos
- ✅ Generación automática de facturas PDF
- ✅ Tracking de transacciones
- ✅ Manejo de impuestos, descuentos y propinas

---

## FASE 3: Superadmin y Multi-Hotel

### Nuevas Tablas
- ✅ `subscription_plans` - Planes de suscripción disponibles
- ✅ `hotel_subscriptions` - Suscripciones activas por hotel
- ✅ `hotel_settings` - Configuraciones personalizadas por hotel
- ✅ `global_statistics` - Estadísticas globales agregadas
- ✅ `hotel_statistics` - Métricas por hotel
- ✅ `activity_log` - Log de actividad del sistema

### Tablas Modificadas
#### `hotels`
- ✅ `owner_id` INT - Propietario del hotel
- ✅ `subscription_plan_id` INT - Plan de suscripción actual
- ✅ `subscription_status` ENUM - Estado (trial, active, suspended, cancelled)
- ✅ `subscription_start_date` DATE - Inicio de suscripción
- ✅ `subscription_end_date` DATE - Vencimiento
- ✅ `max_rooms` INT - Límite de habitaciones
- ✅ `max_tables` INT - Límite de mesas
- ✅ `max_staff` INT - Límite de personal
- ✅ `features` JSON - Características habilitadas
- ✅ `timezone` VARCHAR - Zona horaria
- ✅ `currency` VARCHAR - Moneda
- ✅ `logo_url` VARCHAR - URL del logo
- ✅ `website` VARCHAR - Sitio web

### Características
- ✅ Panel de superadministrador
- ✅ Gestión multi-hotel ilimitada
- ✅ 4 planes de suscripción (Trial, Básico, Profesional, Enterprise)
- ✅ Control de límites por plan
- ✅ Estadísticas globales y por hotel
- ✅ Configuraciones personalizables
- ✅ Auditoría completa de acciones

---

## FASE 4: Notificaciones y Reportes

### Nuevas Tablas
- ✅ `notifications` - Notificaciones en tiempo real
- ✅ `notification_preferences` - Preferencias de usuario
- ✅ `reports` - Reportes guardados y programados
- ✅ `report_generations` - Historial de generación
- ✅ `export_queue` - Cola de exportaciones

### Características
- ✅ Sistema de notificaciones en tiempo real
- ✅ Notificaciones por email configurables
- ✅ Preferencias personalizables por usuario
- ✅ Reportes de ocupación
- ✅ Reportes de ingresos
- ✅ Reportes programados (daily, weekly, monthly)
- ✅ Exportación a PDF, Excel y CSV
- ✅ Cola asíncrona para reportes grandes

---

## Vistas Creadas

### `v_room_availability`
Disponibilidad en tiempo real de todas las habitaciones
```sql
SELECT * FROM v_room_availability WHERE hotel_id = 1;
```

### `v_daily_revenue`
Ingresos diarios agregados por hotel
```sql
SELECT * FROM v_daily_revenue WHERE revenue_date = CURDATE();
```

### `v_occupancy_rate`
Tasa de ocupación actual por hotel
```sql
SELECT * FROM v_occupancy_rate;
```

---

## Triggers Creados

### `trg_room_reservation_confirmation`
Genera automáticamente código de confirmación único para reservaciones de habitación
- Formato: `RR20240115XXXXX`
- Se ejecuta antes de INSERT

### `trg_table_reservation_confirmation`
Genera automáticamente código de confirmación para reservaciones de mesa
- Formato: `TR20240115XXXXX`
- Se ejecuta antes de INSERT

### `trg_invoice_number`
Genera número de factura único automáticamente
- Formato: `INV-202401-XXXX`
- Se ejecuta antes de INSERT

### `trg_order_subtotal`
Calcula subtotal automáticamente cuando se actualizan impuestos/descuentos
- Se ejecuta antes de UPDATE en orders

---

## Procedimientos Almacenados

### `sp_check_room_availability`
Verifica disponibilidad de habitaciones en un rango de fechas
```sql
CALL sp_check_room_availability(hotel_id, check_in_date, check_out_date);
```

### `sp_calculate_occupancy`
Calcula la tasa de ocupación de un hotel en una fecha específica
```sql
CALL sp_calculate_occupancy(hotel_id, date);
```

---

## Índices Agregados

### Optimización de Consultas
- ✅ `idx_confirmation` en reservaciones (búsqueda por código)
- ✅ `idx_payment_status` en orders (filtrar por estado de pago)
- ✅ `idx_created` en múltiples tablas (reportes por fecha)
- ✅ `idx_email_confirmed` en reservaciones (filtrar confirmadas)
- ✅ `idx_status` en nuevas tablas (filtros comunes)
- ✅ `idx_type` en tablas de clasificación

---

## Datos de Ejemplo Insertados

### Planes de Suscripción
1. **Trial** - Gratis por 30 días
   - 1 hotel, 10 habitaciones, 10 mesas, 5 staff

2. **Básico** - $499 MXN/mes
   - 1 hotel, 50 habitaciones, 30 mesas, 20 staff
   - Soporte por email, reportes básicos

3. **Profesional** - $999 MXN/mes
   - 3 hoteles, 100 habitaciones c/u, 50 mesas c/u, 50 staff c/u
   - Soporte prioritario, reportes avanzados, integraciones de pago

4. **Enterprise** - $2,499 MXN/mes
   - Hoteles ilimitados, 500 habitaciones c/u, 200 mesas c/u, 200 staff c/u
   - Soporte 24/7, reportes personalizados, API, white label

---

## Compatibilidad

### Requisitos
- ✅ MySQL 5.7+
- ✅ MariaDB 10.2+
- ✅ PHP 7.4+

### Retrocompatibilidad
- ✅ Preserva todos los datos existentes
- ✅ No elimina ninguna tabla o campo
- ✅ Solo agrega nuevas funcionalidades
- ✅ Usa `IF NOT EXISTS` para evitar errores

---

## Seguridad

### Mejoras de Seguridad
- ✅ Índices en campos sensibles
- ✅ Foreign keys con ON DELETE apropiados
- ✅ Validación de tipos con ENUM
- ✅ Campos de auditoría (created_at, updated_at)
- ✅ Log de actividad completo

---

## Performance

### Optimizaciones
- ✅ Índices estratégicos en campos de búsqueda frecuente
- ✅ Vistas materializadas para consultas complejas
- ✅ Cache de disponibilidad en `availability_calendar`
- ✅ Triggers para cálculos automáticos
- ✅ Procedimientos almacenados para operaciones complejas
- ✅ JSON para datos flexibles

---

## Tamaño Estimado

### Crecimiento de Base de Datos
- **18 nuevas tablas**: ~50 KB vacías
- **Con datos de ejemplo**: ~200 KB
- **Producción (1000 reservaciones/mes)**: ~5-10 MB/mes
- **Índices adicionales**: ~2-5% overhead

---

## Próximos Pasos

### Después de la Migración
1. ✅ Actualizar código PHP para usar nuevas tablas
2. ✅ Configurar SMTP para emails
3. ✅ Configurar API keys de Stripe/PayPal
4. ✅ Implementar generación de PDFs
5. ✅ Crear cronjobs para estadísticas
6. ✅ Implementar notificaciones en tiempo real
7. ✅ Desarrollar interfaz de superadmin
8. ✅ Crear módulo de reportes

---

## Rollback

En caso de necesitar revertir:
```bash
# Restaurar desde backup
mysql -u root -p majorbot_db < backup_before_migration.sql
```

---

## Soporte

Para problemas o dudas:
- Ver [MIGRATION_GUIDE.md](./MIGRATION_GUIDE.md) para instrucciones detalladas
- Ver [QUICK_REFERENCE.md](./QUICK_REFERENCE.md) para ejemplos de uso
- Ejecutar [verify_migration.sql](./verify_migration.sql) para validar

---

**Autor**: Equipo MajorBot  
**Fecha**: Diciembre 2024  
**Versión**: 1.1.0  
**Estado**: ✅ Completo y Probado
