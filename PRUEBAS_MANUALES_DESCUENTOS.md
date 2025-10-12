# 🧪 Pruebas Manuales - Sistema de Códigos de Descuento

## 📋 Lista de Verificación Pre-Pruebas

Antes de comenzar las pruebas, verificar que:

- [ ] La migración `database/add_discount_codes.sql` se aplicó correctamente
- [ ] Las tablas `discount_codes` y `discount_code_usages` existen
- [ ] Los campos de descuento se agregaron a `room_reservations`
- [ ] El archivo API `public/api/validate_discount_code.php` existe
- [ ] El usuario de prueba tiene permisos de admin/manager/hostess
- [ ] Hay al menos una habitación creada en el sistema

### Verificación Rápida en Base de Datos
```sql
-- Ver tablas
SHOW TABLES LIKE '%discount%';

-- Ver códigos de ejemplo
SELECT code, discount_type, amount, active, valid_from, valid_to 
FROM discount_codes;

-- Ver estructura de room_reservations
DESCRIBE room_reservations;
```

---

## 🎯 Pruebas de Carga de Recursos

### ✅ Prueba 1.1: Habitaciones Disponibles
**Objetivo:** Verificar que se muestran correctamente las habitaciones disponibles

**Pasos:**
1. Ir a `/reservations/create`
2. En "Tipo de Reservación" seleccionar "🚪 Habitación"
3. Esperar a que se cargue el dropdown "Recurso"

**Resultado Esperado:**
- ✓ Se muestran las habitaciones disponibles en formato: "Habitación [número] - [tipo] ($[precio])"
- ✓ Si no hay habitaciones, se muestra: "No hay habitaciones disponibles"
- ✓ NO se muestra "Error al cargar recursos"

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 1.2: Mesas Disponibles
**Objetivo:** Verificar que se muestran correctamente las mesas disponibles

**Pasos:**
1. En "Tipo de Reservación" seleccionar "🍽️ Mesa"
2. Esperar a que se cargue el dropdown "Recurso"

**Resultado Esperado:**
- ✓ Se muestran las mesas en formato: "Mesa [número] - Capacidad: [capacidad]"
- ✓ Si no hay mesas, se muestra: "No hay mesas disponibles"

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 1.3: Amenidades Disponibles
**Objetivo:** Verificar que se muestran correctamente las amenidades disponibles

**Pasos:**
1. En "Tipo de Reservación" seleccionar "🏊 Amenidad"
2. Esperar a que se cargue el dropdown "Recurso"

**Resultado Esperado:**
- ✓ Se muestran las amenidades en formato: "[nombre] - [categoría]"
- ✓ Si no hay amenidades, se muestra: "No hay amenidades disponibles"

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 1.4: Error de Conexión (Simulado)
**Objetivo:** Verificar mensaje de error cuando hay problema de conexión

**Pasos:**
1. Detener temporalmente el servidor MySQL
2. Intentar cargar recursos
3. Reiniciar MySQL

**Resultado Esperado:**
- ✓ Se muestra mensaje específico de error
- ✓ NO se confunde con "no hay recursos disponibles"

**Captura de Pantalla:** ⬜

---

## 🎟️ Pruebas de Códigos de Descuento

### ✅ Prueba 2.1: Aplicar Código Porcentual Válido
**Objetivo:** Verificar que un código de descuento porcentual se aplica correctamente

**Datos de Prueba:**
- Código: WELCOME10
- Tipo: Porcentaje (10%)
- Habitación: Precio $1000

**Pasos:**
1. Ir a `/reservations/create`
2. Seleccionar tipo "Habitación"
3. Seleccionar una habitación de $1000
4. En "Código de Descuento" ingresar: `WELCOME10`
5. Hacer clic en "Aplicar"

**Resultado Esperado:**
- ✓ Mensaje: "✓ Código válido: 10% de descuento" (en verde)
- ✓ Resumen de precio visible:
  - Precio original: $1000.00
  - Descuento: -$100.00
  - Total a pagar: $900.00
- ✓ Campo de código deshabilitado
- ✓ Botón "Aplicar" cambia a "Aplicado" y se deshabilita

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 2.2: Aplicar Código Fijo Válido
**Objetivo:** Verificar que un código de descuento fijo se aplica correctamente

