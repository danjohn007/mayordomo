# 📊 Diagrama del Sistema Superadmin

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          SISTEMA MAYORBOT                                │
│                         Multi-Hotel SaaS                                 │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                ┌───────────────────┴───────────────────┐
                │                                       │
        ┌───────▼────────┐                    ┌────────▼────────┐
        │   SUPERADMIN   │                    │   ADMIN LOCAL   │
        │   (Sistema)    │                    │  (Propietario)  │
        └───────┬────────┘                    └────────┬────────┘
                │                                       │
    ┌───────────┼───────────────┐                     │
    │           │               │                     │
    ▼           ▼               ▼                     ▼
┌────────┐  ┌────────┐    ┌────────┐           ┌──────────┐
│Gestión │  │Planes  │    │Métricas│           │ Mi Hotel │
│Hoteles │  │Suscr.  │    │Globales│           │          │
└────────┘  └────────┘    └────────┘           └─────┬────┘
                                                      │
                                    ┌─────────────────┼─────────────────┐
                                    │                 │                 │
                                    ▼                 ▼                 ▼
                              ┌──────────┐      ┌──────────┐     ┌──────────┐
                              │ MANAGER  │      │ HOSTESS  │     │COLABORADOR│
                              └──────────┘      └──────────┘     └──────────┘
```

## 🔐 Jerarquía de Roles

```
                    SUPERADMIN (Nivel 1)
                    ▪ Acceso Total
                    ▪ Gestión Multi-Hotel
                    ▪ Configuración Global
                           │
           ┌───────────────┴───────────────┐
           │                               │
      ADMIN LOCAL (Nivel 2)           ADMIN LOCAL
      Hotel Paradise                  Hotel Sunset
      ▪ Hotel propio                  ▪ Hotel propio
      ▪ Gestión completa              ▪ Gestión completa
           │                               │
      ┌────┴────┐                     ┌────┴────┐
      │         │                     │         │
   MANAGER   HOSTESS              MANAGER   HOSTESS
   (Nivel 3) (Nivel 3)            (Nivel 3) (Nivel 3)
      │         │                     │         │
      ▼         ▼                     ▼         ▼
COLABORADORES  GUEST            COLABORADORES  GUEST
  (Nivel 4)   (Nivel 5)           (Nivel 4)   (Nivel 5)
```

## 📋 Flujo de Registro

```
┌─────────────────────────────────────────────────────────────────┐
│                    REGISTRO PÚBLICO                              │
│              (Solo para Propietarios de Hoteles)                 │
└─────────────────────────────────────────────────────────────────┘
                            │
                            ▼
                 ┌──────────────────────┐
                 │  Formulario Registro │
                 ├──────────────────────┤
                 │ • Nombre del Hotel   │
                 │ • Datos personales   │
                 │ • Email              │
                 │ • Contraseña         │
                 │ • Plan de suscripción│
                 └──────────┬───────────┘
                            │
                ┌───────────▼───────────┐
                │   Validar Datos       │
                │ • Email único?        │
                │ • Contraseña válida?  │
                │ • Campos completos?   │
                └───────────┬───────────┘
                            │
                    ┌───────▼────────┐
                    │ TRANSACCIÓN BD │
                    └───────┬────────┘
                            │
            ┌───────────────┼───────────────┐
            │               │               │
            ▼               ▼               ▼
    ┌────────────┐  ┌────────────┐  ┌────────────┐
    │Crear Hotel │  │Crear Usuario│  │Vincular    │
    │en BD       │  │rol: 'admin' │  │Owner→Hotel │
    └────────────┘  └────────────┘  └────────────┘
            │               │               │
            └───────────────┼───────────────┘
                            │
                    ┌───────▼────────┐
                    │Activar Trial   │
                    │Subscription    │
                    └───────┬────────┘
                            │
                    ┌───────▼────────┐
                    │  COMMIT        │
                    │  (o ROLLBACK)  │
                    └───────┬────────┘
                            │
                    ┌───────▼────────┐
                    │✅ Registro     │
                    │   Exitoso      │
                    └────────────────┘
