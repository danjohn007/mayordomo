# MajorBot - Referencia de Consultas SQL del Dashboard Superadmin

Este documento describe todas las consultas SQL utilizadas en el dashboard del Superadmin.

## 📊 Consultas Implementadas

### 1. Total de Hoteles

```sql
SELECT COUNT(*) as count 
FROM hotels;
```

**Descripción:** Cuenta el número total de hoteles registrados en el sistema.

**Retorna:** Un número entero

---

### 2. Suscripciones Activas

#### Con tabla `hotel_subscriptions` (v1.1.0+)
```sql
SELECT COUNT(*) as count 
FROM hotel_subscriptions 
WHERE status IN ('trial', 'active');
```

#### Con tabla `user_subscriptions` (versión anterior)
```sql
SELECT COUNT(*) as count 
FROM user_subscriptions 
WHERE status = 'active';
```

**Descripción:** Cuenta las suscripciones activas o en periodo de prueba.

**Retorna:** Un número entero

---

### 3. Total de Usuarios

```sql
SELECT COUNT(*) as count 
FROM users;
```

**Descripción:** Cuenta el número total de usuarios registrados (todos los roles).

**Retorna:** Un número entero

---

### 4. Ingresos del Mes Actual

#### Con estructura nueva (v1.1.0+)
```sql
SELECT COALESCE(SUM(sp.price), 0) as revenue 
FROM hotel_subscriptions hs
JOIN subscription_plans sp ON hs.plan_id = sp.id
WHERE hs.status IN ('trial', 'active')
  AND MONTH(hs.start_date) = MONTH(CURRENT_DATE())
  AND YEAR(hs.start_date) = YEAR(CURRENT_DATE());
```

#### Con estructura anterior
```sql
SELECT COALESCE(SUM(s.price), 0) as revenue 
FROM user_subscriptions us
LEFT JOIN subscriptions s ON us.subscription_id = s.id
WHERE us.status = 'active' 
  AND MONTH(us.start_date) = MONTH(CURRENT_DATE())
  AND YEAR(us.start_date) = YEAR(CURRENT_DATE());
```

**Descripción:** Suma los precios de las suscripciones iniciadas en el mes actual.

**Retorna:** Un decimal (formato: 0.00)

**Notas:**
- Usa `COALESCE` para retornar 0 si no hay resultados
- Filtra por mes y año actual
- Solo cuenta suscripciones activas

---

### 5. Hoteles Recientes

#### Con columna `owner_id`
```sql
SELECT h.*, u.first_name, u.last_name, u.email 
FROM hotels h
LEFT JOIN users u ON h.owner_id = u.id
ORDER BY h.created_at DESC
LIMIT 5;
```

#### Sin columna `owner_id` (fallback)
```sql
SELECT * 
FROM hotels
ORDER BY created_at DESC
LIMIT 5;
```

**Descripción:** Obtiene los últimos 5 hoteles registrados con información del propietario.

**Retorna:** Array de objetos con estructura:
```
[
    {
        id: 1,
        name: "Hotel Paradise",
        first_name: "Juan",
        last_name: "Pérez",
        email: "juan@example.com",
        created_at: "2024-01-15 10:30:00",
        ...
    },
    ...
]
```

**Notas:**
- Usa LEFT JOIN para incluir hoteles sin propietario asignado
- Ordenado por fecha de creación (más reciente primero)
- Incluye manejo de errores para BD sin owner_id

---

### 6. Distribución de Suscripciones por Plan

#### Con estructura nueva (v1.1.0+)
```sql
SELECT sp.name, COUNT(hs.id) as count
FROM subscription_plans sp
LEFT JOIN hotel_subscriptions hs 
  ON sp.id = hs.plan_id 
  AND hs.status IN ('trial', 'active')
GROUP BY sp.id, sp.name
ORDER BY sp.sort_order, sp.id;
```

#### Con estructura anterior
```sql
SELECT s.name, COUNT(us.id) as count
FROM subscriptions s
LEFT JOIN user_subscriptions us 
  ON s.id = us.subscription_id 
  AND us.status = 'active'
GROUP BY s.id, s.name
ORDER BY s.id;
```

**Descripción:** Agrupa las suscripciones activas por plan.

**Retorna:** Array de objetos:
```
[
    {name: "Plan Trial", count: 10},
    {name: "Plan Mensual", count: 15},
    {name: "Plan Anual", count: 8},
    {name: "Plan Enterprise", count: 2}
]
```

