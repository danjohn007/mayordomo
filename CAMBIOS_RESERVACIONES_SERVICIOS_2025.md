# 📋 Cambios en Reservaciones y Solicitudes de Servicio

**Fecha:** 2025-10-10  
**Versión:** 3.0

---

## 🎯 Resumen de Cambios

Este documento detalla las mejoras implementadas en el sistema de reservaciones y solicitudes de servicio.

### Cambios Principales

1. ✅ **Formulario Unificado de Reservaciones** - Un solo formulario para crear habitaciones, mesas y amenidades
2. ✅ **Catálogo de Tipos de Servicio** - Reemplazo de título por tipo de servicio en solicitudes
3. ✅ **Asignación Automática** - Los colaboradores se asignan automáticamente al crear solicitudes
4. ✅ **Permisos Actualizados** - Admin, Manager y Hostess pueden crear reservaciones
5. ✅ **API Endpoints** - Búsqueda de huéspedes y recursos en tiempo real

---

## 📦 1. Formulario Unificado de Reservaciones

### Ubicación
- **Vista:** `/app/views/reservations/create.php`
- **Ruta:** `/reservations/create`
- **Controlador:** `ReservationsController::create()` y `::store()`

### Funcionalidad

El nuevo formulario unificado permite crear cualquier tipo de reservación desde un solo lugar:

#### Campos del Formulario

1. **Tipo de Reservación*** (seleccionable)
   - 🚪 Habitación
   - 🍽️ Mesa
   - 🏊 Amenidad

2. **Recurso*** (seleccionable - se carga dinámicamente según el tipo)
   - Habitaciones: Muestra número, tipo y precio
   - Mesas: Muestra número y capacidad
   - Amenidades: Muestra nombre y categoría

3. **Huésped*** (búsqueda o nuevo cliente)
   - **Buscar Huésped Existente:** Búsqueda en tiempo real por nombre, email o teléfono
   - **Nuevo Huésped:** Formulario para crear nuevo cliente con validación de teléfono (10 dígitos)

4. **Fecha/Hora*** (dinámico según tipo)
   - **Habitaciones:** Check-in y Check-out
   - **Mesas/Amenidades:** Fecha y hora de reservación

5. **Detalles Adicionales**
   - **Party Size** (solo para mesas): Número de personas
   - **Estado:** Pendiente o Confirmada
   - **Notas:** Solicitudes especiales

### Código Implementado

```php
// ReservationsController.php
public function create() {
    if (!hasRole(['admin', 'manager', 'hostess'])) {
        flash('error', 'No tienes permiso para acceder a esta página', 'danger');
        redirect('dashboard');
    }
    
    $this->view('reservations/create', [
        'title' => 'Nueva Reservación'
    ]);
}

public function store() {
    // Validación de permisos
    // Creación de huésped nuevo o búsqueda de existente
    // Inserción en la tabla correspondiente según el tipo
    // Bloqueo automático del recurso
}
```

### Comportamiento

- **Validación en Tiempo Real:** Los campos se muestran/ocultan según el tipo seleccionado
- **Búsqueda de Huéspedes:** AJAX con debounce (300ms) para búsqueda eficiente
- **Carga de Recursos:** AJAX que obtiene recursos disponibles según el tipo
- **Validación de Teléfono:** Exactamente 10 dígitos para nuevos huéspedes
- **Bloqueo de Recursos:** Se aplican automáticamente las reglas de bloqueo:
  - Habitaciones: Bloqueo por rango de fechas
  - Mesas/Amenidades: Bloqueo de 2 horas desde la hora de reservación

### Permisos
- ✅ Admin
- ✅ Manager
- ✅ Hostess
- ❌ Collaborator
- ❌ Guest

---

## 🗂️ 2. Actualización de Solicitudes de Servicio

### Cambio de "TÍTULO" a "TIPO DE SERVICIO"

#### Vista de Lista (index.php)

**ANTES:**
```html
<th>Título</th>
...
<td><strong><?= e($req['title']) ?></strong></td>
```

**DESPUÉS:**
```html
<th>Tipo de Servicio</th>
<th>Descripción</th>
...
<td>
    <i class="bi <?= e($req['service_type_icon']) ?>"></i>
    <strong><?= e($req['service_type_name']) ?></strong>
</td>
<td><?= e($req['title']) ?: '-' ?></td>
```

#### Formulario de Creación (create.php)

**ANTES:**
```html
<label for="title" class="form-label">Título *</label>
<input type="text" class="form-control" id="title" name="title" required>
```

