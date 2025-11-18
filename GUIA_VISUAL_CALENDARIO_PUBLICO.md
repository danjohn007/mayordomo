# 📅 Guía Visual - Calendario Público de Reservaciones

## 🎯 ¿Qué es el Calendario Público?

Es una página web que tus clientes pueden visitar **sin necesidad de registrarse** para:
- Ver qué habitaciones están disponibles
- Ver los precios por día
- Reservar directamente por WhatsApp

---

## 📱 Para Administradores: Cómo Compartir el Calendario

### Paso 1: Obtener el Enlace
1. Inicia sesión en el panel de administración
2. Ve a **"Configuraciones del Hotel"** en el menú
3. Busca la sección **"Calendario Público de Reservaciones"** (con fondo azul)

### Paso 2: Copiar el Enlace
- Haz clic en el botón **"Copiar"** 📋
- El enlace se copiará automáticamente
- Verás un mensaje de confirmación "¡Copiado!"

### Paso 3: Compartir
Pega el enlace en:
- ✅ Facebook / Instagram (en tu bio o posts)
- ✅ Sitio web del hotel (botón "Ver Disponibilidad")
- ✅ WhatsApp Business (mensaje automático)
- ✅ Email a clientes
- ✅ Material impreso (genera un QR code)

**Ejemplo de publicación:**
```
🏨 ¡Reserva tu habitación ahora! ✨

Consulta nuestra disponibilidad en tiempo real:
👉 https://tuhotel.com/public-calendar?hotel_id=1

💚 Verde = Disponible
❤️ Rojo = Reservado
```

---

## 👥 Para Clientes: Cómo Reservar

### Paso 1: Abrir el Calendario
- Haz clic en el enlace que te compartió el hotel
- No necesitas crear cuenta ni iniciar sesión
- Se carga automáticamente

### Paso 2: Buscar Fechas Disponibles

**🔍 Filtrar por Tipo:**
- Usa el menú desplegable "Filtrar por tipo de habitación"
- Selecciona: Todos / Sencilla / Doble / Suite / etc.

**📆 Navegar por Meses:**
- Botón **"Anterior"** ⬅️ para ver el mes pasado
- Botón **"Siguiente"** ➡️ para ver el siguiente mes
- El mes actual aparece en grande en el centro

**🎨 Entender los Colores:**
- 🟢 **Verde** = Disponible (¡puedes reservar!)
- 🔴 **Rojo** = Ocupado (no disponible)
- ⚪ **Gris** = Fecha pasada

### Paso 3: Ver Detalles de la Habitación
Cada habitación muestra:
- 🚪 **Número**: Ej. "Habitación 101"
- 🏷️ **Tipo**: Doble, Suite, etc.
- 👥 **Capacidad**: Cuántas personas caben
- 💵 **Precio**: "Desde $850.00"
- 📝 **Descripción**: Detalles de la habitación

### Paso 4: Reservar por WhatsApp

1. **Haz clic en una fecha verde** (disponible)
2. Se abre **WhatsApp** automáticamente
3. Verás un mensaje **pre-llenado** con:
   ```
   Me interesa hacer una reservación
   
   Habitación: 101
   Fecha: lunes, 18 de noviembre de 2025
   Precio: $850.00
   ```
4. **Envía el mensaje** al hotel
5. Espera la confirmación del personal

**💡 Tip:** El mensaje ya viene listo, solo tienes que enviarlo. Puedes agregar más información si deseas (número de personas, peticiones especiales, etc.)

---

## 📊 Ejemplo Visual del Calendario

```
┌─────────────────────────────────────────────────┐
│         🏨 Hotel Rancho Paraíso                 │
│   Calendario de Disponibilidad de Habitaciones │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  Filtrar: [Todos los tipos ▼]   ◀ Nov 2025 ▶  │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  🚪 Habitación 101           🏷️ [Doble]        │
│  👥 2 personas | 💵 Desde $850.00              │
│                                                  │
│  ⚪15  ⚪16  ⚪17  🟢18  🟢19  🔴20  🔴21  🟢22   │
│  Vie  Sáb  Dom  Lun  Mar  Mié  Jue  Vie        │
│             $850 $850            $950           │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  🚪 Habitación 201           🏷️ [Suite]        │
│  👥 4 personas | 💵 Desde $1200.00             │
│                                                  │
│  ⚪15  ⚪16  ⚪17  🟢18  🟢19  🟢20  🟢21  🔴22   │
│  Vie  Sáb  Dom  Lun  Mar  Mié  Jue  Vie        │
│            $1200$1200$1200$1200                 │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ 🟢 Disponible - Click para reservar vía WhatsApp│
│ 🔴 Reservado                                     │
│ ⚪ Fecha pasada                                  │
└─────────────────────────────────────────────────┘
```

