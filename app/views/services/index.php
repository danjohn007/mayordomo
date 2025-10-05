<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-bell"></i> Solicitudes de Servicio</h1>
    <a href="<?= BASE_URL ?>/services/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nueva Solicitud</a>
</div>

<?php if ($flash = flash('success')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" 
                       placeholder="Título, descripción o habitación..." 
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completado</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Prioridad</label>
                <select class="form-select" name="priority">
                    <option value="">Todas</option>
                    <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Baja</option>
                    <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Alta</option>
                    <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?= BASE_URL ?>/services" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($requests)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Huésped</th>
                            <th>Habitación</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Asignado a</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><strong><?= e($req['title']) ?></strong></td>
                                <td><?= e($req['guest_first_name']) ?> <?= e($req['guest_last_name']) ?></td>
                                <td><?= e($req['room_number']) ?: '-' ?></td>
                                <td><?= getPriorityBadge($req['priority']) ?></td>
                                <td><?= getStatusBadge($req['status']) ?></td>
                                <td><?= $req['collab_first_name'] ? e($req['collab_first_name']) . ' ' . e($req['collab_last_name']) : '-' ?></td>
                                <td><?= formatDateTime($req['requested_at']) ?></td>
                                <td class="action-buttons">
                                    <?php if (hasRole(['admin', 'manager'])): ?>
                                        <a href="<?= BASE_URL ?>/services/edit/<?= $req['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/services/cancel/<?= $req['id'] ?>" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de cancelar esta solicitud?')">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Cancelar">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>/services/updateStatus/<?= $req['id'] ?>" style="display: inline-block;">
                                            <select name="status" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                                <option value="pending" <?= $req['status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                                <option value="assigned" <?= $req['status'] === 'assigned' ? 'selected' : '' ?>>Asignado</option>
                                                <option value="in_progress" <?= $req['status'] === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                                                <option value="completed" <?= $req['status'] === 'completed' ? 'selected' : '' ?>>Completado</option>
                                                <option value="cancelled" <?= $req['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                                            </select>
                                        </form>
                                    <?php elseif (hasRole(['collaborator']) && $req['assigned_to'] == currentUser()['id']): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/services/updateStatus/<?= $req['id'] ?>" style="display: inline-block;">
                                            <select name="status" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                                <option value="assigned" <?= $req['status'] === 'assigned' ? 'selected' : '' ?>>Asignado</option>
                                                <option value="in_progress" <?= $req['status'] === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                                                <option value="completed" <?= $req['status'] === 'completed' ? 'selected' : '' ?>>Completado</option>
                                            </select>
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
                <i class="bi bi-bell"></i>
                <h4>No hay solicitudes de servicio</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
