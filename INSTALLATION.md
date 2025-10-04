# Guía de Instalación MajorBot

## Instalación Rápida (5 minutos)

### Paso 1: Preparar el Entorno

```bash
# Verificar versión de PHP
php -v  # Debe ser 7.0 o superior

# Verificar que MySQL esté corriendo
mysql --version
```

### Paso 2: Clonar o Descargar

```bash
# Opción A: Git
git clone https://github.com/danjohn007/mayordomo.git
cd mayordomo

# Opción B: Descargar ZIP y extraer
```

### Paso 3: Configurar Base de Datos

```bash
# Entrar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE majorbot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importar esquema
mysql -u root -p majorbot_db < database/schema.sql

# Importar datos de ejemplo
mysql -u root -p majorbot_db < database/sample_data.sql
```

### Paso 4: Configurar Credenciales

Editar `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'majorbot_db');
define('DB_USER', 'root');
define('DB_PASS', 'tu_password');
```

### Paso 5: Configurar Apache

**Habilitar mod_rewrite:**

```bash
# Linux/Mac
sudo a2enmod rewrite
sudo systemctl restart apache2

# Windows XAMPP - ya viene habilitado
```

**Configurar permisos (Linux/Mac):**

```bash
chmod -R 755 /var/www/html/mayordomo
```

### Paso 6: Probar Instalación

Abrir navegador:

```
http://localhost/mayordomo/test_connection.php
```

Este script verifica:
- ✓ PHP 7.0+
- ✓ Extensiones requeridas
- ✓ Conexión a base de datos
- ✓ URL base detectada

### Paso 7: Acceder al Sistema

```
http://localhost/mayordomo/public/
```

**Credenciales de prueba:**

```
Admin: admin@hotelparadise.com / password123
Manager: manager@hotelparadise.com / password123
Hostess: hostess@hotelparadise.com / password123
Colaborador: colaborador@hotelparadise.com / password123
Huésped: guest@example.com / password123
```

---

## Solución de Problemas Comunes

### Error: "Database connection failed"

**Causa:** Credenciales incorrectas o MySQL no está corriendo

**Solución:**
1. Verificar que MySQL esté corriendo: `sudo systemctl status mysql`
2. Verificar credenciales en `config/config.php`
3. Verificar que la base de datos existe: `SHOW DATABASES;`

### Error 404 en todas las URLs

**Causa:** mod_rewrite no habilitado o .htaccess no funciona

**Solución:**
1. Habilitar mod_rewrite: `sudo a2enmod rewrite`
2. Verificar AllowOverride en configuración de Apache:
   ```apache
   <Directory /var/www/html>
       AllowOverride All
   </Directory>
   ```
3. Reiniciar Apache: `sudo systemctl restart apache2`

### Las rutas no funcionan bien

**Causa:** URL base mal detectada

**Solución:**
Editar `config/config.php` y definir manualmente:

```php
// Cambiar esta línea
define('BASE_URL', detectBaseUrl());

// Por esta (ajustar según tu instalación)
define('BASE_URL', 'http://localhost/mayordomo');
```

### CSS/JS no cargan

**Causa:** Ruta base incorrecta o permisos

**Solución:**
1. Verificar permisos de carpeta public: `chmod -R 755 public`
2. Verificar URL base en `config/config.php`
3. Abrir consola del navegador (F12) para ver errores específicos

### Error: "Call to undefined function password_hash"

**Causa:** PHP < 5.5

**Solución:**
Actualizar PHP a versión 7.0 o superior

---

## Instalación en Diferentes Entornos

### XAMPP (Windows/Mac/Linux)

