# 📱 Ejemplos de Uso - MajorBot v1.2.0

## 🎯 Casos de Uso Prácticos

### 1️⃣ Registro de Nuevo Hotel con Validación de Teléfono

**Antes:**
```
Usuario ingresa: (555) 123-4567
Sistema acepta cualquier formato
```

**Ahora:**
```
Usuario ingresa: 5551234567
✅ Exactamente 10 dígitos numéricos
✅ Sin guiones, paréntesis ni espacios
✅ Validación en tiempo real
```

**Flujo:**
1. Ir a `/auth/register`
2. Llenar formulario
3. Campo teléfono solo acepta 10 dígitos
4. Si intenta enviar con menos/más dígitos → Error
5. Con 10 dígitos exactos → ✅ Registro exitoso

---

### 2️⃣ Gestión de Solicitudes de Servicio

**Escenario:** Hotel Paradise tiene una solicitud de toallas extras en habitación 305

**Antes:**
```
- Solo se podía ver la solicitud
- No se podía editar
- No había manera de cancelar
- Estado estático
```

**Ahora:**
```
Admin/Manager puede:
✅ Editar título, descripción, prioridad
✅ Cambiar número de habitación
✅ Cancelar solicitud con un click
✅ Cambiar estado: Pendiente → Asignado → En Progreso → Completado
```

**Flujo:**
1. Ir a "Solicitudes de Servicio"
2. Ver solicitud de habitación 305
3. Click en ✏️ (editar)
4. Cambiar prioridad de "Normal" a "Urgente"
5. Agregar nota: "Cliente VIP"
6. Guardar cambios
7. En listado, cambiar estado a "En Progreso" con dropdown
8. Cuando se resuelve, cambiar a "Completado"

**O para cancelar:**
1. Click en ❌ (cancelar)
2. Confirmar → Estado cambia a "Cancelado"

---

### 3️⃣ Chatbot Público - Reservación de Habitación

**Escenario:** Cliente quiere reservar una suite para fin de semana

**Flujo Conversacional:**

```
🤖: ¡Hola! Soy el asistente de reservaciones de Hotel Paradise. 
    ¿Qué te gustaría reservar?

    [🚪 Habitación]  [🍽️ Mesa]  [⭐ Amenidad]

👤: [Click en Habitación]

🤖: Perfecto! ¿Para qué fecha?

👤: Check-in: 2024-12-20
    Check-out: 2024-12-22

🤖: Buscando disponibilidad...

🤖: Estas son las opciones disponibles:

    [Habitación 101]
    ┌─────────────────┐
    │ [Imagen]        │
    ├─────────────────┤
    │ Tipo: Suite     │
    │ Capacidad: 4    │
    │ $150/noche      │
    └─────────────────┘

    [Habitación 205]
    ┌─────────────────┐
    │ [Imagen]        │
    ├─────────────────┤
    │ Tipo: Deluxe    │
    │ Capacidad: 2    │
    │ $120/noche      │
    └─────────────────┘

👤: [Click en Habitación 101]

🤖: Por favor proporciona tus datos de contacto:

👤: Nombre: Juan Pérez
    Email: juan@email.com
    Teléfono: 5551234567  ✅
    Notas: Necesito cuna para bebé

🤖: ¡Reservación creada! Te contactaremos pronto para confirmar.
```

**URL del Chatbot:**
```
https://tudominio.com/chatbot/index/1
```

**Compartir en:**
- WhatsApp Business
- Facebook
- Instagram Bio
- Sitio web
- Email marketing

---

### 4️⃣ Administrador Obtiene Link del Chatbot

**Flujo:**
1. Admin inicia sesión
2. Va a "Mi Perfil"
3. Ve nueva sección: **"Chatbot de Reservaciones"**

```
┌────────────────────────────────────┐
│ 💬 Chatbot de Reservaciones       │
├────────────────────────────────────┤
│ Enlace público para que tus        │
│ huéspedes puedan hacer            │
│ reservaciones en línea.           │
│                                    │
│ Enlace del Chatbot:               │
│ ┌──────────────────────────────┐  │
│ │ https://hotel.com/chatbot/... │  │
│ └──────────────────────────────┘  │
│ [📋 Copiar] [🔗 Abrir Chatbot]   │
└────────────────────────────────────┘
```

