<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'MajorBot') ?> - MajorBot</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/public/css/style.css" rel="stylesheet">
    
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand mx-auto" href="<?= BASE_URL ?><?= hasRole(['superadmin']) ? '/superadmin' : '/dashboard' ?>">
                <i class="bi bi-building"></i> MajorBot
            </a>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <?= e(currentUser()['first_name']) ?> <?= e(currentUser()['last_name']) ?>
                        <br><small class="text-muted"><?= e(getRoleLabel(currentUser()['role'])) ?></small>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile">
                        <i class="bi bi-person-circle"></i> Mi Perfil
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar Menu (Offcanvas) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header bg-primary text-white">
            <h5 class="offcanvas-title"><i class="bi bi-building"></i> MajorBot</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column">
                <?php if (hasRole(['superadmin'])): ?>
                <a class="nav-link" href="<?= BASE_URL ?>/superadmin">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <?php else: ?>
                <a class="nav-link" href="<?= BASE_URL ?>/dashboard">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <?php endif; ?>
                
                <?php if (hasRole(['superadmin'])): ?>
                <!-- Superadmin Menu -->
                <a class="nav-link" href="<?= BASE_URL ?>/superadmin/hotels">
                    <i class="bi bi-building"></i> Hoteles
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>/superadmin/subscriptions">
                    <i class="bi bi-credit-card"></i> Suscripciones
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>/superadmin/users">
                    <i class="bi bi-people"></i> Usuarios
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>/superadmin/payments">
                    <i class="bi bi-currency-dollar"></i> Registro de Pagos
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>/superadmin/loyalty">
                    <i class="bi bi-star"></i> Programa de Lealtad
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>/superadmin/settings">
                    <i class="bi bi-gear"></i> Configuración Global
                </a>
                <?php endif; ?>
                
                <?php if (hasRole(['admin', 'manager', 'hostess'])): ?>
                <!-- Admin/Manager/Hostess Menu -->
                <a class="nav-link" href="<?= BASE_URL ?>/rooms">
                    <i class="bi bi-door-closed"></i> Habitaciones
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>/tables">
                    <i class="bi bi-table"></i> Mesas
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>/dishes">
                    <i class="bi bi-egg-fried"></i> Menú
                </a>
                <a class="nav-link" href="<?= BASE_URL ?>/amenities">
                    <i class="bi bi-spa"></i> Amenidades
                </a>
                <?php endif; ?>
                
                <?php if (hasRole(['hostess'])): ?>
                <a class="nav-link" href="<?= BASE_URL ?>/blocks">
                    <i class="bi bi-lock"></i> Bloqueos
                </a>
                <?php endif; ?>
                
                <?php if (!hasRole(['superadmin'])): ?>
                <a class="nav-link" href="<?= BASE_URL ?>/services">
                    <i class="bi bi-bell"></i> Servicios
                </a>
                <?php endif; ?>
                
                <?php if (hasRole(['admin', 'manager'])): ?>
                <a class="nav-link" href="<?= BASE_URL ?>/users">
                    <i class="bi bi-people"></i> Usuarios
                </a>
                <?php endif; ?>
            </nav>
            
            <?php if (hasRole(['admin'])): ?>
            <!-- Subscription Info for Admin -->
            <div class="p-3 border-top mt-auto">
                <?php
                // Get subscription info for sidebar
                global $db;
                if (!isset($db)) {
                    require_once CONFIG_PATH . '/database.php';
                    $db = Database::getInstance()->getConnection();
                }
                $currentUser = currentUser();
                $stmt = $db->prepare("
                    SELECT us.*, s.name as plan_name, s.price,
                           DATEDIFF(us.end_date, CURDATE()) as days_remaining
                    FROM user_subscriptions us
                    JOIN subscriptions s ON us.subscription_id = s.id
                    WHERE us.user_id = ? AND us.status = 'active'
                    ORDER BY us.end_date DESC
                    LIMIT 1
                ");
                $stmt->execute([$currentUser['id']]);
                $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($subscription):
                    $daysRemaining = $subscription['days_remaining'] ?? 0;
                    $badgeClass = $daysRemaining > 7 ? 'success' : ($daysRemaining > 0 ? 'warning' : 'danger');
                ?>
                <div class="card border-<?= $badgeClass ?> mb-0">
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1 small"><i class="bi bi-credit-card"></i> Plan Activo</h6>
                        <p class="mb-1 small"><strong><?= e($subscription['plan_name']) ?></strong></p>
                        <p class="mb-1 small text-muted"><?= formatCurrency($subscription['price']) ?></p>
                        <p class="mb-2">
                            <span class="badge bg-<?= $badgeClass ?> small">
                                <?= $daysRemaining ?> días restantes
                            </span>
                        </p>
                        <a href="<?= BASE_URL ?>/subscription" class="btn btn-<?= $badgeClass ?> btn-sm w-100">
                            <i class="bi bi-arrow-up-circle"></i> Actualizar Plan
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning mb-0 p-2 small">
                    <i class="bi bi-exclamation-triangle"></i> Sin plan activo
                    <br>
                    <a href="<?= BASE_URL ?>/profile" class="btn btn-warning btn-sm mt-1 w-100">
                        Activar Plan
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="<?= isLoggedIn() ? 'container-fluid py-4' : '' ?>">
