# 🎨 Guía Visual - MajorBot v1.1.0

## 📸 Descripción de Interfaces Implementadas

Este documento describe las interfaces visuales implementadas en el sistema.

---

## 🔐 Sistema de Autenticación

### Login Mejorado (`/auth/login`)

**Cambios:**
- ✅ Enlace "¿Olvidaste tu contraseña?" agregado arriba del botón de login
- ✅ Icono: `bi-question-circle`
- ✅ Estilo: texto muted pequeño, alineado a la derecha

**Elementos:**
```
┌─────────────────────────────────┐
│    [Icono Building]             │
│        MajorBot                 │
│  Sistema de Mayordomía Online   │
│                                 │
│  Email: [____________]          │
│  Contraseña: [________]         │
│                                 │
│  ¿Olvidaste tu contraseña? →    │
│                                 │
│  [Iniciar Sesión]              │
│                                 │
│  ¿No tienes cuenta? Regístrate  │
└─────────────────────────────────┘
```

---

### Recuperar Contraseña (`/auth/forgotPassword`)

**Elementos:**
```
┌─────────────────────────────────┐
│      [Icono Key Grande]         │
│    Recuperar Contraseña         │
│  Ingresa tu email para recibir  │
│     instrucciones               │
│                                 │
│  Email: [_______________]       │
│  Te enviaremos un enlace...     │
│                                 │
│  [Enviar Enlace]               │
│                                 │
│  ← Volver al inicio de sesión   │
└─────────────────────────────────┘
```

**Características:**
- Card centrada con sombra
- Icono de llave (3rem)
- Texto de ayuda descriptivo
- Botón primario con icono de envío

---

### Restablecer Contraseña (`/auth/resetPassword?token=XXX`)

**Elementos:**
```
┌─────────────────────────────────┐
│   [Icono Shield-Lock Grande]    │
│   Restablecer Contraseña        │
│   Ingresa tu nueva contraseña   │
│                                 │
│  Nueva Contraseña: [_______]    │
│  Mínimo 6 caracteres            │
│                                 │
│  Confirmar: [______________]    │
│                                 │
│  [Restablecer Contraseña]      │
│                                 │
│  ← Volver al inicio de sesión   │
└─────────────────────────────────┘
```

**Validación:**
- Si token inválido o expirado, muestra alerta de error
- Enlace para solicitar nuevo token

---

### Registro Mejorado (`/auth/register`)

**Nuevo Elemento:**
```
┌─────────────────────────────────┐
│    [Icono Building]             │
│    Registrar Hotel              │
│  Registro para Propietarios y   │
│  Administradores de Hoteles     │
│                                 │
│  ┌───────────────────────────┐  │
│  │ 🎁 ¡Prueba gratis por     │  │
│  │    30 días!               │  │
│  │ Puedes usar MajorBot      │  │
│  │ completamente gratis...   │  │
│  └───────────────────────────┘  │
│                                 │
│  [Formulario de registro...]    │
└─────────────────────────────────┘
```

**Alerta:**
- Color: Verde (alert-success)
- Icono: `bi-gift`
- Texto dinámico según configuración
- Muestra días de prueba desde global_settings

---

## 👤 Mi Perfil

### Vista Completa (`/profile`)

