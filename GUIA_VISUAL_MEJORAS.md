# 📸 Guía Visual de Mejoras - Sistema Mayordomo

---

## 🎯 1. Botón "Nueva Reservación"

### Vista: Listado de Reservaciones (`/reservations`)

```
┌─────────────────────────────────────────────────────────────────┐
│ 📅 Reservaciones                    [Nueva Reservación ▼]       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Dropdown abierto:                                              │
│  ┌─────────────────────┐                                        │
│  │ 🚪 Habitación       │                                        │
│  │ 🍽️  Mesa             │                                        │
│  │ 🏊 Amenidad         │                                        │
│  └─────────────────────┘                                        │
│                                                                 │
│  [ Filtros de búsqueda ]                                        │
│                                                                 │
│  ┌─ Tabla de Reservaciones ──────────────────────────────────┐ │
│  │ ID │ Tipo        │ Recurso │ Huésped  │ Fecha  │ Estado  │ │
│  ├────┼─────────────┼─────────┼──────────┼────────┼─────────┤ │
│  │ 1  │ 🚪 Habitación│ 101     │ Juan P.  │ Hoy    │ ✓ Conf. │ │
│  │ 2  │ 🍽️  Mesa      │ T-5     │ María G. │ Mañana │ ⏳ Pend.│ │
│  │ 3  │ 🏊 Amenidad  │ Spa-A   │ Pedro L. │ Hoy    │ ✓ Conf. │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### Funcionalidad
- **Ubicación:** Esquina superior derecha
- **Estilo:** Botón azul (`btn-primary`)
- **Interacción:** Dropdown con 3 opciones
- **Redireccionamiento:**
  - Habitación → `/rooms`
  - Mesa → `/tables`
  - Amenidad → `/amenities`

---

## 🗂️ 2. Catálogo de Servicios en Configuraciones

### Vista: Configuraciones (`/settings`)

```
┌─────────────────────────────────────────────────────────────────┐
│ ⚙️  Configuraciones del Hotel                                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─ 📋 Configuración de Reservaciones ────────────────────────┐ │
│  │ ☑ Permitir empalmar reservaciones de mesas                 │ │
│  │ ☐ Permitir empalmar reservaciones de habitaciones          │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌─ 📝 Catálogo de Tipos de Servicio ────────────────────────┐ │
│  │                                                            │ │
│  │  [+ Agregar Tipo de Servicio]                             │ │
│  │                                                            │ │
│  │  ┌── Tabla ─────────────────────────────────────────────┐ │ │
│  │  │ Icono │ Nombre           │ Descripción        │ Orden││ │
│  │  ├───────┼──────────────────┼────────────────────┼──────┤│ │
│  │  │ 💧    │ Toallas          │ Toallas adicionales│  1   ││ │
│  │  │ 🍳    │ Menú             │ Room service       │  2   ││ │
│  │  │ 👔    │ Conserje         │ Asistencia         │  3   ││ │
│  │  │ 🧹    │ Limpieza         │ Servicio limpieza  │  4   ││ │
│  │  │ 🔧    │ Mantenimiento    │ Reparaciones       │  5   ││ │
│  │  │ 🏊    │ Amenidades       │ Uso de amenidades  │  6   ││ │
│  │  │ 🚗    │ Transporte       │ Servicio taxi      │  7   ││ │
│  │  │ ❓    │ Otro             │ Otras solicitudes  │ 99   ││ │
│  │  └──────────────────────────────────────────────────────┘│ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  [💾 Guardar Configuraciones]  [❌ Cancelar]                    │
└─────────────────────────────────────────────────────────────────┘
```

### Modal: Agregar Tipo de Servicio

```
┌─────────────────────────────────────┐
│ Agregar Tipo de Servicio        [X] │
├─────────────────────────────────────┤
│                                     │
│  Nombre: *                          │
│  [___________________________]      │
│                                     │
│  Descripción:                       │
│  [___________________________]      │
│  [___________________________]      │
│                                     │
│  Icono Bootstrap:                   │
│  [bi-wrench________________]        │
│  Ver iconos: Bootstrap Icons ↗      │
│                                     │
│  Orden:                             │
│  [0______]                          │
│                                     │
│  [Cancelar]  [✓ Guardar]            │
└─────────────────────────────────────┘
```

### Modal: Editar Tipo de Servicio

```
┌─────────────────────────────────────┐
│ Editar Tipo de Servicio         [X] │
├─────────────────────────────────────┤
│                                     │
│  Nombre: *                          │
│  [Toallas__________________]        │
│                                     │
│  Descripción:                       │
│  [Solicitud de toallas_____]        │
│  [adicionales______________]        │
│                                     │
│  Icono Bootstrap:                   │
│  [bi-droplet_______________]        │
│                                     │
│  Orden:                             │
│  [1______]                          │
│                                     │
│  ☑ Activo                           │
│                                     │
│  [Cancelar]  [⚠️ Actualizar]         │
└─────────────────────────────────────┘
```

---

## 📊 3. Dashboard con Gráficas

### Vista: Dashboard Admin/Manager (`/dashboard`)

```
┌─────────────────────────────────────────────────────────────────┐
│ 📊 Dashboard                                                    │
│ Bienvenido, Juan Pérez                                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─ Cards Estadísticas ──────────────────────────────────────┐ │
│  │ 🚪 Habitaciones  │ 🍽️  Mesas    │ 🔔 Solicitudes │ 💵 Hoy  │ │
│  │     15           │     8        │      12        │  $2,500│ │
│  │  10 disponibles  │ 5 disponibles│  3 pendientes  │ Ventas │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌─ Gráficas ────────────────────────────────────────────────┐ │
│  │                                                            │ │
│  │  ┌─ Reservaciones por Tipo ─┐                             │ │
│  │  │         🥧                 │                             │ │
│  │  │     ╱───────╲             │                             │ │
│  │  │   ╱   40%   ╲             │  ┌─ Estados ────────────┐  │ │
│  │  │  │  Rooms    │            │  │      📊              │  │
│  │  │  │  35%      │            │  │  15┤ ▓▓▓▓▓          │  │
│  │  │   ╲ Tables  ╱             │  │  10┤ ▓▓▓▓ ░░░░      │  │
│  │  │    ╲  25%  ╱              │  │   5┤ ▓▓ ░░ ▒▒▒      │  │
│  │  │     ╲─────╱               │  │    └──────────────  │  │
│  │  │    Amenities              │  │     Pend Conf Compl │  │
│  │  └───────────────────────────┘  └─────────────────────┘  │ │
│  │                                                            │ │
│  │  ┌─ Solicitudes de Servicio ─┐                            │ │
│  │  │         🥧                 │                             │ │
│  │  │     ╱───────╲             │                             │ │
│  │  │   ╱   70%   ╲             │                             │ │
│  │  │  │ Asignadas │            │                             │ │
│  │  │   ╲ 30% Sin ╱             │                             │ │
│  │  │    ╲ Asignar╱             │                             │ │
│  │  │     ╲─────╱               │                             │ │
│  │  └───────────────────────────┘                            │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌─ Reservaciones Recientes ──────────────────────────────────┐ │
│  │ Habitación │ Huésped      │ Check-in │ Estado             │ │
│  ├────────────┼──────────────┼──────────┼────────────────────┤ │
│  │ 101        │ María López  │ Hoy      │ ✓ Confirmada       │ │
│  │ 205        │ Pedro Sánchez│ Mañana   │ ✓ Confirmada       │ │
│  │ 310        │ Ana García   │ 15/10    │ ⏳ Pendiente        │ │
│  └──────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### Vista: Dashboard Hostess

