<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people"></i> Gestión de Usuarios</h1>
    <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Usuario</a>
</div>

<?php if ($flash = flash('success')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" 
                       placeholder="Nombre o email..." 
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Rol</label>
                <select class="form-select" name="role">
                    <option value="">Todos</option>
                    <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    <option value="manager" <?= ($filters['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                    <option value="hostess" <?= ($filters['role'] ?? '') === 'hostess' ? 'selected' : '' ?>>Hostess</option>
                    <option value="collaborator" <?= ($filters['role'] ?? '') === 'collaborator' ? 'selected' : '' ?>>Colaborador</option>
                    <option value="guest" <?= ($filters['role'] ?? '') === 'guest' ? 'selected' : '' ?>>Huésped</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="is_active">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

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
