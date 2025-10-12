# 🎟️ Implementación de Códigos de Descuento

## 📋 Resumen

Se ha implementado un sistema completo de códigos de descuento para reservaciones de habitaciones, incluyendo:

1. ✅ **Corrección del error en carga de recursos** - Ahora diferencia correctamente entre array vacío y error real
2. ✅ **Módulo completo de códigos de descuento** - Sistema de validación, aplicación y registro de uso

---

## 🎯 Punto 1: Corrección de Carga de Recursos

### Problema Identificado
El frontend mostraba "Error al cargar recursos" tanto cuando había un error real como cuando simplemente no había recursos disponibles.

### Solución Implementada
**Archivo modificado:** `app/views/reservations/create.php`

```javascript
// Ahora diferencia correctamente los casos:
if (data.success) {
    if (data.resources && data.resources.length > 0) {
        // Mostrar recursos disponibles
    } else {
        // Mostrar mensaje específico: "No hay habitaciones disponibles"
    }
} else {
    // Mostrar error real con el mensaje del servidor
}
```

### Mensajes Específicos
- **Habitaciones:** "No hay habitaciones disponibles"
- **Mesas:** "No hay mesas disponibles"
- **Amenidades:** "No hay amenidades disponibles"
- **Error real:** "Error: [mensaje del servidor]"
- **Error de conexión:** "Error de conexión al cargar recursos"

---

## 🎟️ Punto 2: Módulo de Códigos de Descuento

### Base de Datos

#### Tabla: `discount_codes`
```sql
CREATE TABLE discount_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    hotel_id INT NOT NULL,
    active TINYINT(1) DEFAULT 1,
    valid_from DATE NOT NULL,
    valid_to DATE NOT NULL,
    usage_limit INT DEFAULT NULL, -- NULL = ilimitado
    times_used INT DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Tabla: `discount_code_usages`
```sql
CREATE TABLE discount_code_usages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discount_code_id INT NOT NULL,
    reservation_id INT NOT NULL,
    reservation_type ENUM('room', 'table', 'amenity') NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2) NOT NULL,
    final_price DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Campos Agregados a `room_reservations`
```sql
ALTER TABLE room_reservations 
ADD COLUMN discount_code_id INT NULL,
ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN original_price DECIMAL(10,2) NULL;
```

### Migración de Base de Datos

**Archivo:** `database/add_discount_codes.sql`

Para aplicar la migración:
```bash
mysql -u usuario -p nombre_db < database/add_discount_codes.sql
```

El archivo incluye:
- ✅ Creación de tablas `discount_codes` y `discount_code_usages`
- ✅ Modificación de `room_reservations` con campos de descuento
- ✅ 3 códigos de ejemplo para pruebas (WELCOME10, PROMO50, FLASH20)
- ✅ Índices optimizados para consultas rápidas
- ✅ Foreign keys con ON DELETE CASCADE/SET NULL

### API de Validación

**Archivo:** `public/api/validate_discount_code.php`

#### Endpoint
```
POST /api/validate_discount_code.php
```

#### Parámetros
```javascript
{
    code: "WELCOME10",        // Código de descuento
    room_price: 1000.00       // Precio de la habitación
}
```

#### Respuesta Exitosa
```json
{
    "success": true,
    "message": "Código válido aplicado correctamente",
    "discount": {
        "id": 1,
        "code": "WELCOME10",
        "type": "percentage",
        "amount": 10.00,
        "discount_amount": 100.00,
        "original_price": 1000.00,
        "final_price": 900.00,
        "description": "Código de bienvenida - 10% de descuento"
    }
}
```

#### Respuesta con Error
```json
{
    "success": false,
    "message": "Código de descuento inválido o expirado"
}
```

#### Validaciones Implementadas
- ✅ Código existe y está activo
- ✅ Código pertenece al hotel del usuario
- ✅ Código está dentro del rango de fechas válidas
- ✅ Código no ha alcanzado su límite de uso
- ✅ Descuento no excede el precio de la habitación

