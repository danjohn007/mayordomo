<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Usuario</h4></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/users/update/<?= $editUser['id'] ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= e($editUser['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= e($editUser['last_name']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= e($editUser['email']) ?>" disabled>
                            <small class="text-muted">El email no se puede modificar</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= e($editUser['phone']) ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Rol *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="guest" <?= $editUser['role'] === 'guest' ? 'selected' : '' ?>>Huésped</option>
                            <option value="collaborator" <?= $editUser['role'] === 'collaborator' ? 'selected' : '' ?>>Colaborador</option>
                            <option value="hostess" <?= $editUser['role'] === 'hostess' ? 'selected' : '' ?>>Hostess</option>
                            <option value="manager" <?= $editUser['role'] === 'manager' ? 'selected' : '' ?>>Gerente</option>
                            <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                            <option value="superadmin" <?= $editUser['role'] === 'superadmin' ? 'selected' : '' ?>>Superadministrador</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?= $editUser['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Usuario Activo</label>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Actualizar</button>
                        <a href="<?= BASE_URL ?>/users" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
