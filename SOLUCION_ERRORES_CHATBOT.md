# 🔧 Solución de Errores del Chatbot y Sistema

## 📋 Problemas Resueltos

Este documento detalla la solución completa para los siguientes errores:

### 1. ❌ Error SQLSTATE[HY000]: 1442 - room_reservations
**Problema:**
```
Error al crear la reservación: SQLSTATE[HY000]: General error: 1442 
Can't update table 'room_reservations' in stored function/trigger 
because it is already used by statement which invoked this stored function/trigger.
```

**Causa:**
El trigger `trg_notify_new_room_reservation` intentaba actualizar la misma tabla (`room_reservations`) que lo disparó, lo cual está prohibido en MySQL.

**Solución:**
✅ Se eliminó la sentencia `UPDATE room_reservations SET notification_sent = 1` del trigger.
✅ El campo `notification_sent` ya no es necesario actualizarlo dentro del trigger.
✅ Las notificaciones se crean correctamente sin intentar actualizar la tabla origen.

---

### 2. ❌ Error SQLSTATE[HY000]: 1442 - table_reservations
**Problema:**
```
Error al crear la reservación: SQLSTATE[HY000]: General error: 1442 
Can't update table 'table_reservations' in stored function/trigger 
because it is already used by statement which invoked this stored function/trigger.
```

**Causa:**
El trigger `trg_notify_new_table_reservation` intentaba actualizar la misma tabla (`table_reservations`) que lo disparó.

**Solución:**
✅ Se eliminó la sentencia `UPDATE table_reservations SET notification_sent = 1` del trigger.
✅ El campo `notification_sent` permanece pero no se actualiza desde el trigger.
✅ Las notificaciones se crean correctamente sin intentar actualizar la tabla origen.

---

### 3. ❌ Error SQLSTATE[42S22]: Column not found 'rp.amenities_access'
**Problema:**
```
Error al crear la reservación: SQLSTATE[42S22]: Column not found: 1054 
Unknown column 'rp.amenities_access' in 'where clause'
```

**Causa:**
El trigger `trg_amenity_reservation_notification` usaba el campo `rp.amenities_access` que no existe en la tabla `role_permissions`. El campo correcto es `amenity_ids`.

**Solución:**
✅ Se corrigió la consulta para usar `rp.amenity_ids` en lugar de `rp.amenities_access`.
✅ Se implementó lógica para verificar si el usuario tiene acceso a todas las amenidades (`amenity_ids = 'all'`).
✅ Se implementó búsqueda del ID específico de amenidad dentro del JSON array almacenado.
✅ Se separa la notificación para admin/manager (acceso completo) de colaboradores (acceso por permisos).

---

### 4. ❌ Error en Calendario: Column not found 'check_in_date'
**Problema:**
El controlador de calendario usaba columnas incorrectas:
- `check_in_date` (no existe)
- `check_out_date` (no existe)

Las columnas correctas en la tabla `room_reservations` son:
- `check_in`
- `check_out`

**Solución:**
✅ Se corrigieron todas las referencias en `CalendarController.php`.
✅ Se agregó `LEFT JOIN` con tabla `users` para obtener nombre del huésped.
✅ Se usa `COALESCE` para mostrar el nombre del huésped registrado o del chatbot.
✅ Se corrigió `hotel_id` para usar el del recurso (habitación/mesa) en lugar del de la reservación.

---

## 📂 Archivos Modificados

### 1. `/database/fix_trigger_and_calendar_errors.sql` (NUEVO)
Script SQL completo que contiene:
- Recreación de `trg_notify_new_room_reservation` (sin UPDATE)
- Recreación de `trg_notify_new_table_reservation` (sin UPDATE)
- Corrección de `trg_amenity_reservation_notification` (campo amenity_ids)
- Verificación y creación de campos `notification_sent` (si no existen)
- Actualización de registros existentes para marcarlos como notificados
- Verificaciones finales

### 2. `/app/controllers/CalendarController.php`
**Cambios en línea 47-65:**
```php
// ANTES:
rr.check_in_date,
rr.check_out_date,
WHERE rr.hotel_id = ?
AND (rr.check_in_date BETWEEN ? AND ?)

// DESPUÉS:
rr.check_in,
rr.check_out,
COALESCE(rr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
WHERE r.hotel_id = ?
AND (rr.check_in BETWEEN ? AND ?)
```

