<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-people"></i> Gestión de Usuarios</h1>
                <a href="<?= BASE_URL ?>/superadmin" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Hotel</th>
                                    <th>Rol</th>
                                    <th>Suscripciones</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No hay usuarios registrados</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= e($user['id']) ?></td>
                                        <td><strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong></td>
                                        <td><?= e($user['email']) ?></td>
                                        <td><?= e($user['hotel_name'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $user['role'] === 'superadmin' ? 'danger' : ($user['role'] === 'admin' ? 'primary' : 'secondary') ?>">
                                                <?= getRoleLabel($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['active_subscriptions'] > 0): ?>
                                                <span class="badge bg-success"><?= $user['active_subscriptions'] ?> activa(s)</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Sin suscripción</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($user['created_at']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" title="Ver Detalles">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>
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
