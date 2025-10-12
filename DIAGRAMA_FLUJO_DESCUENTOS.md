# 🎯 Diagrama de Flujo - Sistema de Códigos de Descuento

## 📊 Arquitectura General

```
┌─────────────────────────────────────────────────────────────────────┐
│                       SISTEMA DE DESCUENTOS                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌─────────────┐      ┌──────────────┐      ┌─────────────────┐   │
│  │  FRONTEND   │ ───▶ │     API      │ ───▶ │   BASE DATOS    │   │
│  │   (create)  │ ◀─── │ (validate)   │ ◀─── │  (discount_*)   │   │
│  └─────────────┘      └──────────────┘      └─────────────────┘   │
│        │                                              │              │
│        │                                              │              │
│        ▼                                              ▼              │
│  ┌─────────────┐                            ┌─────────────────┐   │
│  │ CONTROLADOR │ ────────────────────────▶  │ room_reserv...  │   │
│  │   (store)   │                            │ discount_use... │   │
│  └─────────────┘                            └─────────────────┘   │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujo Completo: Aplicar Código de Descuento

### Fase 1: Selección de Habitación

```
Usuario
  │
  ├─▶ Selecciona tipo "Habitación"
  │
  ├─▶ API get_resources.php
  │     │
  │     ├─▶ Query: SELECT * FROM rooms WHERE hotel_id = ?
  │     │
  │     └─▶ Response:
  │           {
  │             success: true,
  │             resources: [{id, room_number, price, ...}]
  │           }
  │
  └─▶ Muestra dropdown con habitaciones
```

### Fase 2: Aplicar Código de Descuento

```
Usuario ingresa código: "WELCOME10"
  │
  ├─▶ Click en "Aplicar"
  │
  ├─▶ JavaScript valida:
  │     ├─▶ ¿Código ingresado? ✓
  │     └─▶ ¿Habitación seleccionada? ✓
  │
  ├─▶ POST /api/validate_discount_code.php
  │     │
  │     │  Parámetros:
  │     │  - code: "WELCOME10"
  │     │  - room_price: 1000.00
  │     │
  │     ├─▶ Validaciones en Backend:
  │     │     ├─▶ ¿Código existe? ✓
  │     │     ├─▶ ¿Está activo? ✓
  │     │     ├─▶ ¿Es del hotel correcto? ✓
  │     │     ├─▶ ¿Está vigente? ✓
  │     │     ├─▶ ¿No alcanzó límite? ✓
  │     │     └─▶ Calcular descuento
  │     │
  │     └─▶ Response:
  │           {
  │             success: true,
  │             discount: {
  │               id: 1,
  │               discount_amount: 100.00,
  │               final_price: 900.00
  │             }
  │           }
  │
  └─▶ Actualiza UI:
        ├─▶ Mensaje: "✓ Código válido: 10% de descuento"
        ├─▶ Muestra resumen de precio
        ├─▶ Guarda datos en campos ocultos
        └─▶ Deshabilita campo y botón
```

### Fase 3: Guardar Reservación

```
Usuario completa formulario y hace clic en "Crear Reservación"
  │
  ├─▶ POST /reservations/store
  │
  ├─▶ ReservationsController::store()
  │     │
  │     ├─▶ BEGIN TRANSACTION
  │     │
  │     ├─▶ Crear/Obtener Guest ID
  │     │
  │     ├─▶ Obtener precio de habitación
  │     │     SELECT price FROM rooms WHERE id = ?
  │     │
  │     ├─▶ Verificar datos de descuento:
  │     │     ├─▶ discount_code_id: 1
  │     │     ├─▶ discount_amount: 100.00
  │     │     └─▶ original_price: 1000.00
  │     │
  │     ├─▶ Calcular precio final:
  │     │     final_price = 1000.00 - 100.00 = 900.00
  │     │
  │     ├─▶ INSERT INTO room_reservations:
  │     │     (room_id, guest_id, total_price,
  │     │      discount_code_id, discount_amount, original_price)
  │     │     VALUES (?, ?, 900.00, 1, 100.00, 1000.00)
  │     │
  │     ├─▶ Obtener reservation_id (lastInsertId)
  │     │
  │     ├─▶ INSERT INTO discount_code_usages:
  │     │     (discount_code_id, reservation_id, reservation_type,
  │     │      discount_amount, original_price, final_price)
  │     │     VALUES (1, ?, 'room', 100.00, 1000.00, 900.00)
  │     │
  │     ├─▶ UPDATE discount_codes:
  │     │     SET times_used = times_used + 1
  │     │     WHERE id = 1
  │     │
  │     └─▶ COMMIT
  │
  └─▶ Redirect a /reservations
        └─▶ Flash: "Reservación creada exitosamente"