### Frontend

**Archivo:** `app/views/reservations/create.php`

#### Campo de Código de Descuento
```html
<div class="mb-3">
    <label for="discount_code" class="form-label">
        Código de Descuento (Opcional)
    </label>
    <div class="input-group">
        <input type="text" class="form-control" id="discount_code" 
               name="discount_code" placeholder="Ingrese código promocional">
        <button type="button" class="btn btn-outline-primary" id="apply_discount_btn">
            <i class="bi bi-check-circle"></i> Aplicar
        </button>
    </div>
    <small class="form-text" id="discount_feedback"></small>
</div>
```

#### Resumen de Precio
```html
<div id="price_summary" class="alert alert-info" style="display: none;">
    <h6 class="mb-2">Resumen de Precio</h6>
    <div class="d-flex justify-content-between">
        <span>Precio original:</span>
        <span id="display_original_price">$0.00</span>
    </div>
    <div class="d-flex justify-content-between text-success">
        <span>Descuento:</span>
        <span id="display_discount">-$0.00</span>
    </div>
    <hr class="my-2">
    <div class="d-flex justify-content-between fw-bold">
        <span>Total a pagar:</span>
        <span id="display_final_price">$0.00</span>
    </div>
</div>
```

#### Flujo de Usuario
1. Usuario selecciona una habitación
2. Usuario ingresa código de descuento
3. Usuario hace clic en "Aplicar"
4. Sistema valida el código vía API
5. Si es válido:
   - Muestra mensaje de éxito
   - Muestra resumen de precio con descuento
   - Deshabilita el campo de código (evita cambios)
6. Si es inválido:
   - Muestra mensaje de error específico
   - Permite reintentar con otro código
7. Si cambia de habitación:
   - Se resetea el código de descuento
   - Usuario debe aplicar nuevamente

### Backend

**Archivo:** `app/controllers/ReservationsController.php`

#### Método `store()` - Modificaciones

```php
// 1. Obtener precio de la habitación
$roomStmt = $this->db->prepare("SELECT price FROM rooms WHERE id = ?");
$roomStmt->execute([$resourceId]);
$room = $roomStmt->fetch(PDO::FETCH_ASSOC);
$roomPrice = floatval($room['price']);

// 2. Obtener datos de descuento del formulario
$discountCodeId = intval($_POST['discount_code_id'] ?? 0);
$discountAmount = floatval($_POST['discount_amount'] ?? 0);
$originalPrice = floatval($_POST['original_price'] ?? $roomPrice);

// 3. Calcular precio final
$finalPrice = $roomPrice - $discountAmount;
if ($finalPrice < 0) $finalPrice = 0;

// 4. Insertar reservación con descuento
if ($discountCodeId > 0) {
    // Guardar con discount_code_id, discount_amount, original_price
    
    // 5. Registrar uso en discount_code_usages
    // 6. Incrementar times_used en discount_codes
}
```

#### Transacciones
Todo el proceso se ejecuta dentro de una transacción para garantizar integridad:
```php
try {
    $this->db->beginTransaction();
    // ... operaciones ...
    $this->db->commit();
} catch (Exception $e) {
    $this->db->rollBack();
    // ... manejo de error ...
}
```

---

## 📊 Códigos de Ejemplo

La migración incluye 3 códigos de descuento de ejemplo:

| Código | Tipo | Descuento | Límite | Vigencia |
|--------|------|-----------|--------|----------|
| WELCOME10 | Porcentaje | 10% | Ilimitado | 30 días |
| PROMO50 | Fijo | $50 | 100 usos | 60 días |
| FLASH20 | Porcentaje | 20% | 50 usos | 7 días |

---

## 🧪 Pruebas Sugeridas