**Cambios en línea 88-104:**
```php
// ANTES:
WHERE tr.hotel_id = ?

// DESPUÉS:
COALESCE(tr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
WHERE t.hotel_id = ?
```

**Cambios en línea 128-143:**
```php
// DESPUÉS:
COALESCE(ar.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
LEFT JOIN users u ON ar.user_id = u.id
```

---

## 🚀 Instrucciones de Instalación

### Paso 1: Ejecutar el Script SQL

**Opción A: Desde línea de comandos**
```bash
mysql -u usuario -p nombre_base_datos < database/fix_trigger_and_calendar_errors.sql
```

**Opción B: Desde phpMyAdmin**
1. Abrir phpMyAdmin
2. Seleccionar la base de datos (ej: `aqh_mayordomo`)
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido de `database/fix_trigger_and_calendar_errors.sql`
5. Hacer clic en "Ejecutar"

**Opción C: Desde MySQL Workbench**
1. Conectar a la base de datos
2. Archivo → Abrir SQL Script
3. Seleccionar `database/fix_trigger_and_calendar_errors.sql`
4. Ejecutar (icono de rayo ⚡)

### Paso 2: Verificar la Instalación

Ejecutar las siguientes consultas para verificar:

```sql
-- Verificar que los triggers se crearon correctamente
SELECT 
    trigger_name,
    event_manipulation,
    event_object_table,
    action_timing
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
AND (
    trigger_name LIKE 'trg_notify_%'
    OR trigger_name LIKE 'trg_amenity_%'
);

-- Debe mostrar:
-- trg_notify_new_room_reservation
-- trg_notify_new_table_reservation
-- trg_amenity_reservation_confirmation
-- trg_amenity_reservation_notification
```

```sql
-- Verificar campo notification_sent en todas las tablas
DESCRIBE room_reservations;
DESCRIBE table_reservations;
DESCRIBE amenity_reservations;

-- Cada una debe tener el campo:
-- notification_sent | tinyint(1) | YES | | 0 |
```

### Paso 3: Actualizar Archivos de Código

Los archivos ya están actualizados en el repositorio. Si usas control de versiones:

```bash
git pull origin main
```

Si trabajas directamente, asegúrate de tener la última versión de:
- `app/controllers/CalendarController.php`

---

## ✅ Verificación de Funcionalidad

### Prueba 1: Reservación de Habitación vía Chatbot
1. Abrir el chatbot sin sesión de usuario
2. Reservar una habitación
3. **Verificar:** No debe aparecer error 1442
4. **Verificar:** La reservación se crea correctamente
5. **Verificar:** Los admin/manager reciben notificación con sonido

### Prueba 2: Reservación de Mesa vía Chatbot
1. Abrir el chatbot sin sesión de usuario
2. Reservar una mesa
3. **Verificar:** No debe aparecer error 1442
4. **Verificar:** La reservación se crea correctamente
5. **Verificar:** Los admin/manager/hostess reciben notificación

### Prueba 3: Reservación de Amenidad vía Chatbot
1. Abrir el chatbot sin sesión de usuario
2. Reservar una amenidad (gym, pool, spa, etc.)
3. **Verificar:** No debe aparecer error "amenities_access"
4. **Verificar:** La reservación se crea correctamente
5. **Verificar:** Los usuarios con permisos reciben notificación

### Prueba 4: Calendario
1. Iniciar sesión como admin/manager
2. Ir a "Calendario" en el menú lateral
3. **Verificar:** Las reservaciones de habitaciones se muestran correctamente
4. **Verificar:** Las reservaciones de mesas se muestran correctamente
5. **Verificar:** Las reservaciones de amenidades se muestran correctamente
6. **Verificar:** Las solicitudes de servicio se muestran correctamente
7. **Verificar:** No hay errores en consola del navegador

### Prueba 5: Imágenes en Listados
1. Ir a "Habitaciones"
2. **Verificar:** Se muestra la imagen principal de cada habitación
3. **Verificar:** Si no hay imagen, se muestra un placeholder con ícono
4. Ir a "Mesas"
5. **Verificar:** Se muestra la imagen principal de cada mesa
6. Ir a "Amenidades"
7. **Verificar:** Se muestra la imagen principal de cada amenidad

### Prueba 6: Edición de Imágenes
1. Ir a "Habitaciones" → "Editar" (cualquier habitación)
2. **Verificar:** Se muestran todas las imágenes existentes
3. **Verificar:** Se puede marcar una imagen como principal
4. **Verificar:** Se pueden eliminar imágenes individuales
5. **Verificar:** Se pueden agregar nuevas imágenes
6. Repetir para Mesas y Amenidades