4. Click en "Copiar"
5. Pegar en WhatsApp/Facebook/Website
6. Clientes pueden reservar sin registrarse

---

### 5️⃣ Agregar Imágenes a una Habitación

**Escenario:** Hotel agrega fotos de suite presidencial

**Flujo:**
1. Ir a "Habitaciones"
2. Click "Nueva Habitación" (o editar existente)
3. Llenar datos:
   - Número: 501
   - Tipo: Presidencial
   - Capacidad: 6
   - Precio: $500
4. En **"Imágenes (opcional)"**:
   ```
   [Seleccionar archivos]
   ```
5. Click "Examinar"
6. Seleccionar 5 fotos:
   - suite_vista.jpg (será principal)
   - suite_cama.jpg
   - suite_baño.jpg
   - suite_sala.jpg
   - suite_terraza.jpg
7. Click "Guardar"

**Resultado:**
- Primera imagen = imagen principal
- Todas las imágenes almacenadas
- Visible en chatbot
- Visible en panel admin

**Para Editar:**
1. Editar habitación 501
2. Ver imágenes actuales:
   ```
   [Imagen 1] 🌟 Principal  [🗑️ Eliminar]
   [Imagen 2]               [🗑️ Eliminar]
   [Imagen 3]               [🗑️ Eliminar]
   ```
3. Agregar más imágenes si se desea
4. Eliminar las que no sirven

---

### 6️⃣ Liberación Automática - Mesa de Restaurante

**Escenario:** Reservación de mesa para las 19:00

**Timeline:**
```
18:55 → Mesa #5 estado: "disponible"
19:00 → Cliente hace reservación → Estado: "confirmada"
19:00 → Hostess marca como "seated" (sentados)
19:30 → Cliente comiendo... Estado: "seated"
20:00 → Cliente comiendo... Estado: "seated"
20:30 → Cliente comiendo... Estado: "seated"
21:00 → Cliente comiendo... Estado: "seated"
21:05 → ⏰ Sistema automático verifica (han pasado 2 horas)
21:05 → ✅ Estado cambia a "completed"
21:05 → Mesa #5 estado: "disponible"
```

**Evento SQL (se ejecuta cada 5 minutos):**
```sql
UPDATE table_reservations
SET status = 'completed'
WHERE status IN ('confirmed', 'seated')
  AND TIMESTAMPDIFF(HOUR, 
      CONCAT(reservation_date, ' ', reservation_time), 
      NOW()) >= 2;
```

---

### 7️⃣ Liberación Automática - Habitación

**Escenario:** Checkout programado para 12:00, pero habitación no se ha liberado

**Timeline:**
```
Día 1 - 15:00 → Check-in habitación 203
Día 2 - 12:00 → Checkout programado
Día 2 - 12:30 → Cliente sale, pero habitación sigue "ocupada"
Día 2 - 15:00 → ⏰ Sistema verifica cada hora
Día 2 - 15:00 → ✅ Habitación cambia a "disponible"
Día 2 - 15:01 → Lista para nueva reservación
```

**Evento SQL (se ejecuta cada 1 hora):**
```sql
UPDATE room_reservations
SET status = 'checked_out'
WHERE status = 'checked_in'
  AND check_out_date < CURDATE()
  AND HOUR(NOW()) >= 15;
```

---

### 8️⃣ Validación de Disponibilidad en Chatbot

**Escenario:** Cliente intenta reservar habitación ya ocupada

**Flujo:**
1. Cliente selecciona fechas: 20-22 dic
2. Sistema verifica con procedimiento SQL:
   ```sql
   CALL check_resource_availability('room', 101, '2024-12-20', '2024-12-22');
   ```
3. Encuentra conflicto:
   ```
   room_reservations:
   - room_id: 101
   - check_in: 2024-12-19
   - check_out: 2024-12-21 ❌ CONFLICTO
   ```
4. **Habitación 101 NO aparece en lista**
5. Solo muestra habitaciones disponibles

**Ejemplo Real:**
```
Hotel tiene:
- Habitación 101 (ocupada 19-21 dic)
- Habitación 102 (disponible)
- Habitación 103 (disponible)
- Habitación 104 (ocupada 20-23 dic)

Cliente busca: 20-22 dic

Chatbot muestra:
✅ Habitación 102
✅ Habitación 103

NO muestra:
❌ Habitación 101 (conflicto: 19-21)
❌ Habitación 104 (conflicto: 20-23)
```

