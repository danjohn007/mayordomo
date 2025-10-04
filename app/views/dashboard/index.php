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
    
    <!-- Subscription Info Card (Admin only) -->
    <?php if ($user['role'] === 'admin' && isset($stats['subscription'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-<?= ($stats['subscription']['days_remaining'] ?? 0) > 7 ? 'success' : (($stats['subscription']['days_remaining'] ?? 0) > 0 ? 'warning' : 'danger') ?>">
                <div class="card-header bg-<?= ($stats['subscription']['days_remaining'] ?? 0) > 7 ? 'success' : (($stats['subscription']['days_remaining'] ?? 0) > 0 ? 'warning' : 'danger') ?> text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-credit-card"></i> Suscripción Activa</h5>
                        <a href="<?= BASE_URL ?>/profile" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-up-circle"></i> Ver Mi Perfil
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Plan:</strong><br>
                            <?= e($stats['subscription']['plan_name']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Precio:</strong><br>
                            <?= formatCurrency($stats['subscription']['price']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Fecha Fin:</strong><br>
                            <?= formatDate($stats['subscription']['end_date']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Días Restantes:</strong><br>
                            <span class="badge bg-<?= ($stats['subscription']['days_remaining'] ?? 0) > 7 ? 'success' : (($stats['subscription']['days_remaining'] ?? 0) > 0 ? 'warning' : 'danger') ?> fs-6">
                                <?= $stats['subscription']['days_remaining'] ?? 0 ?> días
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Date Filters and Charts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estadísticas</h5>
                        <form method="GET" class="row g-2">
                            <div class="col-auto">
                                <input type="date" class="form-control form-control-sm" name="start_date" 
                                       value="<?= e($stats['startDate'] ?? date('Y-m-01')) ?>" max="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-auto">
                                <input type="date" class="form-control form-control-sm" name="end_date" 
                                       value="<?= e($stats['endDate'] ?? date('Y-m-d')) ?>" max="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-filter"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <h6>Reservaciones por Día</h6>
                            <canvas id="reservationsChart" height="200"></canvas>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <h6>Solicitudes de Servicio</h6>
                            <canvas id="requestsChart" height="200"></canvas>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <h6>Tasa de Ocupación</h6>
                            <canvas id="occupancyChart" height="200"></canvas>
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
    
    <!-- Chart.js Scripts for Admin Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
    <?php if (isset($stats['chart_reservations'])): ?>
    // Reservations Chart
    const reservationsData = <?= json_encode($stats['chart_reservations']) ?>;
    if (document.getElementById('reservationsChart')) {
        const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
        new Chart(reservationsCtx, {
            type: 'line',
            data: {
                labels: reservationsData.map(d => d.date),
                datasets: [{
                    label: 'Reservaciones',
                    data: reservationsData.map(d => d.count),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
    <?php endif; ?>
    
    <?php if (isset($stats['chart_requests'])): ?>
    // Service Requests Chart
    const requestsData = <?= json_encode($stats['chart_requests']) ?>;
    if (document.getElementById('requestsChart')) {
        const requestsCtx = document.getElementById('requestsChart').getContext('2d');
        new Chart(requestsCtx, {
            type: 'bar',
            data: {
                labels: requestsData.map(d => d.date),
                datasets: [{
                    label: 'Solicitudes',
                    data: requestsData.map(d => d.count),
                    backgroundColor: 'rgba(255, 206, 86, 0.5)',
                    borderColor: 'rgb(255, 206, 86)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
    <?php endif; ?>
    
    <?php if (isset($stats['chart_occupancy'])): ?>
    // Occupancy Chart
    const occupancyData = <?= json_encode($stats['chart_occupancy']) ?>;
    if (document.getElementById('occupancyChart')) {
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        new Chart(occupancyCtx, {
            type: 'line',
            data: {
                labels: occupancyData.map(d => d.date),
                datasets: [{
                    label: 'Ocupación (%)',
                    data: occupancyData.map(d => (d.occupied / d.total_rooms * 100).toFixed(2)),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
    </script>

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
