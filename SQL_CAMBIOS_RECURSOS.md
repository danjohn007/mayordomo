# 📊 Cambios en Consultas SQL - Recursos para Reservaciones

## 🎯 Objetivo
Mostrar todos los **registros habilitados** (enabled records) de cada catálogo al seleccionar el tipo de reservación, en lugar de solo mostrar los disponibles actualmente.

---

## 🔄 Comparación de Consultas SQL

### 1️⃣ Habitaciones (Rooms)

#### ❌ ANTES (Restrictivo)
```sql
SELECT id, room_number, type, capacity, price, status 
FROM rooms 
WHERE hotel_id = ? AND status IN ('available', 'reserved')
ORDER BY room_number;
```

**Problema**: Solo mostraba habitaciones con status `available` o `reserved`, excluyendo las `occupied`.

#### ✅ DESPUÉS (Correcto)
```sql
SELECT id, room_number, type, capacity, price, status 
FROM rooms 
WHERE hotel_id = ? AND status != 'maintenance'
ORDER BY room_number;
```

**Solución**: Muestra todas las habitaciones excepto las que están en `maintenance`.

**Registros incluidos**: `available`, `occupied`, `reserved`  
**Registros excluidos**: `maintenance`

---

### 2️⃣ Mesas (Restaurant Tables)

#### ❌ ANTES (Restrictivo)
```sql
SELECT id, table_number, capacity, location, status 
FROM restaurant_tables 
WHERE hotel_id = ? AND status IN ('available', 'reserved')
ORDER BY table_number;
```

**Problema**: Solo mostraba mesas con status `available` o `reserved`, excluyendo las `occupied`.

#### ✅ DESPUÉS (Correcto)
```sql
SELECT id, table_number, capacity, location, status 
FROM restaurant_tables 
WHERE hotel_id = ? AND status != 'blocked'
ORDER BY table_number;
```

**Solución**: Muestra todas las mesas excepto las que están `blocked`.

**Registros incluidos**: `available`, `occupied`, `reserved`  
**Registros excluidos**: `blocked`

---

### 3️⃣ Amenidades (Amenities)

#### ✅ SIN CAMBIOS (Ya estaba correcto)
```sql
SELECT id, name, category, price, capacity, opening_time, closing_time 
FROM amenities 
WHERE hotel_id = ? AND is_available = 1
ORDER BY name;
```

**Correcto**: Muestra todas las amenidades donde `is_available = 1`.

**Registros incluidos**: Todas con `is_available = 1`  
**Registros excluidos**: Todas con `is_available = 0`

---

## 📋 Esquema de Base de Datos

### Tabla: `rooms`
```sql
status ENUM('available', 'occupied', 'maintenance', 'reserved') DEFAULT 'available'
```

| Status | Descripción | ¿Debe mostrarse? |
|--------|-------------|------------------|
| `available` | Disponible para reservar | ✅ Sí |
| `occupied` | Actualmente ocupada | ✅ Sí |
| `reserved` | Reservada | ✅ Sí |
| `maintenance` | En mantenimiento | ❌ No |

### Tabla: `restaurant_tables`
```sql
status ENUM('available', 'occupied', 'reserved', 'blocked') DEFAULT 'available'
```

| Status | Descripción | ¿Debe mostrarse? |
|--------|-------------|------------------|
| `available` | Disponible para reservar | ✅ Sí |
| `occupied` | Actualmente ocupada | ✅ Sí |
| `reserved` | Reservada | ✅ Sí |
| `blocked` | Bloqueada permanentemente | ❌ No |

### Tabla: `amenities`
```sql
is_available TINYINT(1) DEFAULT 1
```

| Valor | Descripción | ¿Debe mostrarse? |
|-------|-------------|------------------|
| `1` | Amenidad habilitada | ✅ Sí |
| `0` | Amenidad deshabilitada | ❌ No |

---

## 🔍 Lógica del Negocio

### Principio Fundamental
> Al crear una reservación, el usuario debe poder **ver todos los recursos habilitados**, no solo los disponibles en ese momento.

### ¿Por qué?
1. **Flexibilidad**: El usuario puede reservar para fechas futuras
2. **Planificación**: Permite ver todos los recursos existentes
3. **Disponibilidad**: El sistema valida conflictos al guardar la reservación

### Validación de Disponibilidad
La disponibilidad **real** se verifica en `ReservationsController::store()` considerando:

