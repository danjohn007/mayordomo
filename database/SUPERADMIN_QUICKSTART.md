# ⚡ Guía Rápida - Configuración Superadmin

## 🚀 Instalación en 3 Pasos

### Paso 1: Ejecutar el Script SQL

```bash
cd /ruta/a/mayordomo/database
mysql -u root -p aqh_mayordomo < superadmin_setup.sql
```

O si prefieres ejecutar desde MySQL:

```sql
mysql -u root -p
USE aqh_mayordomo;
SOURCE /ruta/completa/a/superadmin_setup.sql;
```

### Paso 2: Iniciar Sesión como Superadmin

```
URL:        http://tu-dominio/auth/login
Email:      superadmin@mayorbot.com
Contraseña: Admin@2024!
```

⚠️ **Cambiar la contraseña inmediatamente después del primer inicio de sesión**

### Paso 3: Probar el Registro de Hotel

```
URL: http://tu-dominio/auth/register

1. Nombre del Hotel: "Hotel Test"
2. Tu nombre y apellido
3. Email válido
4. Teléfono (opcional)
5. Contraseña (mínimo 6 caracteres)
6. Plan: "Plan Trial - Prueba Gratuita"
7. Clic en "Registrarse"
```

## ✅ Verificación Rápida

### Verificar Usuario Superadmin

```sql
SELECT id, email, CONCAT(first_name, ' ', last_name) as nombre, role
FROM users 
WHERE role = 'superadmin';
```

**Resultado esperado:**
```
id | email                      | nombre              | role
1  | superadmin@mayorbot.com   | Super Administrador | superadmin
```

### Verificar Planes de Suscripción

```sql
SELECT id, name, price, billing_cycle, trial_days
FROM subscription_plans
ORDER BY sort_order;
```

**Resultado esperado:**
```
id | name                        | price   | billing_cycle | trial_days
1  | Plan Trial - Prueba Gratuita| 0.00    | monthly      | 30
2  | Plan Mensual - Básico       | 99.00   | monthly      | 0
3  | Plan Anual - Profesional    | 999.00  | annual       | 0
4  | Plan Enterprise - Ilimitado | 2999.00 | annual       | 0
```

### Verificar Configuraciones Globales

```sql
SELECT setting_key, setting_value 
FROM global_settings
WHERE category = 'subscription'
LIMIT 5;
```

**Resultado esperado:**
```
setting_key                    | setting_value
trial_period_days              | 30
trial_auto_activate            | 1
default_subscription_plan      | 1
subscription_block_on_expire   | 1
subscription_auto_renew_default| 1
```

## 🎯 ¿Qué se Instaló?

### ✅ Base de Datos

- [x] 1 Usuario Superadmin
- [x] 4 Planes de Suscripción
- [x] 15 Configuraciones Globales
- [x] 1 Tabla Nueva (global_settings)
- [x] Registro en activity_log

### ✅ Código PHP

- [x] Formulario de registro actualizado
- [x] Controlador de autenticación actualizado
- [x] Vistas de usuarios actualizadas
- [x] Sistema de creación de hoteles

### ✅ Funcionalidades

- [x] Registro exclusivo para propietarios de hoteles
- [x] Activación automática de periodo de prueba
- [x] Asignación de rol 'admin' en registro
- [x] Creación automática de hotel
- [x] Vinculación usuario-hotel como propietario
- [x] Control de suscripciones

## 📋 Planes de Suscripción

| Plan | Precio | Periodo | Hoteles | Habitaciones | Mesas | Personal |
|------|--------|---------|---------|--------------|-------|----------|
| Trial | $0 | 30 días | 1 | 10 | 10 | 5 |
| Mensual | $99 | 30 días | 1 | 50 | 30 | 20 |
| Anual | $999 | 365 días | 3 | 150 | 80 | 50 |
| Enterprise | $2,999 | 365 días | Ilimitado | Ilimitado | Ilimitado | Ilimitado |

## 🔑 Roles del Sistema

| Rol | Acceso | Creado por |
|-----|--------|------------|
| **superadmin** | Sistema completo | SQL Script |
| **admin** | Hotel propio | Registro público |
| **manager** | Gestión hotel | Admin/Superadmin |
| **hostess** | Mesas y reservas | Admin/Manager |
| **collaborator** | Tareas asignadas | Admin/Manager |
| **guest** | Reservas propias | Registro como huésped |

## 🎨 Cambios en la UI

### Formulario de Registro (ANTES)
```
Título: "Crear Cuenta"
Icono:  persona-plus
Campos: nombre, apellido, email, teléfono, contraseña, plan
Rol:    'guest' (huésped)
```

### Formulario de Registro (AHORA)
```
Título: "Registrar Hotel"
Icono:  building (edificio)
Campos: NOMBRE DEL HOTEL, nombre, apellido, email, teléfono, contraseña, plan
Rol:    'admin' (Admin Local - propietario)
```

## 🔒 Seguridad

### Contraseña del Superadmin