```

---

## 🗄️ Modelo de Base de Datos

```
┌─────────────────────┐
│  discount_codes     │
├─────────────────────┤
│ id (PK)             │
│ code (UNIQUE)       │◀────────┐
│ discount_type       │         │
│ amount              │         │
│ hotel_id (FK)       │         │
│ active              │         │
│ valid_from          │         │
│ valid_to            │         │
│ usage_limit         │         │
│ times_used          │         │
│ description         │         │
└─────────────────────┘         │
                                │ FK
┌─────────────────────┐         │
│ room_reservations   │         │
├─────────────────────┤         │
│ id (PK)             │         │
│ room_id (FK)        │         │
│ guest_id (FK)       │         │
│ check_in            │         │
│ check_out           │         │
│ total_price         │         │
│ discount_code_id ───┼─────────┘
│ discount_amount     │
│ original_price      │
│ status              │
│ notes               │
└─────────────────────┘
         │
         │ reservation_id (FK)
         │
         ▼
┌─────────────────────────┐
│ discount_code_usages    │
├─────────────────────────┤
│ id (PK)                 │
│ discount_code_id (FK) ──┼──────┐
│ reservation_id          │      │
│ reservation_type        │      │
│ discount_amount         │      │
│ original_price          │      │
│ final_price             │      │
│ used_at                 │      │
└─────────────────────────┘      │
                                 │
                                 │ FK
                                 │
                         ┌───────┘
                         ▼
            ┌─────────────────────┐
            │  discount_codes     │
            │  (tabla de auditoría)│
            └─────────────────────┘
```

---

## 🎨 Interfaz de Usuario

```
┌────────────────────────────────────────────────────────────┐
│  Nueva Reservación                             [Volver]    │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  Tipo de Reservación *                                      │
│  [🚪 Habitación ▼]                                         │
│                                                             │
│  Recurso *                                                  │
│  [Habitación 101 - Double ($1,000) ▼]                     │
│                                                             │
│  Check-in *              Check-out *                        │
│  [2025-10-15]           [2025-10-17]                       │
│                                                             │
│  Código de Descuento (Opcional)                            │
│  ┌──────────────────────────────┬──────────┐              │
│  │ WELCOME10                     │ [Aplicar]│              │
│  └──────────────────────────────┴──────────┘              │
│  ✓ Código válido: 10% de descuento                        │
│                                                             │
│  ┌────────────────────────────────────────┐               │
│  │  Resumen de Precio                      │               │
│  │                                         │               │
│  │  Precio original:         $1,000.00    │               │
│  │  Descuento:                -$100.00    │               │
│  │  ────────────────────────────────────  │               │
│  │  Total a pagar:            $900.00     │               │
│  └────────────────────────────────────────┘               │
│                                                             │
│  [Cancelar]                    [Crear Reservación]         │
│                                                             │
└────────────────────────────────────────────────────────────┘
```

---

## 🔄 Estados de Código de Descuento

```
┌──────────────┐
│ CÓDIGO       │
│ INGRESADO    │
└──────┬───────┘
       │
       ▼
┌──────────────┐      ┌────────────────┐
│ VALIDANDO... │ ───▶ │ ¿CÓDIGO VÁLIDO?│
└──────────────┘      └────┬───────┬───┘
                           │       │
                    SÍ     │       │  NO
                           │       │
                    ┌──────▼───┐   │
                    │ APLICADO │   │
                    │          │   │
                    │ ✓ Activo │   │
                    │ 🔒 Locked│   │
                    └──────┬───┘   │
                           │       │
                           │       ▼
                           │   ┌───────────┐
                           │   │ INVÁLIDO  │
                           │   │           │
                           │   │ ✗ Error   │
                           │   │ 🔓 Desbloq│
                           │   └───────────┘
                           │
                  Cambia   │
                habitación │
                           │
                           ▼
                    ┌──────────────┐
                    │ RESETEADO    │
                    │              │
                    │ Campo limpio │
                    │ 🔓 Desbloq   │
                    └──────────────┘
