# Configuración SMTP del Hotel

Este documento describe la nueva funcionalidad de configuración SMTP agregada al sistema de Mayordomía.

## Descripción General

Se ha implementado la capacidad de configurar los ajustes SMTP del servidor de correo electrónico desde la interfaz de usuario en "Configuraciones del Hotel". Esto permite que cada hotel pueda personalizar su configuración de correo electrónico para el envío de notificaciones de reservaciones y mailings futuros.

## Archivos Modificados

### 1. Base de Datos
- **`database/add_smtp_settings.sql`**: Script SQL para agregar los campos de configuración SMTP a la tabla `hotel_settings`

Los siguientes campos se agregan por cada hotel:
- `smtp_enabled`: Habilitar/deshabilitar el envío de correos
- `smtp_host`: Servidor SMTP (ej: ranchoparaisoreal.com)
- `smtp_port`: Puerto SMTP (465 para SSL, 587 para TLS)
- `smtp_username`: Usuario para autenticación SMTP
- `smtp_password`: Contraseña del correo
- `smtp_encryption`: Tipo de encriptación (ssl/tls)
- `smtp_from_email`: Correo del remitente
- `smtp_from_name`: Nombre del remitente

### 2. Vista (Frontend)
- **`app/views/settings/index.php`**: Agregada nueva sección "Configuración SMTP del Correo" con formulario completo

La nueva sección incluye:
- Switch para habilitar/deshabilitar SMTP
- Campos para servidor, puerto y encriptación
- Campos para usuario y contraseña
- Campos para configurar remitente
- Información sobre puertos y tipos de encriptación

### 3. Controlador
- **`app/controllers/SettingsController.php`**: Agregada lógica para guardar las configuraciones SMTP

El método `save()` ahora procesa y guarda:
- Todos los campos SMTP del formulario
- Validación y sanitización de datos
- Almacenamiento en la tabla `hotel_settings`

### 4. Servicio de Email
- **`app/services/EmailService.php`**: Modificado para usar configuración desde base de datos

Cambios principales:
- Constructor ahora acepta `$hotelId` como parámetro opcional
- Lee configuración SMTP desde `hotel_settings` por hotel
- Usa valores de configuración dinámica en lugar de constantes

### 5. Configuración
- **`config/email.php`**: Actualizada función `getEmailSettings()` para priorizar configuración de base de datos

Comportamiento:
- Primero busca configuración en `hotel_settings` por hotel
- Si no encuentra configuración, usa valores por defecto (fallback)
- Cachea resultados para optimizar rendimiento

### 6. Controladores que usan EmailService
- **`app/controllers/ReservationsController.php`**: Actualizado para pasar `hotelId` al EmailService
- **`app/controllers/ChatbotController.php`**: Actualizado para pasar `hotelId` al EmailService

## Configuración Predeterminada

Los valores predeterminados configurados son:

```
Servidor SMTP: mail.ranchoparaisoreal.com
Puerto: 465
Encriptación: SSL
Usuario: reservaciones@ranchoparaisoreal.com
Contraseña: Danjohn007
Correo Remitente: reservaciones@ranchoparaisoreal.com
Nombre Remitente: Rancho Paraíso Real - Reservaciones
```

### Información Técnica SMTP

**Puertos disponibles:**
- **Puerto 465 (SSL)**: Conexión segura con SSL/TLS desde el inicio
- **Puerto 587 (TLS)**: Conexión STARTTLS (TLS después de conexión inicial)
- **Puerto 993 (IMAP)**: Solo para recibir correos (no se usa para envío)
- **Puerto 995 (POP3)**: Solo para recibir correos (no se usa para envío)

## Instrucciones de Instalación

### Paso 1: Ejecutar migración de base de datos

```bash
mysql -u [usuario] -p [nombre_base_datos] < database/add_smtp_settings.sql
```

O ejecutar el script SQL directamente desde tu gestor de base de datos (phpMyAdmin, MySQL Workbench, etc.)

### Paso 2: Verificar instalación

Accede a la interfaz de administración:
1. Inicia sesión como administrador del hotel
2. Ve a "Configuraciones del Hotel"
3. Verás la nueva sección "Configuración SMTP del Correo"
4. Los campos deben estar prellenados con los valores predeterminados

### Paso 3: Personalizar configuración (opcional)

Si necesitas cambiar la configuración:
1. Modifica los campos según tus credenciales SMTP
2. Haz clic en "Guardar Configuraciones"
3. El sistema comenzará a usar la nueva configuración inmediatamente

## Uso

### Configurar SMTP desde la interfaz

1. Navega a: **Panel de Control → Configuraciones del Hotel**
2. Busca la sección: **"Configuración SMTP del Correo"**
3. Completa o modifica los campos:
   - Habilita/deshabilita el envío de correos con el switch
   - Configura el servidor SMTP y puerto
   - Selecciona el tipo de encriptación (SSL/TLS)
   - Ingresa las credenciales del correo
   - Define el remitente que aparecerá en los emails
4. Haz clic en **"Guardar Configuraciones"**

### Envío de Correos

El sistema enviará correos automáticamente cuando:
- Se crea una nueva reservación (notificación sin PIN)
- Se confirma una reservación (notificación con PIN de confirmación)
- Se requiere cualquier otro tipo de notificación por email

Los correos se enviarán usando la configuración SMTP del hotel correspondiente.

## Seguridad

### Contraseñas
- Las contraseñas SMTP se almacenan en texto plano en la base de datos
- **Recomendación**: Considera implementar encriptación para las contraseñas en el futuro
- Limita el acceso a la base de datos solo a usuarios autorizados

### Permisos
- Solo usuarios con rol "admin" pueden modificar la configuración SMTP
- La configuración es específica por hotel (multi-tenant)

## Troubleshooting

### Los correos no se envían

1. Verifica que `smtp_enabled` esté activado
2. Comprueba que las credenciales SMTP sean correctas
3. Verifica que el puerto y tipo de encriptación coincidan con tu servidor
4. Revisa los logs del servidor (`error_log`) para ver errores específicos
5. Asegúrate de que el servidor de correo permita conexiones desde tu IP

### Error de autenticación

- Verifica que el usuario y contraseña sean correctos
- Algunos servidores requieren habilitar "acceso de aplicaciones menos seguras"
- Contacta a tu proveedor de email si el problema persiste

### Timeout o conexión rechazada

- Verifica que el firewall permita conexiones al puerto SMTP
- Confirma que el servidor SMTP esté en línea y disponible
- Prueba con diferentes puertos (465 o 587)

## Mejoras Futuras

Posibles mejoras a considerar:

1. **Encriptación de contraseñas**: Implementar encriptación AES para las contraseñas SMTP
2. **Prueba de conexión**: Agregar botón para probar la configuración SMTP antes de guardar
3. **Logs de emails**: Tabla para registrar todos los correos enviados
4. **Plantillas personalizables**: Permitir personalizar las plantillas de email por hotel
5. **Múltiples cuentas**: Soporte para diferentes cuentas según el tipo de notificación

## Soporte

Para preguntas o problemas relacionados con esta funcionalidad, contacta al equipo de desarrollo o revisa la documentación del sistema.

---

**Última actualización**: Noviembre 2025  
**Versión**: 1.0
