<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-person-circle"></i> Detalles de Usuario</h1>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/superadmin/editUser/<?= $user['id'] ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Usuario
                    </a>
                    <a href="<?= BASE_URL ?>/superadmin/users" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            
            <div class="row">
                <!-- User Information Card -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Información Personal</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8"><?= e($user['id']) ?></dd>
                                
                                <dt class="col-sm-4">Nombre:</dt>
                                <dd class="col-sm-8"><strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong></dd>
                                
                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8"><?= e($user['email']) ?></dd>
                                
                                <dt class="col-sm-4">Teléfono:</dt>
                                <dd class="col-sm-8"><?= e($user['phone'] ?? '-') ?></dd>
                                
                                <dt class="col-sm-4">Rol:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-<?= $user['role'] === 'superadmin' ? 'danger' : ($user['role'] === 'admin' ? 'primary' : 'secondary') ?>">
                                        <?= getRoleLabel($user['role']) ?>
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Estado:</dt>
                                <dd class="col-sm-8">
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-sm-4">Fecha Registro:</dt>
                                <dd class="col-sm-8"><?= formatDate($user['created_at']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                
                <!-- Hotel Information Card -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-building"></i> Hotel Asociado</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($user['hotel_id']): ?>
                                <dl class="row">
                                    <dt class="col-sm-4">Nombre:</dt>
                                    <dd class="col-sm-8"><strong><?= e($user['hotel_name']) ?></strong></dd>
                                    
                                    <dt class="col-sm-4">Email:</dt>
                                    <dd class="col-sm-8"><?= e($user['hotel_email'] ?? '-') ?></dd>
                                </dl>
                                <a href="<?= BASE_URL ?>/superadmin/viewHotel/<?= $user['hotel_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Hotel
                                </a>
                            <?php else: ?>
                                <p class="text-muted">No hay hotel asociado a este usuario</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Subscriptions History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-credit-card"></i> Historial de Suscripciones</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($subscriptions)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Plan</th>
                                        <th>Precio</th>
                                        <th>Tipo</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Días Restantes</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subscriptions as $sub): ?>
                                    <tr>
                                        <td><strong><?= e($sub['plan_name']) ?></strong></td>
                                        <td><?= formatCurrency($sub['plan_price']) ?></td>
                                        <td>
                                            <?php if ($sub['is_unlimited']): ?>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-infinity"></i> Ilimitado
                                                </span>
                                            <?php else: ?>
                                                <?= ucfirst($sub['billing_cycle']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($sub['start_date']) ?></td>
                                        <td>
                                            <?php if ($sub['is_unlimited']): ?>
                                                <span class="text-muted">Sin vencimiento</span>
                                            <?php else: ?>
                                                <?= formatDate($sub['end_date']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($sub['is_unlimited']): ?>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-infinity"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-<?= $sub['days_remaining'] > 7 ? 'success' : ($sub['days_remaining'] > 0 ? 'warning' : 'danger') ?>">
                                                    <?= $sub['days_remaining'] ?> días
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= getStatusBadge($sub['status']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Este usuario no tiene suscripciones registradas
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
