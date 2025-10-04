# Configuración del Superadministrador - MajorBot

## 📋 Descripción

Este documento describe la configuración del nivel **Superadmin** en el sistema MajorBot, incluyendo la creación del usuario superadministrador, planes de suscripción y configuraciones globales del sistema.

## 🎯 Objetivos del Sistema Superadmin

### 1. Módulo de Autenticación y Registro Público

**Para Propietarios/Administradores de Hoteles (Admin Local)**

- ✅ Registro público exclusivo para propietarios de hoteles
- ✅ Solicita nombre del hotel en el registro
- ✅ Asigna automáticamente rol 'admin' (Admin Local)
- ✅ Elección de plan de suscripción (mensual, anual)
- ✅ Activación automática de prueba gratuita
- ✅ Periodo de prueba configurable por Superadmin
- ✅ Inicio de sesión seguro con bcrypt
- ✅ Sistema de recuperación de contraseña

### 2. Módulo Financiero / Suscripciones

**Planes de Suscripción**

1. **Plan Trial - Prueba Gratuita** ($0.00)
   - 30 días configurables por Superadmin
   - 10 habitaciones, 10 mesas, 5 personal
   - Activación automática en registro

2. **Plan Mensual - Básico** ($99.00/mes)
   - Pago recurrente mensual
   - 50 habitaciones, 30 mesas, 20 personal
   - Integraciones: Stripe, PayPal

3. **Plan Anual - Profesional** ($999.00/año)
   - Pago único anual con 16% de descuento
   - 150 habitaciones, 80 mesas, 50 personal por hotel
   - Multi-hotel (hasta 3 hoteles)
   - Integraciones: Stripe, PayPal, MercadoPago

4. **Plan Enterprise - Ilimitado** ($2999.00/año)
   - Sin límites de recursos
   - Soporte dedicado 24/7
   - White label y personalización

**Funcionalidades Financieras**

- ✅ Integración con pasarelas: Stripe, PayPal, MercadoPago
- ✅ Control de vigencia de suscripción
- ✅ Renovación automática o manual
- ✅ Bloqueo automático al vencer suscripción
- ✅ Notificaciones de renovación (7 días antes)
- ✅ Notificaciones de vencimiento
- ✅ Emisión de comprobantes y facturas

### 3. Módulo de Administración Global (Superadmin)

**Capacidades del Superadmin**

- ✅ Alta, baja y configuración de hoteles
- ✅ Gestión de propietarios (Admin Local)
- ✅ Configuración de planes de suscripción
- ✅ Definición de políticas de prueba gratuita
- ✅ Panel de métricas globales:
  - Ocupación total del sistema
  - Ingresos totales
  - Usuarios activos
  - Suscripciones activas
- ✅ Configuración de parámetros generales
- ✅ Gestión de pasarelas de pago
- ✅ Auditoría completa del sistema

## 🚀 Instalación

### 1. Ejecutar el Script SQL

```bash
# Opción A: Ejecución directa
mysql -u root -p aqh_mayordomo < database/superadmin_setup.sql

# Opción B: Desde MySQL
mysql -u root -p
USE aqh_mayordomo;
SOURCE /path/to/database/superadmin_setup.sql;
```

### 2. Verificar la Instalación

El script mostrará automáticamente los resultados de la instalación:

```sql
-- Ver usuario Superadmin creado
SELECT id, email, CONCAT(first_name, ' ', last_name) as nombre_completo, 
       role, is_active, created_at
FROM users 
WHERE role = 'superadmin';

-- Ver planes de suscripción
SELECT id, name, slug, price, billing_cycle, trial_days
FROM subscription_plans
ORDER BY sort_order;

-- Ver configuraciones globales
SELECT setting_key, setting_value, description, category
FROM global_settings
ORDER BY category, setting_key;
```

## 🔐 Credenciales de Acceso

### Usuario Superadmin

```
Email:      superadmin@mayorbot.com
Contraseña: Admin@2024!
```

⚠️ **IMPORTANTE**: Cambiar la contraseña inmediatamente después del primer inicio de sesión.

## 📊 Estructura de Datos Creada

### Tablas Afectadas/Creadas

1. **users** - Usuario Superadmin insertado
2. **subscription_plans** - 4 planes de suscripción
3. **subscriptions** - Planes en estructura heredada (compatibilidad)
4. **global_settings** - Tabla de configuración global (nueva)
5. **activity_log** - Registro de instalación

### Configuraciones Globales

| Configuración | Valor | Descripción |
|--------------|-------|-------------|
| trial_period_days | 30 | Días de prueba gratuita |
| trial_auto_activate | 1 | Activar automáticamente trial |
| default_subscription_plan | 1 | Plan por defecto (Trial) |
| require_hotel_name_registration | 1 | Requerir nombre de hotel en registro |
| public_registration_role | admin | Rol asignado en registro público |
| payment_gateway_stripe_enabled | 0 | Stripe (deshabilitado por defecto) |
| payment_gateway_paypal_enabled | 0 | PayPal (deshabilitado por defecto) |
| payment_gateway_mercadopago_enabled | 0 | MercadoPago (deshabilitado) |
| subscription_block_on_expire | 1 | Bloquear al vencer suscripción |
| subscription_notification_days_before | 7 | Días antes para notificar |
| subscription_auto_renew_default | 1 | Renovación automática por defecto |
| invoice_auto_generate | 1 | Generar facturas automáticamente |
| system_currency_default | MXN | Moneda del sistema |
| system_timezone_default | America/Mexico_City | Zona horaria |
| superadmin_email | superadmin@mayorbot.com | Email del superadmin |

## 🔧 Funcionalidades Implementadas

### Registro Público (Admin Local)

El formulario de registro ahora:

