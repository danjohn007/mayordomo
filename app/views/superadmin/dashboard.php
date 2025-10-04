<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-speedometer2"></i> Dashboard Superadmin</h1>
                <div>
                    <form method="GET" class="row g-2">
                        <div class="col-auto">
                            <label class="visually-hidden">Fecha Inicio</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="<?= e($startDate) ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-auto">
                            <label class="visually-hidden">Fecha Fin</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="<?= e($endDate) ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($flash = flash('success')): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-subtitle mb-2">Hoteles Activos</h6>
                                    <h2 class="card-title"><?= number_format($stats['total_hotels']) ?></h2>
                                    <small>+<?= number_format($stats['new_hotels']) ?> nuevos en período</small>
                                </div>
                                <div>
                                    <i class="bi bi-building" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-subtitle mb-2">Usuarios Activos</h6>
                                    <h2 class="card-title"><?= number_format($stats['total_users']) ?></h2>
                                    <small>Total en el sistema</small>
                                </div>
                                <div>
                                    <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-subtitle mb-2">Suscripciones Activas</h6>
                                    <h2 class="card-title"><?= number_format($stats['active_subscriptions']) ?></h2>
                                    <small>Suscripciones vigentes</small>
                                </div>
                                <div>
                                    <i class="bi bi-credit-card" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-dark">Ingresos del Período</h6>
                                    <h2 class="card-title text-dark"><?= formatCurrency($stats['total_revenue']) ?></h2>
                                    <small class="text-dark">
                                        <?= formatDate($startDate) ?> - <?= formatDate($endDate) ?>
                                    </small>
                                </div>
                                <div>
                                    <i class="bi bi-cash-stack text-dark" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-subtitle mb-2">Miembros Lealtad</h6>
                                    <h2 class="card-title"><?= number_format($stats['loyalty_members']) ?></h2>
                                    <small>Usuarios en programa</small>
                                </div>
                                <div>
                                    <i class="bi bi-star" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="row">
                <!-- Revenue Chart -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-graph-up"></i> Ingresos por Día</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- New Users Chart -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person-plus"></i> Nuevos Usuarios por Día</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="usersChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Subscriptions by Plan -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Suscripciones por Plan</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="subscriptionsChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="<?= BASE_URL ?>/superadmin/hotels" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-building"></i> Gestionar Hoteles
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?= BASE_URL ?>/superadmin/users" class="btn btn-outline-success w-100">
                                        <i class="bi bi-people"></i> Gestionar Usuarios
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?= BASE_URL ?>/superadmin/subscriptions" class="btn btn-outline-info w-100">
                                        <i class="bi bi-credit-card"></i> Suscripciones
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?= BASE_URL ?>/superadmin/settings" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-gear"></i> Configuración
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Revenue Chart
const revenueData = <?= json_encode($chartData['revenue']) ?>;
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(d => d.date),
        datasets: [{
            label: 'Ingresos (MXN)',
            data: revenueData.map(d => d.revenue),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true }
        }
    }
});

// Users Chart
const usersData = <?= json_encode($chartData['new_users']) ?>;
const usersCtx = document.getElementById('usersChart').getContext('2d');
new Chart(usersCtx, {
    type: 'bar',
    data: {
        labels: usersData.map(d => d.date),
        datasets: [{
            label: 'Nuevos Usuarios',
            data: usersData.map(d => d.count),
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true }
        }
    }
});

// Subscriptions Chart
const subscriptionsData = <?= json_encode($chartData['subscriptions_by_plan']) ?>;
const subscriptionsCtx = document.getElementById('subscriptionsChart').getContext('2d');
new Chart(subscriptionsCtx, {
    type: 'doughnut',
    data: {
        labels: subscriptionsData.map(d => d.name),
        datasets: [{
            label: 'Suscripciones',
            data: subscriptionsData.map(d => d.count),
            backgroundColor: [
                'rgba(255, 99, 132, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