**Layout:**
```
┌─────────────────────────────────────────────┐
│  Mi Perfil                                  │
├──────────────────┬──────────────────────────┤
│ Información      │ Cambiar Contraseña       │
│ Personal         │                          │
│                  │ Contraseña Actual: [___] │
│ Nombre: [____]   │ Nueva Contraseña: [___]  │
│ Apellido: [___]  │ Confirmar: [__________]  │
│ Email: [______]  │                          │
│ Teléfono: [___]  │ [Cambiar Contraseña]     │
│ Rol: Admin       │                          │
│                  │                          │
│ [Actualizar]     │                          │
├──────────────────┴──────────────────────────┤
│ Plan Activo (Admin)                         │
│ Plan: Mensual | Precio: $499.00             │
│ Fecha Fin: 01/01/2025                       │
│ Días Restantes: [15 días] (badge amarillo) │
│ [Actualizar Plan]                           │
├─────────────────────────────────────────────┤
│ Programa de Lealtad                         │
│ Código: ABC12345 [Copiar]                   │
│ Enlace: http://...?ref=ABC12345 [Copiar]   │
│                                             │
│  ┌───────┬──────────┬────────────┐          │
│  │  10   │ $1,500   │  $1,200    │          │
│  │ Refs  │  Total   │ Disponible │          │
│  └───────┴──────────┴────────────┘          │
├─────────────────────────────────────────────┤
│ Historial de Pagos (Admin)                  │
│ [Tabla con últimos 10 pagos]                │
└─────────────────────────────────────────────┘
```

**Características:**
- Grid responsive (col-lg-6)
- Cards separadas por sección
- Badges coloridos para días restantes
- Botones de copiar código/enlace (JavaScript)
- Tabla de pagos con scroll horizontal en móvil

---

## 👑 Dashboard Superadmin

### Vista Principal (`/superadmin`)

**Layout:**
```
┌────────────────────────────────────────────────┐
│ Dashboard Superadmin  [Filtros de Fecha]      │
├───────┬────────┬────────┬────────┬────────────┤
│ 🏨 50 │ 👥 250 │ 💳 200 │ 💰 $50K│ 🆕 5 │⭐ 80│
│Hoteles│Usuarios│Suscr.  │Ingresos│Nuevos│Loyal│
└───────┴────────┴────────┴────────┴────────────┘

┌────────────────┬────────────────┬──────────────┐
│ [Gráfica 1]    │ [Gráfica 2]    │ [Gráfica 3]  │
│ Ingresos/día   │ Usuarios/día   │ Suscr./Plan  │
│ (línea)        │ (barras)       │ (dona)       │
└────────────────┴────────────────┴──────────────┘

┌─────────────────────────────────────────────┐
│ Acciones Rápidas                            │
│ [Hoteles] [Usuarios] [Suscripciones] [⚙️]  │
└─────────────────────────────────────────────┘
```

**Tarjetas de Estadísticas:**
1. **Hoteles** - Fondo azul (primary)
2. **Usuarios** - Fondo verde (success)
3. **Suscripciones** - Fondo celeste (info)
4. **Ingresos** - Fondo amarillo (warning)
5. **Nuevos Hoteles** - (adicional)
6. **Miembros Lealtad** - Fondo rojo (danger)

**Gráficas (Chart.js):**
- Altura: 250px cada una
- Responsive: true
- Tooltips habilitados
- Colores consistentes con Bootstrap

---

### Gestión de Hoteles (`/superadmin/hotels`)

**Tabla:**
```
┌────────────────────────────────────────────────┐
│ ID│Nombre Hotel │Email    │Propietario│Usuarios│
├───┼─────────────┼─────────┼───────────┼────────┤
│ 1 │Hotel Paradise│h@..com  │Juan Pérez │   5    │
│   │             │         │j@..com    │ [info] │
├───┼─────────────┼─────────┼───────────┼────────┤
│ 2 │Beach Resort │b@..com  │María López│   8    │
│   │             │         │m@..com    │ [info] │
└───┴─────────────┴─────────┴───────────┴────────┘
         [Paginación: 1 2 3 4 5 >]
```

**Características:**
- Tabla responsive con scroll
- Badge de número de usuarios
- Estado activo/inactivo con badge
- Botones de acción (ver, editar)
- Paginación (20 items por página)

---

### Configuración Global (`/superadmin/settings`)