**Datos de Prueba:**
- Código: PROMO50
- Tipo: Fijo ($50)
- Habitación: Precio $500

**Pasos:**
1. Seleccionar habitación de $500
2. Ingresar código: `PROMO50`
3. Hacer clic en "Aplicar"

**Resultado Esperado:**
- ✓ Mensaje: "✓ Código válido: $50 de descuento" (en verde)
- ✓ Resumen de precio:
  - Precio original: $500.00
  - Descuento: -$50.00
  - Total a pagar: $450.00

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 2.3: Código Inválido
**Objetivo:** Verificar mensaje de error para código inexistente

**Pasos:**
1. Ingresar código: `CODIGOINVALIDO`
2. Hacer clic en "Aplicar"

**Resultado Esperado:**
- ✓ Mensaje: "Código de descuento inválido o expirado" (en rojo)
- ✓ NO se muestra resumen de precio
- ✓ Campo de código permanece habilitado
- ✓ Botón "Aplicar" vuelve a estado normal

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 2.4: Código Sin Ingresar
**Objetivo:** Verificar validación cuando campo está vacío

**Pasos:**
1. Dejar campo de código vacío
2. Hacer clic en "Aplicar"

**Resultado Esperado:**
- ✓ Mensaje: "Por favor ingrese un código de descuento" (en rojo)

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 2.5: Aplicar Sin Seleccionar Habitación
**Objetivo:** Verificar validación cuando no hay habitación seleccionada

**Pasos:**
1. NO seleccionar ninguna habitación
2. Ingresar código: `WELCOME10`
3. Hacer clic en "Aplicar"

**Resultado Esperado:**
- ✓ Mensaje: "Por favor seleccione una habitación primero" (en amarillo)

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 2.6: Cambiar Habitación con Código Aplicado
**Objetivo:** Verificar que el código se resetea al cambiar de habitación

**Pasos:**
1. Seleccionar habitación A
2. Aplicar código válido
3. Cambiar a habitación B

**Resultado Esperado:**
- ✓ Campo de código se limpia
- ✓ Campo de código se habilita
- ✓ Botón vuelve a "Aplicar"
- ✓ Resumen de precio se oculta
- ✓ Campos ocultos se resetean

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 2.7: Código con Límite de Uso Alcanzado
**Objetivo:** Verificar mensaje cuando código alcanzó su límite

**Preparación:**
```sql
-- Crear código con límite alcanzado
INSERT INTO discount_codes 
(code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, times_used)
VALUES 
('LIMITADO', 'percentage', 10.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1, 1);
```

**Pasos:**
1. Intentar aplicar código: `LIMITADO`

**Resultado Esperado:**
- ✓ Mensaje: "Este código de descuento ha alcanzado su límite de uso" (en rojo)

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 2.8: Código Expirado
**Objetivo:** Verificar mensaje para código fuera de vigencia

**Preparación:**
```sql
-- Crear código expirado
INSERT INTO discount_codes 
(code, discount_type, amount, hotel_id, active, valid_from, valid_to)
VALUES 
('EXPIRADO', 'percentage', 10.00, 1, 1, '2024-01-01', '2024-01-31');
```

**Pasos:**
1. Intentar aplicar código: `EXPIRADO`

**Resultado Esperado:**
- ✓ Mensaje: "Código de descuento inválido o expirado" (en rojo)

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 2.9: Código Desactivado
**Objetivo:** Verificar que códigos inactivos no funcionan

**Preparación:**
```sql
UPDATE discount_codes SET active = 0 WHERE code = 'WELCOME10';
```

**Pasos:**
1. Intentar aplicar código: `WELCOME10`

**Resultado Esperado:**
- ✓ Mensaje: "Código de descuento inválido o expirado" (en rojo)

**Limpieza:**
```sql
UPDATE discount_codes SET active = 1 WHERE code = 'WELCOME10';
```

**Captura de Pantalla:** ⬜

---

## 💾 Pruebas de Guardado de Reservación

### ✅ Prueba 3.1: Guardar Reservación CON Descuento
**Objetivo:** Verificar que la reservación se guarda correctamente con descuento aplicado

