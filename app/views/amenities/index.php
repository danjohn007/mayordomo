<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-spa"></i> Amenidades</h1>
    <?php if (hasRole(['admin', 'manager'])): ?>
        <a href="<?= BASE_URL ?>/amenities/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nueva Amenidad</a>
    <?php endif; ?>
</div>

<?php if ($flash = flash('success')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" 
                       placeholder="Nombre o descripción..." 
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select class="form-select" name="category">
                    <option value="">Todas</option>
                    <option value="gym" <?= ($filters['category'] ?? '') === 'gym' ? 'selected' : '' ?>>Gimnasio</option>
                    <option value="pool" <?= ($filters['category'] ?? '') === 'pool' ? 'selected' : '' ?>>Piscina</option>
                    <option value="spa" <?= ($filters['category'] ?? '') === 'spa' ? 'selected' : '' ?>>Spa</option>
                    <option value="parking" <?= ($filters['category'] ?? '') === 'parking' ? 'selected' : '' ?>>Estacionamiento</option>
                    <option value="other" <?= ($filters['category'] ?? '') === 'other' ? 'selected' : '' ?>>Otros</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Disponibilidad</label>
                <select class="form-select" name="is_active">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Disponible</option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>No Disponible</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?= BASE_URL ?>/amenities" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($amenities)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Capacidad</th>
                            <th>Horario</th>
                            <th>Disponible</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($amenities as $amenity): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($amenity['primary_image'])): ?>
                                        <img src="<?= BASE_URL ?>/public/<?= e($amenity['primary_image']) ?>" 
                                             alt="<?= e($amenity['name']) ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-spa text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= e($amenity['name']) ?></strong></td>
                                <td><?= ucfirst($amenity['category']) ?></td>
                                <td><?= formatCurrency($amenity['price']) ?></td>
                                <td><?= $amenity['capacity'] ? e($amenity['capacity']) : '-' ?></td>
                                <td><?= $amenity['opening_time'] ? substr($amenity['opening_time'], 0, 5) . ' - ' . substr($amenity['closing_time'], 0, 5) : '-' ?></td>
                                <td><?= $amenity['is_available'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                                <td class="action-buttons">
                                    <?php if (hasRole(['admin', 'manager'])): ?>
                                        <a href="<?= BASE_URL ?>/amenities/edit/<?= $amenity['id'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                                        
                                        <form method="POST" action="<?= BASE_URL ?>/amenities/toggleSuspend/<?= $amenity['id'] ?>" style="display: inline-block;">
                                            <?php if (!$amenity['is_available']): ?>
                                                <button type="submit" class="btn btn-sm btn-success" title="Reactivar">
                                                    <i class="bi bi-play-circle"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-secondary" title="Suspender">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        
                                        <form method="POST" action="<?= BASE_URL ?>/amenities/delete/<?= $amenity['id'] ?>" style="display: inline-block;">
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete" title="Eliminar"><i class="bi bi-trash"></i></button>
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
                <i class="bi bi-spa"></i>
                <h4>No hay amenidades registradas</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
