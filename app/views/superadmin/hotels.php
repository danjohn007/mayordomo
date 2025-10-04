<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-building"></i> Gestión de Hoteles</h1>
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
                                    <th>Nombre Hotel</th>
                                    <th>Email</th>
                                    <th>Propietario</th>
                                    <th>Usuarios</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($hotels)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay hoteles registrados</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($hotels as $hotel): ?>
                                    <tr>
                                        <td><?= e($hotel['id']) ?></td>
                                        <td><strong><?= e($hotel['name']) ?></strong></td>
                                        <td><?= e($hotel['email']) ?></td>
                                        <td>
                                            <?= e($hotel['owner_name']) ?><br>
                                            <small class="text-muted"><?= e($hotel['owner_email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $hotel['user_count'] ?> usuarios</span>
                                        </td>
                                        <td>
                                            <?php if ($hotel['is_active']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($hotel['created_at']) ?></td>
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
