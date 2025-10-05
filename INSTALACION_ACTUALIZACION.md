# 🚀 Guía de Instalación - Actualización del Sistema

## ⚠️ IMPORTANTE - LEER ANTES DE CONTINUAR

Esta actualización modifica la base de datos. **Se recomienda hacer un respaldo completo antes de proceder.**

```bash
# Backup de la base de datos (ejemplo)
mysqldump -u usuario -p aqh_mayordomo > backup_antes_actualizacion_$(date +%Y%m%d).sql
```

---

## 📋 Requisitos Previos

- [x] PHP 7.4 o superior
- [x] MySQL 5.7 o superior
- [x] Acceso a la base de datos con permisos de ALTER y CREATE
- [x] Respaldo de la base de datos actual

---

## 🔧 Paso 1: Actualizar Archivos del Sistema

Los archivos ya están en el repositorio. Si usas Git:

```bash
git pull origin main
```

Si subes archivos manualmente, asegúrate de incluir:
- `database/fix_system_issues.sql`
- `app/controllers/ReservationsController.php`
- `app/controllers/RolesController.php`
- `app/controllers/NotificationsController.php`
- `app/views/reservations/` (todo el directorio)
- `app/views/roles/` (todo el directorio)
- `public/assets/js/notifications.js`
- Cambios en `app/views/layouts/header.php`
- Cambios en `app/views/layouts/footer.php`

---

## 🗄️ Paso 2: Ejecutar Migración de Base de Datos

### Opción A: Desde Terminal (Recomendado)

```bash
# Navegar al directorio del proyecto
cd /ruta/al/proyecto

# Ejecutar el script SQL
mysql -u usuario -p aqh_mayordomo < database/fix_system_issues.sql

# Se te pedirá la contraseña
# Espera a que termine (puede tomar 1-2 minutos)
```

### Opción B: Desde phpMyAdmin

1. Accede a phpMyAdmin
2. Selecciona la base de datos `aqh_mayordomo`
3. Ve a la pestaña "SQL"
4. Abre el archivo `database/fix_system_issues.sql` en un editor de texto
5. Copia todo el contenido
6. Pégalo en el área de texto de phpMyAdmin
7. Haz clic en "Continuar"
8. Espera a que termine la ejecución

### Opción C: Desde Herramienta de Administración (Navicat, DBeaver, etc.)

1. Conecta a tu base de datos
2. Abre una nueva ventana SQL
3. Carga el archivo `database/fix_system_issues.sql`
4. Ejecuta todo el script

---

## ✅ Paso 3: Verificar la Instalación

### 3.1 Verificación Automática

Al final del script SQL, deberías ver estos mensajes:

```
verificacion | resultado
-------------+-----------
Verificando campo description en subscriptions...
tiene_description | 1

Verificando precios de suscripciones...
(muestra los precios sincronizados)

Verificando nuevas tablas...
role_permissions
system_notifications

Verificando triggers...
trg_notify_new_room_reservation
trg_notify_new_table_reservation

Migración completada exitosamente!
```

### 3.2 Verificación Manual

Ejecuta estas consultas para confirmar:

```sql
-- 1. Verificar campo description
DESCRIBE subscriptions;
-- Debe mostrar el campo 'description' de tipo TEXT

-- 2. Verificar nuevas tablas
SHOW TABLES LIKE '%role_permissions%';
SHOW TABLES LIKE '%system_notifications%';
-- Deben aparecer las dos tablas

-- 3. Verificar triggers
SHOW TRIGGERS WHERE `Trigger` LIKE 'trg_notify%';
-- Deben aparecer 2 triggers

-- 4. Verificar vista
SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW';
-- Debe aparecer v_all_reservations

-- 5. Ver estructura de nueva tabla
DESCRIBE role_permissions;
DESCRIBE system_notifications;
```

---

## 🔊 Paso 4: Agregar Archivo de Sonido

El sistema necesita un archivo de sonido para las notificaciones.

### Obtener el Archivo

**Opción 1: Descarga Gratuita**
1. Ve a https://mixkit.co/free-sound-effects/notification/
2. Descarga un sonido corto (ejemplo: "Notification")
3. Conviértelo a MP3 si es necesario

