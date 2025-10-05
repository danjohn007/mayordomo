# 🏗️ Arquitectura de Nuevas Funcionalidades

## 📐 Diagrama de Flujo del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                    USUARIO HACE RESERVACIÓN                      │
│                  (Habitación o Mesa - Desde Web)                 │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│              INSERT en room_reservations o                       │
│                    table_reservations                            │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│         TRIGGER: trg_notify_new_room/table_reservation          │
│                    (Se ejecuta automáticamente)                  │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│     1. Obtiene hotel_id y datos de la reservación              │
│     2. Consulta role_permissions para usuarios autorizados      │
│     3. INSERT en system_notifications para cada usuario         │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│              NOTIFICACIONES EN COLA                              │
│            (system_notifications con is_read=0)                  │
└───────────────┬────────────────────────┬────────────────────────┘
                │                        │
       ┌────────▼────────┐      ┌────────▼────────┐
       │  ADMIN/MANAGER  │      │  COLLABORATORS   │
       │   Dashboard     │      │    Dashboard     │
       └────────┬────────┘      └────────┬────────┘
                │                        │
                └────────────┬───────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│             JavaScript: notifications.js                         │
│          Polling cada 15 segundos a /notifications/check        │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│        NotificationsController::check()                          │
│     SELECT notificaciones no leídas del usuario actual          │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                  Response JSON con notificaciones                │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│            JavaScript procesa las notificaciones                 │
│         1. Reproduce sonido (notification.mp3)                   │
│         2. Muestra toast visual (Bootstrap)                      │
│         3. Actualiza badge de contador                           │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🗄️ Estructura de Base de Datos

### Tablas Principales

```
┌──────────────────────┐
│   subscriptions      │
├──────────────────────┤
│ id                   │
│ name                 │
│ type                 │
│ price                │
│ duration_days        │
│ features             │
│ description ◄─────── (NUEVO)
│ is_active            │
└──────────────────────┘

┌──────────────────────┐         ┌──────────────────────┐
│  room_reservations   │         │  table_reservations  │
├──────────────────────┤         ├──────────────────────┤
│ id                   │         │ id                   │
│ room_id              │         │ table_id             │
│ guest_id             │         │ guest_id             │
│ check_in             │         │ reservation_date     │
│ check_out            │         │ reservation_time     │
│ status               │         │ status               │
│ notification_sent ◄──┼─────┐   │ notification_sent ◄──┼─── (NUEVO)
└──────────────────────┘     │   └──────────────────────┘
                             │
                             │   ┌──────────────────────┐
                             └──►│ system_notifications │
                                 ├──────────────────────┤
                                 │ id                   │
                                 │ hotel_id             │
                                 │ user_id              │
                                 │ notification_type    │
                                 │ related_type         │
                                 │ related_id           │
                                 │ title                │
                                 │ message              │
                                 │ is_read              │
                                 │ requires_sound       │
                                 │ priority             │
                                 └──────────────────────┘
                                          ▲
                                          │
┌──────────────────────┐                 │
│  role_permissions    │─────────────────┘
├──────────────────────┤         (Filtra a quién notificar)
│ id                   │
│ hotel_id             │
│ user_id              │
│ role_name            │
│ can_manage_rooms     │
│ can_manage_tables    │
│ can_manage_menu      │
│ amenity_ids (JSON)   │
│ service_types (JSON) │
└──────────────────────┘
```

### Vista Unificada

```sql
v_all_reservations
├─ room_reservations (UNION)
└─ table_reservations

Campos unificados:
- reservation_type ('room' | 'table')
- id, status, created_at
- hotel_id, resource_number
- reservation_date, reservation_time
- guest info, price, notes
```

---

## 🎯 Flujo de Gestión de Roles