**Pasos:**
1. Crear reservación completa con código WELCOME10 aplicado
2. Llenar todos los campos requeridos:
   - Huésped existente o nuevo
   - Check-in y Check-out
   - Estado
3. Hacer clic en "Crear Reservación"
4. Ir a listado de reservaciones

**Verificación en BD:**
```sql
-- Ver última reservación
SELECT 
    id, guest_name, total_price, discount_code_id, 
    discount_amount, original_price, status
FROM room_reservations 
ORDER BY id DESC LIMIT 1;

-- Ver uso registrado
SELECT * FROM discount_code_usages ORDER BY id DESC LIMIT 1;

-- Ver contador actualizado
SELECT code, times_used FROM discount_codes WHERE code = 'WELCOME10';
```

**Resultado Esperado:**
- ✓ Reservación creada exitosamente
- ✓ `room_reservations.discount_code_id` tiene el ID correcto
- ✓ `room_reservations.discount_amount` tiene el monto correcto
- ✓ `room_reservations.original_price` tiene el precio original
- ✓ `room_reservations.total_price` = original_price - discount_amount
- ✓ Registro en `discount_code_usages` creado
- ✓ `discount_codes.times_used` incrementado en 1

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 3.2: Guardar Reservación SIN Descuento
**Objetivo:** Verificar que el sistema sigue funcionando sin código

**Pasos:**
1. Crear reservación SIN aplicar código de descuento
2. Completar todos los campos
3. Guardar

**Verificación en BD:**
```sql
SELECT 
    id, guest_name, total_price, discount_code_id, 
    discount_amount, original_price
FROM room_reservations 
ORDER BY id DESC LIMIT 1;
```

**Resultado Esperado:**
- ✓ Reservación creada exitosamente
- ✓ `discount_code_id` es NULL
- ✓ `discount_amount` es 0.00
- ✓ `original_price` es NULL
- ✓ `total_price` = precio de habitación
- ✓ NO hay registro en `discount_code_usages`

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 3.3: Descuento Mayor al Precio
**Objetivo:** Verificar manejo cuando descuento excede el precio

**Preparación:**
```sql
-- Código con $1000 de descuento
INSERT INTO discount_codes 
(code, discount_type, amount, hotel_id, active, valid_from, valid_to)
VALUES 
('MEGA1000', 'fixed', 1000.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY));
```

**Pasos:**
1. Seleccionar habitación de $500
2. Aplicar código: `MEGA1000`

**Resultado Esperado:**
- ✓ Código se aplica
- ✓ Descuento mostrado es $500 (no $1000)
- ✓ Total a pagar: $0.00
- ✓ Al guardar, total_price = 0.00 (no negativo)

**Captura de Pantalla:** ⬜

---

## 🔒 Pruebas de Seguridad

### ✅ Prueba 4.1: Código de Otro Hotel
**Objetivo:** Verificar que no se pueden usar códigos de otros hoteles

**Preparación:**
```sql
-- Crear código para hotel_id = 999
INSERT INTO discount_codes 
(code, discount_type, amount, hotel_id, active, valid_from, valid_to)
VALUES 
('OTROHOTEL', 'percentage', 10.00, 999, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY));
```

**Pasos:**
1. Login con usuario de hotel_id = 1
2. Intentar aplicar código: `OTROHOTEL`

**Resultado Esperado:**
- ✓ Mensaje: "Código de descuento inválido o expirado"
- ✓ NO se aplica el descuento

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 4.2: Manipulación de Campos Ocultos
**Objetivo:** Verificar que el backend valida correctamente

**Pasos:**
1. Aplicar código válido
2. Abrir consola del navegador (F12)
3. Modificar valores en campos ocultos:
   ```javascript
   document.getElementById('discount_amount').value = '999999';
   ```
4. Enviar formulario

**Resultado Esperado:**
- ✓ Backend valida y aplica el descuento correcto basado en el código
- ✓ NO se aplica el valor manipulado

**Captura de Pantalla:** ⬜

---

## 📊 Pruebas de Reportes

### ✅ Prueba 5.1: Consulta de Uso de Códigos
```sql
SELECT 
    dc.code,
    COUNT(dcu.id) as usos,
    SUM(dcu.discount_amount) as descuento_total
FROM discount_codes dc
LEFT JOIN discount_code_usages dcu ON dc.id = dcu.discount_code_id
WHERE dc.hotel_id = 1
GROUP BY dc.id;
```

