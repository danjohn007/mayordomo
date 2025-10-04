# Guía Rápida de Inicio - Cambios MajorBot

## 🚀 Inicio Rápido (5 minutos)

### Paso 1: Actualizar Base de Datos
```bash
cd /home/runner/work/mayordomo/mayordomo
mysql -u aqh_mayordomo -p aqh_mayordomo < database/add_missing_settings.sql
```

### Paso 2: Configurar el Sistema

1. **Abrir navegador** y ir a tu instalación de MajorBot
2. **Iniciar sesión como SuperAdmin**
3. **Ir al menú** → Configuración Global
4. **Scroll hasta el final** para ver las nuevas secciones:
   - Cuentas Bancarias para Depósitos
   - Términos y Condiciones

### Paso 3: Configurar Términos y Condiciones

En la sección **"Términos y Condiciones"**:
```
Ejemplo de términos básicos:

TÉRMINOS Y CONDICIONES DE USO - MAJORBOT

1. ACEPTACIÓN DE TÉRMINOS
Al utilizar MajorBot, usted acepta estos términos y condiciones.

2. USO DEL SERVICIO
- El servicio es para gestión hotelera
- Período de prueba gratuito de 30 días
- Acceso sujeto a suscripción activa

3. PRIVACIDAD Y DATOS
- Sus datos están protegidos
- No se comparten con terceros
- Uso exclusivo para funciones del sistema

4. RESPONSABILIDADES DEL USUARIO
- Mantener contraseñas seguras
- Uso apropiado del sistema
- Información veraz y actualizada

5. MODIFICACIONES
MajorBot se reserva el derecho de modificar estos términos.

Para más información: contacto@majorbot.com
```

### Paso 4: Configurar Cuentas Bancarias

En la sección **"Cuentas Bancarias para Depósitos"**:
```
Ejemplo de información bancaria:

BBVA Bancomer
Cuenta: 0123456789
CLABE: 012180001234567890
Titular: MajorBot S.A. de C.V.
RFC: MAJ123456ABC

Santander
Cuenta: 9876543210
CLABE: 014180009876543210
Titular: MajorBot S.A. de C.V.
RFC: MAJ123456ABC

Banamex
Cuenta: 5555666677
CLABE: 002180555566667777
Titular: MajorBot S.A. de C.V.
RFC: MAJ123456ABC
```

### Paso 5: Configurar Días de Prueba

En la misma página, sección **"Configuración de Suscripciones"**:
- Buscar: "Días del Periodo Gratuito"
- Cambiar valor (ejemplo: 30, 15, 60 días)
- Guardar cambios

### Paso 6: ✅ Guardar Todo
- Scroll hasta abajo
- Clic en **"Guardar Configuración"**
- Esperar mensaje de confirmación

---

## 🎯 Verificación Rápida

### ¿Funcionó? Verifica esto:

**✅ Login mejorado:**
1. Cerrar sesión
2. Ir a la página de login
3. ¿Ves el checkbox de términos y condiciones? ✓
4. ¿Ves el mensaje verde "¡Prueba gratis por N días!"? ✓

**✅ Menú SuperAdmin:**
1. Iniciar sesión como SuperAdmin
2. Abrir menú lateral (☰)
3. ¿Ves "Registro de Pagos"? ✓
4. ¿Ves "Programa de Lealtad"? ✓

**✅ Mi Perfil:**
1. Clic en tu nombre (arriba derecha)
2. ¿Ves opción "Mi Perfil"? ✓
3. Clic en "Mi Perfil"
4. ¿Se abre tu perfil con información? ✓

**✅ Dashboard Admin:**
1. Iniciar sesión como Admin (propietario de hotel)
2. ¿Las gráficas cargan correctamente? ✓
3. ¿No hay mensajes de "carga infinita"? ✓

---

## 📱 Nuevas Funcionalidades Disponibles

### Para Todos los Usuarios:
- 👤 **Mi Perfil** - Editar información personal
- 🔐 **Cambiar Contraseña** - Desde Mi Perfil
- 🎁 **Código de Referido** - Programa de lealtad

### Para Admin/Superadmin:
- 📊 **Dashboard Mejorado** - Gráficas sin errores
- 💳 **Historial de Pagos** - En Mi Perfil
- 📅 **Días Restantes** - Visible en dashboard y perfil

### Para SuperAdmin:
- 💰 **Registro de Pagos** - Nuevo menú
- ⭐ **Programa de Lealtad** - Nuevo menú
- ⚙️ **Configuración Ampliada** - Más opciones
- 🏦 **Cuentas Bancarias** - Configurables
- 📄 **Términos y Condiciones** - Configurables

---

## 🔧 Configuraciones Avanzadas

### Personalizar Mensaje de Prueba

1. Ir a: Configuración Global
2. Sección: "Configuración de Suscripciones"
3. Campo: "Días del Periodo Gratuito"
4. Cambiar número de días
5. Guardar

El mensaje se actualiza automáticamente en:
- Login: "¡Prueba gratis por X días!"
- Registro: "¡Prueba gratis por X días!"

### Desactivar Período de Prueba

Si deseas desactivar completamente:
1. Ir a: Configuración Global
2. Sección: "Configuración de Suscripciones"
3. Campo: "Días del Periodo Gratuito"
4. Poner: **0** (cero)
5. Guardar

El mensaje de prueba no se mostrará.

### Personalizar Términos y Condiciones

1. Ir a: Configuración Global
2. Scroll hasta: "Términos y Condiciones"
3. Escribir/Pegar tus términos
4. Guardar

Los términos aparecerán en:
- Modal del login (al hacer clic en el enlace)
- Proceso de registro

---

## 📞 Soporte Técnico

### ¿Algo no funciona?

**Problema: No veo el mensaje de prueba gratuita**
- Solución: Verificar que "Días del Periodo Gratuito" > 0

**Problema: No veo el checkbox de términos**
- Solución: Limpiar caché del navegador (Ctrl+F5)

**Problema: Error 404 en /superadmin/settings**
- Solución: Verificar que el archivo SuperadminController.php existe
- Verificar permisos de archivos

**Problema: Las gráficas no cargan**
- Solución: Verificar que hay datos en la base de datos
- Verificar conexión a Chart.js CDN

### Logs de Error

Si hay problemas, revisar:
```bash
# Logs de PHP
tail -f /var/log/apache2/error.log

# O si usa nginx:
tail -f /var/log/nginx/error.log
```

---

## 📚 Documentación Adicional

- `IMPLEMENTATION_NOTES.md` - Notas técnicas detalladas
- `CAMBIOS_IMPLEMENTADOS.md` - Resumen visual completo
- `database/add_missing_settings.sql` - Script de base de datos

---

## ✨ Tips Útiles

### 💡 Para Administradores
- Configura los términos antes de permitir nuevos registros
- Revisa el programa de lealtad regularmente
- Actualiza las cuentas bancarias cuando sea necesario

### 💡 Para Desarrolladores
- Todos los archivos PHP validados sin errores
- Rutas siguen patrón RESTful
- Helper functions disponibles en helpers.php
- Configuraciones en tabla global_settings

### 💡 Para Usuarios Finales
- Accede a "Mi Perfil" desde el menú de usuario
- Cambia tu contraseña regularmente
- Revisa tu código de referido en el perfil

---

**¿Listo para empezar?** 🚀

Sigue los 6 pasos de arriba y tu sistema estará completamente actualizado.

**Tiempo estimado:** 5-10 minutos  
**Dificultad:** ⭐ Fácil  
**Requiere reinicio:** ❌ No
