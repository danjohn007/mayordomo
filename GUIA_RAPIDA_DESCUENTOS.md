# 🎟️ Guía Rápida - Códigos de Descuento

## ⚡ Instalación Rápida

### 1. Aplicar Migración de Base de Datos
```bash
mysql -u tu_usuario -p nombre_base_datos < database/add_discount_codes.sql
```

### 2. Verificar Instalación
```sql
-- Ver tablas creadas
SHOW TABLES LIKE '%discount%';

-- Ver códigos de ejemplo
SELECT * FROM discount_codes;
```

---

## 🎯 Uso Básico

### Para el Usuario (Frontend)

1. **Crear Nueva Reservación**
   - Ir a "Reservaciones" → "Nueva Reservación"
   - Seleccionar tipo "Habitación"
   - Seleccionar una habitación

2. **Aplicar Código de Descuento**
   - En el campo "Código de Descuento", ingresar código (ej: WELCOME10)
   - Hacer clic en botón "Aplicar"
   - Si el código es válido, se mostrará:
     - ✓ Mensaje de éxito
     - Precio original
     - Descuento aplicado
     - Precio final a pagar

3. **Completar Reservación**
   - Llenar los demás campos del formulario
   - Hacer clic en "Crear Reservación"
   - El descuento se aplicará automáticamente

---

## 🔧 Gestión de Códigos

### Crear Nuevo Código de Descuento

#### Código con Descuento Porcentual (15%)
```sql
INSERT INTO discount_codes 
(code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('VERANO15', 'percentage', 15.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), NULL, 
 'Promoción de verano - 15% de descuento');
```

#### Código con Descuento Fijo ($100)
```sql
INSERT INTO discount_codes 
(code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
VALUES 
('FIJO100', 'fixed', 100.00, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 50, 
 'Descuento fijo de $100');
```

### Ver Códigos Activos
```sql
SELECT 
    code as 'Código',
    discount_type as 'Tipo',
    amount as 'Monto',
    CONCAT(valid_from, ' a ', valid_to) as 'Vigencia',
    CONCAT(times_used, '/', IFNULL(usage_limit, '∞')) as 'Uso',
    IF(active=1, '✓', '✗') as 'Activo'
FROM discount_codes
WHERE hotel_id = 1
ORDER BY created_at DESC;
```

### Desactivar Código
```sql
UPDATE discount_codes 
SET active = 0 
WHERE code = 'WELCOME10';
```

### Reactivar Código
```sql
UPDATE discount_codes 
SET active = 1 
WHERE code = 'WELCOME10';
```

### Extender Vigencia
```sql
UPDATE discount_codes 
SET valid_to = DATE_ADD(valid_to, INTERVAL 30 DAY)
WHERE code = 'WELCOME10';
```

### Aumentar Límite de Uso
```sql
UPDATE discount_codes 
SET usage_limit = usage_limit + 50
WHERE code = 'PROMO50';
```

---

## 📊 Reportes y Estadísticas

### Ver Uso de Códigos
```sql
SELECT 
    dc.code as 'Código',
    COUNT(dcu.id) as 'Veces Usado',
    SUM(dcu.discount_amount) as 'Descuento Total',
    AVG(dcu.discount_amount) as 'Descuento Promedio'
FROM discount_codes dc
LEFT JOIN discount_code_usages dcu ON dc.id = dcu.discount_code_id
WHERE dc.hotel_id = 1
GROUP BY dc.id
ORDER BY COUNT(dcu.id) DESC;
```

### Ver Reservaciones con Descuento
```sql
SELECT 
    rr.id as 'ID',
    dc.code as 'Código',
    rr.guest_name as 'Huésped',
    rr.original_price as 'Precio Original',
    rr.discount_amount as 'Descuento',
    rr.total_price as 'Total',
    rr.created_at as 'Fecha'
FROM room_reservations rr
JOIN discount_codes dc ON rr.discount_code_id = dc.id
WHERE rr.hotel_id = 1
ORDER BY rr.created_at DESC
LIMIT 20;
```

