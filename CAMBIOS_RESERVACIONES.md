# 📋 Cambios en el Sistema de Reservaciones

## Resumen de Cambios

Este documento describe las mejoras implementadas en el sistema de reservaciones para amenidades y configuraciones del hotel.

---

## 🎯 1. Nuevas Funcionalidades de Amenidades

### Campos Agregados a la Tabla `amenities`

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| `allow_overlap` | TINYINT(1) | 1 | Permitir empalmar con mismo horario y fecha |
| `max_reservations` | INT | NULL | Capacidad máxima de reservaciones simultáneas |
| `block_duration_hours` | DECIMAL(4,2) | 2.00 | Horas de bloqueo por reservación |

### Comportamiento

#### ✅ Cuando `allow_overlap = 1` (Activado - Default)
- Múltiples huéspedes pueden reservar la amenidad al mismo tiempo
- No hay límite de reservaciones simultáneas
- Ideal para amenidades de uso común (piscina, gimnasio, área común)

#### ❌ Cuando `allow_overlap = 0` (Desactivado)
- Se aplican restricciones de capacidad y tiempo
- **Capacidad Máxima**: Define cuántas reservaciones simultáneas se permiten en el mismo horario
- **Tiempo de Bloqueo**: Define por cuántas horas se bloqueará la amenidad después de cada reservación
- Ejemplo: Sala de juntas con capacidad máxima de 1 reservación, bloqueo de 3 horas

### Interfaz de Usuario

#### Formulario de Creación/Edición de Amenidades
1. **Casilla activada por defecto**: "Permitir empalmar con mismo horario y fecha"
2. **Cuando se desactiva**, aparecen dos campos:
   - **Capacidad Máxima de Reservaciones**: Campo numérico (min: 1, default: 1)
   - **Horas de Bloqueo**: Campo decimal (min: 0.5, step: 0.5, default: 2.00)

---

## ⚙️ 2. Configuraciones del Hotel Actualizadas

### Cambios en Configuraciones

#### Antes:
- ❌ `allow_reservation_overlap` - Una configuración global para todos los recursos

#### Ahora:
- ✅ `allow_table_overlap` - Específica para mesas (activada por defecto)
- ✅ `allow_room_overlap` - Específica para habitaciones (desactivada por defecto)

### Nuevas Reglas de Bloqueo

#### 🍽️ Mesas
- **Configuración**: `allow_table_overlap` (activada por defecto)
- **Cuando está desactivada**: Se bloquean por **2 horas** desde la hora de reservación
- **Ejemplo**: Reservación a las 19:00 → Bloqueada de 19:00 a 21:00

#### 🏨 Habitaciones
- **Configuración**: `allow_room_overlap` (desactivada por defecto)
- **Cuando está desactivada**: Se bloquean por **21 horas** de 15:00 a 12:00 del día siguiente
- **Ejemplo**: 
  - Check-in: 2024-01-15 14:00
  - Check-out: 2024-01-16 12:00
  - Bloqueada hasta: 2024-01-17 09:00 (21 horas después del check-out a las 12:00)

#### 🎭 Amenidades
- **Configuración**: Individual por amenidad (campo `allow_overlap`)
- **Cuando está desactivada**: 
  - Se aplica la capacidad máxima definida
  - Se bloquea por el tiempo configurado (default: 2 horas)
- **Ventajas**: 
  - Flexibilidad total por amenidad
  - Diferentes amenidades pueden tener diferentes configuraciones

---

## 🔧 3. Implementación Técnica

### Archivos Modificados

#### Base de Datos
- **`database/update_amenities_and_settings.sql`** - Script de migración

#### Modelos
- **`app/models/Amenity.php`** - Soporte para nuevos campos

#### Controladores
- **`app/controllers/AmenitiesController.php`** - Manejo de nuevos campos en crear/editar
- **`app/controllers/SettingsController.php`** - Nuevas configuraciones de mesas y habitaciones
- **`app/controllers/ChatbotController.php`** - Lógica de validación actualizada

#### Vistas
- **`app/views/amenities/create.php`** - Formulario con nuevos campos y JavaScript
- **`app/views/amenities/edit.php`** - Formulario con nuevos campos y JavaScript
- **`app/views/settings/index.php`** - Configuraciones separadas para mesas y habitaciones

### Lógica de Validación en ChatbotController

#### Para Habitaciones:
```php
// Bloqueo de 21 horas: desde check-out hasta 21 horas después
DATE_ADD(DATE_ADD(check_out, INTERVAL -12 HOUR), INTERVAL 1 DAY)
```

#### Para Mesas:
```php
// Bloqueo de 2 horas desde reservation_time
ADDTIME(reservation_time, '02:00:00')
```

