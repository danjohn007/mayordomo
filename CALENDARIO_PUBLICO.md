# Calendario Público de Reservaciones

## Descripción General

Se ha implementado un **Calendario Público de Reservaciones** que permite a los clientes ver la disponibilidad de habitaciones en tiempo real y contactar al hotel vía WhatsApp para realizar una reservación.

## Características Principales

### 1. Vista Pública sin Autenticación
- **URL de acceso**: `https://tudominio.com/public-calendar?hotel_id=1`
- No requiere inicio de sesión
- Accesible desde cualquier dispositivo (responsive design)

### 2. Visualización de Disponibilidad
- **Calendario mensual** con navegación (mes anterior/siguiente)
- **Disponibilidad por día** con código de colores:
  - 🟢 **Verde**: Disponible (clickable)
  - 🔴 **Rojo**: Reservado (no disponible)
  - ⚪ **Gris**: Fecha pasada (no disponible)
- **Precios por día**: Muestra el precio específico para cada día (lunes-domingo)
- **Detalles de habitación**:
  - Número de habitación
  - Tipo (Sencilla, Doble, Suite, etc.)
  - Capacidad de personas
  - Descripción
  - Precio base

### 3. Filtros
- **Por tipo de habitación**: Dropdown para filtrar Sencilla, Doble, Suite, Deluxe, Presidential

### 4. Integración WhatsApp
- **Número configurado**: 7206212805
- **Funcionalidad**:
  - Al hacer click en una fecha disponible, se abre WhatsApp
  - Mensaje pre-llenado con:
    - Habitación seleccionada
    - Fecha completa (día, mes, año)
    - Precio para esa fecha
  - Mensaje base: "Me interesa hacer una reservación"

### 5. Enlace en Configuraciones del Hotel
- Nueva sección en "Configuraciones del Hotel" (Admin)
- Muestra el enlace público del calendario
- Botón "Copiar" para copiar al portapapeles
- Botón "Ver" para abrir en nueva pestaña
- Información sobre la integración WhatsApp

## Archivos Modificados/Creados

### Nuevos Archivos

1. **`app/controllers/PublicCalendarController.php`**
   - Controlador para el calendario público
   - Métodos:
     - `index()`: Muestra la vista del calendario
     - `getAvailability()`: API AJAX que retorna disponibilidad de habitaciones

2. **`app/views/calendar/public.php`**
   - Vista HTML del calendario público
   - Incluye todo el CSS y JavaScript necesario
   - Interfaz moderna con gradiente y diseño atractivo

### Archivos Modificados

1. **`app/controllers/BaseController.php`**
   - Línea 20: Agregado `'publiccalendar'` a la lista de rutas públicas

2. **`app/views/settings/index.php`**
   - Agregada nueva sección "Calendario Público de Reservaciones"
   - Función JavaScript `copyToClipboard()` para copiar enlace

## Cómo Usar

### Para el Administrador del Hotel

1. **Acceder a Configuraciones**:
   - Iniciar sesión como Admin
   - Ir a "Configuraciones del Hotel"

2. **Obtener el enlace**:
   - En la sección "Calendario Público de Reservaciones"
   - Ver el enlace público
   - Usar botón "Copiar" para copiar al portapapeles
   - Usar botón "Ver" para abrir en nueva pestaña

3. **Compartir el enlace**:
   - Redes sociales
   - Sitio web del hotel
   - Email marketing
   - Material impreso (QR code)

### Para los Clientes

1. **Acceder al calendario**:
   - Abrir el enlace compartido por el hotel
   - No necesita crear cuenta ni iniciar sesión

2. **Ver disponibilidad**:
   - Navegar entre meses con botones Anterior/Siguiente
   - Filtrar por tipo de habitación si desea
   - Ver fechas disponibles en verde con precio

3. **Reservar vía WhatsApp**:
   - Click en una fecha disponible (verde)
   - Se abre WhatsApp automáticamente
   - Mensaje pre-llenado con detalles
   - Enviar mensaje al hotel

## Detalles Técnicos

### Base de Datos
El calendario consulta las siguientes tablas:
- `hotels`: Información del hotel
- `rooms`: Habitaciones disponibles con precios por día
- `room_reservations`: Reservaciones existentes
- `resource_images`: Imágenes de habitaciones (opcional)

### Precios por Día de la Semana
El sistema soporta precios diferentes para cada día:
- `price_monday` hasta `price_sunday`
- Si no están configurados, usa el `price` base