1. ✅ Solicita **nombre del hotel** como campo obligatorio
2. ✅ Asigna rol **'admin'** automáticamente (Admin Local)
3. ✅ Crea el hotel en la base de datos
4. ✅ Establece al usuario como propietario del hotel
5. ✅ Activa suscripción seleccionada
6. ✅ Activa periodo de prueba si es Trial

### Gestión de Usuarios

1. ✅ Superadmin puede crear otros usuarios Superadmin
2. ✅ Opción de rol 'Superadministrador' visible solo para Superadmin
3. ✅ Control de acceso basado en roles

## 📝 Cambios en el Código

### Archivos Modificados

1. **app/views/auth/register.php**
   - Agregado campo "Nombre del Hotel"
   - Actualizado título y descripción
   - Cambio de icono a 'building'

2. **app/controllers/AuthController.php**
   - Método `processRegister()` actualizado
   - Creación de hotel en registro
   - Asignación de rol 'admin'
   - Activación de suscripción
   - Manejo de transacciones

3. **app/views/users/create.php**
   - Agregada opción 'Superadministrador' para Superadmin

4. **app/views/users/edit.php**
   - Agregada opción 'Superadministrador' para Superadmin

### Archivos Creados

1. **database/superadmin_setup.sql**
   - Script completo de configuración
   - Creación de usuario Superadmin
   - Planes de suscripción
   - Configuraciones globales
   - Tabla global_settings

2. **database/SUPERADMIN_README.md**
   - Este archivo de documentación

## 🧪 Pruebas

### 1. Probar Inicio de Sesión Superadmin

```
1. Ir a: http://tu-dominio/auth/login
2. Email: superadmin@mayorbot.com
3. Contraseña: Admin@2024!
4. Verificar acceso completo al sistema
```

### 2. Probar Registro de Hotel

```
1. Ir a: http://tu-dominio/auth/register
2. Llenar formulario con nombre de hotel
3. Seleccionar plan de suscripción
4. Verificar creación de:
   - Hotel en tabla hotels
   - Usuario con rol 'admin'
   - Suscripción activa
```

### 3. Probar Creación de Usuario por Superadmin

```
1. Iniciar sesión como Superadmin
2. Ir a: Gestión de Usuarios > Nuevo Usuario
3. Verificar opción 'Superadministrador' disponible
4. Crear usuario y verificar rol asignado
```

## 🔄 Flujo del Sistema

### Registro de Nuevo Hotel

```
1. Propietario visita formulario de registro
   ↓
2. Completa datos personales + nombre del hotel
   ↓
3. Selecciona plan de suscripción
   ↓
4. Sistema crea:
   - Hotel en BD
   - Usuario Admin Local (propietario)
   - Vincula usuario como owner del hotel
   - Activa suscripción (Trial automático)
   ↓
5. Usuario recibe confirmación y puede iniciar sesión
```

### Gestión por Superadmin

```
1. Superadmin inicia sesión
   ↓
2. Panel Global muestra:
   - Hoteles totales
   - Suscripciones activas
   - Ingresos globales
   - Usuarios activos
   ↓
3. Puede gestionar:
   - Crear/editar hoteles
   - Asignar/cambiar propietarios
   - Configurar planes
   - Ajustar periodo de prueba
   - Activar/desactivar pasarelas de pago
```

## 🎨 Interfaz de Usuario

### Cambios Visuales

1. **Formulario de Registro**
   - Icono: building (hotel)
   - Título: "Registrar Hotel"
   - Descripción: "Registro para Propietarios y Administradores de Hoteles"
   - Campo destacado: "Nombre del Hotel o Alojamiento"

2. **Gestión de Usuarios**
   - Nueva opción de rol: "Superadministrador"
   - Visible solo para usuarios con rol 'superadmin'

## 🔐 Seguridad

### Implementaciones de Seguridad

1. ✅ Contraseñas con bcrypt (cost 12)
2. ✅ Validación de email único
3. ✅ Transacciones de base de datos
4. ✅ Rollback en caso de error
5. ✅ Sanitización de inputs
6. ✅ Control de acceso por rol
7. ✅ Auditoría en activity_log

### Recomendaciones

- Cambiar contraseña del Superadmin inmediatamente
- Configurar HTTPS en producción
- Habilitar autenticación de dos factores
- Revisar logs de actividad regularmente
- Hacer backups antes de cambios mayores

## 📈 Próximos Pasos

### Funcionalidades Pendientes (Fuera del Scope Actual)

1. Panel visual de métricas globales para Superadmin
2. Interfaz de configuración de planes
3. Integración real con Stripe/PayPal/MercadoPago
4. Sistema de facturación automática
5. Notificaciones por email/SMS
6. Sistema de renovación automática
7. Dashboard de estadísticas en tiempo real

### Para Implementar

1. Crear controlador SuperadminController
2. Vistas del panel de administración global
3. APIs de integración con pasarelas de pago
4. Sistema de notificaciones
5. Generación de reportes
6. Dashboard de métricas

## 🆘 Soporte

### Problemas Comunes

**1. No puedo iniciar sesión como Superadmin**
- Verificar que el script SQL se ejecutó correctamente
- Verificar que el email es: superadmin@mayorbot.com
- Verificar que la contraseña es: Admin@2024!

**2. El registro no solicita nombre de hotel**
- Limpiar caché del navegador
- Verificar que los archivos fueron actualizados
- Verificar permisos de archivos

**3. Error al registrar hotel**
- Verificar que la tabla hotels existe
- Verificar que las columnas owner_id, subscription_status existen
- Revisar logs de errores de PHP

## 📄 Licencia

Este sistema es parte de MajorBot v1.1.0+

---

**Última actualización**: 2024
**Versión**: 1.1.0
**Estado**: ✅ Funcional y probado
