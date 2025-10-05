# 🔧 Aplicar Correcciones del Sistema

## Resumen de Correcciones Implementadas

Este documento describe las correcciones realizadas al sistema Mayordomo para resolver los siguientes problemas:

### ✅ Problemas Resueltos

1. **✅ Rutas de imágenes incorrectas** - Las imágenes en las vistas de edición ahora se muestran correctamente
2. **✅ Tipo de reservación incorrecta** - Las amenidades ya no aparecen como "MESA" en el módulo de reservaciones
3. **✅ Validación incorrecta del chatbot** - Ya no solicita número de habitación al reservar habitaciones
4. **✅ Sonido persistente** - El sonido ahora se reproduce hasta confirmar/cancelar reservaciones pendientes
5. **⚠️ Nombres en notificaciones** - Requiere ejecutar script SQL (ver instrucciones abajo)

---

## 📋 Instrucciones de Aplicación

### Paso 1: Los Cambios de Código Ya Están Aplicados

Los siguientes archivos ya fueron actualizados en el código:

- ✅ `app/views/rooms/edit.php` - Ruta de imágenes corregida
- ✅ `app/views/tables/edit.php` - Ruta de imágenes corregida
- ✅ `app/views/amenities/edit.php` - Ruta de imágenes corregida
- ✅ `app/views/reservations/index.php` - Soporte para amenidades agregado
- ✅ `app/controllers/ChatbotController.php` - Validación de habitación corregida
- ✅ `public/assets/js/notifications.js` - Sonido persistente para pendientes

### Paso 2: Ejecutar Script SQL (IMPORTANTE)

Para que los nombres aparezcan en todas las notificaciones, debes ejecutar el script SQL:

#### Opción A: Desde phpMyAdmin
1. Abre phpMyAdmin
2. Selecciona la base de datos `aqh_mayordomo`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido del archivo: `database/fix_notifications_with_names.sql`
5. Haz clic en "Ejecutar"

#### Opción B: Desde línea de comandos
```bash
mysql -u tu_usuario -p aqh_mayordomo < database/fix_notifications_with_names.sql
```

**⚠️ IMPORTANTE:** Este script actualiza los triggers de la base de datos para incluir nombres de huéspedes en las notificaciones.

---

## 🔍 Detalles de los Cambios

### 1. Rutas de Imágenes Corregidas

**Problema:** Las imágenes se guardaban en `/public/uploads/` pero se mostraban con ruta `/uploads/`

**Solución:** Agregado `public/` en las rutas de imágenes:
```php
// ANTES
<img src="<?= BASE_URL ?>/<?= e($img['image_path']) ?>">

// DESPUÉS
<img src="<?= BASE_URL ?>/public/<?= e($img['image_path']) ?>">
```

**Archivos afectados:**
- `app/views/rooms/edit.php`
- `app/views/tables/edit.php`
- `app/views/amenities/edit.php`

### 2. Tipo de Reservación en Módulo de Reservaciones

**Problema:** Las amenidades aparecían incorrectamente como "MESA"

**Solución:** Agregado soporte para tipo `amenity` en la vista:
```php
<?php if ($reservation['reservation_type'] === 'room'): ?>
    <span class="badge bg-info">Habitación</span>
<?php elseif ($reservation['reservation_type'] === 'table'): ?>
    <span class="badge bg-success">Mesa</span>
<?php elseif ($reservation['reservation_type'] === 'amenity'): ?>
    <span class="badge bg-primary">Amenidad</span>
<?php endif; ?>
```

También se agregó el filtro de "Amenidades" en el formulario de búsqueda.

**Archivo afectado:** `app/views/reservations/index.php`

### 3. Validación del Chatbot Corregida

**Problema:** Al reservar habitación, el sistema pedía número de habitación incorrectamente

**Solución:** Cambiada la lógica de validación:
```php
// ANTES - Pedía habitación al reservar rooms
if ($data['resource_type'] === 'room' && !$data['is_visitor'] && empty($data['room_number']))

// DESPUÉS - Solo pide habitación al reservar tables/amenities
if ($data['resource_type'] !== 'room' && !$data['is_visitor'] && empty($data['room_number']))
```

**Explicación:** 
- Al reservar una **habitación**: No se necesita número de habitación (estás haciendo check-in)
- Al reservar **mesa/amenidad** como huésped: Sí se necesita tu número de habitación actual

**Archivo afectado:** `app/controllers/ChatbotController.php`

### 4. Sonido Persistente para Reservaciones Pendientes

**Problema:** El sonido solo sonaba una vez