**Opción 2: Generar Tono**
1. Ve a https://onlinetonegenerator.com/
2. Configura: Frecuencia 800Hz, Duración 0.5s
3. Descarga como MP3

### Instalar el Archivo

```bash
# Copiar el archivo descargado
cp notification.mp3 /ruta/al/proyecto/public/assets/sounds/notification.mp3

# Verificar permisos
chmod 644 /ruta/al/proyecto/public/assets/sounds/notification.mp3
```

**NOTA:** El sistema funcionará sin el archivo de sonido, pero no reproducirá audio. Las notificaciones visuales seguirán funcionando.

---

## 🧪 Paso 5: Pruebas del Sistema

### 5.1 Probar Módulo de Reservaciones

1. Login como Admin o Manager
2. Ir al menú lateral → "Reservaciones"
3. Verificar que carga sin errores
4. Probar filtros
5. Crear una reservación de prueba desde otro módulo
6. Verificar que aparece en el listado

### 5.2 Probar Gestión de Roles

1. Login como Admin (propietario)
2. Ir al menú lateral → "Roles y Permisos"
3. Verificar que muestra los colaboradores
4. Expandir un colaborador
5. Activar algunos permisos
6. Guardar
7. Verificar mensaje de éxito

### 5.3 Probar Notificaciones

**Preparación:**
1. Login como Admin
2. Abrir consola del navegador (F12)
3. Buscar mensaje: "Sistema de notificaciones iniciado"

**Prueba:**
1. Crear una reservación nueva desde otro navegador/usuario
2. En el dashboard del admin:
   - Debe aparecer badge con número
   - Debe mostrar notificación visual (toast)
   - Debe reproducir sonido (si existe el archivo)

**Verificación en BD:**
```sql
SELECT * FROM system_notifications ORDER BY created_at DESC LIMIT 5;
```

### 5.4 Probar Actualizar Suscripción

1. Login como Admin
2. Ir a "Actualizar Plan"
3. Verificar que NO aparece el error de "description"
4. Verificar que los precios mostrados son correctos
5. Comparar con los precios en Configuración (si superadmin tiene acceso)

---

## 🐛 Solución de Problemas

### Error: "Table 'role_permissions' doesn't exist"

**Causa:** La migración no se ejecutó completamente

**Solución:**
```sql
-- Verificar si existe la tabla
SHOW TABLES LIKE 'role_permissions';

-- Si no existe, ejecutar manualmente esta parte del script
CREATE TABLE IF NOT EXISTS role_permissions (
    -- ... (copiar de fix_system_issues.sql)
);
```

### Error: "Trigger 'trg_notify_new_room_reservation' already exists"

**Causa:** Los triggers ya existían de una ejecución anterior

**Solución:**
```sql
-- Eliminar y recrear
DROP TRIGGER IF EXISTS trg_notify_new_room_reservation;
DROP TRIGGER IF EXISTS trg_notify_new_table_reservation;

-- Luego ejecutar la parte de triggers del script
```

### Error: "Column 'description' already exists"

**No es un problema.** El script usa `IF NOT EXISTS` para evitar esto.

### Error: "Cannot add foreign key constraint"

**Causa:** La base de datos puede tener datos inconsistentes

**Solución:**
```sql
-- Verificar usuarios sin hotel
SELECT * FROM users WHERE hotel_id IS NULL AND role != 'superadmin';

-- Si hay usuarios problemáticos, asignarlos a un hotel o eliminarlos
```

### El sonido no se reproduce

**Causas posibles:**
1. El archivo no existe en la ruta correcta
2. El navegador bloquea la reproducción automática
3. No hay interacción previa del usuario con la página

**Solución:**
1. Verificar que existe: `/public/assets/sounds/notification.mp3`
2. Interactuar con la página (hacer clic) antes de esperar notificaciones
3. Verificar consola del navegador para errores
4. Probar en otro navegador

### No llegan notificaciones

**Verificación:**
```sql
-- Ver si se están creando notificaciones
SELECT * FROM system_notifications 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- Ver permisos del usuario
SELECT * FROM role_permissions WHERE user_id = TU_USER_ID;
```