#### Para Amenidades:
```php
// Bloqueo configurable basado en block_duration_hours
// Validación contra max_reservations para permitir capacidad múltiple
if ($result['conflicts'] >= $maxReservations) {
    // Rechazar reservación
}
```

---

## 📊 4. Migración de Datos

### Ejecutar el Script SQL

```bash
mysql -u usuario -p nombre_base_datos < database/update_amenities_and_settings.sql
```

### Qué hace el script:
1. Agrega los 3 nuevos campos a la tabla `amenities`
2. Renombra `allow_reservation_overlap` a `allow_table_overlap`
3. Inserta configuración `allow_table_overlap` con valor `1` para todos los hoteles
4. Inserta configuración `allow_room_overlap` con valor `0` para todos los hoteles
5. Verifica los cambios

---

## ✅ 5. Pruebas Recomendadas

### Prueba 1: Amenidades sin Empalme
1. Crear una amenidad (ej: Sala de Conferencias)
2. Desactivar "Permitir empalmar"
3. Establecer capacidad máxima: 1
4. Establecer horas de bloqueo: 3
5. Intentar hacer 2 reservaciones en el mismo horario
6. **Resultado esperado**: Segunda reservación rechazada

### Prueba 2: Amenidades con Capacidad Múltiple
1. Crear amenidad (ej: Cancha de Tenis)
2. Desactivar "Permitir empalmar"
3. Establecer capacidad máxima: 2
4. Establecer horas de bloqueo: 1.5
5. Hacer 2 reservaciones en el mismo horario
6. Intentar una tercera reservación
7. **Resultado esperado**: Primera y segunda OK, tercera rechazada

### Prueba 3: Configuración de Habitaciones
1. En Configuraciones, desactivar "Permitir empalmar habitaciones"
2. Crear reservación de habitación: Check-in hoy, Check-out mañana 12:00
3. Intentar reservar la misma habitación: Check-in mañana 14:00
4. **Resultado esperado**: Rechazada (no han pasado 21 horas desde las 12:00)
5. Intentar reservar: Check-in pasado mañana 10:00
6. **Resultado esperado**: Aceptada (ya pasaron las 21 horas)

### Prueba 4: Configuración de Mesas
1. En Configuraciones, desactivar "Permitir empalmar mesas"
2. Crear reservación de mesa: Hoy 19:00
3. Intentar reservar la misma mesa: Hoy 20:00
4. **Resultado esperado**: Rechazada (dentro del bloqueo de 2 horas)
5. Intentar reservar: Hoy 21:30
6. **Resultado esperado**: Aceptada (fuera del bloqueo de 2 horas)

---

## 📝 6. Notas Importantes

### Compatibilidad con Datos Existentes
- Las amenidades existentes tendrán `allow_overlap = 1` por defecto (migración automática)
- Las configuraciones de hotel se migran automáticamente
- No se requiere acción manual para datos existentes

### Valores por Defecto
- **Amenidades**: `allow_overlap = 1` (permite empalme)
- **Mesas**: `allow_table_overlap = 1` (permite empalme)
- **Habitaciones**: `allow_room_overlap = 0` (NO permite empalme)

### Recomendaciones
- **Piscinas, Gimnasios**: Dejar `allow_overlap = 1`
- **Salas de reuniones, Spas**: Configurar `allow_overlap = 0` con capacidad y tiempo apropiados
- **Habitaciones**: Mantener `allow_room_overlap = 0` en la mayoría de casos
- **Mesas**: Ajustar según el tipo de restaurante

---

## 🎨 7. Cambios en la Interfaz

### Formulario de Amenidades
- Nueva sección "Configuración de Reservaciones"
- Switch interactivo que muestra/oculta campos según estado
- Validación con JavaScript para campos requeridos
- Tooltips informativos

### Configuraciones del Hotel
- Dos switches separados para mesas y habitaciones
- Información actualizada sobre el comportamiento de cada configuración
- Descripción específica del comportamiento de amenidades

---

## 🚀 Próximos Pasos Sugeridos

1. ✅ Aplicar el script SQL de migración
2. ✅ Probar creación/edición de amenidades
3. ✅ Configurar amenidades existentes según necesidad
4. ✅ Verificar configuraciones de hotel
5. ✅ Realizar pruebas de reservaciones
6. 📸 Tomar capturas de pantalla para documentación
7. 📢 Notificar a usuarios sobre nuevas funcionalidades

---

## 📞 Soporte

Si encuentras algún problema con estos cambios, por favor reporta:
- Pasos para reproducir el problema
- Configuración de la amenidad/hotel involucrada
- Capturas de pantalla si es posible
