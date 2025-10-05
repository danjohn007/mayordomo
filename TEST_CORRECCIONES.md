# 🧪 Guía de Pruebas - Correcciones Admin Hotel

## ✅ Lista de Verificación Rápida

### 1. 🖼️ Prueba de Imágenes

**Objetivo:** Verificar que las imágenes se muestran correctamente en las vistas de edición.

**Pasos:**
1. Inicia sesión como administrador
2. Ve a **Habitaciones** → Selecciona una habitación con imágenes → Clic en "Editar"
3. ✅ Las imágenes deben mostrarse correctamente en la sección "Imágenes Actuales"
4. Ve a **Mesas** → Selecciona una mesa con imágenes → Clic en "Editar"
5. ✅ Las imágenes deben mostrarse correctamente
6. Ve a **Amenidades** → Selecciona una amenidad con imágenes → Clic en "Editar"
7. ✅ Las imágenes deben mostrarse correctamente

**Resultado Esperado:**
- Las imágenes se cargan desde `/public/uploads/`
- No aparecen iconos de imagen rota
- Las imágenes tienen el tamaño correcto (100px de altura)

---

### 2. 📅 Prueba del Calendario

**Objetivo:** Verificar que el calendario muestra todas las reservaciones con sus detalles.

**Pasos:**
1. Ve a **Calendario** (menú principal)
2. ✅ El calendario debe mostrar eventos con iconos:
   - 🚪 Habitaciones
   - 🍽️ Mesas
   - ⭐ Amenidades
   - 🔔 Servicios
3. Haz clic en cualquier evento del calendario
4. ✅ Debe abrirse un modal con los detalles:
   - Tipo de reservación
   - Estado (con badge de color)
   - Nombre del huésped
   - Recurso (número o nombre)
   - Fecha y hora
5. Verifica los colores de los eventos:
   - 🟡 Amarillo = Pendiente
   - 🟢 Verde = Confirmado
   - 🔵 Azul = En curso/Check-in
   - ⚫ Gris = Completado
   - 🔴 Rojo = Cancelado

**Resultado Esperado:**
- Todos los tipos de reservaciones aparecen en el calendario
- Los colores reflejan correctamente el estado
- Los detalles se muestran completos al hacer clic

---

### 3. 🔔 Prueba de Sonido Persistente

**Objetivo:** Verificar que el sonido de alerta se reproduce persistentemente hasta leer las notificaciones.

**Preparación:**
1. Asegúrate de que existe el archivo: `public/assets/sounds/notification.wav`
2. Abre el navegador y haz clic en cualquier parte de la página (requerido para permitir audio)

**Pasos:**
1. Crea una nueva reservación de habitación (debe quedar en estado 'pending')
   - Usa el chatbot o crea manualmente
2. Espera 15-20 segundos (tiempo de polling)
3. ✅ Debe escucharse un sonido de alerta
4. ✅ El sonido debe repetirse cada 10 segundos
5. Ve a **Reservaciones** → Encuentra la reservación creada
6. Haz clic en el botón ✅ "Confirmar"
7. ✅ El sonido debe detenerse automáticamente

**Prueba Alternativa - Cancelar Reservación:**
1. Crea otra reservación pendiente
2. Espera a que suene la alerta
3. En lugar de confirmar, cancela la reservación
4. ✅ El sonido debe detenerse

**Resultado Esperado:**
- Sonido se reproduce para reservaciones pendientes
- Sonido se repite cada 10 segundos
- Sonido se detiene al confirmar o cancelar
- Badge de notificaciones se actualiza en el menú

---

## 🔧 Solución de Problemas

### ❌ Las imágenes no se ven

**Causa posible:** Permisos incorrectos en el directorio de uploads

**Solución:**
```bash
chmod -R 755 public/uploads/
chown -R www-data:www-data public/uploads/
```

---

### ❌ El calendario no muestra eventos

**Causa posible:** Error en la consulta a la base de datos o hotel_id incorrecto

**Verificación:**
1. Abre la consola del navegador (F12)
2. Ve a la pestaña "Network"
3. Busca la petición a `/calendar/getEvents`
4. Verifica que retorna datos JSON con eventos

**Solución:**
- Asegúrate de que existan reservaciones en la base de datos
- Verifica que el usuario tenga un hotel_id válido
- Revisa los logs del servidor para errores PHP

