<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-door-closed"></i> Habitaciones</h1>
    <?php if (hasRole(['admin', 'manager'])): ?>
        <a href="<?= BASE_URL ?>/rooms/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Habitación
        </a>
    <?php endif; ?>
</div>

<?php if ($flash = flash('success')): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($flash = flash('error')): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= e($filters['search']) ?>" placeholder="Número o descripción">
            </div>
            
            <div class="col-md-3">
                <label for="type" class="form-label">Tipo</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Todos</option>
                    <option value="single" <?= $filters['type'] === 'single' ? 'selected' : '' ?>>Individual</option>
                    <option value="double" <?= $filters['type'] === 'double' ? 'selected' : '' ?>>Doble</option>
                    <option value="suite" <?= $filters['type'] === 'suite' ? 'selected' : '' ?>>Suite</option>
                    <option value="deluxe" <?= $filters['type'] === 'deluxe' ? 'selected' : '' ?>>Deluxe</option>
                    <option value="presidential" <?= $filters['type'] === 'presidential' ? 'selected' : '' ?>>Presidencial</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="available" <?= $filters['status'] === 'available' ? 'selected' : '' ?>>Disponible</option>
                    <option value="occupied" <?= $filters['status'] === 'occupied' ? 'selected' : '' ?>>Ocupado</option>
                    <option value="maintenance" <?= $filters['status'] === 'maintenance' ? 'selected' : '' ?>>Mantenimiento</option>
                    <option value="reserved" <?= $filters['status'] === 'reserved' ? 'selected' : '' ?>>Reservado</option>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/rooms" class="btn btn-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Rooms List -->
<div class="card">
    <div class="card-body">
        <?php if (!empty($rooms)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Número</th>
                            <th>Tipo</th>
                            <th>Piso</th>
                            <th>Capacidad</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($room['primary_image'])): ?>
                                        <img src="<?= BASE_URL ?>/public/<?= e($room['primary_image']) ?>" 
                                             alt="Habitación <?= e($room['room_number']) ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-door-closed text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= e($room['room_number']) ?></strong></td>
                                <td>
                                    <?php
                                    $types = [
                                        'single' => 'Individual',
                                        'double' => 'Doble',
                                        'suite' => 'Suite',
                                        'deluxe' => 'Deluxe',
                                        'presidential' => 'Presidencial'
                                    ];
                                    echo e($types[$room['type']] ?? $room['type']);
                                    ?>
                                </td>
                                <td><?= $room['floor'] ? 'Piso ' . e($room['floor']) : '-' ?></td>
                                <td><?= e($room['capacity']) ?> persona(s)</td>
                                <td><strong><?= formatCurrency($room['price']) ?></strong></td>
                                <td><?= getStatusBadge($room['status']) ?></td>
                                <td class="action-buttons">
                                    <?php if (hasRole(['admin', 'manager'])): ?>
                                        <a href="<?= BASE_URL ?>/rooms/edit/<?= $room['id'] ?>" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (hasRole(['admin'])): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/rooms/delete/<?= $room['id'] ?>" 
                                              style="display: inline-block;">
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
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
                <i class="bi bi-door-closed"></i>
                <h4>No hay habitaciones</h4>
                <p>No se encontraron habitaciones con los filtros seleccionados</p>
                <?php if (hasRole(['admin', 'manager'])): ?>
                    <a href="<?= BASE_URL ?>/rooms/create" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle"></i> Crear Primera Habitación
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
