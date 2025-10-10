# 🚀 Guía Rápida de Usuario - Nuevas Funcionalidades

**Versión:** 3.0 | **Fecha:** 2025-10-10

---

## 🎯 ¿Qué cambió?

### 1. Nueva Reservación - Un Solo Formulario ✨

**Antes:** Tenías que ir a Habitaciones, Mesas o Amenidades por separado  
**Ahora:** Un solo formulario para todo en **Reservaciones → Nueva Reservación**

### 2. Solicitudes de Servicio - Más Organizadas 📊

**Antes:** Escribías un título libre  
**Ahora:** Seleccionas el tipo del catálogo (Toallas, Limpieza, etc.)

---

## 📝 Cómo Crear una Nueva Reservación

### Paso 1: Acceder al Formulario
```
1. Ve a "Reservaciones"
2. Click en botón "Nueva Reservación"
```

### Paso 2: Seleccionar Tipo
```
Elige una opción:
• 🚪 Habitación
• 🍽️ Mesa  
• 🏊 Amenidad
```

### Paso 3: Seleccionar Recurso
```
Se cargarán automáticamente los recursos disponibles:
• Habitaciones: Número, tipo y precio
• Mesas: Número y capacidad
• Amenidades: Nombre y categoría
```

### Paso 4: Buscar o Crear Huésped
```
OPCIÓN A - Buscar Existente:
1. Escribe nombre, email o teléfono
2. Resultados aparecen automáticamente
3. Click en el huésped correcto

OPCIÓN B - Crear Nuevo:
1. Click en "Nuevo Huésped"
2. Llenar: Nombre, Email, Teléfono (10 dígitos)
```

### Paso 5: Completar Fechas
```
Para HABITACIÓN:
• Check-in: [Fecha]
• Check-out: [Fecha]

Para MESA o AMENIDAD:
• Fecha: [Fecha]
• Hora: [Hora]
• Personas: [Solo para mesas]
```

### Paso 6: Finalizar
```
1. Seleccionar Estado: Pendiente o Confirmada
2. Agregar notas (opcional)
3. Click "Crear Reservación"
```

**✅ ¡Listo! El recurso se bloqueará automáticamente**

---

## 📋 Cómo Crear una Solicitud de Servicio

### Paso 1: Ir al Formulario
```
1. Ve a "Solicitudes de Servicio"
2. Click "Nueva Solicitud"
```

### Paso 2: Seleccionar Tipo de Servicio
```
Elige del menú:
💧 Toallas
🍳 Menú / Room Service
👔 Conserje
🧹 Limpieza
🔧 Mantenimiento
🏊 Amenidades
🚗 Transporte
❓ Otro
```

### Paso 3: Completar Detalles
```
• Descripción breve: (Opcional) Ej: "Urgente" o "Para 2 personas"
• Prioridad: Baja / Normal / Alta / Urgente
• Habitación: Número (si aplica)
• Descripción detallada: Explica lo que necesitas
```

### Paso 4: Crear
```
Click "Crear Solicitud"
```

**✅ ¡Se asignará automáticamente si eres Admin/Manager/Hostess!**

---

## 🔍 Ver Solicitudes de Servicio

La lista ahora muestra:

```
┌────────────────────────────────────────────────────┐
│ TIPO DE SERVICIO │ DESCRIPCIÓN │ HUÉSPED │ HAB.  │
├────────────────────────────────────────────────────┤
│ 💧 Toallas       │ Urgente     │ Juan P. │ 101   │
│ 🍳 Room Service  │ Cena x2     │ María G.│ 205   │
│ 🧹 Limpieza      │ Extra       │ Pedro R.│ 310   │
└────────────────────────────────────────────────────┘
```

**Ventajas:**
- ✅ Identificación visual rápida con iconos
- ✅ Tipo estandarizado del catálogo
- ✅ Descripción adicional opcional
- ✅ Mejor para reportes y estadísticas

---

## ⚡ Atajos y Tips

### Búsqueda de Huéspedes
- 💡 Escribe mínimo 2 caracteres
- 💡 Puedes buscar por nombre, email o teléfono
- 💡 Los resultados aparecen al instante

### Crear Nuevo Huésped
- 💡 Teléfono debe ser 10 dígitos exactos
- 💡 Si el email ya existe, se usará ese huésped
- 💡 El huésped se crea automáticamente como "Guest"