### API Endpoint
**URL**: `/public-calendar/getAvailability`

**Parámetros GET**:
- `hotel_id`: ID del hotel (requerido)
- `start`: Fecha inicio YYYY-MM-DD (opcional)
- `end`: Fecha fin YYYY-MM-DD (opcional)

**Respuesta JSON**:
```json
{
  "success": true,
  "availability": [
    {
      "room_number": "101",
      "type": "double",
      "capacity": 2,
      "price": 850.00,
      "prices": {
        "monday": 850.00,
        "tuesday": 850.00,
        "wednesday": 850.00,
        "thursday": 850.00,
        "friday": 950.00,
        "saturday": 1050.00,
        "sunday": 900.00
      },
      "description": "Habitación cómoda...",
      "image": "/uploads/room-101.jpg",
      "dates": {
        "2025-11-18": "available",
        "2025-11-19": "available",
        "2025-11-20": "reserved",
        ...
      }
    },
    ...
  ]
}
```

### Seguridad
- ✅ Sin exposición de datos sensibles
- ✅ Solo muestra habitaciones con status: available, reserved, occupied
- ✅ No permite modificar datos (solo lectura)
- ✅ Usa PDO prepared statements
- ✅ Sanitización de parámetros

## Personalización

### Cambiar el Número de WhatsApp
Editar en `app/views/calendar/public.php`, línea ~267:
```javascript
const whatsappNumber = '5217206212805'; // Cambiar aquí
```

### Cambiar el Mensaje de WhatsApp
Editar en `app/views/calendar/public.php`, línea ~268:
```javascript
const whatsappMessage = 'Me interesa hacer una reservación'; // Cambiar aquí
```

### Personalizar Colores
Editar el CSS en `app/views/calendar/public.php`, sección `<style>`:
- Gradiente del fondo: líneas 15-18
- Colores de disponibilidad: líneas 135-164

### Agregar Filtros Adicionales
En el controlador `PublicCalendarController.php`, método `getAvailability()`:
- Agregar filtros en la consulta SQL (línea ~51)
- Pasar parámetros adicionales desde la vista

## Ejemplos de Uso

### Ejemplo 1: Integrar en Sitio Web
```html
<a href="https://tuhotel.com/public-calendar?hotel_id=1" 
   class="btn btn-primary" 
   target="_blank">
   Ver Disponibilidad
</a>
```

### Ejemplo 2: Generar QR Code
Usar el enlace público para generar un QR code que los clientes puedan escanear.

### Ejemplo 3: Redes Sociales
Publicar el enlace en Facebook, Instagram, etc. con un texto atractivo:
> "¡Consulta nuestra disponibilidad en tiempo real! 🏨✨ Click aquí: [enlace]"

## Solución de Problemas

### El calendario no carga
1. Verificar que la URL incluya `?hotel_id=X`
2. Verificar que el hotel esté activo en la base de datos
3. Revisar los logs de error PHP

### No se muestran habitaciones
1. Verificar que existan habitaciones con status 'available', 'reserved' o 'occupied'
2. Verificar que `hotel_id` sea correcto
3. Revisar la consola del navegador para errores JavaScript

### WhatsApp no se abre
1. Verificar el formato del número: debe incluir código de país sin '+' ni espacios
2. Formato correcto: `5217206212805` (52 = México, 1 = Celular, 7206212805 = número)
3. En computadoras, asegurarse de tener WhatsApp Desktop instalado o usar WhatsApp Web

### Fechas no se actualizan
1. Verificar que las reservaciones tengan status correcto en la BD
2. El sistema solo muestra reservaciones NO canceladas
3. Refrescar la página para obtener datos actualizados

## Mantenimiento

### Actualizar Diseño
Todos los estilos están inline en `app/views/calendar/public.php` para facilitar el deployment sin dependencias externas.

### Agregar Idiomas
El calendario está en español. Para agregar otros idiomas:
1. Duplicar el archivo de vista
2. Traducir textos
3. Crear rutas adicionales por idioma

### Optimización
Para hoteles con muchas habitaciones:
- Considerar paginación
- Implementar lazy loading
- Cachear respuestas del API

## Soporte
Para reportar bugs o solicitar mejoras, contactar al equipo de desarrollo.

---

**Versión**: 1.0.0  
**Fecha**: Noviembre 2025  
**Autor**: MajorBot Development Team
