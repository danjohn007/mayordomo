# Guía de Gestión de Suscripciones y Cuentas Bancarias

## 📋 Tabla de Contenidos

1. [Gestión de Cuentas Bancarias](#gestión-de-cuentas-bancarias)
2. [Gestión de Precios de Suscripción](#gestión-de-precios-de-suscripción)
3. [Sincronización de Precios](#sincronización-de-precios)
4. [Ejemplos de Consultas SQL](#ejemplos-de-consultas-sql)

---

## 🏦 Gestión de Cuentas Bancarias

### Consultar Cuentas Activas

```sql
SELECT * FROM bank_accounts WHERE is_active = 1;
```

### Agregar Nueva Cuenta Bancaria

```sql
INSERT INTO bank_accounts (
    bank_name, 
    account_holder, 
    account_number, 
    clabe, 
    swift,
    account_type,
    currency,
    is_active,
    notes
) VALUES (
    'BBVA México',
    'MajorBot S.A. de C.V.',
    '0123456789',
    '012180001234567890',
    NULL,
    'checking',
    'MXN',
    1,
    'Cuenta principal para depósitos de clientes'
);
```

### Actualizar Cuenta Bancaria Existente

```sql
UPDATE bank_accounts 
SET 
    bank_name = 'Banco Santander México',
    account_holder = 'MajorBot S.A. de C.V.',
    account_number = '9876543210',
    clabe = '014180009876543210',
    notes = 'Cuenta secundaria'
WHERE id = 1;
```

### Desactivar Cuenta Bancaria

```sql
-- Desactivar sin eliminar (recomendado)
UPDATE bank_accounts 
SET is_active = 0 
WHERE id = 1;

-- Eliminar permanentemente (NO recomendado si hay referencias)
DELETE FROM bank_accounts WHERE id = 1;
```

### Listar Todas las Cuentas (Activas e Inactivas)

```sql
SELECT 
    id,
    bank_name,
    account_holder,
    account_number,
    clabe,
    CASE is_active 
        WHEN 1 THEN 'Activa' 
        ELSE 'Inactiva' 
    END as estado,
    created_at
FROM bank_accounts
ORDER BY is_active DESC, id;
```

---

## 💰 Gestión de Precios de Suscripción

### Ver Configuración Actual de Precios

```sql
-- Ver todos los precios configurados
SELECT 
    setting_key as clave,
    setting_value as valor,
    description as descripcion
FROM global_settings
WHERE setting_key LIKE '%price%' OR setting_key LIKE '%promo%'
ORDER BY category, setting_key;
```

### Actualizar Precio del Plan Mensual

```sql
-- Actualizar precio en global_settings
UPDATE global_settings 
SET setting_value = '599' 
WHERE setting_key = 'plan_monthly_price';

-- Sincronizar con la tabla subscriptions
UPDATE subscriptions 
SET price = 599.00 
WHERE type = 'monthly';
```

### Actualizar Precio del Plan Anual

```sql
-- Actualizar precio en global_settings
UPDATE global_settings 
SET setting_value = '5990' 
WHERE setting_key = 'plan_annual_price';

-- Sincronizar con la tabla subscriptions
UPDATE subscriptions 
SET price = 5990.00 
WHERE type = 'annual';
```

### Activar Precios Promocionales

```sql
-- 1. Configurar precios promocionales
UPDATE global_settings SET setting_value = '399' WHERE setting_key = 'promo_monthly_price';
UPDATE global_settings SET setting_value = '3990' WHERE setting_key = 'promo_annual_price';
UPDATE global_settings SET setting_value = '2024-01-01' WHERE setting_key = 'promo_start_date';
UPDATE global_settings SET setting_value = '2024-12-31' WHERE setting_key = 'promo_end_date';

-- 2. Activar promoción
UPDATE global_settings SET setting_value = '1' WHERE setting_key = 'promo_enabled';

-- 3. Aplicar precios promocionales a subscriptions
UPDATE subscriptions 
SET price = (SELECT CAST(setting_value AS DECIMAL(10,2)) FROM global_settings WHERE setting_key = 'promo_monthly_price')
WHERE type = 'monthly';

UPDATE subscriptions 
SET price = (SELECT CAST(setting_value AS DECIMAL(10,2)) FROM global_settings WHERE setting_key = 'promo_annual_price')
WHERE type = 'annual';
```

### Desactivar Precios Promocionales

```sql
-- 1. Desactivar promoción
UPDATE global_settings SET setting_value = '0' WHERE setting_key = 'promo_enabled';

-- 2. Restaurar precios normales
UPDATE subscriptions 
SET price = (SELECT CAST(setting_value AS DECIMAL(10,2)) FROM global_settings WHERE setting_key = 'plan_monthly_price')
WHERE type = 'monthly';

UPDATE subscriptions 
SET price = (SELECT CAST(setting_value AS DECIMAL(10,2)) FROM global_settings WHERE setting_key = 'plan_annual_price')
WHERE type = 'annual';
```

---

## 🔄 Sincronización de Precios

### ¿Por qué sincronizar?

El sistema almacena precios en dos lugares:
- **`global_settings`**: Configuración editable por el superadmin
- **`subscriptions`**: Precios que se muestran a los usuarios

Es importante mantenerlos sincronizados para evitar inconsistencias.

### Script de Sincronización Automática

```sql
-- Sincronizar precios normales
UPDATE subscriptions s
SET s.price = (
    SELECT CAST(setting_value AS DECIMAL(10,2))
    FROM global_settings 
    WHERE setting_key = 'plan_monthly_price'
    LIMIT 1
)
WHERE s.type = 'monthly';

UPDATE subscriptions s
SET s.price = (
    SELECT CAST(setting_value AS DECIMAL(10,2))
    FROM global_settings 
    WHERE setting_key = 'plan_annual_price'
    LIMIT 1
)
WHERE s.type = 'annual';

-- Verificar sincronización
SELECT 
    s.name,
    s.type,
    s.price as precio_mostrado,
    gs.setting_value as precio_configurado,
    CASE 
        WHEN s.price = CAST(gs.setting_value AS DECIMAL(10,2)) THEN '✓ Sincronizado'
        ELSE '✗ Desincronizado'
    END as estado
FROM subscriptions s
LEFT JOIN global_settings gs ON (
    (s.type = 'monthly' AND gs.setting_key = 'plan_monthly_price') OR
    (s.type = 'annual' AND gs.setting_key = 'plan_annual_price')
)
WHERE s.type IN ('monthly', 'annual');
```

---

## 📊 Ejemplos de Consultas SQL

### Reporte de Suscripciones Activas

```sql
SELECT 
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as nombre_completo,
    s.name as plan,
    s.price as precio,
    us.start_date as inicio,
    us.end_date as fin,
    DATEDIFF(us.end_date, CURDATE()) as dias_restantes,
    us.status
FROM user_subscriptions us
JOIN users u ON us.user_id = u.id
JOIN subscriptions s ON us.subscription_id = s.id
WHERE us.status = 'active'
ORDER BY us.end_date ASC;
```

### Reporte de Pagos Pendientes

```sql
SELECT 
    pt.id,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as usuario,
    s.name as plan,
    pt.amount as monto,
    pt.payment_method as metodo,
    pt.transaction_reference as referencia,
    pt.created_at as fecha
FROM payment_transactions pt
JOIN users u ON pt.user_id = u.id
LEFT JOIN subscriptions s ON pt.subscription_id = s.id
WHERE pt.status = 'pending'
ORDER BY pt.created_at DESC;
```

### Estadísticas de Ingresos

```sql
SELECT 
    DATE_FORMAT(pt.created_at, '%Y-%m') as mes,
    COUNT(*) as total_transacciones,
    SUM(pt.amount) as ingresos_totales,
    COUNT(CASE WHEN pt.status = 'completed' THEN 1 END) as pagos_completados,
    COUNT(CASE WHEN pt.status = 'pending' THEN 1 END) as pagos_pendientes
FROM payment_transactions pt
WHERE pt.subscription_id IS NOT NULL
GROUP BY DATE_FORMAT(pt.created_at, '%Y-%m')
ORDER BY mes DESC;
```

### Usuarios Sin Suscripción Activa

```sql
SELECT 
    u.id,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as nombre,
    u.role,
    u.created_at as fecha_registro
FROM users u
LEFT JOIN user_subscriptions us ON u.id = us.user_id AND us.status = 'active'
WHERE us.id IS NULL
    AND u.role = 'admin'
    AND u.is_active = 1
ORDER BY u.created_at DESC;
```

---

## 🛠️ Mantenimiento Recomendado

### Limpieza de Pagos Antiguos

```sql
-- Archivar pagos completados de hace más de 1 año
UPDATE payment_transactions 
SET status = 'archived'
WHERE status = 'completed'
    AND processed_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Actualización Masiva de Precios

```sql
-- Aumentar todos los precios en 10%
UPDATE subscriptions 
SET price = price * 1.10 
WHERE type IN ('monthly', 'annual');

-- No olvides actualizar global_settings también
UPDATE global_settings 
SET setting_value = CAST(setting_value AS DECIMAL(10,2)) * 1.10
WHERE setting_key IN ('plan_monthly_price', 'plan_annual_price');
```

---

## 📝 Notas Importantes

1. **Respaldo antes de cambios**: Siempre hacer backup antes de modificar precios o cuentas bancarias
2. **Sincronización**: Después de cambiar precios en `global_settings`, sincronizar con `subscriptions`
3. **Validación**: Usar el script `verify_subscription_fix.sql` para verificar consistencia
4. **Notificación**: Informar a los usuarios sobre cambios de precios con anticipación
5. **Registro**: Todos los cambios quedan registrados en `activity_log` automáticamente

---

## 🆘 Solución de Problemas

### Los precios no se actualizan en la interfaz

1. Verificar que los cambios se aplicaron en `subscriptions`:
   ```sql
   SELECT * FROM subscriptions WHERE type IN ('monthly', 'annual');
   ```

2. Limpiar caché si existe:
   ```sql
   -- Si hay tabla de caché
   DELETE FROM cache WHERE cache_key LIKE '%subscription%';
   ```

3. Verificar que la vista lee de la tabla correcta:
   - Revisar `app/controllers/SubscriptionController.php`
   - Debe leer de `subscriptions`, no de `global_settings`

### Error al sincronizar precios

Si aparece un error de tipo de dato:
```sql
-- Verificar tipo de columna
DESCRIBE subscriptions;

-- Si es necesario, convertir explícitamente
UPDATE subscriptions 
SET price = CAST('499' AS DECIMAL(10,2))
WHERE type = 'monthly';
```

---

## 📞 Soporte

Para más información, consultar:
- `README_FIX_SUBSCRIPTION.md` - Guía de instalación
- `verify_subscription_fix.sql` - Script de verificación
- `INDEX.md` - Índice de documentación de la base de datos