### Validaciones Automáticas
- ✅ Campos requeridos según tipo de reservación
- ✅ Formato de email válido
- ✅ Teléfono de 10 dígitos
- ✅ Fechas lógicas (check-out después de check-in)

---

## ❓ Preguntas Frecuentes

### ¿Puedo crear reservaciones?
```
✅ SÍ si eres: Admin, Manager o Hostess
❌ NO si eres: Collaborator o Guest
```

### ¿Qué pasa si selecciono "Confirmada"?
```
El recurso se bloquea inmediatamente:
• Habitaciones: Del check-in al check-out
• Mesas: 2 horas desde la hora de reservación
• Amenidades: 2 horas desde la hora de reservación
```

### ¿Puedo cambiar el tipo de servicio después?
```
✅ SÍ - Ve a la solicitud y edítala
   (Solo Admin y Manager pueden editar)
```

### ¿Qué significa "Auto-asignado"?
```
Si eres Admin, Manager o Hostess:
• Cuando creas una solicitud de servicio
• Se te asigna automáticamente como responsable
• Aparecerás en la columna "Asignado a"
```

### ¿Qué pasa con el campo "Título"?
```
Ahora es "Descripción breve" y es OPCIONAL
• Úsalo para detalles adicionales
• El tipo principal viene del catálogo
• Ejemplo: Tipo: "Limpieza", Desc: "Urgente"
```

---

## 🆘 Solución de Problemas

### No aparecen recursos
```
Causa: No hay recursos disponibles de ese tipo
Solución: Contacta al administrador
```

### No encuentro un huésped
```
Causa: Puede no estar registrado
Solución: Usa "Nuevo Huésped" para crearlo
```

### Error al crear huésped
```
Causa: Teléfono incorrecto
Solución: Verifica que sean exactamente 10 dígitos
```

### No puedo crear reservación
```
Causa: No tienes permisos
Solución: Necesitas rol de Admin, Manager o Hostess
```

---

## 📱 Interfaz Simplificada

### Botones Principales

```
┌─────────────────────────────────────┐
│  Reservaciones                      │
├─────────────────────────────────────┤
│  [Nueva Reservación]                │ ← Click aquí
│                                     │
│  Filtros: Tipo | Estado | Buscar   │
│  ┌─────────────────────────────┐   │
│  │ Lista de Reservaciones      │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

```
┌─────────────────────────────────────┐
│  Solicitudes de Servicio            │
├─────────────────────────────────────┤
│  [Nueva Solicitud]                  │ ← Click aquí
│                                     │
│  Filtros: Estado | Prioridad       │
│  ┌─────────────────────────────┐   │
│  │ 💧 Toallas    │ Desc │ ...  │   │
│  │ 🍳 Room Serv  │ Desc │ ...  │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

---

## ⏱️ Tiempos de Respuesta

| Acción | Tiempo |
|--------|--------|
| Búsqueda de huéspedes | Instantáneo |
| Carga de recursos | < 1 segundo |
| Crear reservación | < 2 segundos |
| Crear solicitud | < 1 segundo |

---

## 🎓 Capacitación Recomendada

### Para Nuevos Usuarios
1. ✅ Leer esta guía rápida
2. ✅ Practicar crear 1 reservación de cada tipo
3. ✅ Practicar crear 2-3 solicitudes de servicio
4. ✅ Practicar búsqueda de huéspedes

### Para Administradores
1. ✅ Revisar documentación técnica
2. ✅ Verificar tipos de servicio del catálogo
3. ✅ Monitorear auto-asignaciones
4. ✅ Revisar reportes por tipo

---

## 📞 Contacto y Soporte

### Si tienes problemas:
1. Revisa esta guía
2. Verifica que tienes los permisos correctos
3. Contacta al administrador del sistema
4. Reporta con capturas de pantalla

### Para Sugerencias:
- Nuevos tipos de servicio
- Mejoras en la interfaz
- Funcionalidades adicionales

---

## 🎉 ¡Disfruta las Nuevas Funcionalidades!

**Recordatorios:**
- ✅ Un solo formulario para todo tipo de reservaciones
- ✅ Búsqueda instantánea de huéspedes
- ✅ Tipos de servicio organizados
- ✅ Auto-asignación de responsables
- ✅ Validaciones automáticas
- ✅ Bloqueo automático de recursos

**¡Gracias por usar el Sistema Mayordomo!** 🏨

---

**Versión:** 3.0  
**Última Actualización:** 2025-10-10  
**Estado:** ✅ Activo
