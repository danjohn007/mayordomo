# ⚡ Instrucciones Rápidas - Aplicar Correcciones

## 🎯 Lo que se corrigió

✅ **Imágenes** - Ya se ven correctamente en edición  
✅ **Chatbot** - Ya no pide habitación al reservar rooms  
✅ **Reservaciones** - Amenidades ya no aparecen como "Mesa"  
✅ **Sonido** - Suena cada 10s hasta confirmar/cancelar  
⚠️ **Notificaciones** - Falta ejecutar SQL (ver abajo)

---

## 🚀 Pasos para Completar

### 1️⃣ El código ya está actualizado ✅
No necesitas hacer nada con el código PHP/JavaScript.

### 2️⃣ Ejecuta el script SQL (OBLIGATORIO)

**Desde phpMyAdmin:**
1. Entra a phpMyAdmin
2. Selecciona base de datos `aqh_mayordomo`
3. Click en pestaña "SQL"
4. Abre el archivo `database/fix_notifications_with_names.sql`
5. Copia TODO el contenido
6. Pega en el cuadro de texto de phpMyAdmin
7. Click en "Ejecutar"
8. Debe decir "✓ Migration completed successfully!"

**Desde terminal:**
```bash
mysql -u root -p aqh_mayordomo < database/fix_notifications_with_names.sql
```

### 3️⃣ Prueba que todo funciona

**Probar imágenes:**
- Edita una habitación/mesa/amenidad con imágenes
- ¿Se ven las imágenes? ✅

**Probar chatbot:**
- Abre chatbot como visitante
- Reserva una habitación
- ¿NO pidió número de habitación? ✅

**Probar reservaciones:**
- Ve a `/reservations/`
- Crea una reservación de amenidad
- ¿Aparece con badge azul "Amenidad"? ✅

**Probar sonido:**
- Crea una reservación nueva
- ¿Suena cada 10 segundos? ✅
- Confírmala
- ¿Se detuvo el sonido? ✅

**Probar notificaciones (después de ejecutar SQL):**
- Crea una reservación de habitación
- Ve a notificaciones
- ¿Aparece el nombre del huésped? ✅

---

## ❓ ¿Problemas?

**Imágenes no se ven:**
```bash
chmod -R 755 public/uploads/
```

**Sonido no suena:**
- ¿Existe `public/assets/sounds/notification.mp3`?
- ¿Hiciste clic en la página?

**SQL no ejecuta:**
- ¿Tienes permisos de administrador?
- ¿Seleccionaste la base de datos correcta?

---

## ✅ Listo

Si completaste el paso 2 (SQL) y las pruebas funcionan, ¡ya está todo! 🎉
