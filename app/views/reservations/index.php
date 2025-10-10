<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-calendar-check"></i> Reservaciones</h1>
    
    <!-- Botón Nueva Reservación -->
    <a href="<?= BASE_URL ?>/reservations/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nueva Reservación
    </a>
</div>

<?php if ($flash = flash('success')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>
<?php if ($flash = flash('error')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select class="form-select" name="type">
                    <option value="">Todas</option>
                    <option value="room" <?= ($filters['type'] ?? '') === 'room' ? 'selected' : '' ?>>Habitaciones</option>
                    <option value="table" <?= ($filters['type'] ?? '') === 'table' ? 'selected' : '' ?>>Mesas</option>
                    <option value="amenity" <?= ($filters['type'] ?? '') === 'amenity' ? 'selected' : '' ?>>Amenidades</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="confirmed" <?= ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmada</option>
                    <option value="checked_in" <?= ($filters['status'] ?? '') === 'checked_in' ? 'selected' : '' ?>>Check-in</option>
                    <option value="seated" <?= ($filters['status'] ?? '') === 'seated' ? 'selected' : '' ?>>Sentado</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completada</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" 
                       placeholder="Nombre, email, número..." 
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" class="form-control" name="date_from" 
                       value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" class="form-control" name="date_to" 
                       value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Reservaciones -->
<div class="card">
    <?php if (!empty($reservations)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Recurso</th>
                        <th>Huésped</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?= $reservation['id'] ?></td>
                            <td>
                                <?php if ($reservation['reservation_type'] === 'room'): ?>
                                    <span class="badge bg-info"><i class="bi bi-door-closed"></i> Habitación</span>
                                <?php elseif ($reservation['reservation_type'] === 'table'): ?>
                                    <span class="badge bg-success"><i class="bi bi-table"></i> Mesa</span>
                                <?php elseif ($reservation['reservation_type'] === 'amenity'): ?>
                                    <span class="badge bg-primary"><i class="bi bi-spa"></i> Amenidad</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= e($reservation['reservation_type']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($reservation['resource_number']) ?></strong></td>
                            <td>
                                <?= e($reservation['guest_name']) ?><br>
                                <small class="text-muted"><?= e($reservation['guest_email']) ?></small>
                            </td>
                            <td>
                                <?= formatDate($reservation['reservation_date']) ?>
                                <?php if ($reservation['reservation_time']): ?>
                                    <br><small><?= date('H:i', strtotime($reservation['reservation_time'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'checked_in' => 'primary',
                                    'seated' => 'primary',
                                    'completed' => 'success',
                                    'checked_out' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $statusLabels = [
                                    'pending' => 'Pendiente',
                                    'confirmed' => 'Confirmada',
                                    'checked_in' => 'Check-in',
                                    'seated' => 'Sentado',
                                    'completed' => 'Completada',
                                    'checked_out' => 'Check-out',
                                    'cancelled' => 'Cancelada'
                                ];
                                $color = $statusColors[$reservation['status']] ?? 'secondary';
                                $label = $statusLabels[$reservation['status']] ?? $reservation['status'];
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                            </td>
                            <td class="action-buttons text-center">
                                <?php if (hasRole(['admin', 'manager', 'hostess'])): ?>
                                    <!-- Editar -->
                                    <a href="<?= BASE_URL ?>/reservations/edit/<?= $reservation['id'] ?>?type=<?= $reservation['reservation_type'] ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- Aceptar/Confirmar -->
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <a href="<?= BASE_URL ?>/reservations/accept/<?= $reservation['id'] ?>?type=<?= $reservation['reservation_type'] ?>" 
                                           class="btn btn-sm btn-success"
                                           title="Confirmar"
                                           onclick="return confirm('¿Confirmar esta reservación?')">
                                            <i class="bi bi-check-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Eliminar/Cancelar -->
                                    <?php if (hasRole(['admin', 'manager']) && $reservation['status'] !== 'cancelled'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/reservations/delete/<?= $reservation['id'] ?>" 
                                              style="display: inline-block;">
                                            <input type="hidden" name="type" value="<?= $reservation['reservation_type'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    title="Cancelar"
                                                    onclick="return confirm('¿Cancelar esta reservación?')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card-body text-center py-5">
            <div class="empty-state">
                <i class="bi bi-calendar-check" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">No hay reservaciones</h4>
                <p class="text-muted">No se encontraron reservaciones con los filtros seleccionados.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