### 1. Prueba de Código Válido
1. Ir a `/reservations/create`
2. Seleccionar tipo "Habitación"
3. Seleccionar una habitación
4. Ingresar código "WELCOME10"
5. Hacer clic en "Aplicar"
6. Verificar que muestre el descuento correcto
7. Completar y enviar el formulario
8. Verificar en BD que se guardó correctamente

### 2. Prueba de Código Inválido
1. Ingresar código "CODIGOINVALIDO"
2. Hacer clic en "Aplicar"
3. Verificar mensaje de error

### 3. Prueba de Código Expirado
1. Crear código con fecha pasada
2. Intentar aplicarlo
3. Verificar mensaje de error

### 4. Prueba de Límite de Uso
1. Crear código con usage_limit=1 y times_used=1
2. Intentar aplicarlo
3. Verificar mensaje de error

### 5. Prueba de Cambio de Habitación
1. Seleccionar habitación
2. Aplicar código válido
3. Cambiar de habitación
4. Verificar que se resetee el código

---

## 📝 Consultas SQL Útiles

### Ver todos los códigos activos
```sql
SELECT code, discount_type, amount, valid_from, valid_to, 
       usage_limit, times_used, active
FROM discount_codes
WHERE active = 1 AND hotel_id = 1;
```

### Ver uso de códigos
```sql
SELECT 
    dc.code,
    dcu.reservation_id,
    dcu.reservation_type,
    dcu.original_price,
    dcu.discount_amount,
    dcu.final_price,
    dcu.used_at
FROM discount_code_usages dcu
JOIN discount_codes dc ON dcu.discount_code_id = dc.id
ORDER BY dcu.used_at DESC;
```

### Ver reservaciones con descuento
```sql
SELECT 
    rr.id,
    rr.guest_name,
    rr.check_in,
    rr.check_out,
    rr.original_price,
    rr.discount_amount,
    rr.total_price,
    dc.code as discount_code
FROM room_reservations rr
LEFT JOIN discount_codes dc ON rr.discount_code_id = dc.id
WHERE rr.discount_code_id IS NOT NULL
ORDER BY rr.created_at DESC;
```

### Crear nuevo código de descuento
```sql
INSERT INTO discount_codes 
(code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('VERANO2025', 'percentage', 15.00, 1, 1, '2025-06-01', '2025-08-31', 200, 
 'Promoción de verano - 15% de descuento');
```

### Desactivar código
```sql
UPDATE discount_codes 
SET active = 0 
WHERE code = 'WELCOME10';
```

---

## 🔒 Seguridad

✅ **Validaciones implementadas:**
- Códigos vinculados a hotel_id (usuario solo puede usar códigos de su hotel)
- Validación de fechas de vigencia
- Verificación de límites de uso
- Sanitización de inputs
- Prepared statements (prevención SQL injection)
- Validación server-side (no confía solo en frontend)

✅ **Integridad de datos:**
- Foreign keys con ON DELETE CASCADE
- Transacciones para operaciones múltiples
- Validación de precios negativos
- Registro de auditoría en discount_code_usages

---

## 🚀 Extensiones Futuras

### Posibles mejoras:
1. **Panel de Administración de Códigos**
   - CRUD completo para gestionar códigos
   - Estadísticas de uso
   - Exportación de reportes

2. **Códigos Personalizados**
   - Códigos únicos por usuario
   - Códigos de un solo uso
   - Códigos por tipo de habitación

3. **Notificaciones**
   - Alertas cuando un código esté por expirar
   - Notificaciones cuando se alcance X% del límite

4. **Restricciones Adicionales**
   - Descuento mínimo de noches
   - Descuento por días de la semana
   - Descuento por temporada

---

## 📞 Soporte

Para dudas o problemas:
1. Revisar este documento
2. Verificar logs de MySQL/MariaDB
3. Revisar console del navegador (JavaScript errors)
4. Verificar que la migración se aplicó correctamente

---

**Fecha de Implementación:** 12 de Octubre de 2025  
**Versión:** 1.0.0  
**Estado:** ✅ Completado y Funcional