### Prueba 7: Notificaciones con Sonido
1. Tener dos ventanas/pestañas abiertas:
   - Pestaña 1: Admin/Manager logueado en dashboard
   - Pestaña 2: Chatbot (sin sesión)
2. En Pestaña 2: Crear una reservación
3. En Pestaña 1:
   - **Verificar:** Suena notificación inmediatamente
   - **Verificar:** Sonido se repite cada 10 segundos
   - **Verificar:** Contador de notificaciones aumenta
4. Marcar notificación como leída o cambiar status de reservación
5. **Verificar:** Sonido se detiene automáticamente

---

## 🎯 Casos de Uso Resueltos

### ✅ Caso 1: Huésped reserva habitación vía chatbot
**Flujo:**
1. Huésped abre chatbot → Reserva habitación
2. Sistema crea registro en `room_reservations`
3. Trigger `trg_notify_new_room_reservation` se ejecuta
4. Se crean notificaciones en `system_notifications` para admin/manager
5. JavaScript detecta nuevas notificaciones cada 15s
6. Se reproduce sonido y muestra toast
7. Admin/Manager cambia status de reservación
8. Sonido se detiene automáticamente

### ✅ Caso 2: Huésped reserva mesa vía chatbot
**Flujo:**
1. Huésped abre chatbot → Reserva mesa
2. Sistema crea registro en `table_reservations`
3. Trigger `trg_notify_new_table_reservation` se ejecuta
4. Se crean notificaciones para admin/manager/hostess
5. Notificación con sonido repetitivo hasta cambiar status

### ✅ Caso 3: Huésped reserva amenidad vía chatbot
**Flujo:**
1. Huésped abre chatbot → Reserva amenidad (gym, pool, etc.)
2. Sistema crea registro en `amenity_reservations`
3. Trigger `trg_amenity_reservation_notification` se ejecuta
4. Se notifica a admin/manager (acceso completo)
5. Se notifica a colaboradores con acceso específico a esa amenidad
6. Notificación con sonido hasta cambiar status

### ✅ Caso 4: Visualización en Calendario
**Flujo:**
1. Admin/Manager abre calendario
2. Sistema consulta `room_reservations`, `table_reservations`, `amenity_reservations`, `service_requests`
3. Muestra todos los eventos en el calendario con colores por status
4. Al hacer clic en evento, muestra detalles completos

---

## 🔍 Detalles Técnicos

### Estructura de Triggers

#### trg_notify_new_room_reservation
```sql
AFTER INSERT ON room_reservations
- Obtiene hotel_id y room_number desde tabla rooms
- Crea notificación para cada usuario con rol admin/manager
- NO actualiza room_reservations (evita error 1442)
```

#### trg_notify_new_table_reservation
```sql
AFTER INSERT ON table_reservations
- Obtiene hotel_id y table_number desde tabla restaurant_tables
- Crea notificación para cada usuario con rol admin/manager/hostess
- NO actualiza table_reservations (evita error 1442)
```

#### trg_amenity_reservation_notification
```sql
AFTER INSERT ON amenity_reservations
- Primera consulta: Notifica admin/manager (acceso completo)
- Segunda consulta: Notifica colaboradores con amenity_ids específico
- Verifica: amenity_ids = 'all' O amenity_ids LIKE '%ID%'
- Usa amenity_ids en lugar de amenities_access (corrige error)
```

### Campos de Base de Datos

#### room_reservations
```sql
- check_in DATE (no check_in_date)
- check_out DATE (no check_out_date)
- guest_name VARCHAR(200) (para chatbot)
- guest_id INT NULL (puede ser NULL para chatbot)
- hotel_id INT (agregado para queries eficientes)
- notification_sent TINYINT(1) (histórico, ya no usado en trigger)
```

#### table_reservations
```sql
- reservation_date DATE
- reservation_time TIME
- guest_name VARCHAR(200) (para chatbot)
- guest_id INT NULL (puede ser NULL para chatbot)
- hotel_id INT (agregado para queries eficientes)
- notification_sent TINYINT(1) (histórico)
```

#### amenity_reservations
```sql
- reservation_date DATE
- reservation_time TIME
- guest_name VARCHAR(255) (para chatbot)
- user_id INT NULL (puede ser NULL para chatbot)
- hotel_id INT
- notification_sent TINYINT(1) (histórico)
```

