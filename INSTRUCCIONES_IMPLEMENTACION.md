# 🚀 Instrucciones de Implementación - Correcciones Chatbot

## 📋 Resumen Ejecutivo

Se han corregido **5 errores críticos** en el sistema:
1. ✅ Error de búsqueda de disponibilidad de habitaciones
2. ✅ Error al crear reservación de mesa
3. ✅ Error en editar habitación (getModel)
4. ✅ Notificaciones con sonido persistente
5. ✅ Tracking de status en notificaciones

---

## ⚠️ IMPORTANTE: Ejecutar SQL Primero

**ANTES DE PROBAR EL SISTEMA**, debes ejecutar el script SQL de migración:

### Opción 1: Línea de Comandos
```bash
mysql -u tu_usuario -p tu_base_datos < database/fix_chatbot_errors.sql
```

### Opción 2: phpMyAdmin
1. Abrir phpMyAdmin
2. Seleccionar tu base de datos
3. Ir a pestaña "SQL"
4. Abrir el archivo `database/fix_chatbot_errors.sql`
5. Copiar TODO el contenido
6. Pegar en el área de texto de phpMyAdmin
7. Click en "Ejecutar" (Go)

### Opción 3: cPanel / Hosting
1. Acceder a phpMyAdmin desde cPanel
2. Seleccionar la base de datos `aqh_mayordomo`
3. Seguir pasos de Opción 2

---

## 🔍 Verificar que el SQL se Ejecutó Correctamente

Después de ejecutar el script, verifica en phpMyAdmin:

### 1. Tabla `room_reservations`
```sql
DESCRIBE room_reservations;
```
**Debe mostrar:**
- `hotel_id` INT NOT NULL
- `guest_id` INT NULL (nota: NULL permitido)

### 2. Tabla `table_reservations`
```sql
DESCRIBE table_reservations;
```
**Debe mostrar:**
- `hotel_id` INT NOT NULL
- `guest_id` INT NULL (nota: NULL permitido)

### 3. Verificar índices
```sql
SHOW INDEX FROM room_reservations;
SHOW INDEX FROM table_reservations;
```
**Debe mostrar índices:**
- `idx_hotel_room` en room_reservations
- `idx_hotel_table` en table_reservations

---

## 🧪 Probar las Correcciones

### 1. Probar Chatbot - Habitaciones
1. Ir a: `https://tudominio.com/chatbot/index/1` (donde 1 es tu hotel_id)
2. Seleccionar "Quiero reservar una habitación"
3. Elegir fechas de check-in y check-out
4. **Verificar:** Debe mostrar habitaciones disponibles (no error)
5. Llenar formulario (nombre, email, teléfono)
6. Click en "Confirmar Reservación"
7. **Verificar:** Mensaje de éxito (no error SQL)

### 2. Probar Chatbot - Mesas
1. En el mismo chatbot
2. Seleccionar "Quiero reservar una mesa"
3. Elegir fecha
4. **Verificar:** Debe mostrar mesas disponibles
5. Llenar formulario
6. Click en "Confirmar Reservación"
7. **Verificar:** Mensaje de éxito (no error de hotel_id)

### 3. Probar Chatbot - Amenidades
1. En el mismo chatbot
2. Seleccionar "Quiero reservar una amenidad"
3. Elegir fecha
4. **Verificar:** Debe mostrar amenidades disponibles
5. Llenar formulario
6. Click en "Confirmar Reservación"
7. **Verificar:** Mensaje de éxito

### 4. Probar Editar Habitación
1. Iniciar sesión como Admin/Manager
2. Ir a: Habitaciones → Lista de Habitaciones
3. Click en botón "Editar" de cualquier habitación
4. **Verificar:** Página carga correctamente (no error de getModel)
5. **Verificar:** Se muestran las imágenes actuales de la habitación

### 5. Probar Notificaciones con Sonido
1. Iniciar sesión como Admin/Manager
2. Abrir dos navegadores o pestañas:
   - Pestaña 1: Sesión de Admin
   - Pestaña 2: Chatbot (sin sesión)
3. En Pestaña 2 (Chatbot): Crear una reservación
4. En Pestaña 1 (Admin): 
   - **Verificar:** Suena notificación inmediatamente
   - **Verificar:** Sonido se repite cada 10 segundos
   - **Verificar:** Contador de notificaciones aumenta
5. Click en notificación o marcar como leída
6. **Verificar:** Sonido se detiene
7. Cambiar status de reservación a "completed" o "cancelled"
8. **Verificar:** Sonido se detiene automáticamente

---

## 📊 Cambios Realizados en el Código

### Archivo: `app/controllers/ChatbotController.php`

**Línea 74-76:** Corregidos nombres de columnas
```php
// ANTES:
(check_in_date <= ? AND check_out_date > ?)

// DESPUÉS:
(check_in <= ? AND check_out > ?)
```

**Línea 166-178:** Agregado hotel_id y guest_id NULL
```php
// DESPUÉS:
INSERT INTO room_reservations 
(hotel_id, room_id, guest_id, guest_name, guest_email, guest_phone, check_in, check_out, total_price, status, special_requests)
VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 0, 'pending', ?)
```

**Línea 185-197:** Agregado hotel_id a table_reservations
```php
INSERT INTO table_reservations 
(hotel_id, table_id, guest_id, guest_name, guest_email, guest_phone, reservation_date, reservation_time, party_size, status, notes)
VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, 'pending', ?)
```

### Archivo: `app/helpers/helpers.php`

