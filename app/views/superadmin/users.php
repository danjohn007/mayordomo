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
            
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Buscar</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Nombre, email o hotel..." 
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
                            <a href="<?= BASE_URL ?>/superadmin/users" class="btn btn-outline-secondary">
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
                                                <a href="<?= BASE_URL ?>/superadmin/viewUser/<?= $user['id'] ?>" 
                                                   class="btn btn-outline-primary" title="Ver Detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/superadmin/editUser/<?= $user['id'] ?>" 
                                                   class="btn btn-outline-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($user['is_active']): ?>
                                                    <button onclick="suspendUser(<?= $user['id'] ?>)" 
                                                            class="btn btn-outline-danger" title="Suspender">
                                                        <i class="bi bi-pause-circle"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button onclick="activateUser(<?= $user['id'] ?>)" 
                                                            class="btn btn-outline-success" title="Activar">
                                                        <i class="bi bi-play-circle"></i>
                                                    </button>
                                                <?php endif; ?>
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

<script>
function suspendUser(userId) {
    if (confirm('¿Estás seguro de que deseas suspender este usuario?')) {
        fetch('<?= BASE_URL ?>/superadmin/suspendUser/' + userId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error al suspender el usuario');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}

function activateUser(userId) {
    if (confirm('¿Estás seguro de que deseas activar este usuario?')) {
        fetch('<?= BASE_URL ?>/superadmin/activateUser/' + userId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error al activar el usuario');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