```
┌─────────────────────────────────────────────────────────────────┐
│               ADMIN PROPIETARIO DEL HOTEL                        │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│           Accede a "Roles y Permisos" (/roles)                  │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│     RolesController::index()                                     │
│     SELECT users + role_permissions WHERE hotel_id = X          │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│              Vista: roles/index.php                              │
│         Acordeón con cada colaborador del hotel                  │
│                                                                  │
│  Para cada usuario:                                              │
│  ┌──────────────────────────────────────────────┐              │
│  │ [✓] Habitaciones                             │              │
│  │ [✓] Mesas                                    │              │
│  │ [ ] Menú                                     │              │
│  │                                              │              │
│  │ Amenidades:                                  │              │
│  │ [✓] Spa         [ ] Gimnasio                 │              │
│  │ [✓] Piscina     [ ] Sauna                    │              │
│  │                                              │              │
│  │ Servicios:                                   │              │
│  │ [✓] Limpieza    [✓] Mantenimiento           │              │
│  │ [ ] Room Service [ ] Conserjería             │              │
│  │                                              │              │
│  │          [Guardar Permisos]                  │              │
│  └──────────────────────────────────────────────┘              │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│         Admin guarda cambios (POST /roles/update/{id})          │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│     RolesController::update()                                    │
│     INSERT/UPDATE en role_permissions                            │
│     - Permisos generales (rooms, tables, menu)                   │
│     - Amenidades específicas (JSON array)                        │
│     - Tipos de servicio (JSON array)                             │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│          PERMISOS GUARDADOS EN BD                                │
│      Ahora el usuario solo recibe notificaciones                 │
│           de las áreas asignadas                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📱 Flujo del Módulo de Reservaciones

```
┌─────────────────────────────────────────────────────────────────┐
│      Usuario Staff (Admin/Manager/Hostess/Collaborator)         │
│              Accede a "Reservaciones" (/reservations)            │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│       ReservationsController::index()                            │
│       SELECT * FROM v_all_reservations WHERE hotel_id = X       │
│       Aplica filtros: tipo, estado, búsqueda, fechas            │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│              Vista: reservations/index.php                       │
│                                                                  │
│  Filtros: [Tipo▼] [Estado▼] [Buscar...] [Desde] [Hasta] [🔍]  │
│                                                                  │
│  Tabla de Reservaciones:                                         │
│  ┌──────┬────────┬─────────┬──────────┬──────────┬──────────┐  │
│  │ ID   │ Tipo   │ Recurso │ Huésped  │ Fecha    │ Acciones │  │
│  ├──────┼────────┼─────────┼──────────┼──────────┼──────────┤  │
│  │ 123  │ 🏠 H   │ 101     │ Juan P.  │ 15/10/24 │ 🖊️ ✓ ✗  │  │
│  │ 124  │ 🍽️ M   │ Mesa 5  │ María G. │ 15/10/24 │ 🖊️ ✓    │  │
│  └──────┴────────┴─────────┴──────────┴──────────┴──────────┘  │
│                                                                  │
│  Acciones:                                                       │
│  🖊️ = Editar    ✓ = Confirmar    ✗ = Cancelar                  │
└───────────────────────────┬─────────────────────────────────────┘
                            │
              ┌─────────────┼─────────────┐
              │             │             │
              ▼             ▼             ▼
         ┌────────┐    ┌────────┐    ┌────────┐
         │ EDITAR │    │CONFIRMAR│   │CANCELAR│
         └────┬───┘    └────┬───┘    └────┬───┘
              │             │             │
              ▼             ▼             ▼
      reservations/   UPDATE status   UPDATE status
         edit.php      = confirmed    = cancelled
```

---

## 🔄 Ciclo de Vida de una Notificación

```
1. CREACIÓN
   └─ Trigger en INSERT de reservación
      └─ INSERT en system_notifications
         ├─ hotel_id
         ├─ user_id (según role_permissions)
         ├─ notification_type
         ├─ title, message
         ├─ is_read = 0
         ├─ requires_sound = 1
         └─ priority = 'high'

2. DETECCIÓN (Cliente)
   └─ JavaScript polling cada 15s
      └─ GET /notifications/check
         └─ SELECT WHERE is_read=0 AND user_id=X

3. PRESENTACIÓN
   ├─ Reproduce sonido (notification.mp3)
   ├─ Muestra toast (Bootstrap)
   └─ Actualiza badge (+1)

4. MARCADO COMO LEÍDA
   └─ Usuario hace click en notificación
      └─ POST /notifications/markAsRead/{id}
         └─ UPDATE is_read=1, read_at=NOW()

5. LIMPIEZA (Opcional - Manual o Cron)
   └─ DELETE WHERE created_at < 30 días
