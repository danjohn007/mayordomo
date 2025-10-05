# Fix para Error Fatal en Actualizar Plan

## Problemas

### 1. Error Fatal al Acceder a "Actualizar Plan"

Al acceder a "Actualizar Plan" aparece el siguiente error fatal:

```
Fatal error: Uncaught PDOException: SQLSTATE[42S02]: Base table or view not found: 
1146 Table 'aqh_mayordomo.bank_accounts' doesn't exist in 
/home1/aqh/public_html/majorbot/9/app/controllers/SubscriptionController.php:48
```

### 2. Precios Mostrados No Corresponden a Configuración Global

Los precios mostrados en el registro y en la página de suscripciones no corresponden a los precios establecidos en la Configuración Global del Sistema.

## Causa

1. La tabla `bank_accounts` no existe en la base de datos, pero el código en `SubscriptionController.php` línea 48 intenta consultarla.
2. La tabla `payment_transactions` le faltan columnas necesarias para procesar pagos de suscripciones.
3. Los precios en la tabla `subscriptions` no están sincronizados con los valores configurados en `global_settings`.

## Solución

Ejecutar el script SQL `create_bank_accounts_table.sql` que:

1. **Crea la tabla `bank_accounts`** con la estructura necesaria para almacenar información bancaria
2. **Agrega columnas faltantes a `payment_transactions`**:
   - `subscription_id`: Para asociar pagos con suscripciones
   - `payment_proof`: Para almacenar comprobantes de pago
   - `transaction_reference`: Para referencias de transacciones manuales
3. **Sincroniza los precios** de la tabla `subscriptions` con los valores de `global_settings`:
   - Lee `plan_monthly_price` y actualiza planes mensuales
   - Lee `plan_annual_price` y actualiza planes anuales

## Instrucciones de Instalación

### Opción 1: Línea de comandos

```bash
mysql -u aqh_mayordomo -p aqh_mayordomo < database/create_bank_accounts_table.sql
```

### Opción 2: phpMyAdmin

1. Acceder a phpMyAdmin
2. Seleccionar la base de datos `aqh_mayordomo`
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido de `create_bank_accounts_table.sql`
5. Hacer clic en "Continuar"

### Opción 3: Panel de control del hosting

1. Acceder al panel de control (cPanel, Plesk, etc.)
2. Buscar "MySQL Databases" o "phpMyAdmin"
3. Seleccionar la base de datos
4. Ejecutar el script SQL

## Verificación

Después de ejecutar el script, verificar que:

1. La tabla `bank_accounts` existe:
   ```sql
   SHOW TABLES LIKE 'bank_accounts';
   ```

2. Las columnas fueron agregadas a `payment_transactions`:
   ```sql
   DESCRIBE payment_transactions;
   ```

3. Ya no aparece el error fatal al acceder a "/subscription" o "Actualizar Plan"

4. Los precios se sincronizaron correctamente:
   ```sql
   -- Verificar precios en subscriptions vs global_settings
   SELECT 
       s.name,
       s.type,
       s.price as precio_actual,
       gs.setting_value as precio_configurado
   FROM subscriptions s
   LEFT JOIN global_settings gs ON (
       (s.type = 'monthly' AND gs.setting_key = 'plan_monthly_price') OR
       (s.type = 'annual' AND gs.setting_key = 'plan_annual_price')
   )
   WHERE s.type IN ('monthly', 'annual');
   ```

## Configuración Post-Instalación

### Configurar Cuentas Bancarias

Una vez instalada la migración, hay un registro por defecto con datos de placeholder. Para configurar las cuentas bancarias reales:

**Opción A: Directamente en la base de datos**

```sql
-- Actualizar el registro por defecto
UPDATE bank_accounts 
SET 
    bank_name = 'BBVA',
    account_holder = 'MajorBot S.A. de C.V.',
    account_number = '0123456789',
    clabe = '012180001234567890',
    is_active = 1
WHERE id = 1;

-- O insertar nuevas cuentas
INSERT INTO bank_accounts (bank_name, account_holder, account_number, clabe, is_active)
VALUES 
    ('Santander', 'MajorBot S.A. de C.V.', '9876543210', '014180009876543210', 1),
    ('Banorte', 'MajorBot S.A. de C.V.', '5555555555', '072180005555555555', 1);
```

**Opción B: Desde el panel de Superadmin** (si está implementado)

1. Iniciar sesión como Superadmin
2. Ir a Configuración Global
3. Gestionar Cuentas Bancarias
4. Agregar o editar cuentas

## Estructura de la Tabla bank_accounts

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único (auto-incremental) |
| bank_name | VARCHAR(100) | Nombre del banco |
| account_holder | VARCHAR(200) | Titular de la cuenta |
| account_number | VARCHAR(50) | Número de cuenta |
| clabe | VARCHAR(18) | CLABE interbancaria |
| swift | VARCHAR(11) | Código SWIFT/BIC |
| account_type | ENUM | Tipo de cuenta (checking, savings, other) |
| currency | VARCHAR(3) | Moneda (default: MXN) |
| is_active | TINYINT(1) | Estado activo/inactivo |
| notes | TEXT | Notas adicionales |
| created_at | TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | Fecha de última actualización |

## Notas Importantes

- ✅ Este script es **idempotente**: puede ejecutarse múltiples veces sin causar errores
- ✅ Utiliza `IF NOT EXISTS` y validaciones para evitar duplicados
- ✅ No elimina ni modifica datos existentes
- ✅ Compatible con MySQL 5.7+ y MariaDB 10.2+
- ⚠️ Requiere permisos de `CREATE TABLE` y `ALTER TABLE`

## Problemas Conocidos y Soluciones

### Error: "Table 'activity_log' doesn't exist"

Si al ejecutar el script aparece este error, comentar o eliminar la última sección del script (INSERT INTO activity_log).

### Error de permisos

Si aparece un error de permisos, asegurarse de que el usuario de la base de datos tenga permisos suficientes:

```sql
GRANT CREATE, ALTER, INSERT, UPDATE, SELECT ON aqh_mayordomo.* TO 'aqh_mayordomo'@'localhost';
FLUSH PRIVILEGES;
```

## Soporte

Para cualquier duda o problema:
- Revisar los logs de MySQL/MariaDB
- Verificar permisos del usuario de base de datos
- Consultar la documentación en `/database/INDEX.md`
