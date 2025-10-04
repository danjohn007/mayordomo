<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="dashboard-header">
    <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <p class="text-muted">Bienvenido, <?= e($user['first_name']) ?> <?= e($user['last_name']) ?></p>
</div>

<?php if ($flash = flash('success')): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($user['role'] === 'admin' || $user['role'] === 'manager'): ?>
    <!-- Admin/Manager Dashboard -->
    <div class="row">
        <!-- Rooms Stats -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Habitaciones</h6>
                            <h2 class="mb-0"><?= $stats['rooms']['total'] ?></h2>
                            <small><?= $stats['rooms']['available'] ?> disponibles</small>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-door-closed"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tables Stats -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Mesas</h6>
                            <h2 class="mb-0"><?= $stats['tables']['total'] ?></h2>
                            <small><?= $stats['tables']['available'] ?> disponibles</small>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-table"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Requests Stats -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Solicitudes</h6>
                            <h2 class="mb-0"><?= $stats['requests']['total'] ?></h2>
                            <small><?= $stats['requests']['pending'] ?> pendientes</small>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-bell"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Revenue Stats -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Ingresos Hoy</h6>
                            <h2 class="mb-0"><?= formatCurrency($stats['today_revenue']) ?></h2>
                            <small>Ventas del día</small>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Reservations -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Reservaciones Recientes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['recent_reservations'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Habitación</th>
                                        <th>Huésped</th>
                                        <th>Check-in</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['recent_reservations'] as $res): ?>
                                        <tr>
                                            <td><?= e($res['room_number']) ?></td>
                                            <td><?= e($res['first_name']) ?> <?= e($res['last_name']) ?></td>
                                            <td><?= formatDate($res['check_in']) ?></td>
                                            <td><?= getStatusBadge($res['status']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay reservaciones recientes</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Room Status Overview -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Estado de Habitaciones</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Disponibles</span>
                            <strong><?= $stats['rooms']['available'] ?></strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: <?= $stats['rooms']['total'] > 0 ? ($stats['rooms']['available'] / $stats['rooms']['total'] * 100) : 0 ?>%">
                                <?= $stats['rooms']['total'] > 0 ? round($stats['rooms']['available'] / $stats['rooms']['total'] * 100) : 0 ?>%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Ocupadas</span>
                            <strong><?= $stats['rooms']['occupied'] ?></strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-warning" style="width: <?= $stats['rooms']['total'] > 0 ? ($stats['rooms']['occupied'] / $stats['rooms']['total'] * 100) : 0 ?>%">
                                <?= $stats['rooms']['total'] > 0 ? round($stats['rooms']['occupied'] / $stats['rooms']['total'] * 100) : 0 ?>%
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Mantenimiento</span>
                            <strong><?= $stats['rooms']['maintenance'] ?></strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-secondary" style="width: <?= $stats['rooms']['total'] > 0 ? ($stats['rooms']['maintenance'] / $stats['rooms']['total'] * 100) : 0 ?>%">
                                <?= $stats['rooms']['total'] > 0 ? round($stats['rooms']['maintenance'] / $stats['rooms']['total'] * 100) : 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($user['role'] === 'hostess'): ?>
    <!-- Hostess Dashboard -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Mesas Disponibles</h6>
                            <h2 class="mb-0"><?= $stats['available_tables'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-table"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Reservaciones Hoy</h6>
                            <h2 class="mb-0"><?= $stats['today_reservations'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card stats-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Bloqueos Activos</h6>
                            <h2 class="mb-0"><?= $stats['active_blocks'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-lock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($user['role'] === 'superadmin'): ?>
    <!-- Superadmin Dashboard -->
    <div class="row">
        <!-- Total Hotels -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Hoteles Totales</h6>
                            <h2 class="mb-0"><?= $stats['total_hotels'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Subscriptions -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Suscripciones Activas</h6>
                            <h2 class="mb-0"><?= $stats['active_subscriptions'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Users -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Usuarios Totales</h6>
                            <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Revenue -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Ingresos del Mes</h6>
                            <h2 class="mb-0"><?= formatCurrency($stats['monthly_revenue']) ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Hotels -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Hoteles Recientes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['recent_hotels'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Hotel</th>
                                        <th>Propietario</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['recent_hotels'] as $hotel): ?>
                                        <tr>
                                            <td><?= e($hotel['name']) ?></td>
                                            <td>
                                                <?= e($hotel['first_name'] ?? 'N/A') ?> <?= e($hotel['last_name'] ?? '') ?>
                                                <?php if ($hotel['email']): ?>
                                                    <br><small class="text-muted"><?= e($hotel['email']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= formatDate($hotel['created_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay hoteles registrados</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Subscription Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Distribución de Suscripciones</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['subscription_distribution'])): ?>
                        <?php foreach ($stats['subscription_distribution'] as $sub): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?= e($sub['name']) ?></span>
                                    <strong><?= $sub['count'] ?> suscripciones</strong>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <?php 
                                    $total = array_sum(array_column($stats['subscription_distribution'], 'count'));
                                    $percentage = $total > 0 ? ($sub['count'] / $total * 100) : 0;
                                    ?>
                                    <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%">
                                        <?= round($percentage) ?>%
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay suscripciones activas</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue Trend -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Tendencia de Ingresos (Últimos 6 Meses)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['revenue_trend'])): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Ingresos</th>
                                        <th>Suscripciones</th>
                                        <th>Promedio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['revenue_trend'] as $trend): ?>
                                        <tr>
                                            <td><?= e($trend['month']) ?></td>
                                            <td><?= formatCurrency($trend['revenue']) ?></td>
                                            <td><?= $trend['subscriptions'] ?></td>
                                            <td><?= formatCurrency($trend['subscriptions'] > 0 ? $trend['revenue'] / $trend['subscriptions'] : 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay datos de ingresos disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($user['role'] === 'collaborator'): ?>
    <!-- Collaborator Dashboard -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Tareas Totales</h6>
                            <h2 class="mb-0"><?= $stats['tasks']['total'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-list-task"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card stats-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">En Progreso</h6>
                            <h2 class="mb-0"><?= $stats['tasks']['in_progress'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card stats-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Completadas</h6>
                            <h2 class="mb-0"><?= $stats['tasks']['completed'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($stats['recent_tasks'])): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Tareas Recientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Habitación</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_tasks'] as $task): ?>
                                <tr>
                                    <td><?= e($task['title']) ?></td>
                                    <td><?= e($task['room_number']) ?></td>
                                    <td><?= getPriorityBadge($task['priority']) ?></td>
                                    <td><?= getStatusBadge($task['status']) ?></td>
                                    <td><?= formatDateTime($task['requested_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Guest Dashboard -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card stats-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Reservaciones Activas</h6>
                            <h2 class="mb-0"><?= $stats['active_reservations'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card stats-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Solicitudes Pendientes</h6>
                            <h2 class="mb-0"><?= $stats['pending_requests'] ?></h2>
                        </div>
                        <div class="stats-icon bg-white bg-opacity-25">
                            <i class="bi bi-bell"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-info-circle text-primary" style="font-size: 3rem;"></i>
            <h4 class="mt-3">Bienvenido a MajorBot</h4>
            <p class="text-muted">Puedes crear solicitudes de servicio y gestionar tus reservaciones</p>
            <a href="<?= BASE_URL ?>/services" class="btn btn-primary">
                <i class="bi bi-bell"></i> Ver Servicios
            </a>
        </div>
    </div>
<?php endif; ?>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