```

## 💰 Planes de Suscripción

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       PLANES DE SUSCRIPCIÓN                              │
└─────────────────────────────────────────────────────────────────────────┘

┌────────────────────┐  ┌────────────────────┐  ┌────────────────────┐
│   PLAN TRIAL       │  │   PLAN MENSUAL     │  │   PLAN ANUAL       │
├────────────────────┤  ├────────────────────┤  ├────────────────────┤
│ 💰 $0.00           │  │ 💰 $99.00/mes      │  │ 💰 $999.00/año     │
├────────────────────┤  ├────────────────────┤  ├────────────────────┤
│ ⏱️  30 días        │  │ 🔁 Recurrente      │  │ 💾 Pago único      │
├────────────────────┤  ├────────────────────┤  ├────────────────────┤
│ 🏨 1 hotel         │  │ 🏨 1 hotel         │  │ 🏨 3 hoteles       │
│ 🛏️  10 habitaciones│  │ 🛏️  50 habitaciones│  │ 🛏️  150 habitaciones│
│ 🍽️  10 mesas       │  │ 🍽️  30 mesas       │  │ 🍽️  80 mesas       │
│ 👥 5 personal      │  │ 👥 20 personal     │  │ 👥 50 personal     │
├────────────────────┤  ├────────────────────┤  ├────────────────────┤
│ 📧 Soporte Email   │  │ ⚡ Soporte Priority│  │ 🌟 Soporte 24/7    │
│ 📊 Reportes Básicos│  │ 📊 Reportes Avanzad│  │ 📊 Reportes Custom │
│ ❌ Sin integraciones│  │ ✅ Stripe, PayPal  │  │ ✅ Todas pasarelas │
│                    │  │                    │  │ ✅ SMS             │
│                    │  │                    │  │ ✅ Capacitación    │
└────────────────────┘  └────────────────────┘  └────────────────────┘

         ▲                       │                       │
         │                       │                       │
    Auto-Activa              Pago                    Pago
    en Registro            Mensual                  Anual
                                                  (16% dcto)

┌────────────────────────────────────────────────────────────────┐
│                    PLAN ENTERPRISE                              │
├────────────────────────────────────────────────────────────────┤
│ 💰 $2,999.00/año                                               │
├────────────────────────────────────────────────────────────────┤
│ 🏨 Hoteles ilimitados                                          │
│ 🛏️  Habitaciones ilimitadas                                    │
│ 🍽️  Mesas ilimitadas                                           │
│ 👥 Personal ilimitado                                          │
├────────────────────────────────────────────────────────────────┤
│ ✅ Soporte dedicado 24/7 con gestor de cuenta                  │
│ ✅ Reportes personalizados con BI                              │
│ ✅ Todas las integraciones de pago                             │
│ ✅ API de acceso                                               │
│ ✅ White label y personalización                               │
└────────────────────────────────────────────────────────────────┘
```

## 🗄️ Estructura de Base de Datos

```
┌─────────────────────────────────────────────────────────────────┐
│                      BASE DE DATOS                               │
└─────────────────────────────────────────────────────────────────┘

users                           hotels                    subscription_plans
├─ id                          ├─ id                      ├─ id
├─ email                       ├─ name                    ├─ name
├─ password (bcrypt)           ├─ owner_id ──┐           ├─ slug
├─ first_name                  ├─ email      │           ├─ price
├─ last_name                   ├─ phone      │           ├─ billing_cycle
├─ role (superadmin/admin)     ├─ address    │           ├─ trial_days
├─ hotel_id ──────────┐        ├─ subscription_status    ├─ max_hotels
├─ subscription_id    │        ├─ subscription_start     ├─ max_rooms_per_hotel
├─ is_active          │        ├─ subscription_end       ├─ max_tables_per_hotel
└─ timestamps         │        ├─ max_rooms              ├─ max_staff_per_hotel
                      │        ├─ max_tables             ├─ features (JSON)
                      │        ├─ max_staff              └─ timestamps
                      │        ├─ features (JSON)
                      │        ├─ timezone
                      │        ├─ currency
                      └────────┤ logo_url
                               └─ timestamps

global_settings                user_subscriptions         activity_log
├─ id                          ├─ id                      ├─ id
├─ setting_key (unique)        ├─ user_id                 ├─ user_id
├─ setting_value               ├─ subscription_id         ├─ hotel_id
├─ setting_type                ├─ start_date              ├─ action
├─ description                 ├─ end_date                ├─ entity_type
├─ category                    ├─ status                  ├─ entity_id
└─ updated_at                  └─ timestamps              ├─ description
                                                          ├─ ip_address
                                                          └─ created_at
```

