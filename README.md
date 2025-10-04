# MajorBot - Sistema de Mayordomía Online

Sistema completo de gestión hotelera con módulos de habitaciones, restaurante, amenidades, servicios de mayordomía y suscripciones.

## 🌟 Características Principales

- **Gestión de Habitaciones**: Control completo de habitaciones, disponibilidad, tipos y precios
- **Restaurante**: Gestión de mesas, menú de platillos con categorías
- **Amenidades**: Spa, gimnasio, piscina, transporte y más servicios
- **Sistema de Bloqueos**: Control manual de disponibilidad por mantenimiento o eventos
- **Servicios de Mayordomía**: Solicitudes y asignación de tareas al personal
- **Gestión de Personal**: Roles personalizados y permisos específicos
- **Suscripciones**: Planes mensual, anual y prueba gratuita
- **Dashboard Inteligente**: Métricas en tiempo real por rol
- **Multi-rol**: Superadmin, Admin Hotel, Gerente, Hostess, Colaborador, Huésped

## 📋 Requisitos del Sistema

- PHP 7.0 o superior
- MySQL 5.7 o superior
- Servidor Web Apache con mod_rewrite habilitado
- Extensiones PHP: PDO, PDO_MySQL, mbstring, openssl

## 🚀 Instalación en Servidor Apache

### 1. Clonar o Descargar el Proyecto

```bash
git clone https://github.com/danjohn007/mayordomo.git
cd mayordomo
```

O descarga el archivo ZIP y extráelo en tu directorio del servidor (por ejemplo: `/var/www/html/` o `htdocs/`).

### 2. Configurar Base de Datos

#### Crear la base de datos MySQL:

```bash
mysql -u root -p
```

Luego ejecuta:

```sql
CREATE DATABASE majorbot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Importar el esquema:

```bash
mysql -u root -p majorbot_db < database/schema.sql
```

#### Importar datos de ejemplo (opcional):

```bash
mysql -u root -p majorbot_db < database/sample_data.sql
```

#### Aplicar migración para nuevas funcionalidades (Fases 1-4):

**Opción A - Usando el script automático (Recomendado):**
```bash
cd database
./install_migration.sh --user root --password tu_password
```

**Opción B - Manual:**
```bash
mysql -u root -p majorbot_db < database/migration_v1.1.0.sql
```

> **Nota**: La migración agrega funcionalidades de Reservaciones, Pedidos & Facturación, Superadmin y Notificaciones. Ver [database/MIGRATION_GUIDE.md](database/MIGRATION_GUIDE.md) para más detalles.

### 3. Configurar Credenciales

Edita el archivo `config/config.php` y actualiza las credenciales de la base de datos:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'majorbot_db');
define('DB_USER', 'root');      // Tu usuario MySQL
define('DB_PASS', '');          // Tu contraseña MySQL
```

### 4. Configurar Apache

#### Habilitar mod_rewrite (si no está habilitado):

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Configurar VirtualHost (opcional pero recomendado):

```apache
<VirtualHost *:80>
    ServerName majorbot.local
    DocumentRoot /var/www/html/mayordomo
    
    <Directory /var/www/html/mayordomo>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/majorbot_error.log
    CustomLog ${APACHE_LOG_DIR}/majorbot_access.log combined
</VirtualHost>
```

Si usas VirtualHost, agrega a `/etc/hosts`:
```
127.0.0.1   majorbot.local
```

### 5. Verificar Permisos

```bash
chmod 755 /var/www/html/mayordomo
chmod -R 755 /var/www/html/mayordomo/public
```

### 6. Probar Instalación

Abre tu navegador y accede a:

```
http://localhost/mayordomo/test_connection.php
```

Este script verificará:
- ✓ Versión de PHP
- ✓ URL Base detectada automáticamente
- ✓ Conexión a la base de datos
- ✓ Extensiones PHP requeridas
- ✓ Permisos de directorios

### 7. Acceder al Sistema

```
http://localhost/mayordomo/public/
```

O si configuraste VirtualHost:
```
http://majorbot.local/public/
```

## 👤 Credenciales de Prueba

Después de importar los datos de ejemplo, puedes acceder con:

**Administrador:**
- Email: `admin@hotelparadise.com`
- Contraseña: `password123`

**Gerente:**
- Email: `manager@hotelparadise.com`
- Contraseña: `password123`

**Hostess:**
- Email: `hostess@hotelparadise.com`
- Contraseña: `password123`

**Colaborador:**
- Email: `colaborador@hotelparadise.com`
- Contraseña: `password123`

**Huésped:**
- Email: `guest@example.com`
- Contraseña: `password123`

## 🏗️ Estructura del Proyecto

