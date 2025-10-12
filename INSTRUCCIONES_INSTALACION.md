# 🚀 Instrucciones de Instalación

## Pasos para Implementar los Nuevos Cambios

### 1️⃣ Actualizar el Código

Si estás usando Git, ejecuta:

```bash
git pull origin main
```

O descarga los archivos actualizados del repositorio.

### 2️⃣ Ejecutar Script de Base de Datos

**IMPORTANTE:** Debes ejecutar este script SQL para agregar el campo de cumpleaños a las tablas de reservaciones.

#### Opción A: Desde línea de comandos

```bash
mysql -u tu_usuario -p tu_base_de_datos < database/add_birthday_field.sql
```

#### Opción B: Desde phpMyAdmin

1. Abre phpMyAdmin
2. Selecciona tu base de datos
3. Ve a la pestaña "SQL"
4. Abre el archivo `database/add_birthday_field.sql`
5. Copia y pega su contenido
6. Click en "Ejecutar"

#### Opción C: Manualmente

Si prefieres ejecutar las consultas manualmente:

```sql
-- Agregar campo birthday a room_reservations
ALTER TABLE room_reservations 
ADD COLUMN guest_birthday DATE NULL 
AFTER guest_phone;

-- Agregar campo birthday a table_reservations
ALTER TABLE table_reservations 
ADD COLUMN guest_birthday DATE NULL 
AFTER guest_phone;

-- Agregar campo birthday a amenity_reservations (si existe)
ALTER TABLE amenity_reservations 
ADD COLUMN guest_birthday DATE NULL 
AFTER guest_phone;
```

### 3️⃣ Verificar Tablas de Códigos de Descuento

Asegúrate de que las siguientes tablas existan en tu base de datos:

- `discount_codes`
- `discount_code_usages`

Si no existen, ejecuta:

```bash
mysql -u tu_usuario -p tu_base_de_datos < database/add_discount_codes.sql
```

### 4️⃣ Verificar Permisos

Los nuevos archivos deben tener los permisos correctos:

```bash
chmod 644 app/controllers/DiscountCodesController.php
chmod 644 app/views/discount_codes/*.php
chmod 644 app/views/reservations/*.php
chmod 644 app/views/settings/index.php
```

### 5️⃣ Probar la Funcionalidad

#### Probar Códigos de Descuento:

1. Inicia sesión como **Admin**
2. Ve a **Configuraciones del Hotel**
3. Click en **"Administrar Códigos de Descuento"**
4. Crea un nuevo código de prueba:
   - Código: `TEST10`
   - Tipo: Porcentaje
   - Monto: `10`
   - Válido desde: Hoy
   - Válido hasta: +30 días
   - Estado: Activo
5. Guarda el código

#### Probar Selección Múltiple de Habitaciones:

1. Ve a **Reservaciones** → **Nueva Reservación**
2. Selecciona un huésped (o crea uno nuevo)
3. Selecciona tipo: **Habitación**
4. Verás una lista de checkboxes con todas las habitaciones
5. Marca 2 o más habitaciones
6. Ingresa fechas de check-in y check-out
7. Guarda la reservación
8. Verifica que se crearon múltiples reservaciones

#### Probar Campo de Cumpleaños:

1. En **Nueva Reservación**, cuando crees un nuevo huésped
2. Llena el campo **Fecha de Cumpleaños**
3. Guarda la reservación
4. Edita la reservación y verifica que el cumpleaños se guardó
5. Puedes modificar el cumpleaños en la edición

#### Probar Código de Descuento con Múltiples Habitaciones:

1. En **Nueva Reservación** de tipo Habitación
2. Selecciona 2 habitaciones (ej: Suite $250 + Suite $250 = $500)
3. Ingresa el código `TEST10`
4. Click en **Aplicar**
5. Debes ver:
   - Precio original: $500.00
   - Descuento: -$50.00
   - Total: $450.00
6. Al guardar, cada habitación debe tener su descuento proporcional ($25 cada una)

## 🔍 Verificación Final

### Checklist de Funcionalidad

- [ ] Puedo acceder a `/discount-codes` desde Configuraciones
- [ ] Puedo crear un nuevo código de descuento
- [ ] Puedo editar un código existente
- [ ] Puedo eliminar un código
- [ ] En nueva reservación, veo checkboxes de habitaciones (no dropdown)
- [ ] Puedo seleccionar múltiples habitaciones
- [ ] Se crean múltiples reservaciones al guardar
- [ ] El campo de cumpleaños aparece en nueva reservación
- [ ] El campo de cumpleaños aparece al editar reservación
- [ ] El cumpleaños se guarda correctamente
- [ ] Puedo aplicar un código de descuento
- [ ] El descuento se distribuye entre las habitaciones seleccionadas
- [ ] El contador de uso del código se incrementa

## ❓ Troubleshooting

### Error: "Tabla no encontrada"

**Problema:** La tabla `discount_codes` no existe.

**Solución:** Ejecuta el script `database/add_discount_codes.sql`

### Error: "Columna guest_birthday no existe"

**Problema:** El campo de cumpleaños no fue agregado.

**Solución:** Ejecuta el script `database/add_birthday_field.sql`

### Error 404 en `/discount-codes`

**Problema:** El routing no está reconociendo la URL.

**Solución:** Verifica que el archivo `public/index.php` esté actualizado con el código de conversión de guiones.

### No veo checkboxes de habitaciones

**Problema:** JavaScript no está cargando o hay error en consola.

**Solución:** 
1. Abre la consola del navegador (F12)
2. Busca errores de JavaScript
3. Verifica que el archivo `app/views/reservations/create.php` esté actualizado

### El descuento no se aplica

**Problema:** API de validación no funciona o código inválido.

**Solución:**
1. Verifica que el archivo `public/api/validate_discount_code.php` exista
2. Verifica que el código esté activo y dentro del rango de fechas
3. Verifica que no haya alcanzado el límite de uso

## 📞 Soporte

Si encuentras algún problema durante la instalación:

1. Revisa los logs de PHP en tu servidor
2. Revisa los logs de errores de MySQL
3. Verifica que todas las tablas necesarias existan
4. Verifica que los archivos estén en las rutas correctas
5. Consulta la documentación en `CAMBIOS_MODULO_DESCUENTOS_Y_CUMPLEANOS.md`

## ✅ Siguientes Pasos

Una vez que todo esté funcionando:

1. Crea tus códigos de descuento reales
2. Capacita al personal sobre la nueva funcionalidad
3. Informa a los huéspedes sobre los códigos promocionales
4. Monitorea el uso de los códigos desde el panel de administración

¡Disfruta de las nuevas funcionalidades! 🎉