---

### ❌ El sonido no se reproduce

**Causa posible:** El navegador bloqueó la reproducción automática

**Solución:**
1. Haz clic en cualquier parte de la página antes de esperar notificaciones
2. Verifica que existe el archivo: `public/assets/sounds/notification.wav`
3. Abre la consola del navegador (F12) y busca errores

**Comando para verificar archivo:**
```bash
ls -lh public/assets/sounds/notification.wav
```

**Resultado esperado:** Archivo de ~44KB

---

### ❌ El sonido no se detiene

**Causa posible:** Las notificaciones no se están marcando como leídas correctamente

**Solución:**
1. Ve a **Notificaciones** en el menú
2. Haz clic en "Marcar todas como leídas"
3. El sonido debe detenerse inmediatamente

---

## 📊 Matriz de Pruebas

| Prueba | Esperado | Real | Estado |
|--------|----------|------|--------|
| Imágenes en Habitaciones | ✅ Se muestran | | ⬜ |
| Imágenes en Mesas | ✅ Se muestran | | ⬜ |
| Imágenes en Amenidades | ✅ Se muestran | | ⬜ |
| Calendario - Habitaciones | ✅ Aparecen con 🚪 | | ⬜ |
| Calendario - Mesas | ✅ Aparecen con 🍽️ | | ⬜ |
| Calendario - Amenidades | ✅ Aparecen con ⭐ | | ⬜ |
| Calendario - Detalles | ✅ Modal completo | | ⬜ |
| Sonido - Primera alerta | ✅ Se reproduce | | ⬜ |
| Sonido - Repetición | ✅ Cada 10 seg | | ⬜ |
| Sonido - Al confirmar | ✅ Se detiene | | ⬜ |
| Sonido - Al cancelar | ✅ Se detiene | | ⬜ |

---

## 🎯 Casos de Uso Completos

### Caso 1: Recepción de Reservación de Habitación
1. Un huésped reserva una habitación por el chatbot
2. La reservación queda en estado 'pending'
3. El admin recibe notificación con sonido
4. El sonido se repite cada 10 segundos
5. El admin revisa la reservación en el módulo de Reservaciones
6. El admin ve la reservación en el Calendario con 🚪
7. El admin confirma la reservación
8. El sonido se detiene
9. El calendario actualiza el color a verde

### Caso 2: Reservación de Mesa desde Chatbot
1. Un huésped reserva una mesa
2. La reservación aparece en el módulo de Reservaciones con 🍽️
3. El admin ve la reservación en el Calendario con fecha y hora
4. El admin recibe notificación con sonido persistente
5. El admin confirma o cancela
6. El sonido se detiene

### Caso 3: Gestión de Imágenes
1. El admin edita una habitación
2. Ve correctamente todas las imágenes actuales
3. Puede marcar una imagen como principal
4. Puede eliminar imágenes
5. Puede agregar nuevas imágenes
6. Todo funciona igual para Mesas y Amenidades

---

## ✅ Checklist Final

Antes de dar por completadas las pruebas, verifica:

- [ ] Las 3 vistas de edición (habitaciones, mesas, amenidades) muestran imágenes
- [ ] El calendario muestra los 3 tipos de reservaciones (habitaciones, mesas, amenidades)
- [ ] El modal del calendario muestra todos los detalles correctamente
- [ ] Los colores del calendario reflejan los estados correctamente
- [ ] El archivo de sonido existe en `public/assets/sounds/notification.wav`
- [ ] El sonido se reproduce al recibir notificaciones pendientes
- [ ] El sonido se repite cada 10 segundos
- [ ] El sonido se detiene al confirmar reservaciones
- [ ] El sonido se detiene al cancelar reservaciones
- [ ] El badge de notificaciones se actualiza correctamente

---

**Estado de Pruebas:** Pendiente de ejecución
**Tester:** _____________
**Fecha:** _____________
**Ambiente:** Producción / Desarrollo

---

## 📋 Reporte de Errores

Si encuentras algún problema durante las pruebas, documéntalo aquí:

| # | Prueba | Error Encontrado | Severidad | Estado |
|---|--------|------------------|-----------|--------|
| 1 | | | Alta/Media/Baja | |
| 2 | | | Alta/Media/Baja | |
| 3 | | | Alta/Media/Baja | |

---

**Fin de las Pruebas**