**Nueva función agregada:**
```php
function getModel($modelName, $db = null) {
    if ($db === null) {
        require_once CONFIG_PATH . '/database.php';
        $db = Database::getInstance()->getConnection();
    }
    
    $modelFile = APP_PATH . '/models/' . $modelName . '.php';
    
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return new $modelName($db);
    }
    
    return null;
}
```

### Archivo: `public/assets/js/notifications.js`

**Nuevas variables:**
```javascript
const SOUND_REPEAT_INTERVAL = 10000; // Repetir cada 10 segundos
let soundIntervalId = null;
let activeNotifications = new Set();
```

**Nuevas funciones:**
- `startPersistentSound()` - Inicia sonido repetitivo
- `stopPersistentSound()` - Detiene sonido repetitivo

### Archivo: `app/controllers/NotificationsController.php`

**Agregado a la consulta:**
- Campo `related_type`
- Campo `related_id`
- Campo `status` (consultado dinámicamente de la tabla relacionada)

---

## 🎯 Escenarios de Prueba Detallados

### Escenario 1: Reservación Completa por Chatbot
```
1. Visitante anónimo abre chatbot
2. Elige habitación del 15/12/2024 al 20/12/2024
3. Llena: Juan Pérez, juan@email.com, 1234567890
4. Confirma reservación
5. Sistema crea registro en room_reservations con:
   - hotel_id: ID del hotel
   - guest_id: NULL (anónimo)
   - guest_name: "Juan Pérez"
   - check_in: 2024-12-15
   - check_out: 2024-12-20
   - status: pending
```

### Escenario 2: Notificación Persistente
```
1. Admin está logueado en el dashboard
2. Cliente crea reservación por chatbot
3. Sistema crea notificación con requires_sound=1
4. Admin escucha sonido inmediatamente
5. Sonido se repite cada 10 segundos
6. Admin revisa la reservación
7. Admin cambia status a "confirmed"
8. Sonido se detiene automáticamente
```

### Escenario 3: Múltiples Notificaciones
```
1. Admin logueado
2. Cliente 1 reserva habitación → SONIDO
3. Cliente 2 reserva mesa → SONIDO
4. Cliente 3 reserva amenidad → SONIDO
5. Sonido continúa cada 10 segundos
6. Admin marca habitación como leída → SONIDO CONTINÚA (aún hay 2 pendientes)
7. Admin completa reservación de mesa → SONIDO CONTINÚA (aún hay 1 pendiente)
8. Admin cancela reservación de amenidad → SONIDO SE DETIENE (0 pendientes)
```

---

## 🛠️ Troubleshooting

### Problema: "Column 'hotel_id' doesn't exist"
**Solución:**
- El script SQL no se ejecutó correctamente
- Ejecutar manualmente: `database/fix_chatbot_errors.sql`
- Verificar con: `DESCRIBE room_reservations;`

### Problema: "Column 'guest_id' cannot be null"
**Solución:**
- El script SQL no modificó la columna correctamente
- Ejecutar manualmente:
```sql
ALTER TABLE room_reservations MODIFY COLUMN guest_id INT NULL;
ALTER TABLE table_reservations MODIFY COLUMN guest_id INT NULL;
```

### Problema: "Call to undefined function getModel()"
**Solución:**
- Verificar que `app/helpers/helpers.php` tiene la función `getModel()`
- Hacer un `git pull` para obtener la última versión

### Problema: El sonido no se repite
**Solución:**
- Verificar que existe el archivo: `/public/assets/sounds/notification.mp3`
- Abrir consola del navegador y verificar errores JavaScript
- Verificar que el navegador permite reproducción de audio automática

### Problema: El sonido no se detiene
**Solución:**
- Verificar que `NotificationsController.php` devuelve el campo `status`
- Verificar en consola del navegador que `activeNotifications` se vacía
- Marcar todas las notificaciones como leídas manualmente

---

## 📞 Contacto de Soporte

Si después de seguir estas instrucciones aún hay problemas:

1. **Verificar logs de PHP:** `error_log` o `/var/log/apache2/error.log`
2. **Verificar consola del navegador:** F12 → Console
3. **Verificar base de datos:** Ejecutar consultas de verificación
4. **Revisar documentación completa:** `CHATBOT_FIXES_README.md`

---

## ✅ Checklist Final de Implementación

Marca cada item después de completarlo:

- [ ] Script SQL ejecutado sin errores
- [ ] Verificado que `hotel_id` existe en room_reservations
- [ ] Verificado que `hotel_id` existe en table_reservations
- [ ] Verificado que `guest_id` es NULL en ambas tablas
- [ ] Probado reservación de habitación por chatbot ✅
- [ ] Probado reservación de mesa por chatbot ✅
- [ ] Probado reservación de amenidad por chatbot ✅
- [ ] Probado editar habitación sin errores ✅
- [ ] Probado notificación con sonido ✅
- [ ] Probado sonido persistente (repite cada 10s) ✅
- [ ] Probado detención de sonido al marcar como leída ✅
- [ ] Probado detención de sonido al cambiar status ✅

---

## 🎉 Felicitaciones

¡Si todos los items están marcados, la implementación está completa!

El sistema ahora tiene:
- ✅ Chatbot funcional para reservaciones anónimas
- ✅ Sistema de notificaciones con sonido persistente
- ✅ Base de datos actualizada y optimizada
- ✅ Código corregido y documentado

---

**Versión:** 1.1.1  
**Fecha:** 2024  
**Autor:** GitHub Copilot Assistant
