# Database - MajorBot

Este directorio contiene todos los archivos relacionados con la base de datos del sistema MajorBot.

## 📁 Archivos Disponibles

### Esquema y Datos Base
- **`schema.sql`** - Esquema base de la base de datos (v1.0.0)
- **`sample_data.sql`** - Datos de ejemplo para desarrollo

### Migración v1.1.0+ (Fases 1-4)
- **`migration_v1.1.0.sql`** ⭐ - Script de migración completo para actualizar a v1.1.0+
- **`MIGRATION_GUIDE.md`** - Guía detallada de instalación y migración
- **`install_migration.sh`** - Script automatizado para ejecutar la migración
- **`verify_migration.sql`** - Script de verificación post-migración
- **`CHANGELOG_DB.md`** - Registro detallado de todos los cambios
- **`QUICK_REFERENCE.md`** - Referencia rápida con ejemplos de SQL

## 🚀 Inicio Rápido

### Instalación Nueva

```bash
# 1. Crear base de datos
mysql -u root -p -e "CREATE DATABASE majorbot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Importar esquema base
mysql -u root -p majorbot_db < schema.sql

# 3. Aplicar migración (Fases 1-4)
./install_migration.sh --user root --password tu_password

# 4. (Opcional) Importar datos de ejemplo
mysql -u root -p majorbot_db < sample_data.sql
```

### Migración desde v1.0.0

```bash
# 1. Backup automático y migración
./install_migration.sh --user root --password tu_password

# 2. Verificar resultados
mysql -u root -p majorbot_db < verify_migration.sql
```

## 📊 ¿Qué Incluye la Migración v1.1.0+?

### 🎯 Fase 1: Reservaciones
- ✅ Sistema de confirmación por email
- ✅ Códigos únicos de reservación
- ✅ Calendario de disponibilidad
- ✅ Gestión de solicitudes especiales

### 💳 Fase 2: Pedidos y Facturación
- ✅ Carrito de compras
- ✅ Sistema de pagos (Stripe/PayPal)
- ✅ Generación de facturas PDF
- ✅ Tracking de transacciones

### 👑 Fase 3: Superadmin
- ✅ Panel de superadministrador
- ✅ Gestión multi-hotel
- ✅ 4 planes de suscripción
- ✅ Control de límites por plan
- ✅ Estadísticas globales

### 🔔 Fase 4: Notificaciones y Reportes
- ✅ Notificaciones en tiempo real
- ✅ Notificaciones por email
- ✅ Reportes de ocupación e ingresos
- ✅ Exportación a PDF/Excel/CSV

## 📈 Resumen de Cambios

| Categoría | Cantidad |
|-----------|----------|
| **Nuevas Tablas** | 18 |
| **Tablas Modificadas** | 3 |
| **Nuevos Campos** | 35+ |
| **Vistas** | 3 |
| **Triggers** | 4 |
| **Procedimientos** | 2 |

## 🔧 Uso del Script de Instalación

### Opciones Disponibles

```bash
./install_migration.sh [opciones]

Opciones:
  --host HOST          Host de MySQL (default: localhost)
  --db DATABASE        Nombre de la base de datos (default: majorbot_db)
  --user USER          Usuario de MySQL (default: root)
  --password PASS      Contraseña de MySQL
  --skip-backup        No crear backup antes de migrar
  --rollback          Restaurar desde el último backup
  --help              Mostrar ayuda
```

### Ejemplos

```bash
# Migración básica
./install_migration.sh --user root --password mypass

# Sin backup (no recomendado para producción)
./install_migration.sh --user root --password mypass --skip-backup

# Rollback al último backup
./install_migration.sh --rollback
```

## 📚 Documentación Detallada

### Para Administradores
- **[MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)** - Guía completa de migración
  - Requisitos previos
  - Pasos de instalación
  - Validación post-migración
  - Troubleshooting
  - Configuración post-migración

### Para Desarrolladores
- **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** - Ejemplos de uso
  - Consultas SQL comunes
  - Casos de uso prácticos
  - Mejores prácticas
  - Tips de optimización

