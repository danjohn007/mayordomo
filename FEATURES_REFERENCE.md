# Referencia Rápida de Funcionalidades - MajorBot v1.1.0

## 🌐 Rutas Nuevas del Sistema

### Autenticación y Recuperación

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/auth/login` | GET | Página de login (ahora con enlace de recuperación) |
| `/auth/forgotPassword` | GET | Formulario de recuperación de contraseña |
| `/auth/processForgotPassword` | POST | Procesar solicitud de recuperación |
| `/auth/resetPassword?token=XXX` | GET | Formulario de nueva contraseña |
| `/auth/processResetPassword` | POST | Guardar nueva contraseña |
| `/auth/register` | GET | Registro (ahora muestra días de prueba) |

### Perfil de Usuario

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/profile` | GET | Ver perfil completo del usuario |
| `/profile/update` | POST | Actualizar información personal |
| `/profile/changePassword` | POST | Cambiar contraseña |
| `/profile/referral` | POST | Activar programa de lealtad |

### Superadmin

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/superadmin` | GET | Dashboard principal con gráficas |
| `/superadmin/hotels` | GET | Gestión de hoteles (listado paginado) |
| `/superadmin/users` | GET | Gestión de usuarios (listado paginado) |
| `/superadmin/subscriptions` | GET | Gestión de suscripciones |
| `/superadmin/payments` | GET | Registro de pagos |
| `/superadmin/loyalty` | GET | Programa de lealtad |
| `/superadmin/settings` | GET | Configuración global |
| `/superadmin/settings` | POST | Guardar configuración |

### Dashboard Mejorado

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/dashboard?start_date=X&end_date=Y` | GET | Dashboard con filtros de fecha |

## 🎯 Funcionalidades por Rol

### 👤 Todos los Usuarios

#### Mi Perfil
- ✅ Ver y editar información personal
- ✅ Cambiar contraseña de forma segura
- ✅ Ver rol asignado
- ✅ Activar programa de lealtad
- ✅ Generar código de referido único
- ✅ Copiar enlace de referido
- ✅ Ver estadísticas de referencias

#### Recuperación de Contraseña
- ✅ Solicitar enlace de recuperación por email
- ✅ Recibir email con token seguro (válido 1 hora)
- ✅ Restablecer contraseña

#### Programa de Lealtad
- ✅ Código único: `XXXXXXXX` (8 caracteres)
- ✅ Enlace personalizado: `BASE_URL/auth/register?ref=CODIGO`
- ✅ Ver total de referencias
- ✅ Ver ganancias totales
- ✅ Ver balance disponible
- ✅ Ver balance retirado

### 🏨 Administrador de Hotel (Admin)

Todo lo anterior, más:

#### Dashboard Mejorado
- ✅ Tarjeta de suscripción activa
  - Plan actual
  - Precio
  - Fecha de vencimiento
  - Días restantes (con indicador de color)
- ✅ 3 Gráficas interactivas:
  1. **Reservaciones por día** (línea)
  2. **Solicitudes de servicio por día** (barras)
  3. **Tasa de ocupación por día** (línea, en %)
- ✅ Filtros de fecha (inicio y fin)
- ✅ Por defecto muestra mes actual

#### Perfil Extendido
- ✅ Ver plan activo y detalles
- ✅ Ver historial de pagos (últimos 10)
- ✅ Acceso rápido a actualizar plan

#### Estadísticas
- ✅ Total habitaciones (por estado)
- ✅ Total mesas (por estado)
- ✅ Solicitudes de servicio (por estado)
- ✅ Ingresos del día

### 👑 Superadministrador

Todo lo anterior, más:

#### Dashboard Superadmin
- ✅ 6 Tarjetas de estadísticas principales:
  1. **Hoteles activos** + nuevos en período
  2. **Usuarios activos** totales
  3. **Suscripciones activas** vigentes
  4. **Ingresos del período** con rango de fechas
  5. **Nuevos hoteles** en período
  6. **Miembros de lealtad** activos

- ✅ 3 Gráficas principales (Chart.js):
  1. **Ingresos por día** (gráfica de línea)
     - Suma de pagos completados por día
     - Rango de fechas personalizable
  
  2. **Nuevos usuarios por día** (gráfica de barras)
     - Contador de registros por día
     - Filtrable por fecha
  
  3. **Suscripciones por plan** (gráfica de dona)
     - Distribución de suscripciones activas
     - Por tipo de plan

- ✅ Filtros de fecha globales (inicio y fin)
- ✅ Accesos rápidos a todas las secciones

#### Gestión de Hoteles
- ✅ Listado completo con paginación
- ✅ Ver información del propietario
- ✅ Ver email y datos del hotel
- ✅ Contador de usuarios por hotel
- ✅ Estado activo/inactivo
- ✅ Fecha de creación
- ✅ Botones de ver detalles y editar