**Layout:**
```
┌──────────────────────────────────────────────┐
│ Configuración Global del Sistema             │
├──────────────────────────────────────────────┤
│ 💳 Configuración de Pagos                    │
│ ┌──────────────┬──────────────────────────┐  │
│ │ PayPal       │ Client ID │ Secret       │  │
│ │ Habilitado   │ [______]  │ [________]   │  │
│ └──────────────┴──────────────────────────┘  │
├──────────────────────────────────────────────┤
│ 📧 Configuración de Email (SMTP)             │
│ ┌──────────────┬──────────────────────────┐  │
│ │ SMTP Host    │ Puerto │ Usuario         │  │
│ │ [_________]  │ [____] │ [__________]    │  │
│ └──────────────┴──────────────────────────┘  │
├──────────────────────────────────────────────┤
│ ⭐ Programa de Lealtad                       │
│ [% Comisión] [Mínimo Retiro]                │
├──────────────────────────────────────────────┤
│ 💰 Configuración Financiera                  │
│ [Moneda] [Código] [% Impuesto]              │
├──────────────────────────────────────────────┤
│ 🌐 Información del Sitio                     │
│ [Nombre] [Logo URL] [Descripción]           │
├──────────────────────────────────────────────┤
│ 📅 Configuración de Suscripciones            │
│ [Días Trial] [Precio Mensual] [Precio Anual]│
│ [Promociones con fechas]                     │
├──────────────────────────────────────────────┤
│ 📱 WhatsApp                                  │
│ [Número] [API Key]                          │
├──────────────────────────────────────────────┤
│            [Guardar]  [Cancelar]             │
└──────────────────────────────────────────────┘
```

**Características:**
- Cards por categoría con colores distintivos
- Campos de texto, número, fecha según tipo
- Passwords ocultos
- Validación en frontend
- Feedback visual al guardar

---

## 🏨 Dashboard Admin

### Vista Mejorada (`/dashboard`)

**Nuevo Elemento - Tarjeta de Suscripción:**
```
┌─────────────────────────────────────────────┐
│ 💳 Suscripción Activa    [Ver Mi Perfil]   │
├─────────────┬──────────┬─────────┬─────────┤
│ Plan:       │ Precio:  │ Fecha:  │ Días:   │
│ Profesional │ $999.00  │ 31/12/24│ 🟢 15   │
└─────────────┴──────────┴─────────┴─────────┘
```

**Color del badge según días:**
- 🟢 Verde: > 7 días
- 🟡 Amarillo: 1-7 días
- 🔴 Rojo: 0 o vencido

**Sección de Gráficas:**
```
┌─────────────────────────────────────────────┐
│ 📊 Estadísticas      [🗓️ Filtros de Fecha] │
├──────────────┬──────────────┬──────────────┤
│ [Gráfica 1]  │ [Gráfica 2]  │ [Gráfica 3]  │
│ Reservaciones│ Solicitudes  │ Tasa         │
│ por día      │ de servicio  │ de ocupación │
│ (línea)      │ (barras)     │ (línea %)    │
└──────────────┴──────────────┴──────────────┘
```

**Filtros:**
- Fecha inicio: campo date
- Fecha fin: campo date
- Botón filtrar con icono
- Default: mes actual (01/dic - hoy)

---

## 🎨 Elementos UI Comunes

### Badges de Estado

**Suscripciones:**
- `active` - Verde
- `expired` - Gris
- `cancelled` - Rojo

**Pagos:**
- `completed` - Verde
- `pending` - Amarillo
- `failed` - Rojo

**Usuarios:**
- `is_active=1` - Verde "Activo"
- `is_active=0` - Rojo "Inactivo"

**Roles:**
- `superadmin` - Rojo
- `admin` - Azul
- `manager` - Celeste
- `otros` - Gris

### Botones de Acción

**Primarios:**
- 🟦 Azul - Guardar, Actualizar
- 🟩 Verde - Confirmar, Completar
- 🟨 Amarillo - Cambiar, Editar
- 🟥 Rojo - Eliminar, Cancelar