- **[CHANGELOG_DB.md](CHANGELOG_DB.md)** - Historial de cambios
  - Cambios por fase
  - Nuevas tablas y campos
  - Características agregadas
  - Compatibilidad

## 🔍 Verificación Post-Migración

Ejecuta el script de verificación para asegurarte de que todo está correcto:

```bash
mysql -u root -p majorbot_db < verify_migration.sql
```

Esto verificará:
- ✅ Todas las nuevas tablas existen
- ✅ Todos los campos fueron agregados
- ✅ Vistas están creadas
- ✅ Triggers funcionan
- ✅ Procedimientos están disponibles
- ✅ Datos de ejemplo insertados

## 🎯 Nuevas Funcionalidades SQL

### Vistas Útiles

```sql
-- Ver disponibilidad de habitaciones
SELECT * FROM v_room_availability WHERE hotel_id = 1;

-- Ver ingresos del día
SELECT * FROM v_daily_revenue WHERE revenue_date = CURDATE();

-- Ver ocupación actual
SELECT * FROM v_occupancy_rate;
```

### Procedimientos Almacenados

```sql
-- Verificar disponibilidad
CALL sp_check_room_availability(1, '2024-01-15', '2024-01-20');

-- Calcular ocupación
CALL sp_calculate_occupancy(1, CURDATE());
```

### Triggers Automáticos
- Códigos de confirmación se generan automáticamente
- Números de factura son únicos y automáticos
- Subtotales se calculan al guardar

## 🔐 Seguridad y Backups

### Backups Automáticos
El script `install_migration.sh` crea automáticamente un backup antes de migrar:
```
database/backups/majorbot_backup_YYYYMMDD_HHMMSS.sql
```

### Backup Manual
```bash
# Crear backup
mysqldump -u root -p majorbot_db > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u root -p majorbot_db < backup_YYYYMMDD.sql
```

## 📊 Planes de Suscripción Incluidos

La migración inserta automáticamente 4 planes:

1. **Trial** - Gratis (30 días)
   - 1 hotel, 10 habitaciones, 10 mesas, 5 staff

2. **Básico** - $499 MXN/mes
   - 1 hotel, 50 habitaciones, 30 mesas, 20 staff

3. **Profesional** - $999 MXN/mes
   - 3 hoteles, 100 habitaciones c/u, 50 mesas c/u

4. **Enterprise** - $2,499 MXN/mes
   - Ilimitado todo

## ⚠️ Importante

### Antes de Migrar en Producción
1. ✅ Hacer backup completo
2. ✅ Probar en ambiente de desarrollo
3. ✅ Notificar a usuarios de mantenimiento
4. ✅ Verificar espacio en disco suficiente
5. ✅ Tener plan de rollback

### Después de Migrar
1. ✅ Ejecutar verify_migration.sql
2. ✅ Probar funcionalidades críticas
3. ✅ Configurar credenciales de email
4. ✅ Configurar API keys de pagos
5. ✅ Actualizar código de la aplicación

## 🆘 Troubleshooting

### Error: "Table already exists"
Es normal, la migración usa `IF NOT EXISTS` para seguridad.

### Error: "Cannot add foreign key constraint"
Verifica que las tablas base existan ejecutando primero `schema.sql`.

### Error de conexión
Verifica credenciales con:
```bash
mysql -u root -p -e "USE majorbot_db; SHOW TABLES;"
```

### Rollback necesario
```bash
./install_migration.sh --rollback
```

## 📞 Soporte

- Ver documentación completa en [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)
- Ver ejemplos en [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- Ver cambios en [CHANGELOG_DB.md](CHANGELOG_DB.md)
- Issues en GitHub: https://github.com/danjohn007/mayordomo/issues

## 📝 Licencia

Este proyecto es código abierto bajo licencia MIT.

---

**Versión**: 1.1.0  
**Última actualización**: Diciembre 2024  
**Compatibilidad**: MySQL 5.7+ / MariaDB 10.2+