**Notas:**
- Usa LEFT JOIN para incluir planes sin suscripciones (count = 0)
- Solo cuenta suscripciones activas o en trial
- Ordenado por sort_order (prioridad) o por ID

---

### 7. Tendencia de Ingresos (Últimos 6 Meses)

#### Con estructura nueva (v1.1.0+)
```sql
SELECT 
    DATE_FORMAT(hs.start_date, '%Y-%m') as month,
    SUM(sp.price) as revenue,
    COUNT(hs.id) as subscriptions
FROM hotel_subscriptions hs
JOIN subscription_plans sp ON hs.plan_id = sp.id
WHERE hs.start_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(hs.start_date, '%Y-%m')
ORDER BY month;
```

#### Con estructura anterior
```sql
SELECT 
    DATE_FORMAT(us.start_date, '%Y-%m') as month,
    SUM(s.price) as revenue,
    COUNT(us.id) as subscriptions
FROM user_subscriptions us
LEFT JOIN subscriptions s ON us.subscription_id = s.id
WHERE us.start_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(us.start_date, '%Y-%m')
ORDER BY month;
```

**Descripción:** Agrupa ingresos y suscripciones por mes (últimos 6 meses).

**Retorna:** Array de objetos:
```
[
    {
        month: "2023-08",
        revenue: 1980.00,
        subscriptions: 20
    },
    {
        month: "2023-09",
        revenue: 2277.00,
        subscriptions: 23
    },
    ...
]
```

**Notas:**
- Formato de mes: YYYY-MM
- Suma total de ingresos por mes
- Cuenta número de suscripciones iniciadas en cada mes
- Últimos 6 meses desde la fecha actual

---

## 🔧 Detección Automática de Tablas

El código implementa detección automática de tablas disponibles:

```sql
SHOW TABLES LIKE '%subscription%';
```

**Retorna:** Lista de tablas que contienen "subscription" en el nombre.

**Uso:**
```php
$tables = $this->db->query("SHOW TABLES LIKE '%subscription%'")->fetchAll(\PDO::FETCH_COLUMN);
$hasSubscriptionPlans = in_array('subscription_plans', $tables);
$hasHotelSubscriptions = in_array('hotel_subscriptions', $tables);
$hasUserSubscriptions = in_array('user_subscriptions', $tables);
```

Esto permite al sistema adaptarse automáticamente a la estructura de BD disponible.

---

## 📈 Cálculos Adicionales en la Vista

### Porcentaje de Distribución de Suscripciones

```php
$total = array_sum(array_column($stats['subscription_distribution'], 'count'));
$percentage = $total > 0 ? ($sub['count'] / $total * 100) : 0;
```

**Ejemplo:**
- Plan Trial: 10 suscripciones
- Plan Mensual: 15 suscripciones
- Plan Anual: 5 suscripciones
- Total: 30 suscripciones

**Porcentajes:**
- Plan Trial: 10/30 * 100 = 33.33%
- Plan Mensual: 15/30 * 100 = 50%
- Plan Anual: 5/30 * 100 = 16.67%

---

### Promedio por Suscripción

```php
$average = $subscriptions > 0 ? $revenue / $subscriptions : 0;
```

**Ejemplo:**
- Ingresos del mes: $2,970
- Suscripciones: 30
- Promedio: $2,970 / 30 = $99

---

## 🛡️ Seguridad y Buenas Prácticas

### 1. Uso de COALESCE
```sql
SELECT COALESCE(SUM(price), 0) as revenue
```
**Propósito:** Retornar 0 en lugar de NULL cuando no hay resultados.

### 2. Prepared Statements
Aunque estas consultas específicas no usan parámetros variables, el sistema está preparado para usar prepared statements cuando sea necesario:

```php
$stmt = $this->db->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$hotelId]);
```

### 3. Manejo de Excepciones
```php
try {
    // Intenta consulta con nueva estructura
    $stmt = $this->db->query("...");
} catch (\PDOException $e) {
    // Fallback a estructura antigua
    $stmt = $this->db->query("...");
}
```

### 4. Índices Recomendados

Para optimizar estas consultas, asegúrate de tener índices en:

```sql
-- En tabla hotels
CREATE INDEX idx_created_at ON hotels(created_at);
CREATE INDEX idx_owner ON hotels(owner_id);

-- En tabla hotel_subscriptions
CREATE INDEX idx_status ON hotel_subscriptions(status);
CREATE INDEX idx_start_date ON hotel_subscriptions(start_date);
CREATE INDEX idx_plan ON hotel_subscriptions(plan_id);

-- En tabla subscription_plans
CREATE INDEX idx_active ON subscription_plans(is_active);
CREATE INDEX idx_sort ON subscription_plans(sort_order);

-- En tabla users
CREATE INDEX idx_role ON users(role);
```

