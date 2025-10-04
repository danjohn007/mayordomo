# ❓ Preguntas Frecuentes - Sistema Superadmin

## 📚 Índice

1. [Instalación y Configuración](#instalación-y-configuración)
2. [Acceso y Autenticación](#acceso-y-autenticación)
3. [Registro de Hoteles](#registro-de-hoteles)
4. [Suscripciones y Planes](#suscripciones-y-planes)
5. [Gestión de Usuarios](#gestión-de-usuarios)
6. [Errores Comunes](#errores-comunes)
7. [Mejores Prácticas](#mejores-prácticas)

---

## 🔧 Instalación y Configuración

### ❓ ¿En qué orden debo ejecutar los scripts SQL?

**Respuesta:** El orden correcto es:

```bash
1. schema.sql              # Estructura base
2. migration_v1.1.0.sql    # Migración a v1.1.0+ (opcional si ya tienes la estructura)
3. superadmin_setup.sql    # Configuración Superadmin (NUEVO)
4. sample_data.sql         # Datos de prueba (opcional)
```

### ❓ ¿Puedo ejecutar superadmin_setup.sql múltiples veces?

**Respuesta:** Sí, el script usa `ON DUPLICATE KEY UPDATE` para datos críticos. Sin embargo:

- ✅ Es seguro re-ejecutarlo
- ⚠️ No duplicará el usuario Superadmin
- ⚠️ Actualizará configuraciones existentes
- ⚠️ Podría duplicar planes si tienen IDs diferentes

**Recomendación:** Ejecutar solo una vez. Si necesitas re-ejecutar, respalda primero.

### ❓ ¿Qué pasa si falta la migración v1.1.0?

**Respuesta:** Obtendrás errores como:

```
ERROR 1054: Unknown column 'owner_id' in 'hotels'
ERROR 1146: Table 'subscription_plans' doesn't exist
```

**Solución:**
```bash
mysql -u root -p aqh_mayordomo < migration_v1.1.0.sql
mysql -u root -p aqh_mayordomo < superadmin_setup.sql
```

### ❓ ¿Dónde están las credenciales de la base de datos?

**Respuesta:** En `/config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aqh_mayordomo');
define('DB_USER', 'aqh_mayordomo');
define('DB_PASS', 'Danjohn007!');
```

---

## 🔐 Acceso y Autenticación

### ❓ ¿Cuáles son las credenciales del Superadmin?

**Respuesta:**
```
Email:      superadmin@mayorbot.com
Contraseña: Admin@2024!
```

⚠️ **Importante:** Cambiar inmediatamente después del primer login.

### ❓ No puedo iniciar sesión como Superadmin. ¿Qué hago?

**Verificaciones:**

1. **Confirmar que el usuario existe:**
```sql
SELECT id, email, role FROM users WHERE email = 'superadmin@mayorbot.com';
```

2. **Verificar el rol:**
```sql
-- Debe mostrar 'superadmin'
SELECT role FROM users WHERE email = 'superadmin@mayorbot.com';
```

3. **Restablecer la contraseña:**
```sql
UPDATE users 
SET password = '$2y$12$LQv3c1yycULr6hXVmn2vI.iILcRdLB1xI0qdHvvL4F8QHhEWtXivy'
WHERE email = 'superadmin@mayorbot.com';
```

4. **Verificar que el usuario está activo:**
```sql
UPDATE users SET is_active = 1 WHERE email = 'superadmin@mayorbot.com';
```

### ❓ ¿Cómo cambio la contraseña del Superadmin?

**Opción A - Desde la interfaz:**
1. Login como Superadmin
2. Ir a Perfil → Cambiar Contraseña

**Opción B - Desde SQL:**
```sql
-- Generar hash con PHP
php -r "echo password_hash('NuevaContraseña123!', PASSWORD_BCRYPT, ['cost' => 12]);"

-- Actualizar en BD
UPDATE users 
SET password = 'hash_generado_arriba'
WHERE email = 'superadmin@mayorbot.com';
```

### ❓ ¿Puedo tener múltiples Superadmins?

**Respuesta:** ¡Sí! Hay dos formas:

**Opción 1 - Desde el sistema:**
1. Login como Superadmin existente
2. Ir a Gestión de Usuarios → Nuevo Usuario
3. Seleccionar rol "Superadministrador"
4. Completar formulario y guardar

**Opción 2 - Desde SQL:**
```sql
INSERT INTO users (email, password, first_name, last_name, role, is_active)
VALUES (
    'otro-superadmin@ejemplo.com',
    '$2y$12$hash_de_contraseña',
    'Nombre',
    'Apellido',
    'superadmin',
    1
);
```

---

## 🏨 Registro de Hoteles

### ❓ ¿Quién puede registrar un hotel?

**Respuesta:** Cualquier persona a través del formulario público de registro en `/auth/register`. El sistema automáticamente:

1. ✅ Crea el hotel
2. ✅ Crea el usuario como Admin Local (rol 'admin')
3. ✅ Vincula al usuario como propietario del hotel
4. ✅ Activa la suscripción seleccionada

### ❓ ¿El registro es solo para propietarios?

**Respuesta:** Sí, el registro público es exclusivamente para propietarios/administradores de hoteles. Los usuarios de otros roles (Manager, Hostess, Colaborador, Huésped) deben ser creados por el Admin Local del hotel.

### ❓ ¿Qué pasa si no completo el campo "Nombre del Hotel"?

**Respuesta:** El registro fallará con el mensaje: "El nombre del hotel es requerido". Es un campo obligatorio.

### ❓ ¿Puedo cambiar el nombre de mi hotel después del registro?

**Respuesta:** Sí, como Admin Local:
1. Login con tus credenciales
2. Ir a Configuración del Hotel
3. Editar el nombre
4. Guardar cambios

O desde SQL (Superadmin):
```sql
UPDATE hotels SET name = 'Nuevo Nombre' WHERE id = X;
```

### ❓ ¿Qué pasa si el registro falla a medias?

**Respuesta:** El sistema usa transacciones:

```
BEGIN TRANSACTION
  ├─ Crear hotel
  ├─ Crear usuario
  ├─ Vincular owner
  └─ Activar suscripción
COMMIT (o ROLLBACK si falla algo)
```

Si cualquier paso falla, **todo se revierte automáticamente**. No quedarán datos inconsistentes.

---

## 💳 Suscripciones y Planes

### ❓ ¿Qué planes están disponibles?

**Respuesta:**

| Plan | Precio | Periodo | Hoteles | Habitaciones | Activación |
|------|--------|---------|---------|--------------|------------|
| Trial | $0 | 30 días | 1 | 10 | Automática |
| Mensual | $99 | 30 días | 1 | 50 | Manual |
| Anual | $999 | 365 días | 3 | 150 | Manual |
| Enterprise | $2,999 | 365 días | Ilimitado | Ilimitado | Manual |

### ❓ ¿El periodo de prueba se activa automáticamente?

**Respuesta:** Sí, si seleccionas el "Plan Trial" en el registro, se activa automáticamente por 30 días.

### ❓ ¿Puedo cambiar el periodo de prueba de 30 días?

**Respuesta:** Sí, como Superadmin:

```sql
-- Ver configuración actual
SELECT setting_value FROM global_settings WHERE setting_key = 'trial_period_days';

-- Cambiar a 15 días
UPDATE global_settings 
SET setting_value = '15' 
WHERE setting_key = 'trial_period_days';

-- O 60 días
UPDATE global_settings 
SET setting_value = '60' 
WHERE setting_key = 'trial_period_days';
```

### ❓ ¿Qué pasa cuando expira mi suscripción?

**Respuesta:** Depende de la configuración:

**Si `subscription_block_on_expire = 1` (por defecto):**
- ❌ Acceso bloqueado al sistema
- 📧 Notificación de vencimiento enviada
- ⚠️ Datos preservados (no se eliminan)
- ✅ Al renovar, acceso restaurado

**Para desactivar bloqueo:**
```sql
UPDATE global_settings 
SET setting_value = '0' 
WHERE setting_key = 'subscription_block_on_expire';
```

### ❓ ¿Cómo extiendo manualmente una suscripción?

**Respuesta:** Como Superadmin:

```sql
-- Ver suscripciones activas
SELECT 
    u.email,
    us.start_date,
    us.end_date,
    us.status
FROM user_subscriptions us
JOIN users u ON us.user_id = u.id
WHERE u.email = 'admin@hotel.com';

-- Extender 30 días más
UPDATE user_subscriptions 
SET end_date = DATE_ADD(end_date, INTERVAL 30 DAY)
WHERE user_id = (SELECT id FROM users WHERE email = 'admin@hotel.com')
  AND status = 'active';

-- O establecer fecha específica
UPDATE user_subscriptions 
SET end_date = '2024-12-31'
WHERE user_id = X AND status = 'active';
```

### ❓ ¿Cómo cambio el plan de un hotel?

**Respuesta:**

```sql
-- 1. Ver plan actual
SELECT 
    h.name as hotel,
    sp.name as plan_actual,
    hs.end_date
FROM hotels h
JOIN hotel_subscriptions hs ON h.id = hs.hotel_id
JOIN subscription_plans sp ON hs.plan_id = sp.id
WHERE h.id = 1;

-- 2. Actualizar a nuevo plan (ejemplo: plan_id=3 es Anual)
UPDATE hotel_subscriptions 
SET plan_id = 3,
    end_date = DATE_ADD(CURDATE(), INTERVAL 365 DAY)
WHERE hotel_id = 1 
  AND status = 'active';

-- 3. Actualizar límites en tabla hotels
UPDATE hotels h
JOIN subscription_plans sp ON sp.id = 3
SET h.max_rooms = sp.max_rooms_per_hotel,
    h.max_tables = sp.max_tables_per_hotel,
    h.max_staff = sp.max_staff_per_hotel
WHERE h.id = 1;
```

---

## 👥 Gestión de Usuarios

### ❓ ¿Qué diferencia hay entre 'admin' y 'superadmin'?

**Respuesta:**

| Característica | admin (Admin Local) | superadmin |
|----------------|---------------------|------------|
| **Alcance** | Un hotel específico | Sistema completo |
| **Crear hoteles** | ❌ No | ✅ Sí |
| **Gestionar propietarios** | ❌ No | ✅ Sí |
| **Configurar planes** | ❌ No | ✅ Sí |
| **Ver métricas globales** | ❌ No | ✅ Sí |
| **Gestionar su hotel** | ✅ Sí | ✅ Sí |
| **Crear usuarios** | ✅ En su hotel | ✅ En cualquier hotel |

### ❓ ¿Por qué no veo la opción "Superadministrador" al crear usuarios?

**Respuesta:** Solo aparece si estás logueado como Superadmin. Verificar:

```php
// En el código
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
    <option value="superadmin">Superadministrador</option>
<?php endif; ?>
```

**Solución:** Cerrar sesión y entrar con credenciales de Superadmin.

### ❓ ¿Un Admin Local puede ver otros hoteles?

**Respuesta:** No, el sistema filtra automáticamente:

```sql
-- Los datos se filtran por hotel_id
SELECT * FROM rooms WHERE hotel_id = current_user_hotel_id;
```

Solo Superadmin puede ver/gestionar todos los hoteles.

### ❓ ¿Cómo cambio el propietario de un hotel?

**Respuesta:** Como Superadmin:

```sql
-- Ver propietario actual
SELECT 
    h.name as hotel,
    CONCAT(u.first_name, ' ', u.last_name) as propietario_actual,
    u.email
FROM hotels h
JOIN users u ON h.owner_id = u.id
WHERE h.id = 1;

-- Cambiar propietario
UPDATE hotels 
SET owner_id = (SELECT id FROM users WHERE email = 'nuevo-admin@ejemplo.com')
WHERE id = 1;
```

---

## 🐛 Errores Comunes

### ❓ Error: "Table 'global_settings' doesn't exist"

**Causa:** El script de setup no se ejecutó completamente.

**Solución:**
```bash
mysql -u root -p aqh_mayordomo < database/superadmin_setup.sql
```

### ❓ Error: "Duplicate entry 'superadmin@mayorbot.com' for key 'email'"

**Causa:** Ya existe un usuario con ese email.

**Solución A - Usar el existente:**
```sql
-- Verificar y actualizar rol si es necesario
UPDATE users 
SET role = 'superadmin',
    password = '$2y$12$LQv3c1yycULr6hXVmn2vI.iILcRdLB1xI0qdHvvL4F8QHhEWtXivy'
WHERE email = 'superadmin@mayorbot.com';
```

**Solución B - Eliminar y recrear:**
```sql
DELETE FROM users WHERE email = 'superadmin@mayorbot.com';
-- Luego ejecutar superadmin_setup.sql nuevamente
```

### ❓ Error: "Unknown column 'owner_id' in 'field list'"

**Causa:** Falta ejecutar la migración v1.1.0.

**Solución:**
```bash
mysql -u root -p aqh_mayordomo < database/migration_v1.1.0.sql
```

### ❓ Error al registrar hotel: "SQLSTATE[HY000]: General error: 1364 Field 'subscription_status' doesn't have a default value"

**Causa:** Falta campo en la tabla hotels.

**Solución:**
```sql
ALTER TABLE hotels 
ADD COLUMN subscription_status ENUM('trial', 'active', 'suspended', 'cancelled') DEFAULT 'trial'
AFTER email;
```

### ❓ El dashboard muestra "Access Denied"

**Causa:** El usuario no tiene permisos o la sesión no está correcta.

**Verificación:**
```php
// Verificar en PHP
var_dump($_SESSION);

// Debe mostrar:
// ['role'] => 'superadmin' o 'admin'
// ['user_id'] => número
// ['hotel_id'] => número (para admin) o NULL (para superadmin)
```

**Solución:**
1. Cerrar sesión completamente
2. Limpiar cookies del navegador
3. Iniciar sesión nuevamente

---

## 📖 Mejores Prácticas

### ✅ Seguridad

1. **Cambiar contraseña del Superadmin inmediatamente**
   ```
   No usar: Admin@2024!
   Usar: Contraseña fuerte única de 16+ caracteres
   ```

2. **Habilitar HTTPS en producción**
   ```apache
   <VirtualHost *:443>
       SSLEngine on
       SSLCertificateFile /path/to/cert.pem
       SSLCertificateKeyFile /path/to/key.pem
   </VirtualHost>
   ```

3. **Limitar acceso al panel de Superadmin**
   ```apache
   # .htaccess
   <Location "/superadmin">
       Require ip 192.168.1.0/24
   </Location>
   ```

4. **Backups regulares**
   ```bash
   # Backup diario automatizado
   mysqldump -u root -p aqh_mayordomo > backup_$(date +%Y%m%d).sql
   ```

### ✅ Mantenimiento

1. **Revisar logs regularmente**
   ```sql
   SELECT * FROM activity_log 
   WHERE action IN ('system_error', 'failed_login', 'unauthorized_access')
   ORDER BY created_at DESC 
   LIMIT 50;
   ```

2. **Limpiar sesiones expiradas**
   ```sql
   DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR);
   ```

3. **Monitorear suscripciones**
   ```sql
   -- Suscripciones que vencen en los próximos 7 días
   SELECT 
       u.email,
       h.name,
       us.end_date
   FROM user_subscriptions us
   JOIN users u ON us.user_id = u.id
   JOIN hotels h ON u.hotel_id = h.id
   WHERE us.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
     AND us.status = 'active';
   ```

### ✅ Performance

1. **Índices en columnas frecuentes**
   ```sql
   CREATE INDEX idx_email ON users(email);
   CREATE INDEX idx_hotel_id ON users(hotel_id);
   CREATE INDEX idx_status ON user_subscriptions(status);
   ```

2. **Limpiar datos antiguos**
   ```sql
   -- Eliminar logs de más de 1 año
   DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
   ```

### ✅ Documentación

1. **Documentar cambios personalizados**
   - Mantener un changelog interno
   - Documentar configuraciones específicas
   - Registrar personalizaciones de código

2. **Capacitar al equipo**
   - Guías de uso para cada rol
   - Procedimientos de emergencia
   - Contactos de soporte

---

## 📞 Soporte

### ¿Necesitas más ayuda?

1. **Revisar documentación:**
   - `SUPERADMIN_README.md` - Documentación completa
   - `SUPERADMIN_QUICKSTART.md` - Guía rápida
   - `SUPERADMIN_DIAGRAM.md` - Diagramas visuales
   - `SUPERADMIN_FAQ.md` - Este documento

2. **Verificar logs del sistema:**
   ```bash
   # Logs de PHP
   tail -f /var/log/apache2/error.log
   
   # Logs de MySQL
   tail -f /var/log/mysql/error.log
   ```

3. **Consultar activity_log:**
   ```sql
   SELECT * FROM activity_log 
   ORDER BY created_at DESC 
   LIMIT 20;
   ```

4. **Contactar soporte:**
   - Email: superadmin@mayorbot.com
   - GitHub Issues: [Reportar problema]

---

**Última actualización:** 2024  
**Versión:** 1.1.0  
**Mantenido por:** Equipo MajorBot