### Ver Ingresos vs Descuentos (Último Mes)
```sql
SELECT 
    COUNT(*) as 'Total Reservaciones',
    COUNT(discount_code_id) as 'Con Descuento',
    SUM(IFNULL(original_price, total_price)) as 'Ingresos Potenciales',
    SUM(discount_amount) as 'Descuentos Aplicados',
    SUM(total_price) as 'Ingresos Reales'
FROM room_reservations
WHERE hotel_id = 1 
  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH);
```

### Códigos Más Populares
```sql
SELECT 
    dc.code as 'Código',
    dc.description as 'Descripción',
    dc.times_used as 'Usos',
    SUM(dcu.discount_amount) as 'Ahorro Total Clientes',
    dc.usage_limit as 'Límite'
FROM discount_codes dc
LEFT JOIN discount_code_usages dcu ON dc.id = dcu.discount_code_id
WHERE dc.hotel_id = 1
GROUP BY dc.id
ORDER BY dc.times_used DESC
LIMIT 10;
```

---

## 🎯 Códigos de Ejemplo Incluidos

| Código | Tipo | Descuento | Límite | Descripción |
|--------|------|-----------|--------|-------------|
| **WELCOME10** | Porcentaje | 10% | Ilimitado | Código de bienvenida |
| **PROMO50** | Fijo | $50 | 100 usos | Promoción especial |
| **FLASH20** | Porcentaje | 20% | 50 usos | Flash Sale |

---

## ⚠️ Solución de Problemas

### "Código de descuento inválido o expirado"
- ✓ Verificar que el código esté escrito correctamente (case-sensitive)
- ✓ Verificar que el código esté activo: `SELECT active FROM discount_codes WHERE code = 'TU_CODIGO'`
- ✓ Verificar fechas de vigencia

### "Este código ha alcanzado su límite de uso"
- ✓ Aumentar el límite: `UPDATE discount_codes SET usage_limit = 200 WHERE code = 'TU_CODIGO'`
- ✓ O hacer ilimitado: `UPDATE discount_codes SET usage_limit = NULL WHERE code = 'TU_CODIGO'`

### El descuento no se aplica al guardar
- ✓ Verificar que la migración se aplicó correctamente
- ✓ Verificar que las columnas existen en `room_reservations`:
```sql
DESCRIBE room_reservations;
```
- ✓ Revisar logs de errores de PHP

### No aparece el campo de código en el formulario
- ✓ Verificar que seleccionaste tipo "Habitación" (no Mesa ni Amenidad)
- ✓ Limpiar caché del navegador

---

## 💡 Mejores Prácticas

### ✅ Hacer
- Crear códigos con nombres descriptivos (VERANO2025, BLACKFRIDAY, etc.)
- Establecer fechas de vigencia claras
- Establecer límites de uso para controlar el impacto financiero
- Monitorear el uso de códigos regularmente
- Desactivar códigos expirados en lugar de eliminarlos

### ❌ Evitar
- Crear códigos genéricos fáciles de adivinar (PROMO, DESC, etc.)
- Dejar códigos sin fecha de expiración si son promociones limitadas
- No monitorear el uso de códigos
- Eliminar códigos (mejor desactivarlos para mantener historial)

---

## 🔄 Mantenimiento Regular

### Semanal
```sql
-- Revisar códigos por expirar en 7 días
SELECT code, valid_to, times_used, usage_limit
FROM discount_codes
WHERE active = 1 
  AND valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY);
```

### Mensual
```sql
-- Desactivar códigos expirados
UPDATE discount_codes 
SET active = 0 
WHERE active = 1 
  AND valid_to < CURDATE();

-- Reporte mensual de uso
SELECT 
    MONTH(used_at) as Mes,
    COUNT(*) as Usos,
    SUM(discount_amount) as 'Total Descuentos'
FROM discount_code_usages
WHERE YEAR(used_at) = YEAR(CURDATE())
GROUP BY MONTH(used_at);
```

---

## 📧 Contacto

Para soporte técnico o reportar problemas, revisar:
- `IMPLEMENTACION_CODIGOS_DESCUENTO.md` - Documentación técnica completa
- Logs de MySQL/MariaDB
- Console del navegador (F12)

---

**Última Actualización:** 12 de Octubre de 2025