---

## 📊 Ejemplos de Datos de Retorno

### Dashboard Completo del Superadmin

```php
$stats = [
    // Estadísticas principales
    'total_hotels' => 25,
    'active_subscriptions' => 18,
    'total_users' => 127,
    'monthly_revenue' => 2450.00,
    
    // Hoteles recientes
    'recent_hotels' => [
        [
            'id' => 1,
            'name' => 'Hotel Paradise',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'created_at' => '2024-01-15 10:30:00'
        ],
        // ... 4 más
    ],
    
    // Distribución de suscripciones
    'subscription_distribution' => [
        ['name' => 'Plan Trial', 'count' => 10],
        ['name' => 'Plan Mensual', 'count' => 15],
        ['name' => 'Plan Anual', 'count' => 8],
        ['name' => 'Plan Enterprise', 'count' => 2]
    ],
    
    // Tendencia de ingresos
    'revenue_trend' => [
        [
            'month' => '2023-08',
            'revenue' => 1980.00,
            'subscriptions' => 20
        ],
        [
            'month' => '2023-09',
            'revenue' => 2277.00,
            'subscriptions' => 23
        ],
        // ... hasta 6 meses
    ]
];
```

---

## 🔍 Consultas de Verificación

### Verificar Usuario Superadmin
```sql
SELECT id, email, 
       CONCAT(first_name, ' ', last_name) as nombre_completo,
       role, is_active, created_at
FROM users 
WHERE role = 'superadmin';
```

### Verificar Planes de Suscripción
```sql
-- Nueva estructura
SELECT id, name, slug, price, billing_cycle, 
       trial_days, max_hotels, is_active
FROM subscription_plans
ORDER BY sort_order;

-- Estructura anterior
SELECT id, name, type, price, 
       duration_days, is_active
FROM subscriptions
ORDER BY id;
```

### Verificar Suscripciones Activas con Detalles
```sql
-- Nueva estructura
SELECT 
    h.name as hotel,
    sp.name as plan,
    hs.status,
    hs.start_date,
    hs.end_date,
    sp.price
FROM hotel_subscriptions hs
JOIN hotels h ON hs.hotel_id = h.id
JOIN subscription_plans sp ON hs.plan_id = sp.id
WHERE hs.status IN ('trial', 'active')
ORDER BY hs.start_date DESC;

-- Estructura anterior
SELECT 
    u.first_name,
    u.last_name,
    s.name as plan,
    us.status,
    us.start_date,
    us.end_date,
    s.price
FROM user_subscriptions us
JOIN users u ON us.user_id = u.id
JOIN subscriptions s ON us.subscription_id = s.id
WHERE us.status = 'active'
ORDER BY us.start_date DESC;
```

---

## 💡 Optimizaciones Futuras

### 1. Cache de Estadísticas
Para sistemas con muchos datos, considera cachear las estadísticas:

```php
// Cachear por 5 minutos
$cacheKey = 'superadmin_stats';
$cachedStats = Cache::get($cacheKey);

if (!$cachedStats) {
    $stats = $this->getSuperadminStats();
    Cache::set($cacheKey, $stats, 300); // 5 minutos
} else {
    $stats = $cachedStats;
}
```

### 2. Paginación para Hoteles Recientes
```sql
SELECT h.*, u.first_name, u.last_name, u.email 
FROM hotels h
LEFT JOIN users u ON h.owner_id = u.id
ORDER BY h.created_at DESC
LIMIT ? OFFSET ?;  -- Usar parámetros para paginación
```

### 3. Filtros Adicionales
Agregar filtros por fecha, estado, plan, etc:

```sql
-- Ejemplo: Filtrar por rango de fechas
WHERE h.created_at BETWEEN ? AND ?

-- Ejemplo: Filtrar por plan específico
WHERE sp.slug = ?

-- Ejemplo: Filtrar por estado de suscripción
WHERE hs.status = ?
```

---

## 🎯 Conclusión

Todas las consultas están optimizadas para:
- ✅ Compatibilidad con múltiples versiones de BD
- ✅ Manejo seguro de valores NULL
- ✅ Rendimiento óptimo con índices
- ✅ Prevención de errores
- ✅ Escalabilidad

Para más información, consultar:
- `app/controllers/DashboardController.php` - Implementación completa
- `database/schema.sql` - Estructura de BD base
- `database/migration_v1.1.0.sql` - Migración a nueva estructura