**DESPUÉS:**
```html
<label for="service_type_id" class="form-label">Tipo de Servicio *</label>
<select class="form-select" id="service_type_id" name="service_type_id" required>
    <option value="">Seleccione un tipo de servicio...</option>
    <?php foreach ($serviceTypes as $type): ?>
        <option value="<?= $type['id'] ?>">
            <?= e($type['name']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="title" class="form-label">Descripción breve</label>
<input type="text" class="form-control" id="title" name="title" 
       placeholder="Opcional - descripción adicional">
```

#### Formulario de Edición (edit.php)

Similar al formulario de creación, con el tipo de servicio preseleccionado.

### Controlador Actualizado (ServicesController.php)

```php
public function create() {
    $user = currentUser();
    $serviceTypeCatalogModel = $this->model('ServiceTypeCatalog');
    $serviceTypes = $serviceTypeCatalogModel->getAllActive($user['hotel_id']);
    
    $this->view('services/create', [
        'title' => 'Nueva Solicitud',
        'serviceTypes' => $serviceTypes
    ]);
}

public function store() {
    $user = currentUser();
    
    // Auto-asignar al usuario actual si es admin, manager o hostess
    $assignedTo = null;
    if (hasRole(['admin', 'manager', 'hostess'])) {
        $assignedTo = $user['id'];
    }
    
    $data = [
        'hotel_id' => $user['hotel_id'],
        'guest_id' => $user['id'],
        'service_type_id' => sanitize($_POST['service_type_id'] ?? null),
        'title' => sanitize($_POST['title'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'priority' => sanitize($_POST['priority'] ?? 'normal'),
        'room_number' => sanitize($_POST['room_number'] ?? ''),
        'assigned_to' => $assignedTo
    ];
    
    $model = $this->model('ServiceRequest');
    if ($model->create($data)) {
        flash('success', 'Solicitud creada exitosamente', 'success');
    }
    redirect('services');
}
```

---

## 🔌 3. API Endpoints

### get_resources.php

**Ruta:** `/api/get_resources.php?type={room|table|amenity}`

**Respuesta:**
```json
{
  "success": true,
  "resources": [
    {
      "id": 1,
      "room_number": "101",
      "type": "double",
      "price": "100.00",
      "status": "available"
    }
  ]
}
```

### search_guests.php

**Ruta:** `/api/search_guests.php?q={query}`

**Respuesta:**
```json
{
  "success": true,
  "guests": [
    {
      "id": 5,
      "first_name": "Juan",
      "last_name": "Pérez",
      "email": "juan@example.com",
      "phone": "5551234567"
    }
  ]
}
```

---

## 🗄️ 4. Migración SQL

### Archivo
`database/update_reservations_and_services_2025.sql`

### Qué hace el Script

1. ✅ Verifica y crea tabla `service_type_catalog` si no existe
2. ✅ Inserta 8 tipos de servicio predeterminados por hotel:
   - 💧 Toallas
   - 🍳 Menú / Room Service
   - 👔 Conserje
   - 🧹 Limpieza
   - 🔧 Mantenimiento
   - 🏊 Amenidades
   - 🚗 Transporte
   - ❓ Otro
3. ✅ Agrega columna `service_type_id` a `service_requests` si no existe
4. ✅ Migra datos existentes: asigna tipo "Otro" a solicitudes sin tipo
5. ✅ Verifica y agrega `hotel_id` a tablas de reservaciones si falta
6. ✅ Actualiza `hotel_id` en reservaciones existentes

### Ejecutar el Script

```bash
mysql -u usuario -p nombre_base_datos < database/update_reservations_and_services_2025.sql
```

### Compatibilidad
- ✅ No afecta funcionalidad existente
- ✅ Campo `service_type_id` puede ser NULL para compatibilidad
- ✅ Campo `title` se mantiene para descripción adicional
- ✅ Campo `assigned_to` se establece automáticamente en la aplicación

---

## 📝 5. Cambios en el Botón "Nueva Reservación"

### ANTES

Botón dropdown con 3 opciones que redirigen a módulos separados:
- Habitación → `/rooms`
- Mesa → `/tables`
- Amenidad → `/amenities`

```html
<div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" 
            data-bs-toggle="dropdown">
        <i class="bi bi-plus-circle"></i> Nueva Reservación
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?= BASE_URL ?>/rooms">...</a></li>
        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tables">...</a></li>
        <li><a class="dropdown-item" href="<?= BASE_URL ?>/amenities">...</a></li>
    </ul>
</div>
```

### DESPUÉS

Botón simple que redirige al formulario unificado:

```html
<a href="<?= BASE_URL ?>/reservations/create" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Nueva Reservación
</a>
```

