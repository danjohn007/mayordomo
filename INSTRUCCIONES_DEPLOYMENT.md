# 🚀 Instrucciones de Deployment - Códigos de Descuento

## 📋 Checklist Pre-Deployment

Antes de aplicar los cambios en producción, verificar:

- [ ] Backup de la base de datos actual
- [ ] Acceso al servidor de base de datos
- [ ] Acceso al servidor web
- [ ] Verificar versión de PHP (7.4+)
- [ ] Verificar versión de MySQL/MariaDB (5.7+)
- [ ] Usuario de BD tiene permisos CREATE, ALTER, INSERT

---

## 📦 Archivos a Desplegar

### Código PHP (Backend)
```bash
# Copiar archivos modificados
/app/controllers/ReservationsController.php  # MODIFICADO
/app/views/reservations/create.php          # MODIFICADO

# Copiar archivos nuevos
/public/api/validate_discount_code.php      # NUEVO
```

### Migración de Base de Datos
```bash
/database/add_discount_codes.sql            # NUEVO
```

---

## 🔧 Pasos de Deployment

### Paso 1: Backup de Base de Datos

```bash
# Crear backup completo
mysqldump -u usuario -p nombre_base_datos > backup_$(date +%Y%m%d_%H%M%S).sql

# Verificar backup
ls -lh backup_*.sql
```

### Paso 2: Verificar Conexión a Base de Datos

```bash
# Conectar a MySQL
mysql -u usuario -p nombre_base_datos

# Verificar base de datos actual
mysql> SHOW DATABASES;
mysql> USE nombre_base_datos;
mysql> SHOW TABLES;
mysql> SELECT COUNT(*) FROM room_reservations;
mysql> exit;
```

### Paso 3: Aplicar Migración de Base de Datos

```bash
# Opción 1: Aplicar migración completa
mysql -u usuario -p nombre_base_datos < database/add_discount_codes.sql

# Opción 2: Ejecutar paso a paso
mysql -u usuario -p nombre_base_datos
mysql> source /ruta/completa/database/add_discount_codes.sql
mysql> exit;
```

### Paso 4: Verificar Migración

```sql
-- Verificar tablas creadas
SHOW TABLES LIKE '%discount%';

-- Resultado esperado:
-- discount_codes
-- discount_code_usages

-- Verificar estructura discount_codes
DESCRIBE discount_codes;

-- Verificar estructura discount_code_usages
DESCRIBE discount_code_usages;

-- Verificar columnas agregadas a room_reservations
DESCRIBE room_reservations;

-- Resultado esperado incluye:
-- discount_code_id
-- discount_amount
-- original_price

-- Verificar códigos de ejemplo insertados
SELECT id, code, discount_type, amount, active, valid_from, valid_to 
FROM discount_codes;

-- Resultado esperado: 3 códigos (WELCOME10, PROMO50, FLASH20)
```

### Paso 5: Copiar Archivos PHP

```bash
# Método 1: Via FTP/SFTP
# Subir archivos manualmente usando cliente FTP

# Método 2: Via SSH/SCP
scp app/controllers/ReservationsController.php usuario@servidor:/ruta/app/controllers/
scp app/views/reservations/create.php usuario@servidor:/ruta/app/views/reservations/
scp public/api/validate_discount_code.php usuario@servidor:/ruta/public/api/

# Método 3: Via Git
git pull origin copilot/fix-reservation-resources-and-add-discount-codes
```

### Paso 6: Verificar Permisos de Archivos

```bash
# Verificar permisos
ls -la public/api/validate_discount_code.php

# Ajustar si es necesario (644 para archivos PHP)
chmod 644 public/api/validate_discount_code.php
chmod 644 app/controllers/ReservationsController.php
chmod 644 app/views/reservations/create.php

# Verificar propietario
chown www-data:www-data public/api/validate_discount_code.php
```

### Paso 7: Limpiar Caché (si aplica)

```bash
# PHP OpCache (si está habilitado)
# Opción 1: Restart PHP-FPM
sudo systemctl restart php7.4-fpm  # o php8.0-fpm, etc.

# Opción 2: Restart Apache/Nginx
sudo systemctl restart apache2
# o
sudo systemctl restart nginx
```

### Paso 8: Pruebas Básicas en Producción

#### A. Verificar API de Validación
```bash
# Prueba con curl (reemplazar con datos reales)
curl -X POST http://tu-dominio.com/api/validate_discount_code.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "code=WELCOME10&room_price=1000" \
  --cookie "PHPSESSID=tu-session-id"

# Respuesta esperada:
# {"success":true,"discount":{...}}
```