#### Gestión de Usuarios
- ✅ Listado completo con paginación
- ✅ Ver nombre completo y email
- ✅ Ver hotel asociado
- ✅ Ver rol con badge colorido
- ✅ Ver suscripciones activas
- ✅ Estado activo/inactivo
- ✅ Fecha de registro
- ✅ Botones de ver detalles y editar

#### Gestión de Suscripciones
- ✅ Listado completo con paginación
- ✅ Ver usuario y hotel asociados
- ✅ Plan suscrito con precio
- ✅ Fechas de inicio y fin
- ✅ Días restantes con badge colorido:
  - 🟢 Verde: >7 días
  - 🟡 Amarillo: 1-7 días
  - 🔴 Rojo: 0 o vencido
- ✅ Estado de la suscripción

#### Registro de Pagos
- ✅ Historial completo de transacciones
- ✅ Usuario y hotel asociados
- ✅ Monto y moneda
- ✅ Método de pago
- ✅ ID de transacción externa
- ✅ Estado del pago
- ✅ Fecha de creación

#### Programa de Lealtad
- ✅ Todos los miembros del programa
- ✅ Usuario con email
- ✅ Rol del usuario
- ✅ Código de referido único
- ✅ Total de referencias generadas
- ✅ Total ganado acumulado
- ✅ Balance disponible actual
- ✅ Balance retirado histórico
- ✅ Estado activo/inactivo
- ✅ Fecha de registro en programa

#### Configuración Global
Panel completo dividido en categorías:

##### 💳 Configuración de Pagos (PayPal)
- `paypal_enabled` - Habilitar/deshabilitar PayPal
- `paypal_client_id` - Client ID de PayPal
- `paypal_secret` - Secret Key de PayPal
- `paypal_mode` - Modo (sandbox/live)

##### 📧 Configuración de Email (SMTP)
- `smtp_enabled` - Habilitar/deshabilitar SMTP
- `smtp_host` - Servidor SMTP (ej: smtp.gmail.com)
- `smtp_port` - Puerto SMTP (ej: 587)
- `smtp_username` - Usuario SMTP
- `smtp_password` - Contraseña SMTP (campo oculto)
- `smtp_from_email` - Email remitente
- `smtp_from_name` - Nombre remitente

##### ⭐ Programa de Lealtad
- `loyalty_enabled` - Habilitar/deshabilitar programa
- `loyalty_default_percentage` - % de comisión por defecto
- `loyalty_min_withdrawal` - Monto mínimo para retiro

##### 💰 Configuración Financiera
- `currency_symbol` - Símbolo de moneda (ej: MXN)
- `currency_code` - Código de moneda (ej: MXN)
- `tax_rate` - % de tasa de impuesto
- `tax_enabled` - Aplicar impuestos sí/no

##### 🌐 Información del Sitio
- `site_name` - Nombre del sitio público
- `site_logo` - URL del logo
- `site_description` - Descripción del sitio (textarea)
- `site_url` - URL del sitio web

##### 📅 Configuración de Suscripciones
- `trial_days` - Días del período gratuito
- `plan_monthly_price` - Precio plan mensual
- `plan_annual_price` - Precio plan anual
- `promo_enabled` - Activar precios promocionales
- `promo_monthly_price` - Precio promo mensual
- `promo_annual_price` - Precio promo anual
- `promo_start_date` - Fecha inicio promoción
- `promo_end_date` - Fecha fin promoción

##### 📱 Configuración de WhatsApp
- `whatsapp_enabled` - Habilitar chatbot
- `whatsapp_number` - Número de WhatsApp
- `whatsapp_api_key` - API Key de WhatsApp Business

##### 🏦 Cuentas Bancarias
- `bank_accounts` - JSON con array de cuentas

## 🎨 Elementos UI Nuevos

### Badges y Indicadores

#### Estado de Suscripción
```php
// Días restantes > 7: Badge verde
// Días restantes 1-7: Badge amarillo  
// Días restantes 0 o vencido: Badge rojo
```

#### Roles de Usuario
- Superadmin: Badge rojo
- Admin: Badge azul
- Manager/Otros: Badge gris

### Gráficas (Chart.js)

#### Tipos Implementados
1. **Line Chart** (línea)
   - Ingresos por día
   - Reservaciones por día
   - Tasa de ocupación

2. **Bar Chart** (barras)
   - Nuevos usuarios por día
   - Solicitudes de servicio por día

3. **Doughnut Chart** (dona)
   - Suscripciones por plan

#### Configuración Común
- Responsive: true
- maintainAspectRatio: false
- Altura fija: 200-250px
- Leyendas personalizadas
- Colores consistentes con Bootstrap

## 📊 Base de Datos