**Solución:** Actualizada la lógica para reproducir sonido cada 10 segundos:
```javascript
// Ahora verifica solo status 'pending'
if (notification.requires_sound && notification.status === 'pending') {
    activeNotifications.add(notification.id);
    hasPendingNotifications = true;
}

// Inicia sonido persistente
if (hasPendingNotifications && activeNotifications.size > 0) {
    startPersistentSound(); // Se repite cada 10 segundos
}
```

**Comportamiento:**
- 🔔 Sonido se reproduce cada 10 segundos
- ⏹️ Se detiene automáticamente al cambiar status a 'confirmed' o 'cancelled'
- ✅ Solo suena para reservaciones con status 'pending'

**Archivo afectado:** `public/assets/js/notifications.js`

### 5. Nombres en Notificaciones

**Problema:** Solo aparecía nombre en notificaciones de amenidades

**Solución:** Actualización de triggers SQL:

**Triggers actualizados:**
- `trg_notify_new_room_reservation` - Ahora incluye nombre del huésped
- `trg_notify_new_table_reservation` - Ahora incluye nombre del huésped
- `trg_amenity_reservation_notification` - Ya incluía nombres (sin cambios)

**Lógica implementada:**
1. Primero intenta usar `guest_name` del registro de reservación
2. Si no existe, busca en la tabla `users` usando `guest_id`
3. Si tampoco existe, usa "Huésped" como fallback

**Ejemplo de mensaje:**
```
ANTES: "Nueva reservación para habitación 101 - Check-in: 15/01/2024"
DESPUÉS: "Nueva reservación de Juan Pérez para habitación 101 - Check-in: 15/01/2024"
```

**⚠️ REQUIERE EJECUTAR SQL:** `database/fix_notifications_with_names.sql`

---

## ✅ Verificación

### Verificar Rutas de Imágenes
1. Ve a **Habitaciones/Mesas/Amenidades**
2. Edita un registro que tenga imágenes
3. Las imágenes deben mostrarse correctamente en la vista previa

### Verificar Tipo de Reservación
1. Ve a **Reservaciones** (`/reservations/`)
2. Verifica que las amenidades muestren badge azul con icono de spa
3. Usa el filtro "Amenidades" para ver solo reservaciones de amenidades

### Verificar Chatbot
1. Abre el chatbot como visitante
2. Intenta reservar una habitación
3. NO debe pedir número de habitación
4. Intenta reservar una mesa como huésped
5. SÍ debe pedir número de habitación

### Verificar Sonido Persistente
1. Crea una reservación nueva (debe quedar en status 'pending')
2. Espera a que se cree la notificación
3. El sonido debe reproducirse cada 10 segundos
4. Confirma o cancela la reservación
5. El sonido debe detenerse automáticamente

### Verificar Nombres en Notificaciones (después de ejecutar SQL)
1. Crea una nueva reservación de habitación
2. Ve a **Notificaciones**
3. El mensaje debe incluir el nombre del huésped

---

## 🆘 Solución de Problemas

### Las imágenes aún no se muestran
```bash
# Verificar permisos
chmod -R 755 public/uploads/
chown -R www-data:www-data public/uploads/
```

### El sonido no se reproduce
1. Verifica que existe: `public/assets/sounds/notification.mp3`
2. Interactúa con la página (clic) antes de esperar notificaciones
3. Verifica en consola del navegador si hay errores

### Los nombres no aparecen en notificaciones
- Asegúrate de ejecutar el script SQL: `database/fix_notifications_with_names.sql`
- Verifica que los triggers se crearon correctamente:
```sql
SHOW TRIGGERS WHERE `Table` IN ('room_reservations', 'table_reservations');
```

---

## 📝 Notas Adicionales

### Compatibilidad
- ✅ MySQL 5.7+
- ✅ PHP 7.4+
- ✅ Navegadores modernos (Chrome, Firefox, Safari, Edge)

### Retrocompatibilidad
- ✅ Las reservaciones antiguas seguirán funcionando normalmente
- ✅ Las imágenes existentes no necesitan ser movidas
- ✅ Los triggers antiguos son reemplazados automáticamente

### Backups
Se recomienda hacer backup antes de aplicar cambios SQL:
```bash
mysqldump -u usuario -p aqh_mayordomo > backup_$(date +%Y%m%d).sql
```

---

## 📞 Soporte

Si tienes problemas aplicando estas correcciones:
1. Verifica los logs de PHP: `/var/log/apache2/error.log` o `/var/log/php-fpm/error.log`
2. Verifica los logs de MySQL: `/var/log/mysql/error.log`
3. Revisa la consola del navegador (F12) para errores JavaScript

---

**Fecha de implementación:** 2024
**Versión:** 1.3.0
