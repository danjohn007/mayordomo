# ⚠️ LEER PRIMERO - Correcciones Críticas

## 🔴 Problema Urgente: Error Fatal en "Actualizar Plan"

Si estás experimentando el siguiente error:

```
Fatal error: Uncaught PDOException: SQLSTATE[42S02]: Base table or view not found: 
1146 Table 'aqh_mayordomo.bank_accounts' doesn't exist
```

**Y/O** los precios mostrados no corresponden a tu Configuración Global...

## ✅ Solución Rápida (5 minutos)

### Paso 1: Ejecutar Script de Corrección

**Usando línea de comandos:**
```bash
cd /ruta/a/tu/proyecto
mysql -u aqh_mayordomo -p aqh_mayordomo < database/create_bank_accounts_table.sql
```

**Usando phpMyAdmin:**
1. Abre phpMyAdmin
2. Selecciona la base de datos `aqh_mayordomo`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `database/create_bank_accounts_table.sql`
5. Haz clic en "Continuar"

### Paso 2: Verificar que Funcionó

```bash
mysql -u aqh_mayordomo -p aqh_mayordomo < database/verify_subscription_fix.sql
```

Deberías ver mensajes con ✓ indicando que todo está correcto.

### Paso 3: Configurar Datos Reales (Importante)

Actualiza la información bancaria por defecto:

```sql
UPDATE bank_accounts 
SET 
    bank_name = 'Tu Banco',
    account_holder = 'Tu Empresa S.A. de C.V.',
    account_number = 'Tu número de cuenta',
    clabe = 'Tu CLABE'
WHERE id = 1;
```

### Paso 4: Probar

1. Accede a tu sistema
2. Ve a "Actualizar Plan" o "/subscription"
3. **Debería funcionar sin error fatal**
4. Los precios deben coincidir con tu Configuración Global

## 📚 Documentación Completa

Para más detalles, consulta:

- **[SUBSCRIPTION_FIX_SUMMARY.md](SUBSCRIPTION_FIX_SUMMARY.md)** - Resumen ejecutivo completo
- **[database/README_FIX_SUBSCRIPTION.md](database/README_FIX_SUBSCRIPTION.md)** - Guía de instalación detallada
- **[database/MANAGE_SUBSCRIPTIONS_GUIDE.md](database/MANAGE_SUBSCRIPTIONS_GUIDE.md)** - Gestión continua de suscripciones

## ❓ ¿Qué se Corrigió?

✅ Se creó la tabla `bank_accounts` que faltaba  
✅ Se agregaron columnas a `payment_transactions` para suscripciones  
✅ Se sincronizaron los precios con Configuración Global  
✅ Se agregaron índices y foreign keys para integridad  

## 🆘 ¿Sigues Teniendo Problemas?

1. Revisa que tienes permisos suficientes en MySQL
2. Verifica que el usuario de BD puede crear tablas y alterar estructuras
3. Consulta los logs de MySQL para más detalles
4. Revisa la sección "Solución de Problemas" en SUBSCRIPTION_FIX_SUMMARY.md

## ⚡ Características Adicionales

Después de la corrección, tendrás:

- ✨ Gestión estructurada de cuentas bancarias
- ✨ Sincronización automática de precios
- ✨ Comprobantes de pago almacenados
- ✨ Referencias de transacción rastreables
- ✨ Sistema preparado para múltiples métodos de pago

---

**Importante**: Este script es seguro y puede ejecutarse múltiples veces sin causar problemas. No elimina ni modifica datos existentes.