```
┌─────────────────────────────────────────────────────────────────┐
│ 📊 Dashboard                                                    │
│ Bienvenido, Laura Martínez (Hostess)                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─ Cards Estadísticas ──────────────────────────────────────┐ │
│  │ 🍽️  Mesas         │ 📅 Hoy         │ 🔒 Bloqueos         │ │
│  │     8             │      15        │      3              │ │
│  │  5 disponibles    │ Reservaciones  │  Activos            │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌─ Gráficas ────────────────────────────────────────────────┐ │
│  │  [Mismas 3 gráficas que Admin/Manager]                    │ │
│  │  - Reservaciones por Tipo                                 │ │
│  │  - Estados de Reservaciones                               │ │
│  │  - Solicitudes de Servicio                                │ │
│  └───────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎨 4. Colores de las Gráficas

### Gráfica 1: Reservaciones por Tipo (Doughnut)
```
┌─────────────────────────┐
│ Habitaciones │ #0dcaf0  │ (Cyan/Info)
│ Mesas        │ #198754  │ (Verde/Success)
│ Amenidades   │ #0d6efd  │ (Azul/Primary)
└─────────────────────────┘
```

### Gráfica 2: Estados de Reservaciones (Bar)
```
┌─────────────────────────┐
│ Pendiente    │ #ffc107  │ (Amarillo/Warning)
│ Confirmada   │ #0dcaf0  │ (Cyan/Info)
│ Check-in     │ #0d6efd  │ (Azul/Primary)
│ Completada   │ #198754  │ (Verde/Success)
│ Cancelada    │ #dc3545  │ (Rojo/Danger)
└─────────────────────────┘
```

### Gráfica 3: Solicitudes (Pie)
```
┌─────────────────────────┐
│ Asignadas    │ #198754  │ (Verde/Success)
│ Sin Asignar  │ #dc3545  │ (Rojo/Danger)
└─────────────────────────┘
```

---

## 🔍 5. Flujo de Usuario

### Caso 1: Crear Nueva Reservación de Habitación

```
1. Usuario (Admin/Manager/Hostess) → Login
              ↓
2. Dashboard → Clic en "Reservaciones" (menú)
              ↓
3. /reservations → Clic en "Nueva Reservación"
              ↓