## 🔄 Ciclo de Vida de Suscripción

```
                     NUEVO REGISTRO
                          │
                          ▼
              ┌───────────────────────┐
              │  TRIAL ACTIVATION     │
              │  (Automático)         │
              │  • Duration: 30 días  │
              │  • Status: 'trial'    │
              └───────────┬───────────┘
                          │
              ┌───────────▼───────────┐
              │   PERÍODO DE PRUEBA   │
              │   ✅ Acceso completo  │
              │   📧 Notificaciones   │
              └───────────┬───────────┘
                          │
          ┌───────────────┴───────────────┐
          │                               │
          ▼                               ▼
┌─────────────────┐            ┌──────────────────┐
│  CONVERTIR A    │            │   TRIAL EXPIRA   │
│  PLAN DE PAGO   │            │   (Sin pago)     │
├─────────────────┤            ├──────────────────┤
│ • Elige plan    │            │ • Bloqueo acceso │
│ • Procesa pago  │            │ • Notificación   │
│ • Status:active │            │ • Status:expired │
└────────┬────────┘            └──────────────────┘
         │
         ▼
┌─────────────────┐
│ SUSCRIPCIÓN     │
│ ACTIVA          │
├─────────────────┤
│ • Acceso total  │
│ • Facturación   │
│ • Renovaciones  │
└────────┬────────┘
         │
         │ (Cada mes/año)
         │
         ▼
┌─────────────────┐
│ RENOVACIÓN      │
│ AUTOMÁTICA      │
├─────────────────┤
│ • Cargo auto    │
│ • Notificación  │
│ • Comprobante   │
└─────────────────┘
```

## 🎛️ Panel de Control Superadmin

```
┌─────────────────────────────────────────────────────────────────┐
│                 DASHBOARD SUPERADMIN                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────┬─────────────────┬─────────────────┬──────────┐
│   📊 MÉTRICAS GLOBALES                                          │
├─────────────────┼─────────────────┼─────────────────┼──────────┤
│ 🏨 Total Hoteles│ 👥 Usuarios     │ 💰 Ingresos    │ 📈 Ocup. │
│      125        │    1,458        │  $124,500      │   78%    │
└─────────────────┴─────────────────┴─────────────────┴──────────┘

┌──────────────────────────────────────────────────────────────────┐
│   🎯 ACCIONES RÁPIDAS                                            │
├──────────────────────────────────────────────────────────────────┤
│  [➕ Nuevo Hotel]  [👤 Nuevo Admin]  [⚙️ Configuraciones]       │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│   📋 GESTIÓN DE HOTELES                                          │
├─────────┬──────────────┬─────────────┬──────────┬───────────────┤
│ ID      │ Hotel        │ Propietario │ Plan     │ Estado        │
├─────────┼──────────────┼─────────────┼──────────┼───────────────┤
│ #001    │ Paradise     │ Juan López  │ Anual    │ ✅ Activo     │
│ #002    │ Sunset Beach │ Ana Martín  │ Mensual  │ ✅ Activo     │
│ #003    │ Grand Plaza  │ Carlos Ruiz │ Trial    │ ⏱️ Prueba     │
│ #004    │ Ocean View   │ María García│ Anual    │ ⚠️ Por vencer │
└─────────┴──────────────┴─────────────┴──────────┴───────────────┘

┌──────────────────────────────────────────────────────────────────┐
│   💳 GESTIÓN DE SUSCRIPCIONES                                    │
├──────────────────┬───────────┬──────────────┬────────────────────┤
│ Plan             │ Activos   │ Ingresos/mes │ Acciones           │
├──────────────────┼───────────┼──────────────┼────────────────────┤
│ Trial            │    45     │     $0       │ [Configurar]       │
│ Mensual          │    65     │  $6,435      │ [Ver detalles]     │
│ Anual            │    12     │  $11,988     │ [Ver detalles]     │
│ Enterprise       │     3     │  $8,997      │ [Ver detalles]     │
└──────────────────┴───────────┴──────────────┴────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│   ⚙️ CONFIGURACIONES GLOBALES                                    │
├──────────────────────────────────────────────────────────────────┤
│ • Período de prueba: [30] días                    [Guardar]     │
│ • Auto-activar trial: [✓] Sí  [ ] No             [Guardar]     │
│ • Bloquear al expirar: [✓] Sí  [ ] No            [Guardar]     │
│ • Notificar antes de: [7] días                    [Guardar]     │
│ • Pasarelas activas: [✓] Stripe [✓] PayPal [ ] MercadoPago    │
└──────────────────────────────────────────────────────────────────┘
```