**Secundarios:**
- ⬜ Gris - Volver, Cancelar
- 🟦 Outline - Ver detalles, Info

### Iconos (Bootstrap Icons)

- `bi-building` - Hoteles
- `bi-people` - Usuarios
- `bi-credit-card` - Suscripciones
- `bi-cash-stack` - Pagos
- `bi-star` - Programa de lealtad
- `bi-gear` - Configuración
- `bi-speedometer2` - Dashboard
- `bi-graph-up` - Gráficas
- `bi-envelope` - Email
- `bi-key` - Contraseña
- `bi-shield-lock` - Seguridad
- `bi-person-circle` - Perfil

---

## 📱 Responsive Design

### Breakpoints

- **Desktop** (≥992px): Grid de 3-4 columnas
- **Tablet** (768-991px): Grid de 2 columnas
- **Mobile** (<768px): 1 columna, stack vertical

### Adaptaciones Móviles

**Tablas:**
- Scroll horizontal automático
- Columnas priorizadas visibles
- Resto con overflow scroll

**Cards:**
- Stack vertical en móvil
- 100% width
- Padding reducido

**Gráficas:**
- Mantienen aspect ratio
- Height reducido en móvil
- Labels simplificados

**Formularios:**
- Campos full-width
- Botones apilados
- Labels encima de inputs

---

## 🎨 Paleta de Colores

### Bootstrap 5 Colors

```
Primary:   #0d6efd (Azul)
Secondary: #6c757d (Gris)
Success:   #198754 (Verde)
Danger:    #dc3545 (Rojo)
Warning:   #ffc107 (Amarillo)
Info:      #0dcaf0 (Celeste)
Light:     #f8f9fa (Blanco)
Dark:      #212529 (Negro)
```

### Uso en el Sistema

- **Primary**: Botones principales, enlaces
- **Success**: Estados positivos, confirmaciones
- **Warning**: Alertas, advertencias
- **Danger**: Errores, eliminaciones
- **Info**: Información, estadísticas
- **Secondary**: Elementos secundarios

---

## ✨ Animaciones y Transiciones

### Bootstrap 5 Utilities

- Fade in/out en modales
- Hover effects en botones
- Smooth scroll en tablas
- Tooltips con fade
- Alerts dismissible con fade

### Chart.js Animations

- Animación de entrada de datos
- Transiciones suaves al filtrar
- Hover effects en puntos/barras
- Tooltips animados

---

## 🔍 UX Considerations

### Feedback Visual

- ✅ Mensajes de éxito (verde)
- ⚠️ Mensajes de advertencia (amarillo)
- ❌ Mensajes de error (rojo)
- ℹ️ Mensajes informativos (azul)

### Loading States

- Botones con spinners al procesar
- Placeholders en carga de datos
- Skeleton screens sugeridos

### Accesibilidad

- Labels en todos los inputs
- Alt text en iconos importantes
- Contraste de colores adecuado
- Focus visible en elementos

---

## 📸 Capturas Sugeridas

Para documentación visual completa, se sugiere tomar screenshots de:

1. ✅ Login con enlace de recuperación
2. ✅ Formulario de recuperación de contraseña
3. ✅ Registro con mensaje de prueba gratuita
4. ✅ Mi Perfil completo (usuario normal)
5. ✅ Mi Perfil con suscripción (admin)
6. ✅ Dashboard Superadmin con gráficas
7. ✅ Configuración Global completa
8. ✅ Gestión de Hoteles
9. ✅ Gestión de Usuarios
10. ✅ Programa de Lealtad
11. ✅ Dashboard Admin con gráficas
12. ✅ Mobile view de cualquier pantalla

---

**Nota:** Todas las interfaces son completamente funcionales y responsive.
Las descripciones aquí son representaciones textuales de las interfaces reales.

---

**Última Actualización:** Diciembre 2024
**Versión:** 1.1.0
**Sistema:** MajorBot - Mayordomía Online