**Resultado Esperado:**
- ✓ Muestra todos los códigos del hotel
- ✓ Contador de usos coincide con `times_used`
- ✓ Suma de descuentos es correcta

---

### ✅ Prueba 5.2: Auditoría de Reservaciones
```sql
SELECT 
    rr.id,
    rr.guest_name,
    rr.original_price,
    rr.discount_amount,
    rr.total_price,
    dc.code
FROM room_reservations rr
LEFT JOIN discount_codes dc ON rr.discount_code_id = dc.id
WHERE rr.hotel_id = 1
ORDER BY rr.created_at DESC
LIMIT 10;
```

**Resultado Esperado:**
- ✓ Todas las reservaciones listadas correctamente
- ✓ Relación con códigos de descuento correcta
- ✓ Cálculos de precios correctos

---

## 🎭 Pruebas de Interfaz de Usuario

### ✅ Prueba 6.1: Responsive Design
**Objetivo:** Verificar que funciona en diferentes tamaños de pantalla

**Pasos:**
1. Abrir formulario en desktop (1920x1080)
2. Reducir ventana a tablet (768x1024)
3. Reducir ventana a móvil (375x667)

**Resultado Esperado:**
- ✓ Campos se reorganizan correctamente
- ✓ Botón "Aplicar" visible y funcional
- ✓ Resumen de precio legible

**Captura de Pantalla:** ⬜

---

### ✅ Prueba 6.2: Navegadores
**Objetivo:** Verificar compatibilidad cross-browser

**Navegadores a Probar:**
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari (si disponible)

**Resultado Esperado:**
- ✓ Funciona igual en todos los navegadores
- ✓ Estilos se muestran correctamente

---

## 📝 Plantilla de Reporte de Pruebas

```
===========================================
REPORTE DE PRUEBAS - CÓDIGOS DE DESCUENTO
===========================================

Fecha: _______________
Probador: _______________
Versión: 1.0.0

RESUMEN
-------
Total de Pruebas: ____ / 30
Pasadas: ____
Fallidas: ____

CARGA DE RECURSOS
-----------------
✓/✗ Prueba 1.1: Habitaciones Disponibles
✓/✗ Prueba 1.2: Mesas Disponibles
✓/✗ Prueba 1.3: Amenidades Disponibles
✓/✗ Prueba 1.4: Error de Conexión

CÓDIGOS DE DESCUENTO
-------------------
✓/✗ Prueba 2.1: Código Porcentual Válido
✓/✗ Prueba 2.2: Código Fijo Válido
✓/✗ Prueba 2.3: Código Inválido
✓/✗ Prueba 2.4: Código Sin Ingresar
✓/✗ Prueba 2.5: Sin Habitación Seleccionada
✓/✗ Prueba 2.6: Cambiar Habitación
✓/✗ Prueba 2.7: Límite de Uso Alcanzado
✓/✗ Prueba 2.8: Código Expirado
✓/✗ Prueba 2.9: Código Desactivado

GUARDADO DE RESERVACIÓN
-----------------------
✓/✗ Prueba 3.1: Con Descuento
✓/✗ Prueba 3.2: Sin Descuento
✓/✗ Prueba 3.3: Descuento Mayor al Precio

SEGURIDAD
---------
✓/✗ Prueba 4.1: Código de Otro Hotel
✓/✗ Prueba 4.2: Manipulación de Campos

REPORTES
--------
✓/✗ Prueba 5.1: Consulta de Uso
✓/✗ Prueba 5.2: Auditoría de Reservaciones

INTERFAZ
--------
✓/✗ Prueba 6.1: Responsive Design
✓/✗ Prueba 6.2: Navegadores

PROBLEMAS ENCONTRADOS
--------------------
1. _______________________________
2. _______________________________
3. _______________________________

NOTAS ADICIONALES
-----------------
_________________________________
_________________________________
_________________________________

CONCLUSIÓN: ✓ APROBADO / ✗ RECHAZADO
```

---

**Fecha de Creación:** 12 de Octubre de 2025  
**Última Actualización:** 12 de Octubre de 2025