**Si no hay notificaciones:**
- Los triggers no están activos
- Re-ejecutar la parte de triggers del script

**Si hay notificaciones pero no se muestran:**
- Verificar consola del navegador (F12)
- Verificar que notifications.js se está cargando
- Verificar que `BASE_URL` está definido

---

## 🔄 Rollback (Revertir Cambios)

Si algo sale mal y necesitas revertir:

### 1. Restaurar Base de Datos

```bash
mysql -u usuario -p aqh_mayordomo < backup_antes_actualizacion_YYYYMMDD.sql
```

### 2. Revertir Archivos

```bash
# Si usas Git
git reset --hard COMMIT_ANTERIOR

# O restaurar desde backup de archivos
```

### 3. Eliminar Nuevas Tablas (Alternativa)

Si prefieres solo eliminar las nuevas tablas:

```sql
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS system_notifications;
DROP VIEW IF EXISTS v_all_reservations;
DROP TRIGGER IF EXISTS trg_notify_new_room_reservation;
DROP TRIGGER IF EXISTS trg_notify_new_table_reservation;

ALTER TABLE subscriptions DROP COLUMN IF EXISTS description;
ALTER TABLE room_reservations DROP COLUMN IF EXISTS notification_sent;
ALTER TABLE table_reservations DROP COLUMN IF EXISTS notification_sent;
```

---

## 📊 Post-Instalación

### Tareas Recomendadas

1. **Configurar Permisos Iniciales:**
   - Ir a Roles y Permisos
   - Configurar cada colaborador con sus áreas correspondientes

2. **Probar con Datos Reales:**
   - Crear algunas reservaciones de prueba
   - Verificar que las notificaciones llegan correctamente

3. **Capacitar al Personal:**
   - Mostrar el nuevo módulo de Reservaciones
   - Explicar el sistema de notificaciones
   - Enseñar cómo usar los filtros

4. **Monitorear:**
   - Revisar logs de errores PHP
   - Verificar crecimiento de tabla `system_notifications`
   - Limpiar notificaciones antiguas periódicamente:
     ```sql
     DELETE FROM system_notifications 
     WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
     ```

5. **Optimizar (Opcional):**
   - Agregar índices adicionales si es necesario
   - Ajustar frecuencia de polling en notifications.js (línea 8)
   - Configurar limpieza automática de notificaciones antiguas

---

## 📞 Soporte

Si encuentras problemas durante la instalación:

1. **Revisar Logs:**
   - Logs de PHP: `/var/log/apache2/error.log` o similar
   - Logs de MySQL: Verificar en phpMyAdmin o terminal

2. **Consultar Documentación:**
   - `NUEVAS_FUNCIONALIDADES.md` - Documentación completa de features
   - `database/fix_system_issues.sql` - Ver comentarios en el script

3. **Debugging:**
   - Activar display_errors en PHP temporalmente
   - Usar `var_dump()` o `error_log()` para debugging
   - Verificar permisos de archivos y directorios

---

## ✅ Checklist Final

Antes de marcar como completado, verifica:

- [ ] Backup de base de datos realizado
- [ ] Script SQL ejecutado sin errores
- [ ] Verificaciones manuales pasadas
- [ ] Archivo de sonido agregado
- [ ] Módulo de Reservaciones accesible
- [ ] Módulo de Roles accesible (admin)
- [ ] Notificaciones funcionando (badge visible)
- [ ] Sonido reproduciéndose (si archivo existe)
- [ ] Error de "description" resuelto
- [ ] Precios de suscripciones correctos
- [ ] Personal capacitado en nuevas funciones

---

## 🎉 ¡Instalación Completada!

Si todos los pasos se completaron exitosamente, el sistema está listo para usar con todas las nuevas funcionalidades.

**Próximos pasos sugeridos:**
1. Leer `NUEVAS_FUNCIONALIDADES.md` para guía de uso completa
2. Configurar permisos de roles para todos los colaboradores
3. Monitorear el sistema durante los primeros días
4. Recopilar feedback del personal sobre las nuevas funciones

---

**Última actualización:** $(date +%Y-%m-%d)
**Versión del sistema:** 1.1.0
**Autor:** Sistema MajorBot
