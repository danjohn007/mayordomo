# ⚡ GUÍA RÁPIDA - Ejecutar Actualización

## 🎯 ACCIÓN INMEDIATA REQUERIDA

Para activar todas las nuevas funcionalidades, ejecuta el siguiente script SQL:

```bash
mysql -u USUARIO -p aqh_mayordomo < database/fix_system_issues.sql
```

Reemplaza `USUARIO` con tu usuario de MySQL (comúnmente `root` o el usuario de tu hosting).

---

## 📱 Desde cPanel / phpMyAdmin

1. **Accede a phpMyAdmin** desde tu panel de control
2. **Selecciona la base de datos** `aqh_mayordomo`
3. **Ve a la pestaña "SQL"**
4. **Abre el archivo** `database/fix_system_issues.sql` en un editor de texto
5. **Copia TODO el contenido**
6. **Pégalo** en el área de texto de phpMyAdmin
7. **Haz clic en "Continuar"**
8. **Espera** a que termine (1-2 minutos)

---

## ✅ Verificación Rápida

Después de ejecutar el script, verifica que todo se instaló correctamente:

```sql
-- Ejecuta esta query para verificar
SELECT 'OK' as status WHERE (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'aqh_mayordomo' 
    AND TABLE_NAME IN ('role_permissions', 'system_notifications')
) = 2;
```

**Resultado esperado:** Debe mostrar "OK"

---

## 🔴 Si hay errores

### Error: "Table already exists"
✅ **Ignorar** - La tabla ya existe, continúa con el siguiente paso

### Error: "Column already exists"  
✅ **Ignorar** - El campo ya existe, el script es seguro

### Error: "Can't connect to MySQL server"
❌ **Revisar:**
- Usuario y contraseña correctos
- Base de datos `aqh_mayordomo` existe
- Servidor MySQL está corriendo

### Error: "Access denied"
❌ **Solución:**
- El usuario necesita permisos de ALTER, CREATE, INSERT
- Contacta a tu proveedor de hosting para otorgar permisos

---

## 📊 ¿Qué hace este script?

1. ✅ Agrega campo `description` a tabla `subscriptions`
2. ✅ Sincroniza precios de suscripciones con configuración
3. ✅ Crea tabla `role_permissions` para gestión de permisos
4. ✅ Crea tabla `system_notifications` para notificaciones
5. ✅ Agrega triggers automáticos para reservaciones
6. ✅ Crea vista `v_all_reservations` para consultas optimizadas
7. ✅ Inserta permisos por defecto para usuarios existentes

---

## 🚀 Después de ejecutar

1. **Accede al sistema** con tu usuario admin
2. **Verifica** que aparece el menú "Reservaciones"
3. **Verifica** que aparece el menú "Roles y Permisos"
4. **Configura** permisos para tus colaboradores
5. **Agrega** el archivo de sonido (ver `public/assets/sounds/README.md`)

---

## 📞 Soporte

Si tienes problemas:
1. Lee `INSTALACION_ACTUALIZACION.md` (guía completa paso a paso)
2. Lee `NUEVAS_FUNCIONALIDADES.md` (documentación de features)
3. Revisa los logs de MySQL/PHP
4. Verifica permisos de usuario de base de datos

---

## ⏱️ Tiempo estimado

- Backup de BD: 1-2 minutos
- Ejecución del script: 1-2 minutos
- Verificación: 30 segundos
- **Total: ~5 minutos**

---

## 🎉 ¡Listo!

Una vez ejecutado el script, todas las nuevas funcionalidades estarán activas:
- 📅 Módulo de Reservaciones
- 🔐 Gestión de Roles y Permisos
- 🔔 Sistema de Notificaciones con Sonido
- ✅ Bugs corregidos

**¡Disfruta las nuevas funcionalidades!**