#### Para Habitaciones
```php
// Se valida contra el rango de fechas
$checkIn = $_POST['check_in'];
$checkOut = $_POST['check_out'];
// El sistema verifica si la habitación está disponible en esas fechas
```

#### Para Mesas y Amenidades
```php
// Se valida contra fecha y hora específica
$reservationDate = $_POST['reservation_date'];
$reservationTime = $_POST['reservation_time'];
// El sistema verifica si hay conflictos en ese horario
```

---

## 🧪 Casos de Prueba

### Escenario 1: Hotel con 10 habitaciones
- 5 habitaciones: `available`
- 3 habitaciones: `occupied`
- 1 habitación: `reserved`
- 1 habitación: `maintenance`

**Antes**: Se mostraban 6 habitaciones (5 available + 1 reserved)  
**Ahora**: Se muestran 9 habitaciones (5 available + 3 occupied + 1 reserved)  
**Excluida**: 1 habitación en maintenance

### Escenario 2: Restaurante con 20 mesas
- 10 mesas: `available`
- 8 mesas: `occupied`
- 1 mesa: `reserved`
- 1 mesa: `blocked`

**Antes**: Se mostraban 11 mesas (10 available + 1 reserved)  
**Ahora**: Se muestran 19 mesas (10 available + 8 occupied + 1 reserved)  
**Excluida**: 1 mesa blocked

### Escenario 3: Hotel con 5 amenidades
- 4 amenidades: `is_available = 1`
- 1 amenidad: `is_available = 0`

**Antes y Ahora**: Se muestran 4 amenidades (sin cambios)  
**Excluida**: 1 amenidad deshabilitada

---

## ✅ Verificación de la Corrección

### Prueba Manual
1. Iniciar sesión como Admin/Manager/Hostess
2. Ir a `/reservations/create`
3. Seleccionar cada tipo de reservación:

#### Habitaciones 🚪
```
- Dropdown debe mostrar todas las habitaciones excepto las en maintenance
- Debe incluir habitaciones occupied y reserved
- NO debe mostrar "Error al cargar recursos"
```

#### Mesas 🍽️
```
- Dropdown debe mostrar todas las mesas excepto las blocked
- Debe incluir mesas occupied y reserved
- NO debe mostrar "Error al cargar recursos"
```

#### Amenidades 🏊
```
- Dropdown debe mostrar todas las amenidades habilitadas
- Solo muestra donde is_available = 1
- NO debe mostrar "Error al cargar recursos"
```

---

## 📝 Referencia de Implementación

### Archivo: `public/api/get_resources.php`
**Líneas modificadas**: 35, 43  
**Tipo de cambio**: Quirúrgico (2 líneas)

### Cambio Exacto
```diff
--- a/public/api/get_resources.php
+++ b/public/api/get_resources.php
@@ -32,7 +32,7 @@ try {
         $stmt = $db->prepare("
             SELECT id, room_number, type, capacity, price, status 
             FROM rooms 
-            WHERE hotel_id = ? AND status IN ('available', 'reserved')
+            WHERE hotel_id = ? AND status != 'maintenance'
             ORDER BY room_number
         ");
         $stmt->execute([$hotelId]);
@@ -40,7 +40,7 @@ try {
         $stmt = $db->prepare("
             SELECT id, table_number, capacity, location, status 
             FROM restaurant_tables 
-            WHERE hotel_id = ? AND status IN ('available', 'reserved')
+            WHERE hotel_id = ? AND status != 'blocked'
             ORDER BY table_number
         ");
         $stmt->execute([$hotelId]);
```

---

## 🎓 Mejores Prácticas Aplicadas

### ✅ Seguridad
- Uso de prepared statements con placeholders `?`
- Filtrado por `hotel_id` para aislamiento de datos
- Validación de sesión antes de ejecutar queries

### ✅ Escalabilidad
- Query simple y eficiente
- Uso de índices existentes (`idx_hotel`, `idx_status`)
- Sin JOINs innecesarios

### ✅ Mantenibilidad
- Lógica clara y fácil de entender
- Comentarios explicativos
- Consistencia con el resto del código

---

**Fecha**: 12 de Octubre 2025  
**Issue**: Error al cargar recursos en reservations/create  
**Tipo**: Corrección de lógica de negocio  
**Impacto**: Alto - Funcionalidad crítica restaurada
