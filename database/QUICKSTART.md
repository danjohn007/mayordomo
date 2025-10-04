# 🚀 Quickstart - Migración en 3 Pasos

## Para usuarios que solo quieren actualizar rápidamente

### Paso 1: Backup
```bash
mysqldump -u root -p majorbot_db > backup.sql
```

### Paso 2: Migrar
```bash
cd database
./install_migration.sh --user root --password tu_password
```

### Paso 3: Verificar
```bash
mysql -u root -p majorbot_db < verify_migration.sql
```

## ✅ ¡Listo!

Tu base de datos ahora tiene:
- ✅ 18 nuevas tablas
- ✅ Sistema de reservaciones con email
- ✅ Carrito de compras y pagos
- ✅ Panel de superadmin
- ✅ Notificaciones y reportes

## 📚 ¿Necesitas más ayuda?

- **Guía completa**: [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)
- **Ejemplos SQL**: [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- **Lista de cambios**: [CHANGELOG_DB.md](CHANGELOG_DB.md)
- **Resumen visual**: [SUMMARY.md](SUMMARY.md)

## 🔧 Si algo sale mal

```bash
# Rollback automático
./install_migration.sh --rollback

# O manual
mysql -u root -p majorbot_db < backup.sql
```

## 💡 Configuración Post-Migración

### 1. Email (config/email.php)
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'tu_email@gmail.com');
define('SMTP_PASS', 'tu_contraseña');
```

### 2. Pagos (config/payment.php)
```php
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('PAYPAL_CLIENT_ID', 'tu_client_id');
```

### 3. Asignar Plan Trial a Hoteles
```sql
INSERT INTO hotel_subscriptions (hotel_id, plan_id, start_date, end_date, status)
SELECT h.id, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'trial'
FROM hotels h;
```

---

**Tiempo estimado**: 5-10 minutos  
**Dificultad**: ⭐ Fácil  
**Riesgo**: 🛡️ Bajo (con backup)