```

---

## 🛡️ Validaciones de Seguridad

```
┌─────────────────────────────────────────────────────────────┐
│                    CAPAS DE VALIDACIÓN                       │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  FRONTEND (JavaScript)                                        │
│  ├─▶ ¿Código ingresado?                                     │
│  └─▶ ¿Habitación seleccionada?                              │
│                                                               │
│  API (validate_discount_code.php)                            │
│  ├─▶ ¿Usuario autenticado?                                  │
│  ├─▶ ¿Código existe en DB?                                  │
│  ├─▶ ¿Código está activo?                                   │
│  ├─▶ ¿Código es del hotel correcto?                         │
│  ├─▶ ¿Código está vigente?                                  │
│  ├─▶ ¿Código no alcanzó límite?                             │
│  └─▶ ¿Descuento no excede precio?                           │
│                                                               │
│  BACKEND (ReservationsController)                            │
│  ├─▶ ¿Usuario tiene permisos?                               │
│  ├─▶ ¿Datos de descuento válidos?                           │
│  ├─▶ Transacción (rollback en error)                        │
│  ├─▶ Prepared statements (SQL injection)                    │
│  └─▶ Sanitización de inputs                                  │
│                                                               │
│  BASE DE DATOS                                               │
│  ├─▶ Foreign keys (integridad referencial)                  │
│  ├─▶ Índices (performance)                                   │
│  └─▶ Constraints (validaciones)                              │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Flujo de Datos: Guardado

```
┌───────────────┐
│  FORMULARIO   │
│               │
│  • guest_id   │
│  • room_id    │
│  • check_in   │
│  • check_out  │
│  • disc_id    │──┐
│  • disc_amt   │  │
│  • orig_price │  │
└───────┬───────┘  │
        │          │
        ▼          │
┌──────────────────┤───────────────────┐
│  CONTROLLER      │                   │
│                  │                   │
│  1. Validar      │                   │
│  2. Calcular     │                   │
│  3. BEGIN TRANS  │                   │
│  4. INSERT res   │◀──────────────────┘
│  5. INSERT usage │
│  6. UPDATE code  │
│  7. COMMIT       │
└────────┬─────────┘
         │
         ▼
┌─────────────────────────────────────┐
│         BASE DE DATOS               │
│                                     │
│  ┌───────────────────────┐         │
│  │ room_reservations     │         │
│  │ • id = 123            │         │
│  │ • total_price = 900   │         │
│  │ • discount_id = 1     │──┐     │
│  │ • discount_amt = 100  │  │     │
│  │ • original_pr = 1000  │  │     │
│  └───────────────────────┘  │     │
│                              │     │
│  ┌───────────────────────┐  │     │
│  │ discount_code_usages  │  │     │
│  │ • id = 45             │  │     │
│  │ • discount_id = 1     │◀─┘     │
│  │ • reservation_id=123  │        │
│  │ • disc_amount = 100   │        │
│  │ • final_price = 900   │        │
│  └───────────────────────┘        │
│                                    │
│  ┌───────────────────────┐        │
│  │ discount_codes        │        │
│  │ • id = 1              │        │
│  │ • code = WELCOME10    │        │
│  │ • times_used += 1     │◀──┐   │
│  └───────────────────────┘   │   │
│                               │   │
└───────────────────────────────┼───┘
                                │
                                │
                        Auditoría completa
```

---

## 🎯 Casos de Uso Principales

### Caso 1: Código Válido

```
Usuario → Selecciona habitación ($1000)
       → Ingresa "WELCOME10" (10%)
       → Click "Aplicar"
       → API valida ✓
       → UI muestra: "Ahorro: $100"
       → Usuario completa formulario
       → Sistema guarda con descuento
       → Total guardado: $900
```

### Caso 2: Código Inválido

```
Usuario → Selecciona habitación
       → Ingresa "CODIGOINVALIDO"
       → Click "Aplicar"
       → API valida ✗
       → UI muestra: "Código inválido"
       → Usuario puede reintentar
```

### Caso 3: Sin Código

```
Usuario → Selecciona habitación ($1000)
       → NO ingresa código
       → Completa formulario
       → Sistema guarda sin descuento
       → Total guardado: $1000
```

### Caso 4: Cambio de Habitación

```
Usuario → Selecciona habitación A
       → Aplica código ✓
       → Cambia a habitación B
       → Código se resetea automáticamente
       → Debe aplicar nuevamente
```

---

## 🔍 Puntos Clave de Implementación

### ✅ Corrección de Recursos
- Diferencia entre array vacío y error
- Mensajes específicos por tipo
- Mejor experiencia de usuario

### ✅ Código de Descuento
- Validación en múltiples capas
- Cálculo automático (% o fijo)
- Auditoría completa
- Transacciones atómicas
- UI intuitiva y responsive

### ✅ Seguridad
- Validación server-side obligatoria
- Prepared statements
- Foreign keys
- Sanitización de inputs
- No confía en frontend

### ✅ Escalabilidad
- Índices optimizados
- Consultas eficientes
- Fácil extensión a otros recursos
- Preparado para dashboard admin

---

**Versión:** 1.0.0  
**Fecha:** 12 de Octubre de 2025  
**Estado:** ✅ COMPLETADO