#### B. Verificar Frontend
1. Navegar a: `https://tu-dominio.com/reservations/create`
2. Login con usuario admin/manager/hostess
3. Seleccionar tipo "Habitación"
4. Verificar que se muestran habitaciones correctamente
5. Seleccionar una habitación
6. Verificar que aparece campo "Código de Descuento"
7. Ingresar código: `WELCOME10`
8. Click en "Aplicar"
9. Verificar que muestra descuento calculado
10. Completar formulario y guardar

#### C. Verificar en Base de Datos
```sql
-- Ver última reservación creada
SELECT 
    id, guest_name, room_id, 
    original_price, discount_amount, total_price,
    discount_code_id, created_at
FROM room_reservations 
ORDER BY id DESC LIMIT 1;

-- Verificar uso registrado
SELECT * FROM discount_code_usages ORDER BY id DESC LIMIT 1;

-- Verificar contador actualizado
SELECT code, times_used FROM discount_codes WHERE code = 'WELCOME10';
```

---

## 🔍 Verificación Post-Deployment

### Checklist de Funcionalidad

- [ ] **Carga de recursos funciona**
  - [ ] Habitaciones se listan correctamente
  - [ ] Mesas se listan correctamente
  - [ ] Amenidades se listan correctamente
  - [ ] Mensaje específico cuando no hay recursos

- [ ] **Validación de códigos funciona**
  - [ ] Código válido se aplica correctamente
  - [ ] Código inválido muestra error apropiado
  - [ ] Límite de uso se respeta
  - [ ] Fechas de vigencia se respetan

- [ ] **Guardado funciona**
  - [ ] Reservación con descuento se guarda
  - [ ] Registro en discount_code_usages se crea
  - [ ] Contador times_used se incrementa
  - [ ] Reservación sin descuento sigue funcionando

- [ ] **Seguridad funciona**
  - [ ] Solo usuarios autenticados pueden acceder
  - [ ] Solo códigos del hotel correcto se aceptan
  - [ ] Validación server-side no se puede saltear

### Pruebas de Integración

#### 1. Flujo Completo con Descuento
```
1. Login como admin
2. Ir a Reservaciones > Nueva Reservación
3. Seleccionar Habitación
4. Seleccionar habitación de $1000
5. Ingresar código: WELCOME10
6. Aplicar código
7. Verificar descuento: -$100, Total: $900
8. Completar datos de huésped
9. Completar fechas check-in/out
10. Guardar reservación
11. Verificar mensaje de éxito
12. Ir a listado de reservaciones
13. Verificar que aparece con total $900
```

#### 2. Flujo Sin Descuento
```
1. Crear reservación sin aplicar código
2. Verificar que total es precio completo
3. Verificar que se guarda correctamente
```

#### 3. Flujo con Código Inválido
```
1. Intentar aplicar código "INVALIDO"
2. Verificar mensaje de error
3. Poder continuar con reservación sin descuento
```

---

## 🚨 Troubleshooting

### Problema 1: Error al aplicar migración

**Error:** `Table 'discount_codes' already exists`

**Solución:**
```sql
-- Verificar si la tabla existe
SHOW TABLES LIKE 'discount_codes';

-- Si existe, la migración ya se aplicó
-- Verificar que tiene todos los campos
DESCRIBE discount_codes;
```

### Problema 2: API retorna error 500

**Error:** Internal Server Error al validar código

**Diagnóstico:**
```bash
# Ver logs de PHP
tail -f /var/log/apache2/error.log
# o
tail -f /var/log/nginx/error.log
```

**Posibles causas:**
- Archivo no tiene permisos correctos
- Sintaxis PHP inválida (verificar con `php -l archivo.php`)
- Conexión a base de datos falla
- Session no iniciada correctamente

### Problema 3: Código no se aplica

**Síntomas:** Click en "Aplicar" no hace nada

**Diagnóstico:**
```javascript
// Abrir consola del navegador (F12)
// Buscar errores JavaScript
// Verificar llamadas a API en Network tab
```

**Verificar:**
- Ruta del API es correcta en create.php
- Usuario está autenticado (SESSION activa)
- Request llega al servidor

### Problema 4: Descuento no se guarda

**Síntomas:** Reservación se crea pero sin descuento

**Verificar en BD:**
```sql
-- Ver última reservación
SELECT * FROM room_reservations ORDER BY id DESC LIMIT 1;

-- Ver campos de descuento
SELECT discount_code_id, discount_amount, original_price 
FROM room_reservations 
ORDER BY id DESC LIMIT 1;
```