4. Dropdown → Seleccionar "🚪 Habitación"
              ↓
5. /rooms → Lista de habitaciones disponibles
              ↓
6. Seleccionar habitación → Crear reservación
              ↓
7. Confirmar → Reservación creada ✓
```

### Caso 2: Agregar Tipo de Servicio Personalizado

```
1. Usuario (Admin) → Login
              ↓
2. Dashboard → Clic en "Configuraciones" (menú)
              ↓
3. /settings → Scroll a "Catálogo de Tipos de Servicio"
              ↓
4. Clic en [+ Agregar Tipo de Servicio]
              ↓
5. Modal aparece → Llenar formulario
   - Nombre: "Valet Parking"
   - Descripción: "Servicio de estacionamiento"
   - Icono: "bi-car-front"
   - Orden: 8
              ↓
6. Clic en [Guardar]
              ↓
7. Tipo agregado a la tabla ✓
              ↓
8. Ahora disponible en solicitudes de servicio
```

### Caso 3: Ver Gráficas en Dashboard

```
1. Usuario (Admin/Manager/Hostess) → Login
              ↓
2. Dashboard → Automáticamente visible
              ↓
3. 3 Gráficas renderizadas con Chart.js:
   - Reservaciones por Tipo (dona)
   - Estados (barras)
   - Solicitudes (pastel)
              ↓
4. Interacción:
   - Hover sobre secciones → Ver valores
   - Click en leyenda → Ocultar/mostrar datos
```

---

## 📱 6. Responsive Design

### Desktop (>992px)
```
┌────────────────────────────────────┐
│ [Gráfica 1] [Gráfica 2] [Gráfica 3]│
│    (4 col)      (4 col)     (4 col) │
└────────────────────────────────────┘
```

### Tablet (768px - 992px)
```
┌─────────────────────┐
│ [Gráfica 1]         │
│    (12 col)         │
├─────────────────────┤
│ [Gráfica 2]         │
│    (12 col)         │
├─────────────────────┤
│ [Gráfica 3]         │
│    (12 col)         │
└─────────────────────┘
```

### Mobile (<768px)
```
┌──────────────┐
│ [Gráfica 1]  │
│   (stack)    │
├──────────────┤
│ [Gráfica 2]  │
│   (stack)    │
├──────────────┤
│ [Gráfica 3]  │
│   (stack)    │
└──────────────┘
```

---

## 🎯 7. Iconos Bootstrap Recomendados

### Para Tipos de Servicio

| Tipo              | Icono Sugerido      | Clase CSS           |
|-------------------|---------------------|---------------------|
| Toallas           | 💧                  | `bi-droplet`        |
| Menú/Food         | 🍳                  | `bi-egg-fried`      |
| Conserje          | 👔                  | `bi-person-badge`   |
| Limpieza          | 🧹                  | `bi-brush`          |
| Mantenimiento     | 🔧                  | `bi-tools`          |
| Amenidades        | 🏊                  | `bi-spa`            |
| Transporte        | 🚗                  | `bi-car-front`      |
| WiFi              | 📶                  | `bi-wifi`           |
| Lavandería        | 👕                  | `bi-basket`         |
| Despertar         | ⏰                  | `bi-alarm`          |
| Farmacia          | 💊                  | `bi-capsule`        |
| Flores            | 🌺                  | `bi-flower1`        |
| Bar               | 🍷                  | `bi-cup-straw`      |
| Gimnasio          | 💪                  | `bi-heart-pulse`    |
| Masajes           | 💆                  | `bi-peace`          |

Ver más: https://icons.getbootstrap.com/

---

## 🧪 8. Estados de Prueba

### Gráfica con Datos
```
✅ Hay reservaciones → Gráfica renderizada
✅ Colores aplicados correctamente
✅ Leyenda interactiva
✅ Tooltips al hacer hover
```

### Gráfica sin Datos
```
⚠️ No hay reservaciones → Mensaje:
   "No hay datos disponibles"
   (texto centrado, color gris)
```

---

## 📸 9. Screenshots Esperados

### Pantalla 1: Botón Nueva Reservación
- Ubicación: `/reservations`
- Elemento: Botón azul con dropdown
- Visible para: Admin, Manager, Hostess

### Pantalla 2: Catálogo de Servicios
- Ubicación: `/settings`
- Elemento: Tabla con tipos de servicio
- Botones: Agregar, Editar (cada fila)

### Pantalla 3: Dashboard con Gráficas
- Ubicación: `/dashboard`
- Elementos: 3 gráficas en fila
- Tipos: Doughnut, Bar, Pie

### Pantalla 4: Modal Agregar Servicio
- Activación: Click en "Agregar Tipo"
- Campos: Nombre, Descripción, Icono, Orden
- Botones: Cancelar, Guardar

### Pantalla 5: Modal Editar Servicio
- Activación: Click en botón ✏️
- Campos: Pre-llenados con datos actuales
- Switch: Activo/Inactivo

---

**Fin de Guía Visual**