### Tablas Nuevas

#### `password_resets`
```sql
id, user_id, token, expires_at, used, created_at
```

#### `loyalty_program`
```sql
id, user_id, referral_code, total_referrals, 
total_earnings, available_balance, withdrawn_balance,
is_active, created_at, updated_at
```

#### `referrals`
```sql
id, referrer_id, referred_user_id, referral_code,
status, commission_percentage, commission_amount,
completed_at, created_at
```

#### `payment_transactions`
```sql
id, user_id, hotel_id, subscription_id, amount, currency,
payment_method, transaction_id, status, payment_data,
notes, created_at, completed_at
```

#### `activity_log`
```sql
id, user_id, action, entity_type, entity_id,
description, ip_address, user_agent, created_at
```

### Tabla Expandida

#### `global_settings`
45+ configuraciones organizadas por categoría:
- payment (4 configuraciones)
- email (7 configuraciones)
- loyalty (3 configuraciones)
- financial (4 configuraciones)
- site (4 configuraciones)
- subscription (8+ configuraciones)
- whatsapp (3 configuraciones)

### Columnas Nuevas en `users`
```sql
avatar, timezone, language, last_login
```

## 🔐 Seguridad

### Implementada
- ✅ Bcrypt para contraseñas (cost 12)
- ✅ Tokens únicos para recuperación (32 bytes)
- ✅ Expiración de tokens (1 hora)
- ✅ Validación de email única
- ✅ Sanitización de todos los inputs
- ✅ CSRF protection preparado
- ✅ Transacciones de BD con rollback
- ✅ Passwords ocultos en formularios
- ✅ Verificación de roles en cada acción

### Helper Functions de Seguridad
```php
sanitize($input)           // Limpia HTML y SQL
isValidEmail($email)       // Valida formato email
password_hash()            // Hash seguro bcrypt
password_verify()          // Verifica hash
generateToken($length)     // Token aleatorio seguro
verifyCsrfToken($token)   // Verifica CSRF
```

## 📧 Sistema de Emails

### Configuración Dinámica
Lee configuración desde `global_settings` automáticamente.

### Templates HTML
- Email de recuperación de contraseña
- Diseño responsive
- Botones de acción coloridos
- Footer automático

### Función Principal
```php
sendEmail($to, $subject, $body, $isHtml = true)
```

## 🎁 Programa de Lealtad

### Flujo Completo

1. **Activación**
   - Usuario va a `/profile`
   - Click en "Activar Programa de Lealtad"
   - Sistema genera código único de 8 caracteres

2. **Compartir**
   - Usuario copia código o enlace
   - Enlace formato: `/auth/register?ref=CODIGO`

3. **Registro Referido**
   - Nuevo usuario usa enlace con código
   - Sistema valida código
   - Crea entrada en tabla `referrals`
   - Estado: pending

4. **Comisión**
   - Al completarse pago del referido
   - Sistema calcula comisión (% configurable)
   - Actualiza balance del referidor
   - Cambia estado a: completed

5. **Retiro**
   - Admin/Superadmin procesa retiros
   - Mínimo configurable
   - Actualiza `withdrawn_balance`

## 🔄 Flujo de Recuperación de Contraseña

1. Usuario hace click en "¿Olvidaste tu contraseña?"
2. Ingresa su email
3. Sistema valida que el email existe
4. Genera token único de 32 bytes
5. Guarda token en `password_resets` con expiración 1 hora
6. Envía email con enlace que incluye token
7. Usuario hace click en enlace
8. Sistema valida que token existe y no ha expirado
9. Muestra formulario de nueva contraseña
10. Usuario ingresa y confirma nueva contraseña
11. Sistema valida contraseñas (mínimo 6 caracteres, coinciden)
12. Actualiza password con bcrypt
13. Marca token como usado
14. Redirige a login con mensaje de éxito

## 📱 Responsive Design

Todas las vistas nuevas son completamente responsive:
- Mobile First approach
- Grid de Bootstrap 5
- Tablas con scroll horizontal en móvil
- Cards apilables
- Formularios adaptables
- Gráficas responsivas (Chart.js)

## ✨ Features Destacados

### 1. Configuración Todo-en-Uno
Un solo panel para configurar TODO el sistema sin tocar código.

### 2. Gráficas Interactivas
Visualización de datos en tiempo real con filtros personalizables.

### 3. Sistema de Referidos Único
Cada usuario puede generar ingresos pasivos recomendando el sistema.

### 4. Dashboard Inteligente
Muestra información relevante según el rol del usuario.

### 5. Gestión Centralizada
Superadmin puede ver y gestionar TODO desde un solo lugar.

---

**Actualizado:** Diciembre 2024
**Versión:** 1.1.0
**Sistema:** MajorBot - Mayordomía Online