---

## 🔧 6. Archivos Modificados

### Controladores
- ✅ `app/controllers/ReservationsController.php`
  - Añadido: `create()` - Muestra formulario unificado
  - Añadido: `store()` - Guarda nueva reservación con validación y bloqueo

- ✅ `app/controllers/ServicesController.php`
  - Modificado: `create()` - Pasa tipos de servicio a la vista
  - Modificado: `store()` - Auto-asigna colaborador, usa service_type_id
  - Modificado: `edit()` - Pasa tipos de servicio a la vista
  - Modificado: `update()` - Actualiza con service_type_id

### Vistas
- ✅ `app/views/reservations/index.php` - Botón simplificado
- ✅ `app/views/reservations/create.php` - NUEVO: Formulario unificado
- ✅ `app/views/services/index.php` - Columna "Tipo de Servicio"
- ✅ `app/views/services/create.php` - Dropdown de tipos
- ✅ `app/views/services/edit.php` - Dropdown de tipos

### API
- ✅ `public/api/get_resources.php` - NUEVO: Obtener recursos por tipo
- ✅ `public/api/search_guests.php` - NUEVO: Buscar huéspedes

### Base de Datos
- ✅ `database/update_reservations_and_services_2025.sql` - Script de migración

---

## 🚀 7. Instrucciones de Implementación

### Paso 1: Aplicar Migración SQL

```bash
mysql -u usuario -p base_datos < database/update_reservations_and_services_2025.sql
```

### Paso 2: Verificar Permisos

Asegurar que admin, manager y hostess tienen permisos para:
- Crear reservaciones
- Crear solicitudes de servicio
- Asignar colaboradores

### Paso 3: Probar Funcionalidad

1. **Nueva Reservación:**
   - Ir a `/reservations`
   - Clic en "Nueva Reservación"
   - Completar formulario para cada tipo (room, table, amenity)
   - Verificar que el recurso se bloquea correctamente

2. **Solicitudes de Servicio:**
   - Ir a `/services/create`
   - Seleccionar tipo de servicio del catálogo
   - Verificar que se asigna automáticamente
   - Ver que en la lista se muestra el tipo con icono

3. **Búsqueda de Huéspedes:**
   - En formulario de reservación
   - Escribir en búsqueda y verificar resultados en tiempo real
   - Probar creación de nuevo huésped

---

## 📊 8. Impacto en la Funcionalidad

### Reservaciones
- ✅ Creación más rápida y consistente
- ✅ Validación mejorada de datos
- ✅ Búsqueda eficiente de huéspedes
- ✅ Bloqueo automático de recursos

### Solicitudes de Servicio
- ✅ Mejor organización por tipo
- ✅ Iconos visuales para identificación rápida
- ✅ Descripción adicional opcional
- ✅ Asignación automática de colaboradores

### Experiencia de Usuario
- ✅ Interfaz más intuitiva
- ✅ Menos clics para crear reservaciones
- ✅ Validación en tiempo real
- ✅ Feedback visual inmediato

---

## 🔐 9. Permisos y Roles

### Crear Reservaciones
- ✅ Admin
- ✅ Manager
- ✅ Hostess
- ❌ Collaborator
- ❌ Guest

### Crear Solicitudes de Servicio
- ✅ Admin (auto-asignado)
- ✅ Manager (auto-asignado)
- ✅ Hostess (auto-asignado)
- ✅ Collaborator (sin auto-asignación)
- ✅ Guest (sin auto-asignación)

### Asignar Colaboradores
- ✅ Admin
- ✅ Manager
- ❌ Hostess
- ❌ Collaborator
- ❌ Guest

---

## 🐛 10. Solución de Problemas

### Problema: No aparecen tipos de servicio
**Solución:** Ejecutar el script SQL de migración para insertar tipos predeterminados

### Problema: Error al buscar huéspedes
**Solución:** Verificar que el usuario tenga sesión activa y hotel_id configurado

### Problema: No se cargan recursos
**Solución:** Verificar que las tablas rooms, restaurant_tables y amenities tengan registros

### Problema: Error al crear huésped nuevo
**Solución:** Verificar validación de teléfono (debe ser exactamente 10 dígitos)

---

## 📞 Soporte

Si encuentras algún problema con estos cambios, por favor reporta:
- Pasos para reproducir el problema
- Usuario y rol involucrado
- Capturas de pantalla si es posible
- Mensajes de error

---

**Fecha de Implementación:** 2025-10-10  
**Versión del Sistema:** 3.0  
**Estado:** ✅ Implementado y Probado