#### role_permissions
```sql
- amenity_ids TEXT (JSON array o 'all')
  Ejemplo: '[1,3,5]' o 'all'
- NOT amenities_access (campo incorrecto que causaba error)
```

### Sistema de Notificaciones

#### Flujo:
```
1. Nueva reservación → INSERT en tabla correspondiente
   ↓
2. Trigger se ejecuta AFTER INSERT
   ↓
3. Trigger crea registros en system_notifications
   (uno por cada usuario con permisos)
   ↓
4. JavaScript polling (cada 15s) detecta nuevas notificaciones
   ↓
5. Reproduce sonido y muestra toast
   ↓
6. Sonido se repite cada 10s mientras status = 'pending'/'confirmed'
   ↓
7. Al marcar como leída o cambiar status → sonido se detiene
```

#### system_notifications
```sql
- notification_type: 'new_reservation_room', 'new_reservation_table', 'amenity_request'
- related_type: 'room_reservation', 'table_reservation', 'amenity_reservation'
- related_id: ID del registro relacionado
- requires_sound: 1 (siempre para reservaciones)
- priority: 'high' (siempre para reservaciones)
```

---

## 🛠️ Troubleshooting

### Error: "Trigger already exists"
**Solución:**
```sql
DROP TRIGGER IF EXISTS trg_notify_new_room_reservation;
DROP TRIGGER IF EXISTS trg_notify_new_table_reservation;
DROP TRIGGER IF EXISTS trg_amenity_reservation_notification;
-- Luego ejecutar el script completo
```

### Error: "Column notification_sent doesn't exist"
**Solución:**
El script ya maneja esto automáticamente con:
```sql
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE ...)
```
Si aún falla, ejecutar manualmente:
```sql
ALTER TABLE room_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0;
ALTER TABLE table_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0;
ALTER TABLE amenity_reservations ADD COLUMN notification_sent TINYINT(1) DEFAULT 0;
```

### Error: "Unknown column 'hotel_id' in room_reservations"
**Solución:**
Ejecutar primero el script `fix_chatbot_errors.sql` que agrega hotel_id:
```bash
mysql -u usuario -p database < database/fix_chatbot_errors.sql
```

### Notificaciones no llegan
**Verificar:**
1. Que el usuario tenga el rol correcto (admin/manager para habitaciones)
2. Que el usuario esté activo (`is_active = 1`)
3. Que el usuario pertenezca al mismo hotel
4. Que JavaScript esté haciendo polling (abrir consola del navegador)

### Sonido no se reproduce
**Verificar:**
1. Que existe el archivo: `/public/assets/sounds/notification.mp3`
2. Que el navegador permite reproducción automática de audio
3. Abrir consola del navegador y verificar errores
4. Verificar que `requires_sound = 1` en la notificación

### Calendario no muestra eventos
**Verificar:**
1. Que las reservaciones existen en la base de datos
2. Que el usuario está logueado y tiene hotel_id
3. Abrir consola del navegador y verificar errores en petición AJAX
4. Verificar que CalendarController devuelve JSON válido

---

## 📊 Compatibilidad

- **MySQL:** 5.7+
- **PHP:** 7.2+
- **Navegadores:** Chrome, Firefox, Safari, Edge (últimas versiones)
- **Bootstrap:** 5.x
- **FullCalendar:** 5.x

---

## 📝 Notas Adicionales

### Seguridad
- Todas las consultas usan prepared statements
- Los datos del chatbot se validan antes de insertar
- guest_id puede ser NULL para reservaciones anónimas
- hotel_id se valida en el frontend antes de enviar

### Rendimiento
- Los triggers son ligeros y eficientes
- Las consultas tienen índices apropiados
- El polling de notificaciones es cada 15s (no sobrecarga)
- El sonido se repite cada 10s (no cada segundo)

### Mantenimiento
- El campo `notification_sent` permanece por compatibilidad
- Los triggers no lo actualizan (evita error 1442)
- Se puede eliminar en futuras versiones si no se usa

---

## 📞 Soporte

Si encuentras algún problema después de aplicar estos cambios:

1. Verifica que el script SQL se ejecutó completamente
2. Revisa los logs de error de PHP
3. Abre la consola del navegador para errores JavaScript
4. Verifica que todos los archivos tienen la última versión
5. Asegúrate de que existe el archivo de sonido

---

**Fecha:** 2024  
**Versión:** 1.2.0  
**Estado:** ✅ Completado y Probado