1. Descargar XAMPP de [apachefriends.org](https://www.apachefriends.org/)
2. Instalar XAMPP
3. Copiar carpeta `mayordomo` a `C:\xampp\htdocs\` (Windows) o `/Applications/XAMPP/htdocs/` (Mac)
4. Iniciar Apache y MySQL desde el panel de XAMPP
5. Seguir pasos de configuración de base de datos
6. Acceder a: `http://localhost/mayordomo/test_connection.php`

### WAMP (Windows)

1. Descargar WAMP de [wampserver.com](https://www.wampserver.com/)
2. Instalar WAMP
3. Copiar carpeta `mayordomo` a `C:\wamp64\www\`
4. Iniciar WAMP
5. Click en ícono de WAMP > PHP > Versión > Seleccionar 7.x
6. Seguir pasos de configuración de base de datos
7. Acceder a: `http://localhost/mayordomo/test_connection.php`

### MAMP (Mac)

1. Descargar MAMP de [mamp.info](https://www.mamp.info/)
2. Instalar MAMP
3. Copiar carpeta `mayordomo` a `/Applications/MAMP/htdocs/`
4. Iniciar MAMP
5. Seguir pasos de configuración de base de datos
6. Acceder a: `http://localhost:8888/mayordomo/test_connection.php`

### Servidor Linux (Ubuntu/Debian)

```bash
# Instalar Apache, PHP y MySQL
sudo apt update
sudo apt install apache2 php php-mysql mysql-server libapache2-mod-php php-mbstring

# Habilitar mod_rewrite
sudo a2enmod rewrite

# Clonar proyecto
cd /var/www/html
sudo git clone https://github.com/danjohn007/mayordomo.git
sudo chown -R www-data:www-data mayordomo

# Configurar base de datos
sudo mysql -u root -p < mayordomo/database/schema.sql
sudo mysql -u root -p majorbot_db < mayordomo/database/sample_data.sql

# Reiniciar Apache
sudo systemctl restart apache2
```

---

## Configuración de Producción

### Seguridad

1. **Cambiar contraseñas de usuarios de prueba**
2. **Deshabilitar error display en** `config/config.php`:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

3. **Cambiar credenciales de base de datos**
4. **Usar HTTPS** (certificado SSL)
5. **Configurar backup automático de base de datos**

### Optimización

1. **Habilitar caché de PHP (OpCache)**
2. **Configurar expires headers para assets estáticos**
3. **Usar CDN para Bootstrap y librerías**
4. **Comprimir assets CSS/JS**

### Backup

```bash
# Backup de base de datos
mysqldump -u root -p majorbot_db > backup_$(date +%Y%m%d).sql

# Backup de archivos
tar -czf majorbot_backup_$(date +%Y%m%d).tar.gz mayordomo/
```

---

## Verificación Post-Instalación

### Checklist de Funcionalidades

- [ ] Login funciona correctamente
- [ ] Dashboard muestra estadísticas
- [ ] Módulo de Habitaciones (crear, editar, eliminar)
- [ ] Módulo de Mesas (crear, editar, eliminar)
- [ ] Módulo de Menú (crear, editar, eliminar)
- [ ] Módulo de Amenidades (crear, editar, eliminar)
- [ ] Sistema de Bloqueos (crear, liberar)
- [ ] Solicitudes de Servicio (crear, asignar, actualizar estado)
- [ ] Gestión de Usuarios (crear, editar, eliminar)
- [ ] Logout funciona correctamente
- [ ] Permisos por rol funcionan correctamente

### Pruebas Recomendadas

1. **Crear un usuario de cada rol** y verificar que solo vean lo permitido
2. **Crear habitación, mesa, platillo** para verificar CRUD
3. **Crear solicitud de servicio** como huésped
4. **Asignar solicitud** como manager
5. **Actualizar estado** como colaborador
6. **Crear bloqueo** como hostess

---

## Soporte y Recursos

- **Documentación:** Ver README.md
- **Issues:** [GitHub Issues](https://github.com/danjohn007/mayordomo/issues)
- **Wiki:** [GitHub Wiki](https://github.com/danjohn007/mayordomo/wiki)

---

## Próximos Pasos

Una vez instalado, puedes:

1. Personalizar los datos de tu hotel
2. Agregar más habitaciones, mesas y amenidades
3. Crear usuarios reales del personal
4. Comenzar a recibir solicitudes de huéspedes
5. Explorar el roadmap para futuras funcionalidades

¡Disfruta usando MajorBot! 🎉
