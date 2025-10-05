<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-credit-card"></i> Gestión de Suscripciones</h1>
                <a href="<?= BASE_URL ?>/superadmin" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Buscar</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Plan, usuario o hotel..." 
                                   value="<?= e($search ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="<?= e($startDate ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="<?= e($endDate ?? '') ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="<?= BASE_URL ?>/superadmin/subscriptions" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Hotel</th>
                                    <th>Plan</th>
                                    <th>Precio</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Días Restantes</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($subscriptions)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No hay suscripciones registradas</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($subscriptions as $sub): ?>
                                    <tr>
                                        <td><?= e($sub['id']) ?></td>
                                        <td>
                                            <strong><?= e($sub['user_name']) ?></strong><br>
                                            <small class="text-muted"><?= e($sub['user_email']) ?></small>
                                        </td>
                                        <td><?= e($sub['hotel_name'] ?? '-') ?></td>
                                        <td><strong><?= e($sub['plan_name']) ?></strong></td>
                                        <td><?= formatCurrency($sub['plan_price']) ?></td>
                                        <td><?= formatDate($sub['start_date']) ?></td>
                                        <td><?= formatDate($sub['end_date']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $sub['days_remaining'] > 7 ? 'success' : ($sub['days_remaining'] > 0 ? 'warning' : 'danger') ?>">
                                                <?= $sub['days_remaining'] ?> días
                                            </span>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($sub['status']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