La contraseña `Admin@2024!` está hasheada con bcrypt (cost 12):
```
$2y$12$LQv3c1yycULr6hXVmn2vI.iILcRdLB1xI0qdHvvL4F8QHhEWtXivy
```

**⚠️ IMPORTANTE:**
1. Cambiar inmediatamente después del primer login
2. Usar contraseña fuerte (mínimo 12 caracteres)
3. Incluir mayúsculas, minúsculas, números y símbolos
4. No compartir credenciales

### Transacciones

El registro de hotel usa transacciones de BD:
1. Crea hotel
2. Crea usuario admin
3. Vincula usuario como owner
4. Activa suscripción
5. Si falla cualquier paso: ROLLBACK automático

## 🧪 Pruebas Sugeridas

### Test 1: Login Superadmin
```
✓ Ir a /auth/login
✓ Email: superadmin@mayorbot.com
✓ Password: Admin@2024!
✓ Verificar acceso al dashboard
✓ Verificar que puede ver todos los módulos
```

### Test 2: Registro de Hotel
```
✓ Ir a /auth/register
✓ Completar formulario
✓ Verificar creación de hotel en BD
✓ Verificar usuario con rol 'admin'
✓ Verificar activación de suscripción Trial
✓ Login con nuevas credenciales
```

### Test 3: Crear Usuario Superadmin
```
✓ Login como Superadmin
✓ Ir a Gestión de Usuarios > Nuevo
✓ Verificar opción "Superadministrador" visible
✓ Crear usuario con rol superadmin
✓ Login con nuevo usuario superadmin
```

### Test 4: Validaciones
```
✓ Registro sin nombre de hotel: debe fallar
✓ Email duplicado: debe fallar
✓ Contraseña < 6 caracteres: debe fallar
✓ Contraseñas no coinciden: debe fallar
```

## 📊 Consultas Útiles

### Ver todos los Superadmins
```sql
SELECT * FROM users WHERE role = 'superadmin';
```

### Ver todos los Hoteles con Propietarios
```sql
SELECT 
    h.id,
    h.name as hotel,
    CONCAT(u.first_name, ' ', u.last_name) as propietario,
    u.email,
    h.subscription_status,
    h.subscription_end_date
FROM hotels h
LEFT JOIN users u ON h.owner_id = u.id
ORDER BY h.created_at DESC;
```

### Ver Suscripciones Activas
```sql
SELECT 
    u.email,
    s.name as plan,
    us.start_date,
    us.end_date,
    us.status
FROM user_subscriptions us
JOIN users u ON us.user_id = u.id
JOIN subscriptions s ON us.subscription_id = s.id
WHERE us.status = 'active'
ORDER BY us.end_date ASC;
```

### Ver Actividad Reciente
```sql
SELECT 
    al.created_at,
    CONCAT(u.first_name, ' ', u.last_name) as usuario,
    al.action,
    al.description
FROM activity_log al
LEFT JOIN users u ON al.user_id = u.id
ORDER BY al.created_at DESC
LIMIT 10;
```

## 🐛 Solución de Problemas

### Error: "Table 'global_settings' doesn't exist"

**Causa:** El script no se ejecutó completamente

**Solución:**
```sql
-- Verificar si existe
SHOW TABLES LIKE 'global_settings';

-- Si no existe, ejecutar solo esa parte
CREATE TABLE IF NOT EXISTS global_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    category VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Error: "Duplicate entry for key 'email'"

**Causa:** Ya existe un usuario con ese email

**Solución:**
```sql
-- Ver usuarios existentes
SELECT id, email, role FROM users;

-- Eliminar usuario duplicado (si es necesario)
DELETE FROM users WHERE email = 'superadmin@mayorbot.com';

-- Volver a ejecutar el script
```

### Error: "Column 'owner_id' doesn't exist in 'hotels'"

**Causa:** Falta ejecutar la migración v1.1.0

**Solución:**
```bash
# Ejecutar primero la migración
mysql -u root -p aqh_mayordomo < migration_v1.1.0.sql

# Luego ejecutar el setup de superadmin
mysql -u root -p aqh_mayordomo < superadmin_setup.sql
```

### No puedo ver la opción "Superadministrador"

**Causa:** No has iniciado sesión como Superadmin

**Solución:**
1. Cerrar sesión actual
2. Login con: superadmin@mayorbot.com / Admin@2024!
3. Verificar en sesión: `echo $_SESSION['role'];` debe ser 'superadmin'

## 📚 Documentación Adicional

- **Documentación completa:** `SUPERADMIN_README.md`
- **Guía de migración:** `MIGRATION_GUIDE.md`
- **Changelog:** `CHANGELOG_DB.md`
- **Referencia rápida:** `QUICK_REFERENCE.md`

## ✉️ Contacto

Para soporte adicional o reportar problemas:
- Email: superadmin@mayorbot.com
- Revisar logs en: `activity_log` table

---

**Versión:** 1.1.0  
**Última actualización:** 2024  
**Estado:** ✅ Probado y Funcional
