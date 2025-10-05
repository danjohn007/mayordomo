# 🚀 Guía Rápida de Actualización - MajorBot v1.2.0

## ⚡ Inicio Rápido

### 1️⃣ Hacer Backup
```bash
mysqldump -u usuario -p nombre_bd > backup_$(date +%Y%m%d).sql
```

### 2️⃣ Ejecutar Migración
```bash
mysql -u usuario -p nombre_bd < database/migration_complete_features.sql
```

### 3️⃣ Crear Directorios
```bash
mkdir -p public/uploads/{rooms,tables,amenities}
chmod -R 755 public/uploads
```

### 4️⃣ Verificar Event Scheduler
```sql
SET GLOBAL event_scheduler = ON;
```

### 5️⃣ ¡Listo! 🎉

---

## 📋 ¿Qué hay de nuevo?

### ✅ Validación de Teléfono (10 dígitos)
- ✅ Registro público
- ✅ Nuevo usuario (admin)
- ✅ Chatbot público

### ✅ Solicitudes de Servicio Mejoradas
- ✅ Botón editar ✏️
- ✅ Botón cancelar ❌
- ✅ Cambiar estado (dropdown)

### ✅ Chatbot Público 🤖
- ✅ Reservaciones sin login
- ✅ Validación de disponibilidad
- ✅ Teléfono 10 dígitos obligatorio
- ✅ Link en "Mi Perfil"

**Acceso:** `https://tudominio.com/chatbot/index/{hotel_id}`

### ✅ Imágenes para Recursos 🖼️
- ✅ Habitaciones
- ✅ Mesas
- ✅ Amenidades
- ✅ Múltiples imágenes por recurso

### ✅ Liberación Automática ⏰
- ✅ Mesas: 2 horas después
- ✅ Amenidades: 2 horas después
- ✅ Habitaciones: 15:00 hrs día siguiente

---

## 🔍 Verificación Rápida

```sql
-- ¿Se crearon las tablas?
SHOW TABLES LIKE '%resource_images%';
SHOW TABLES LIKE '%chatbot_reservations%';

-- ¿Están activos los eventos?
SHOW EVENTS WHERE Db = DATABASE();

-- ¿Event scheduler activo?
SHOW VARIABLES LIKE 'event_scheduler';
```

---

## 📁 Archivos SQL Importantes

### 🎯 TODO EN UNO (Recomendado)
```
database/migration_complete_features.sql
```
Contiene TODO: tablas, eventos, procedimientos.

### 📦 Individuales (Opcional)
```
database/add_images_support.sql           # Solo imágenes
database/chatbot_reservations.sql         # Solo chatbot
```

---

## 🔐 Permisos Necesarios

### MySQL
```sql
GRANT CREATE, ALTER, INSERT, UPDATE, DELETE, SELECT, 
      CREATE ROUTINE, ALTER ROUTINE, EXECUTE, EVENT 
ON nombre_bd.* TO 'usuario'@'localhost';

GRANT SUPER ON *.* TO 'usuario'@'localhost'; -- Para event_scheduler
FLUSH PRIVILEGES;
```

### Servidor Web
```bash
chmod -R 755 public/uploads
chown -R www-data:www-data public/uploads
```

---

## 🎯 Casos de Uso

### 1. Obtener Link del Chatbot
1. Iniciar sesión como Admin/Manager/Hostess
2. Ir a "Mi Perfil"
3. Ver sección "Chatbot de Reservaciones"
4. Copiar link
5. Compartir con clientes

### 2. Subir Imágenes a Habitación
1. Ir a "Habitaciones"
2. Click "Nueva Habitación" o editar existente
3. Llenar datos
4. Seleccionar imágenes (botón "Examinar")
5. Guardar

### 3. Editar Solicitud de Servicio
1. Ir a "Solicitudes de Servicio"
2. Click ✏️ en la solicitud
3. Modificar datos
4. Guardar

---

## ⚠️ Problemas Comunes

### Event Scheduler no funciona
```sql
-- En MySQL
SET GLOBAL event_scheduler = ON;

-- En my.cnf (permanente)
[mysqld]
event_scheduler = ON
```

### No se pueden subir imágenes
```bash
# Verificar PHP
php -i | grep upload_max_filesize
php -i | grep post_max_size

# En php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Chatbot da error 404
Verificar `.htaccess` o configuración de rutas.

---

## 📞 Testing

### Probar Chatbot
```
1. Abrir: https://tudominio.com/chatbot/index/1
2. Seleccionar tipo de reservación
3. Elegir fecha
4. Seleccionar recurso
5. Llenar datos (teléfono 10 dígitos)
6. Confirmar
```

### Probar Imágenes
```
1. Crear habitación nueva
2. Subir 2-3 imágenes
3. Editar habitación
4. Ver imágenes
5. Eliminar una imagen
```

### Probar Liberación Automática
```sql
-- Simular reservación de mesa hace 3 horas
INSERT INTO table_reservations (...) 
VALUES (..., DATE_SUB(NOW(), INTERVAL 3 HOUR), ...);

-- Esperar 5 minutos
-- Verificar que cambió a 'completed'
SELECT * FROM table_reservations WHERE id = X;
```

---

## 📊 Base de Datos

### Nuevas Tablas
```
resource_images          # Imágenes de recursos
chatbot_reservations     # Reservaciones del chatbot
```

### Nuevos Eventos
```
auto_release_table_amenity_reservations   # Cada 5 min
auto_release_room_reservations            # Cada 1 hora
```

### Nuevos Procedimientos
```
check_resource_availability   # Validar disponibilidad
```

---

## 📖 Documentación Completa

Ver archivo: **`NUEVAS_CARACTERISTICAS_2024.md`**

---

## ✅ Checklist Final

- [ ] Backup realizado ✅
- [ ] Migración ejecutada ✅
- [ ] Directorios creados ✅
- [ ] Event scheduler activo ✅
- [ ] Prueba de registro con teléfono ✅
- [ ] Prueba de chatbot ✅
- [ ] Prueba de subir imágenes ✅
- [ ] Link del chatbot visible en perfil ✅

---

## 🎉 ¡Todo Listo!

El sistema ha sido actualizado exitosamente con todas las nuevas funcionalidades.

**Versión:** 1.2.0  
**Fecha:** 2024  
**Estado:** ✅ COMPLETO

---

💡 **Tip:** Comparte el link del chatbot en redes sociales, WhatsApp Business o tu sitio web para empezar a recibir reservaciones automáticas.
