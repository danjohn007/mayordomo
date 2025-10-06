# 🚀 Instalación Rápida - Ajustes Admin Hotel

## ⚡ Pasos Rápidos de Instalación

### 1️⃣ Aplicar Base de Datos (REQUERIDO)

```bash
# Opción A: Desde línea de comandos
mysql -u root -p aqh_mayordomo < database/add_hotel_settings.sql

# Opción B: Desde phpMyAdmin
# 1. Abrir phpMyAdmin
# 2. Seleccionar base de datos "aqh_mayordomo"
# 3. Ir a pestaña "SQL"
# 4. Copiar y pegar el contenido de database/add_hotel_settings.sql
# 5. Ejecutar
```

### 2️⃣ Verificar Archivos

Todos los archivos ya están incluidos en el repositorio:

```
✅ app/controllers/SettingsController.php
✅ app/views/settings/index.php
✅ app/views/layouts/header.php (actualizado)
✅ app/controllers/ChatbotController.php (actualizado)
✅ app/views/calendar/index.php (actualizado)
✅ database/add_hotel_settings.sql
```

### 3️⃣ Probar Funcionalidades

#### A. Calendario
1. Iniciar sesión como **Admin**
2. Ir a **Menú → Calendario**
3. Verificar que se muestran las reservaciones
4. Hacer clic en un evento para ver detalles

#### B. Configuraciones
1. Iniciar sesión como **Admin**
2. Ir a **Menú → Configuraciones** (nuevo ítem)
3. Ver la opción "Permitir empalmar reservaciones"
4. Probar activar/desactivar y guardar

#### C. Validación de Disponibilidad
1. Desactivar "Permitir empalmar reservaciones"
2. Crear una reservación desde el chatbot público
3. Intentar crear otra reservación del mismo recurso en horario conflictivo
4. Verificar que se bloquea con mensaje de error

#### D. Sonido de Alertas (Ya implementado)
1. Crear reservación en estado PENDIENTE
2. Esperar 15 segundos
3. Verificar que suena alerta cada 10 segundos
4. Confirmar/cancelar reservación
5. Verificar que sonido se detiene

---

## 📋 Funcionalidades Implementadas

### ✅ Calendario de Reservaciones
- **Muestra**: Tipo, Estado, Huésped, Recurso, Fecha
- **Modal mejorado** con detalles completos
- **Estilos CSS** para mejor visibilidad
- **Leyenda** clara con colores y tipos

### ✅ Sonido de Alerta Persistente
- **Ya implementado** en versión anterior
- Alerta cada 10 segundos para reservaciones PENDIENTES
- Se detiene al cambiar estado

### ✅ Módulo de Configuraciones
- **Nuevo menú** "Configuraciones" para Admin
- **Opción**: Permitir empalmar reservaciones
- **Panel de ayuda** con información detallada

### ✅ Validación de Disponibilidad
- **Habitaciones**: Bloqueadas 15 horas después del check-out
- **Mesas**: Bloqueadas 2 horas
- **Amenidades**: Bloqueadas 2 horas
- **Configurable**: Se puede desactivar la validación

---

## 🎯 Reglas de Bloqueo

### Habitaciones
```
Check-in:  15/01/2024 14:00
Check-out: 16/01/2024 12:00
Bloqueada: Hasta 17/01/2024 15:00 (27 horas después del check-out)
```

### Mesas
```
Reservación: 19:00
Bloqueada:   19:00 - 21:00 (2 horas)
```

### Amenidades
```
Reservación: 10:00
Bloqueada:   10:00 - 12:00 (2 horas)
```

---

## 🔧 Configuración Inicial

La tabla `hotel_settings` se crea automáticamente con el script SQL y establece:

```sql
allow_reservation_overlap = 0 (desactivado)
```

Esto significa que **por defecto** se valida la disponibilidad y NO se permiten empalmes.

---

## ⚠️ Importante

### Para Producción
1. **Hacer backup de la base de datos** antes de aplicar el script SQL
2. **Verificar** que no exista ya la tabla `hotel_settings`
3. **Probar** primero en ambiente de desarrollo

### Roles de Usuario
- Solo usuarios con rol **admin** pueden:
  - Acceder a `/settings`
  - Cambiar la configuración de empalmes

---

## 🧪 Prueba Rápida de Validación

### Test de Habitaciones
```bash
1. Configuraciones → Desactivar "Permitir empalmar"
2. Chatbot → Reservar Habitación 101 (Hoy - Mañana 12:00)
3. Chatbot → Intentar reservar Habitación 101 (Mañana 14:00 - ...)
4. Resultado esperado: ❌ "La habitación no está disponible"
5. Chatbot → Intentar reservar Habitación 101 (Mañana 16:00 - ...)
6. Resultado esperado: ✅ Permite la reservación
```

### Test de Mesas
```bash
1. Chatbot → Reservar Mesa 5 (Hoy 19:00)
2. Chatbot → Intentar reservar Mesa 5 (Hoy 20:00)
3. Resultado esperado: ❌ "La mesa no está disponible"
4. Chatbot → Intentar reservar Mesa 5 (Hoy 21:30)
5. Resultado esperado: ✅ Permite la reservación
```

---

## 📞 Solución de Problemas

### El calendario no muestra eventos
```
✓ Verificar que existen reservaciones en la base de datos
✓ Abrir consola del navegador (F12) y buscar errores
✓ Verificar que el usuario tiene hotel_id válido
✓ Revisar logs del servidor
```

### Menú "Configuraciones" no aparece
```
✓ Verificar que el usuario tiene rol "admin"
✓ Limpiar caché del navegador
✓ Verificar que app/views/layouts/header.php está actualizado
```

### Validación no funciona
```
✓ Verificar que la tabla hotel_settings existe
✓ Verificar que el setting allow_reservation_overlap está en la base de datos
✓ Revisar logs del servidor para errores SQL
```

### Sonido no se reproduce
```
✓ Verificar que existe el archivo public/assets/sounds/notification.mp3
✓ Verificar permisos del navegador para reproducir audio
✓ Verificar consola del navegador (F12) para errores
✓ Interactuar con la página antes (click en cualquier lado)
```

---

## ✅ Checklist Final

Después de la instalación, verificar:

- [ ] Tabla `hotel_settings` creada en base de datos
- [ ] Menú "Configuraciones" visible para admin
- [ ] Página `/settings` accesible
- [ ] Calendario muestra eventos correctamente
- [ ] Modal de eventos muestra detalles completos
- [ ] Validación de disponibilidad funciona
- [ ] Mensajes de error claros al intentar empalmes
- [ ] Opción de permitir empalmes se puede activar/desactivar
- [ ] Sonido de alertas funciona para reservaciones pendientes

---

## 📚 Documentación Completa

Para más detalles, ver:
- **IMPLEMENTACION_AJUSTES_ADMIN.md** - Documentación técnica completa
- **CAMBIOS_ADMIN_PANEL.md** - Cambios previos ya implementados
- **SOLUCION_ISSUES_ADMIN.md** - Soluciones anteriores

---

**¡Listo para usar!** 🎉

Las funcionalidades están implementadas y listas para producción.
