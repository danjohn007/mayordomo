<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-table"></i> Mesas de Restaurante</h1>
    <?php if (hasRole(['admin', 'manager'])): ?>
        <a href="<?= BASE_URL ?>/tables/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Mesa
        </a>
    <?php endif; ?>
</div>

<?php if ($flash = flash('success')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>
<?php if ($flash = flash('error')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" 
                       placeholder="Número o descripción..." 
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Ubicación/Zona</label>
                <input type="text" class="form-control" name="location" 
                       placeholder="Ej: Terraza, Interior..." 
                       value="<?= e($filters['location'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="available" <?= ($filters['status'] ?? '') === 'available' ? 'selected' : '' ?>>Disponible</option>
                    <option value="occupied" <?= ($filters['status'] ?? '') === 'occupied' ? 'selected' : '' ?>>Ocupado</option>
                    <option value="reserved" <?= ($filters['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>Reservado</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($tables)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Mesa</th>
                            <th>Capacidad</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($table['primary_image'])): ?>
                                        <img src="<?= BASE_URL ?>/public/<?= e($table['primary_image']) ?>" 
                                             alt="Mesa <?= e($table['table_number']) ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-table text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= e($table['table_number']) ?></strong></td>
                                <td><?= e($table['capacity']) ?> personas</td>
                                <td><?= e($table['location']) ?></td>
                                <td><?= getStatusBadge($table['status']) ?></td>
                                <td class="action-buttons">
                                    <?php if (hasRole(['admin', 'manager'])): ?>
                                        <a href="<?= BASE_URL ?>/tables/edit/<?= $table['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="<?= BASE_URL ?>/tables/delete/<?= $table['id'] ?>" style="display: inline-block;">
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
                <i class="bi bi-table"></i>
                <h4>No hay mesas registradas</h4>
                <p>Comienza creando tu primera mesa</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