---

## ❓ Preguntas Frecuentes

### ❓ ¿Los clientes necesitan crear cuenta?
**No.** El calendario es completamente público y accesible sin registro.

### ❓ ¿Los precios se actualizan automáticamente?
**Sí.** Los precios que ves son los configurados en el sistema para cada día de la semana.

### ❓ ¿La disponibilidad es en tiempo real?
**Sí.** Cada vez que alguien abre el calendario, se consulta la base de datos para mostrar la disponibilidad actual.

### ❓ ¿Qué pasa si hago clic en una fecha roja (ocupada)?
**Nada.** Las fechas ocupadas no son clickables. Solo las verdes (disponibles) abren WhatsApp.

### ❓ ¿Funciona en celular?
**Sí.** El calendario es totalmente responsive y se adapta a cualquier tamaño de pantalla.

### ❓ ¿Puedo cambiar el número de WhatsApp?
**Sí.** Como administrador, contacta al equipo de desarrollo para cambiar el número configurado.

### ❓ ¿Se puede reservar directamente desde el calendario?
**No directamente.** El calendario abre WhatsApp para que el cliente contacte al hotel. El personal confirma la reservación manualmente.

### ❓ ¿Puedo tener múltiples calendarios públicos?
**Sí.** Cada hotel tiene su propio `hotel_id` en el enlace, permitiendo calendarios independientes.

---

## 🎨 Personalización

### Cambiar Colores del Calendario
Contacta al equipo de desarrollo para personalizar:
- Color del gradiente de fondo
- Colores de disponibilidad (verde/rojo/gris)
- Color del encabezado
- Estilo de las tarjetas de habitación

### Cambiar el Mensaje de WhatsApp
Puedes personalizar:
- El texto inicial ("Me interesa hacer una reservación")
- El formato de la fecha
- La información incluida

### Agregar Logo del Hotel
Se puede agregar el logo del hotel en el encabezado del calendario público.

---

## 💡 Ideas para Maximizar el Uso

### 1. Redes Sociales
- 📌 **Pin** el enlace en Instagram Stories destacadas
- 🔗 Agrega el enlace en tu **bio de Instagram**
- 📱 Publica posts semanales mostrando disponibilidad
- 📹 Crea **Reels/TikToks** mostrando cómo usar el calendario

### 2. Marketing por Email
```html
<a href="ENLACE_CALENDARIO" style="background: #25D366; color: white; padding: 15px 30px; border-radius: 25px; text-decoration: none;">
    📅 Ver Disponibilidad
</a>
```

### 3. QR Code
- Genera un **código QR** con el enlace del calendario
- Imprime en:
  - Tarjetas de presentación
  - Folletos del hotel
  - Menu del restaurante
  - Lobby del hotel

### 4. Google My Business
- Agrega el enlace en la descripción de tu negocio
- Publícalo como un "Post" en tu perfil

### 5. WhatsApp Business
- Configura una **respuesta automática**:
  ```
  ¡Hola! 👋 Gracias por contactarnos.
  
  Consulta nuestra disponibilidad aquí:
  [ENLACE_CALENDARIO]
  
  O dime en qué puedo ayudarte 😊
  ```

---

## 🎉 Beneficios del Sistema

### Para el Hotel
- ✅ Reduce llamadas y mensajes de consulta
- ✅ Disponibilidad visible 24/7
- ✅ Cliente puede auto-servirse
- ✅ Proceso de reservación más rápido
- ✅ Mejor imagen profesional

### Para los Clientes
- ✅ Transparencia en precios
- ✅ Disponibilidad en tiempo real
- ✅ Proceso simple y rápido
- ✅ No necesita crear cuenta
- ✅ Reservación directa por WhatsApp

---

## 📞 Soporte

¿Necesitas ayuda? Contacta:
- 📧 Email: soporte@mayorbot.com
- 💬 WhatsApp: [Número de soporte]
- 📚 Documentación técnica: Ver `CALENDARIO_PUBLICO.md`

---

**¡Felices reservaciones! 🎊**
