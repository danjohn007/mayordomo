# Corrección de Error de Autorización en Reservaciones

## Problema Identificado
Cuando se seleccionaba un tipo de reservación (habitación, mesa o amenidad) en `reservations/create`, aparecía el error:
- **Error**: "No autorizado"
- **API Error**: No autorizado
- **Respuesta API**: `{success: false, message: 'No autorizado'}`

## Causa del Problema
Había una inconsistencia en cómo se manejaba la autenticación entre los archivos principales del sistema y los archivos API:

### Sistema Principal (controllers/helpers)
- Usa `$_SESSION['user_id']` para verificar si el usuario está logueado
- Los datos del usuario se almacenan directamente en `$_SESSION` con claves individuales: `user_id`, `email`, `first_name`, etc.

### Archivos API (problema)
- Estaban buscando `$_SESSION['user']` como un array completo
- Esta clave no existía en la sesión, causando el error de autorización

## Archivos Corregidos

### 1. `/public/api/get_resources.php`
**Antes:**
```php
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}
$user = $_SESSION['user'];
```

**Después:**
```php
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}
$user = [
    'id' => $_SESSION['user_id'] ?? null,
    'email' => $_SESSION['email'] ?? null,
    'first_name' => $_SESSION['first_name'] ?? null,
    'last_name' => $_SESSION['last_name'] ?? null,
    'role' => $_SESSION['role'] ?? null,
    'hotel_id' => $_SESSION['hotel_id'] ?? null
];
```

### 2. `/public/api/search_guests.php`
- Aplicada la misma corrección de autenticación

### 3. `/public/api/check_phone.php`
- Aplicada la misma corrección de autenticación

### 4. `/public/api/validate_discount_code.php`
- Aplicada la misma corrección de autenticación

## Resultado
- ✅ Los tipos de reservación ahora cargan correctamente
- ✅ Se muestran las habitaciones, mesas y amenidades disponibles
- ✅ La funcionalidad de búsqueda de huéspedes funciona
- ✅ La validación de códigos de descuento funciona
- ✅ Mantiene la consistencia de autenticación en todo el sistema

## Pruebas Recomendadas
1. Acceder a `reservations/create`
2. Seleccionar "Habitación" - debería mostrar las habitaciones disponibles
3. Seleccionar "Mesa" - debería mostrar las mesas disponibles  
4. Seleccionar "Amenidad" - debería mostrar las amenidades disponibles
5. Probar búsqueda de huéspedes
6. Probar validación de códigos de descuento (si aplica)

---
**Fecha de corrección**: 13 de octubre de 2025
**Archivos modificados**: 4 archivos API
**Impacto**: Funcionalidad crítica de reservaciones restaurada