---

### 9️⃣ Crear Usuario Nuevo con Teléfono Validado

**Escenario:** Manager crea cuenta para nuevo colaborador

**Flujo:**
1. Admin va a "Usuarios" → "Nuevo Usuario"
2. Llenar formulario:
   - Nombre: María
   - Apellido: García
   - Email: maria@hotel.com
   - Teléfono: [___________]  ← Solo acepta 10 dígitos
3. Intenta: 555-123-4567 → ❌ Error (guiones no permitidos)
4. Intenta: 555 123 4567 → ❌ Error (espacios no permitidos)
5. Intenta: 5551234 → ❌ Error (solo 7 dígitos)
6. Ingresa: 5551234567 → ✅ Válido
7. Selecciona rol: "Colaborador"
8. Guardar → Usuario creado exitosamente

---

### 🔟 Editar Estado de Múltiples Solicitudes

**Escenario:** Manager actualiza estados masivamente

**Vista de Solicitudes:**
```
┌──────────────────────────────────────────────────────────────┐
│ Solicitudes de Servicio                  [+ Nueva Solicitud] │
├──────────────────────────────────────────────────────────────┤
│ Título           │ Huésped  │ Hab │ Estado    │ Acciones     │
├──────────────────┼──────────┼─────┼───────────┼──────────────┤
│ Toallas extras   │ Juan P.  │ 305 │ Pendiente │ [dropdown ▼] │
│ TV no funciona   │ Ana M.   │ 201 │ Asignado  │ [dropdown ▼] │
│ Limpieza urgente │ Luis G.  │ 412 │ En Prog.  │ [dropdown ▼] │
└──────────────────┴──────────┴─────┴───────────┴──────────────┘
```

**Acción:**
1. Primera solicitud: Cambiar de "Pendiente" a "Asignado"
   → Click dropdown → Seleccionar "Asignado" → Auto-submit
2. Segunda solicitud: Cambiar de "Asignado" a "En Progreso"
   → Click dropdown → Seleccionar "En Progreso" → Auto-submit
3. Tercera solicitud: Cambiar de "En Progreso" a "Completado"
   → Click dropdown → Seleccionar "Completado" → Auto-submit

**Resultado:** 3 solicitudes actualizadas en segundos

---

## 🎓 Mejores Prácticas

### Uso del Chatbot
- ✅ Compartir link en redes sociales
- ✅ Agregar a firma de email
- ✅ Incluir en WhatsApp Business respuesta automática
- ✅ Widget en sitio web
- ✅ QR code en recepción

### Gestión de Imágenes
- ✅ Usar fotos de alta calidad (1920x1080 o superior)
- ✅ Optimizar peso (< 2MB por imagen)
- ✅ Primera imagen: mejor vista del recurso
- ✅ Máximo 5-7 imágenes por recurso
- ✅ Actualizar fotos regularmente

### Liberación Automática
- ✅ Verificar que Event Scheduler esté activo
- ✅ Monitorear logs regularmente
- ✅ Ajustar tiempos si es necesario (modificar eventos SQL)

### Solicitudes de Servicio
- ✅ Responder rápidamente (cambiar estado)
- ✅ Asignar colaboradores específicos
- ✅ Usar prioridades correctamente
- ✅ Cancelar solicitudes duplicadas

---

## 📊 Métricas Recomendadas

### Chatbot
- Reservaciones por día
- Tasa de conversión (visitas vs reservaciones)
- Tipos de recursos más reservados
- Horarios de mayor actividad

### Imágenes
- Recursos con imágenes vs sin imágenes
- Impacto en reservaciones
- Recursos más vistos

### Solicitudes
- Tiempo promedio de resolución
- Solicitudes por prioridad
- Tasa de cancelación
- Colaboradores más eficientes

---

## 🚀 Siguiente Nivel

### Integraciones Futuras
- Pagos en línea en chatbot
- Notificaciones por SMS
- WhatsApp Business API
- Google Calendar sync
- Generación de facturas PDF

---

**Versión:** 1.2.0  
**Última actualización:** 2024  
**Estado:** ✅ Producción
