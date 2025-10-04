# Guía de Instalación Completa - MajorBot Sistema Mejorado

## 📋 Resumen de Nuevas Funcionalidades

Este sistema ahora incluye:

1. **Sistema de Recuperación de Contraseña** por correo electrónico
2. **Programa de Lealtad** con enlaces únicos de referidos para todos los usuarios
3. **Configuración Global Completa** en Superadmin
4. **Dashboard Superadmin** con gráficas y filtros de fecha
5. **Mi Perfil** para todos los usuarios con gestión completa
6. **Dashboard Admin Mejorado** con suscripción, gráficas y filtros
7. **Gestión Completa de Hoteles, Usuarios, Suscripciones, Pagos y Lealtad**

## 🚀 Instalación de Base de Datos

### Paso 1: Aplicar Actualizaciones SQL

```bash
# Conéctate a MySQL
mysql -u root -p

# Selecciona la base de datos
USE aqh_mayordomo;

# Aplica el archivo de actualizaciones
SOURCE /ruta/a/mayordomo/database/updates_comprehensive.sql;
```

O desde línea de comandos:

```bash
mysql -u root -p aqh_mayordomo < database/updates_comprehensive.sql
```

### Paso 2: Verificar Instalación

Ejecuta estas queries para verificar:

```sql
-- Verificar tablas nuevas
SHOW TABLES LIKE '%password_resets%';
SHOW TABLES LIKE '%loyalty_program%';
SHOW TABLES LIKE '%referrals%';
SHOW TABLES LIKE '%payment_transactions%';

-- Verificar configuraciones globales
SELECT COUNT(*) as total_settings FROM global_settings;
SELECT category, COUNT(*) as count 
FROM global_settings 
GROUP BY category;

-- Ver todas las configuraciones por categoría
SELECT setting_key, setting_value, description, category 
FROM global_settings 
ORDER BY category, setting_key;
```

## ⚙️ Configuración Post-Instalación

### 1. Configurar SMTP para Emails

Accede como **Superadmin** a:
`/superadmin/settings`

En la sección **Configuración de Email (SMTP)**, configura:

- **smtp_enabled**: 1 (Habilitado)
- **smtp_host**: smtp.gmail.com (o tu servidor)
- **smtp_port**: 587
- **smtp_username**: tu_email@gmail.com
- **smtp_password**: tu_contraseña_de_aplicación
- **smtp_from_email**: noreply@tudominio.com
- **smtp_from_name**: MajorBot

**Nota Gmail**: Si usas Gmail, necesitas generar una "Contraseña de aplicación" en la configuración de seguridad de tu cuenta Google.

### 2. Configurar PayPal

En la misma página de configuración global:

- **paypal_enabled**: 1 (Habilitado)
- **paypal_client_id**: tu_client_id_de_paypal
- **paypal_secret**: tu_secret_de_paypal
- **paypal_mode**: sandbox (pruebas) o live (producción)

### 3. Configurar Programa de Lealtad

- **loyalty_enabled**: 1 (Habilitado)
- **loyalty_default_percentage**: 10 (10% de comisión por defecto)
- **loyalty_min_withdrawal**: 500 (Monto mínimo para retiro)

### 4. Configurar Moneda e Impuestos

- **currency_symbol**: MXN
- **currency_code**: MXN
- **tax_rate**: 16 (16% IVA)
- **tax_enabled**: 1

### 5. Configurar Información del Sitio

- **site_name**: MajorBot
- **site_logo**: URL del logo
- **site_description**: Sistema de Mayordomía Online
- **site_url**: https://tudominio.com

### 6. Configurar Periodo de Prueba

- **trial_days**: 30 (Días de prueba gratuita)

### 7. Configurar Precios de Planes

- **plan_monthly_price**: 499 (Plan mensual)
- **plan_annual_price**: 4990 (Plan anual)
- **promo_enabled**: 0 (Deshabilitado por defecto)
- **promo_monthly_price**: 399 (Precio promocional mensual)
- **promo_annual_price**: 3990 (Precio promocional anual)
- **promo_start_date**: Fecha inicio promoción
- **promo_end_date**: Fecha fin promoción

### 8. Configurar WhatsApp Chatbot

- **whatsapp_enabled**: 0 (Deshabilitado por defecto)
- **whatsapp_number**: +52 1 999 123 4567
- **whatsapp_api_key**: tu_api_key

### 9. Configurar Cuentas Bancarias

El campo `bank_accounts` acepta un JSON con la siguiente estructura:

```json
[
  {
    "bank": "BBVA",
    "account_number": "1234567890",
    "clabe": "012345678901234567",
    "holder": "MajorBot S.A. de C.V."
  },
  {
    "bank": "Santander",
    "account_number": "9876543210",
    "clabe": "098765432109876543",
    "holder": "MajorBot S.A. de C.V."
  }
]
```

## 🔐 Credenciales de Acceso

### Superadmin (si usaste superadmin_setup.sql)

- **Email**: superadmin@mayorbot.com
- **Contraseña**: Superadmin2024!

**IMPORTANTE**: Cambia esta contraseña inmediatamente después del primer login.

## 📱 Funcionalidades Implementadas

### Para Todos los Usuarios

1. **Recuperación de Contraseña**
   - Enlace "¿Olvidaste tu contraseña?" en login
   - Email con enlace de recuperación (válido 1 hora)
   - Formulario de nueva contraseña

2. **Mi Perfil** (`/profile`)
   - Editar información personal
   - Cambiar contraseña
   - Ver plan activo (Admin)
   - Ver historial de pagos (Admin/Superadmin)
   - Programa de lealtad con código único
   - Copiar enlace de referido

3. **Programa de Lealtad**
   - Código de referido único por usuario
   - Enlace personalizado: `/auth/register?ref=CODIGO`
   - Tracking de referencias
   - Balance disponible y retirado
   - Comisiones configurables

### Para Administradores de Hotel (Admin)

1. **Dashboard Mejorado** (`/dashboard`)
   - Tarjeta de suscripción activa con días restantes
   - 3 Gráficas con filtros de fecha:
     - Reservaciones por día
     - Solicitudes de servicio por día
     - Tasa de ocupación por día
   - Filtro por rango de fechas (mes actual por defecto)
   - Estadísticas de habitaciones, mesas y servicios

2. **Gestión de Suscripción**
   - Ver plan activo en dashboard
   - Días restantes con indicador de color
   - Acceso rápido a actualizar plan desde perfil

### Para Superadministrador

1. **Dashboard Superadmin** (`/superadmin`)
   - 6 Tarjetas de estadísticas:
     - Total hoteles activos
     - Total usuarios
     - Suscripciones activas
     - Ingresos del período
     - Nuevos hoteles
     - Miembros del programa de lealtad
   - 3 Gráficas interactivas (Chart.js):
     - Ingresos por día (línea)
     - Nuevos usuarios por día (barras)
     - Suscripciones por plan (dona)
   - Filtros de fecha personalizables
   - Accesos rápidos a todas las secciones

2. **Gestión de Hoteles** (`/superadmin/hotels`)
   - Listado completo con paginación
   - Información del propietario
   - Contador de usuarios por hotel
   - Estado activo/inactivo

3. **Gestión de Usuarios** (`/superadmin/users`)
   - Listado completo con roles
   - Hotel asociado
   - Suscripciones activas
   - Estado y fecha de registro

4. **Gestión de Suscripciones** (`/superadmin/subscriptions`)
   - Todas las suscripciones del sistema
   - Plan, precio y fechas
   - Días restantes con indicador visual
   - Usuario y hotel asociados

5. **Registro de Pagos** (`/superadmin/payments`)
   - Historial completo de transacciones
   - ID de transacción
   - Método de pago
   - Estado y fechas

6. **Programa de Lealtad** (`/superadmin/loyalty`)
   - Todos los miembros del programa
   - Código de referido
   - Total referencias y ganancias
   - Balance disponible y retirado

7. **Configuración Global** (`/superadmin/settings`)
   - Panel completo de configuración dividido por categorías:
     - Pagos (PayPal)
     - Email/SMTP
     - Programa de Lealtad
     - Financiero (moneda, impuestos)
     - Sitio Web
     - Suscripciones y Precios
     - WhatsApp
   - Todas las configuraciones editables desde interfaz web
   - Guardado en base de datos (tabla global_settings)

## 🎨 Tecnologías Utilizadas

- **Backend**: PHP 7.4+ (MVC Pattern)
- **Base de Datos**: MySQL 5.7+
- **Frontend**: Bootstrap 5, Bootstrap Icons
- **Gráficas**: Chart.js 3.9.1
- **Email**: PHP Mail con configuración SMTP
- **Seguridad**: 
  - Bcrypt para contraseñas (cost 12)
  - Tokens únicos para recuperación
  - Validación y sanitización de inputs
  - CSRF protection

## 📊 Estructura de Archivos Nuevos/Modificados

