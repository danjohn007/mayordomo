# Fix: Error de Conexión al Cargar Recursos

## Problema
Al seleccionar un "Tipo de Reservación" (Habitación, Mesa o Amenidad) en el formulario de Nueva Reservación, el sistema mostraba el error "Error de conexión al cargar recursos" y no cargaba las opciones disponibles.

## Causa Raíz
El archivo `.htaccess` en la raíz del proyecto tenía reglas de reescritura que,  bajo ciertas configuraciones de Apache, podían interferir con el acceso directo a los endpoints de la API ubicados en `public/api/`.

Cuando el JavaScript intentaba cargar recursos mediante:
```javascript
fetch('<?= BASE_URL ?>/public/api/get_resources.php?type=room')
```

En algunos entornos de servidor, el proceso era:
1. La solicitud llegaba como `/majorbot/public/api/get_resources.php`
2. El `.htaccess` raíz evaluaba si el archivo existía
3. Dependiendo de la configuración del DocumentRoot y cómo Apache resolvía `REQUEST_FILENAME`, la verificación podía fallar
4. La solicitud se redirigía a `public/index.php?url=public/api/get_resources.php`
5. El front controller intentaba buscar un `PublicController` que no existía
6. Retornaba una página de error HTML (409 Conflict) en lugar de JSON
7. El JavaScript no podía parsear el HTML como JSON, generando el error "Error de conexión al cargar recursos"

## Solución
Se modificó el archivo `.htaccess` en la raíz para permitir acceso directo a los archivos de API:

```apache
RewriteEngine On

# Deny access to config and database directories
RewriteRule ^(config|database)/ - [F,L]

# Allow direct access to files in public/api directory
RewriteRule ^public/api/(.+)$ public/api/$1 [L]

# Redirect all requests to public/index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php?url=$1 [QSA,L]
```

La línea clave agregada es:
```apache
RewriteRule ^public/api/(.+)$ public/api/$1 [L]
```

Esta regla:
- Detecta cualquier solicitud que comience con `public/api/`
- La procesa ANTES de las otras reglas de reescritura
- El flag `[L]` (Last) detiene el procesamiento de más reglas para estas solicitudes
- Esto asegura que las solicitudes a la API nunca lleguen a la regla general que redirige a `public/index.php`
- Efectivamente "protege" las rutas de API de ser reescritas incorrectamente

## Archivos Modificados
- `.htaccess` - Reglas de reescritura actualizadas

## Archivos de API Verificados
Todos los siguientes endpoints ahora funcionan correctamente y retornan JSON:
- ✓ `public/api/get_resources.php` - Obtener habitaciones, mesas o amenidades
- ✓ `public/api/search_guests.php` - Buscar huéspedes existentes
- ✓ `public/api/check_phone.php` - Verificar disponibilidad de teléfono
- ✓ `public/api/validate_discount_code.php` - Validar códigos de descuento

## Pruebas Realizadas
1. ✓ Los endpoints de API retornan JSON correctamente (no HTML)
2. ✓ Las rutas normales (dashboard, reservations, etc.) siguen funcionando
3. ✓ Los directorios config y database siguen bloqueados
4. ✓ Todos los endpoints de API responden con Content-Type: application/json

## Impacto
- **Sin cambios en el código de la aplicación**: Solo se modificó la configuración de Apache
- **Compatibilidad**: La solución es compatible con la funcionalidad existente
- **Seguridad**: Se mantiene la protección de directorios sensibles (config, database)

## Próximos Pasos
Una vez desplegado el cambio en el servidor de producción, el formulario de Nueva Reservación debería:
1. Cargar correctamente las habitaciones cuando se seleccione "Habitación"
2. Cargar correctamente las mesas cuando se seleccione "Mesa"  
3. Cargar correctamente las amenidades cuando se seleccione "Amenidad"
4. Eliminar el mensaje de error "Error de conexión al cargar recursos"