```

---

## 🛡️ Control de Acceso por Rol

```
┌──────────────────────────────────────────────────────────────┐
│                         ROLES                                 │
├──────────────┬───────────────────────────────────────────────┤
│ SUPERADMIN   │ • Acceso total al sistema                     │
│              │ • Gestión de hoteles y suscripciones          │
│              │ • NO accede a módulos operativos del hotel    │
├──────────────┼───────────────────────────────────────────────┤
│ ADMIN        │ • Propietario del hotel                       │
│ (Propietario)│ • Acceso a TODO en su hotel                   │
│              │ • Gestiona usuarios y roles                   │
│              │ • Recibe TODAS las notificaciones             │
│              │ • Acceso a: Reservaciones, Roles, Usuarios    │
├──────────────┼───────────────────────────────────────────────┤
│ MANAGER      │ • Gerente del hotel                           │
│              │ • Similar a Admin pero sin gestión de roles   │
│              │ • Recibe notificaciones de todas las áreas    │
│              │ • Acceso a: Reservaciones, Usuarios           │
├──────────────┼───────────────────────────────────────────────┤
│ HOSTESS      │ • Anfitriona / Recepcionista                  │
│              │ • Gestiona reservaciones y bloqueos           │
│              │ • Recibe notificaciones de reservaciones      │
│              │ • Acceso a: Reservaciones, Bloqueos           │
├──────────────┼───────────────────────────────────────────────┤
│ COLLABORATOR │ • Personal operativo                          │
│              │ • Notificaciones SOLO de áreas asignadas      │
│              │ • Configurado por Admin en Roles y Permisos   │
│              │ • Acceso a: Reservaciones (vista), Servicios  │
├──────────────┼───────────────────────────────────────────────┤
│ GUEST        │ • Huésped del hotel                           │
│              │ • Hace reservaciones y solicitudes            │
│              │ • Ve solo sus propias reservaciones           │
│              │ • NO recibe notificaciones del staff          │
└──────────────┴───────────────────────────────────────────────┘
```

---

## 📊 Métricas y Optimización

### Índices en Base de Datos

```sql
role_permissions:
  - PRIMARY KEY (id)
  - INDEX idx_hotel (hotel_id)
  - INDEX idx_user (user_id)
  - INDEX idx_role (role_name)
  - UNIQUE KEY unique_user_hotel (user_id, hotel_id)

system_notifications:
  - PRIMARY KEY (id)
  - INDEX idx_hotel (hotel_id)
  - INDEX idx_user (user_id)
  - INDEX idx_is_read (is_read)
  - INDEX idx_type (notification_type)
  - INDEX idx_created (created_at)
```

### Configuración de Polling

```javascript
// En notifications.js línea 8
const POLL_INTERVAL = 15000; // 15 segundos

Ajustable según:
- 5000ms (5s)  = Más responsive, más carga servidor
- 15000ms (15s) = Balance óptimo (RECOMENDADO)
- 30000ms (30s) = Menos carga, menos responsive
```

### Limpieza de Notificaciones Antiguas

```sql
-- Ejecutar periódicamente (cron mensual recomendado)
DELETE FROM system_notifications 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- O mantener las importantes
DELETE FROM system_notifications 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
AND priority NOT IN ('urgent', 'high');
```

---

## 🔒 Seguridad Implementada

1. **Validación de Permisos**
   ```php
   if (!hasRole(['admin', 'manager'])) {
       flash('error', 'No tienes permiso');
       redirect('dashboard');
   }
   ```

2. **Aislamiento por Hotel**
   ```php
   $stmt->prepare("SELECT * FROM reservations WHERE hotel_id = ?");
   $stmt->execute([$currentUser['hotel_id']]);
   ```

3. **Prevención SQL Injection**
   ```php
   // Siempre usar prepared statements
   $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
   ```

4. **Escape de HTML (XSS)**
   ```php
   <?= e($user['name']) ?> // Función helper que hace htmlspecialchars
   ```

5. **Validación de Entrada**
   ```php
   $planId = sanitize($_POST['plan_id'] ?? '');
   if (empty($planId)) {
       $errors[] = 'Campo requerido';
   }
   ```

---

## 🎨 Stack Tecnológico

```
Frontend:
├─ HTML5
├─ Bootstrap 5.3
├─ Bootstrap Icons
├─ JavaScript Vanilla (ES6+)
└─ CSS3 Custom

Backend:
├─ PHP 7.4+
├─ PDO (Database Abstraction)
├─ MVC Architecture
└─ Session Management

Database:
├─ MySQL 5.7+ / MariaDB 10.3+
├─ InnoDB Engine
├─ UTF8MB4 Charset
├─ Triggers & Views
└─ JSON Data Type (for arrays)

Security:
├─ Prepared Statements
├─ CSRF Protection (via sessions)
├─ Role-Based Access Control (RBAC)
├─ Input Sanitization
└─ Output Escaping
```

---

## 📈 Escalabilidad

El sistema está diseñado para crecer:

- ✅ Índices optimizados en campos clave
- ✅ Queries con paginación donde corresponde
- ✅ Polling configurable (ajustable según carga)
- ✅ Triggers eficientes (solo inserts necesarios)
- ✅ Vista materializada (v_all_reservations) para joins complejos
- ✅ JSON para arrays flexibles (amenities, services)
- ✅ Soft deletes (cambio de estado vs DELETE físico)

**Capacidad estimada:**
- Hasta 100 hoteles sin cambios
- Hasta 1000 usuarios simultáneos
- Hasta 10,000 reservaciones por mes
- Con optimizaciones adicionales: ilimitado

---

Este documento proporciona una visión completa de cómo funcionan internamente las nuevas funcionalidades implementadas.