```
mayordomo/
├── app/
│   ├── controllers/     # Controladores MVC
│   ├── models/          # Modelos de datos
│   ├── views/           # Vistas HTML
│   └── helpers/         # Funciones auxiliares
├── config/              # Configuración
│   ├── config.php       # Configuración general
│   └── database.php     # Conexión a BD
├── database/            # Scripts SQL
│   ├── schema.sql       # Estructura de tablas
│   └── sample_data.sql  # Datos de ejemplo
├── public/              # Archivos públicos
│   ├── css/            # Estilos
│   ├── js/             # JavaScript
│   ├── images/         # Imágenes
│   └── index.php       # Front Controller
├── .htaccess           # Configuración Apache
└── test_connection.php # Script de prueba
```

## 📱 Módulos Implementados

### ✅ Completos
- [x] Autenticación y Registro
- [x] Dashboard por Roles
- [x] Gestión de Habitaciones (CRUD)
- [x] Gestión de Mesas (CRUD)
- [x] Gestión de Menú/Platillos (CRUD)
- [x] Gestión de Amenidades (CRUD)
- [x] Sistema de Bloqueos (Hostess)
- [x] Solicitudes de Servicio
- [x] Gestión de Usuarios
- [x] Sistema de Roles y Permisos

### 🔄 Roadmap - Próximas Funcionalidades

#### Fase 1 - Reservaciones
- [ ] Módulo de reservaciones de habitaciones
- [ ] Calendario de disponibilidad
- [ ] Reservaciones de mesas de restaurante
- [ ] Sistema de confirmación por email

#### Fase 2 - Pedidos y Facturación
- [ ] Gestión de pedidos de comida
- [ ] Carrito de compras
- [ ] Sistema de pagos (integración Stripe/PayPal)
- [ ] Generación de facturas PDF

#### Fase 3 - Superadmin
- [ ] Panel de Superadministrador
- [ ] Gestión multi-hotel
- [ ] Control de suscripciones
- [ ] Estadísticas globales

#### Fase 4 - Notificaciones y Reportes
- [ ] Sistema de notificaciones en tiempo real
- [ ] Notificaciones por email
- [ ] Reportes de ocupación
- [ ] Reportes de ingresos
- [ ] Exportación a Excel/PDF

#### Fase 5 - Mejoras UI/UX
- [ ] Implementación de gráficas (Chart.js)
- [ ] Calendario de actividades (FullCalendar.js)
- [ ] Modo oscuro
- [ ] PWA (Progressive Web App)

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7+ (sin framework)
- **Base de Datos**: MySQL 5.7
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5
- **Iconos**: Bootstrap Icons
- **Arquitectura**: MVC (Model-View-Controller)
- **Autenticación**: Sesiones PHP con password_hash()
- **Seguridad**: Prepared Statements (PDO), CSRF protection

## 🔒 Características de Seguridad

- ✓ Contraseñas encriptadas con bcrypt
- ✓ Protección contra SQL Injection (PDO Prepared Statements)
- ✓ Protección contra XSS (escapado de HTML)
- ✓ Validación de entrada de datos
- ✓ Control de acceso basado en roles
- ✓ Sesiones seguras con httponly cookies

## 📝 Convenciones de Código

- **PSR-4**: Autoloading de clases
- **Nombres de archivos**: PascalCase para clases, snake_case para otros
- **Variables**: camelCase
- **Constantes**: UPPER_CASE
- **Base de datos**: snake_case

## 🐛 Solución de Problemas

### Error: "Database connection failed"
- Verifica las credenciales en `config/config.php`
- Asegúrate de que MySQL esté ejecutándose
- Verifica que la base de datos `majorbot_db` exista

### Error 404 en todas las URLs
- Verifica que mod_rewrite esté habilitado
- Revisa que los archivos `.htaccess` estén presentes
- Verifica la configuración de AllowOverride en Apache

### Las rutas no funcionan correctamente
- La URL base se detecta automáticamente
- Si hay problemas, edita `config/config.php` y ajusta BASE_URL manualmente

### Estilos o JavaScript no cargan
- Verifica que la carpeta `public` tenga permisos de lectura
- Comprueba la consola del navegador para errores
- Verifica que BASE_URL sea correcto

## 👥 Roles y Permisos

| Módulo | Superadmin | Admin | Manager | Hostess | Collaborator | Guest |
|--------|------------|-------|---------|---------|--------------|-------|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Habitaciones | ✓ | ✓ | ✓ | View | - | - |
| Mesas | ✓ | ✓ | ✓ | ✓ | - | - |
| Menú | ✓ | ✓ | ✓ | View | - | View |
| Amenidades | ✓ | ✓ | ✓ | View | - | View |
| Bloqueos | ✓ | ✓ | ✓ | ✓ | - | - |
| Servicios | ✓ | ✓ | ✓ | View | ✓ | ✓ |
| Usuarios | ✓ | ✓ | ✓ | - | - | - |

## 📧 Soporte

Para reportar bugs o solicitar funcionalidades, abre un issue en GitHub.

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 🙏 Créditos

Desarrollado con ❤️ por el equipo de MajorBot

---

**Versión**: 1.0.0  
**Última actualización**: 2024