### Controladores
- `app/controllers/AuthController.php` - Recuperación de contraseña
- `app/controllers/SuperadminController.php` - Nuevo, gestión completa
- `app/controllers/ProfileController.php` - Nuevo, perfil de usuario
- `app/controllers/DashboardController.php` - Mejorado con gráficas

### Vistas
- `app/views/auth/login.php` - Enlace de recuperación
- `app/views/auth/register.php` - Mensaje de prueba gratuita
- `app/views/auth/forgot_password.php` - Nueva
- `app/views/auth/reset_password.php` - Nueva
- `app/views/profile/index.php` - Nueva
- `app/views/superadmin/dashboard.php` - Nueva con Chart.js
- `app/views/superadmin/settings.php` - Nueva, configuración completa
- `app/views/superadmin/hotels.php` - Nueva
- `app/views/superadmin/users.php` - Nueva
- `app/views/superadmin/subscriptions.php` - Nueva
- `app/views/superadmin/payments.php` - Nueva
- `app/views/superadmin/loyalty.php` - Nueva
- `app/views/dashboard/index.php` - Mejorado con gráficas y suscripción

### Configuración
- `config/email.php` - Nueva, configuración SMTP

### Helpers
- `app/helpers/helpers.php` - Funciones agregadas:
  - `sendEmail()` - Envío de emails
  - `getSetting()` - Obtener configuración
  - `updateSetting()` - Actualizar configuración
  - `generateReferralCode()` - Generar código único
  - `generateToken()` - Tokens seguros

### Base de Datos
- `database/updates_comprehensive.sql` - Script completo de actualización

## 🧪 Pruebas Sugeridas

### 1. Probar Recuperación de Contraseña

```
1. Ir a /auth/login
2. Clic en "¿Olvidaste tu contraseña?"
3. Ingresar email registrado
4. Verificar recepción de email
5. Hacer clic en enlace del email
6. Ingresar nueva contraseña
7. Iniciar sesión con nueva contraseña
```

### 2. Probar Programa de Lealtad

```
1. Login como cualquier usuario
2. Ir a /profile
3. Clic en "Activar Programa de Lealtad"
4. Copiar enlace de referido
5. Abrir en navegador incógnito
6. Registrar nuevo usuario con ese enlace
7. Verificar que se registre la referencia
```

### 3. Probar Configuraciones Globales

```
1. Login como Superadmin
2. Ir a /superadmin/settings
3. Modificar cualquier configuración
4. Guardar cambios
5. Verificar que se actualice en global_settings
6. Recargar página y verificar persistencia
```

### 4. Probar Dashboard con Gráficas

```
1. Login como Admin
2. Ir a /dashboard
3. Verificar visualización de gráficas
4. Cambiar fechas de filtro
5. Verificar actualización de gráficas
6. Verificar tarjeta de suscripción
```

## 🔧 Solución de Problemas

### No se envían los emails

1. Verificar que SMTP esté habilitado en `/superadmin/settings`
2. Verificar credenciales SMTP correctas
3. Si usas Gmail, generar contraseña de aplicación
4. Verificar logs de PHP para errores de mail()

### Las gráficas no se muestran

1. Verificar que Chart.js se carga correctamente (CDN)
2. Abrir consola del navegador para ver errores JavaScript
3. Verificar que haya datos en el rango de fechas seleccionado

### Error "Table doesn't exist"

1. Verificar que se aplicó updates_comprehensive.sql
2. Ejecutar las queries de verificación
3. Verificar nombre de base de datos en config.php

### No puedo acceder a /superadmin

1. Verificar que el usuario tenga role = 'superadmin'
2. Ejecutar: `SELECT * FROM users WHERE role = 'superadmin'`
3. Si no existe, ejecutar superadmin_setup.sql

## 📞 Soporte

Para problemas o preguntas, contactar al equipo de desarrollo.

## 📝 Changelog

### v1.1.0 - Sistema Completo

- ✅ Sistema de recuperación de contraseña por email
- ✅ Programa de lealtad con enlaces únicos
- ✅ Configuración global completa en superadmin
- ✅ Dashboard superadmin con gráficas interactivas
- ✅ Mi Perfil para todos los usuarios
- ✅ Dashboard admin mejorado con gráficas y suscripción
- ✅ Gestión completa de hoteles, usuarios, suscripciones y pagos
- ✅ Todas las configuraciones editables desde interfaz web

---

**Desarrollado para MajorBot - Sistema de Mayordomía Online**