## 🔐 Modelo de Seguridad

```
┌────────────────────────────────────────────────────────────────┐
│                    SEGURIDAD DEL SISTEMA                        │
└────────────────────────────────────────────────────────────────┘

AUTENTICACIÓN
├─ Contraseñas con bcrypt (cost 12)
├─ Hash único por usuario
├─ Validación de email único
└─ Sesiones seguras con httponly

AUTORIZACIÓN
├─ Control de acceso basado en roles (RBAC)
├─ Permisos granulares por módulo
├─ Verificación en cada acción
└─ Aislamiento de datos por hotel

AUDITORÍA
├─ Registro en activity_log
│  ├─ Quién: user_id
│  ├─ Qué: action + description
│  ├─ Cuándo: timestamp
│  ├─ Dónde: ip_address
│  └─ Contexto: data (JSON)
└─ Trazabilidad completa

TRANSACCIONES
├─ BEGIN TRANSACTION
├─ Operaciones atómicas
├─ COMMIT en éxito
└─ ROLLBACK en error
```

## 📈 Métricas y Reportes

```
┌────────────────────────────────────────────────────────────────┐
│                    ESTADÍSTICAS GLOBALES                        │
└────────────────────────────────────────────────────────────────┘

global_statistics
├─ Diarias   → Ocupación, ingresos, reservas
├─ Semanales → Tendencias, comparativas
├─ Mensuales → Crecimiento, proyecciones
└─ Anuales   → Resúmenes, análisis

hotel_statistics
├─ Por hotel → Métricas individuales
├─ Comparativas → Benchmarking entre hoteles
├─ Rankings → Mejores/peores performers
└─ Alertas → Hoteles en riesgo

Reportes Disponibles
├─ 📊 Ocupación por periodo
├─ 💰 Ingresos y facturación
├─ 📈 Crecimiento de suscripciones
├─ 👥 Usuarios activos
├─ 🏨 Performance por hotel
└─ 💳 Estado de pagos
```

## 🌐 Arquitectura Multi-Tenant

```
          ┌─────────────────────────────────┐
          │   APLICACIÓN MAYORBOT (SaaS)    │
          └───────────────┬─────────────────┘
                          │
          ┌───────────────┴─────────────────┐
          │                                 │
    ┌─────▼─────┐                    ┌──────▼──────┐
    │  HOTEL A  │                    │   HOTEL B   │
    │  (Tenant) │                    │   (Tenant)  │
    └─────┬─────┘                    └──────┬──────┘
          │                                 │
    ┌─────┴─────┐                    ┌──────┴──────┐
    │   DB      │                    │     DB      │
    │ hotel_id=1│                    │  hotel_id=2 │
    └───────────┘                    └─────────────┘

Aislamiento de Datos:
• WHERE hotel_id = current_hotel_id
• Índices en hotel_id para performance
• Foreign keys para integridad
• Session tracking de hotel actual
```

---

**Versión:** 1.1.0  
**Fecha:** 2024  
**Estado:** ✅ Documentado y Funcional
