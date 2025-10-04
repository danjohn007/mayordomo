<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people"></i> Gestión de Usuarios</h1>
    <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Usuario</a>
</div>

<?php if ($flash = flash('success')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>

<div class="card">
    <div class="card-body">
        <?php if (!empty($users)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?= e($user['first_name']) ?> <?= e($user['last_name']) ?></strong></td>
                                <td><?= e($user['email']) ?></td>
                                <td><span class="badge bg-info"><?= getRoleLabel($user['role']) ?></span></td>
                                <td><?= e($user['phone']) ?></td>
                                <td><?= $user['is_active'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                                <td class="action-buttons">
                                    <a href="<?= BASE_URL ?>/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <?php if (hasRole(['admin']) && $user['id'] != currentUser()['id']): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/users/delete/<?= $user['id'] ?>" style="display: inline-block;">
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h4>No hay usuarios registrados</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