**Verificar en código:**
- Campos ocultos tienen valores al enviar formulario
- Controlador recibe los valores POST
- Transacción se completa sin errores

### Problema 5: Foreign Key Error

**Error:** Cannot add or update a child row: a foreign key constraint fails

**Solución:**
```sql
-- Verificar que las foreign keys existen
SHOW CREATE TABLE room_reservations;
SHOW CREATE TABLE discount_code_usages;

-- Si falta alguna, agregarla manualmente
ALTER TABLE room_reservations 
ADD CONSTRAINT fk_room_reservation_discount 
FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id) 
ON DELETE SET NULL;
```

---

## 🔄 Rollback (Si es necesario)

### Plan de Rollback

#### 1. Restaurar Archivos PHP
```bash
# Revertir cambios con Git
git checkout main -- app/controllers/ReservationsController.php
git checkout main -- app/views/reservations/create.php

# Eliminar archivo nuevo
rm public/api/validate_discount_code.php
```

#### 2. Revertir Base de Datos
```bash
# Restaurar desde backup
mysql -u usuario -p nombre_base_datos < backup_YYYYMMDD_HHMMSS.sql
```

#### 3. Rollback Parcial (Solo BD)
```sql
-- Si solo quieres eliminar las tablas nuevas
-- pero mantener los datos existentes

-- Eliminar foreign key primero
ALTER TABLE room_reservations 
DROP FOREIGN KEY fk_room_reservation_discount;

-- Eliminar columnas agregadas
ALTER TABLE room_reservations 
DROP COLUMN discount_code_id,
DROP COLUMN discount_amount,
DROP COLUMN original_price;

-- Eliminar tablas nuevas
DROP TABLE discount_code_usages;
DROP TABLE discount_codes;
```

---

## 📞 Contacto y Soporte

### Antes de Deployment
- [ ] Revisar toda la documentación
- [ ] Probar en ambiente de desarrollo
- [ ] Hacer backup completo
- [ ] Tener plan de rollback listo

### Durante Deployment
- [ ] Seguir pasos en orden
- [ ] Verificar cada paso antes de continuar
- [ ] Documentar cualquier problema

### Después de Deployment
- [ ] Ejecutar todas las pruebas
- [ ] Monitorear logs por 24-48 horas
- [ ] Verificar con usuarios finales

### En Caso de Problemas
1. Revisar logs de PHP y MySQL
2. Consultar sección de Troubleshooting
3. Considerar rollback si es crítico
4. Documentar el problema para referencia

---

## 📚 Referencias

### Documentación Técnica
- `IMPLEMENTACION_CODIGOS_DESCUENTO.md` - Guía técnica completa
- `DIAGRAMA_FLUJO_DESCUENTOS.md` - Diagramas y flujos
- `RESUMEN_IMPLEMENTACION_DESCUENTOS.md` - Resumen ejecutivo

### Documentación de Usuario
- `GUIA_RAPIDA_DESCUENTOS.md` - Guía rápida de uso
- `PRUEBAS_MANUALES_DESCUENTOS.md` - Plan de pruebas

### Archivos de Código
- `database/add_discount_codes.sql` - Migración
- `public/api/validate_discount_code.php` - API
- `app/controllers/ReservationsController.php` - Controlador
- `app/views/reservations/create.php` - Vista

---

## ✅ Checklist Final

### Pre-Deployment
- [ ] Backup de base de datos creado
- [ ] Documentación revisada
- [ ] Plan de rollback preparado
- [ ] Ambiente de pruebas validado

### Deployment
- [ ] Migración aplicada exitosamente
- [ ] Archivos PHP copiados
- [ ] Permisos verificados
- [ ] Caché limpiado

### Post-Deployment
- [ ] API funciona correctamente
- [ ] Frontend funciona correctamente
- [ ] Base de datos actualizada correctamente
- [ ] Todas las pruebas pasaron
- [ ] Usuarios notificados (si aplica)

### Monitoreo (Primeras 48 horas)
- [ ] Logs sin errores críticos
- [ ] Performance normal
- [ ] Usuarios reportan funcionamiento correcto
- [ ] Códigos de descuento se aplican correctamente

---

**Fecha de Preparación:** 12 de Octubre de 2025  
**Versión del Sistema:** 1.0.0  
**Estado:** ✅ LISTO PARA DEPLOYMENT

**NOTA IMPORTANTE:** Este deployment agrega funcionalidad nueva sin afectar funcionalidad existente. Es backward compatible y seguro de aplicar.
