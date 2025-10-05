<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-egg-fried"></i> Menú / Platillos</h1>
    <?php if (hasRole(['admin', 'manager'])): ?>
        <a href="<?= BASE_URL ?>/dishes/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Platillo</a>
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
                    <option value="appetizers" <?= ($filters['category'] ?? '') === 'appetizers' ? 'selected' : '' ?>>Entradas</option>
                    <option value="main_courses" <?= ($filters['category'] ?? '') === 'main_courses' ? 'selected' : '' ?>>Platos Principales</option>
                    <option value="desserts" <?= ($filters['category'] ?? '') === 'desserts' ? 'selected' : '' ?>>Postres</option>
                    <option value="drinks" <?= ($filters['category'] ?? '') === 'drinks' ? 'selected' : '' ?>>Bebidas</option>
                    <option value="breakfast" <?= ($filters['category'] ?? '') === 'breakfast' ? 'selected' : '' ?>>Desayunos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Disponibilidad</label>
                <select class="form-select" name="is_available">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filters['is_available'] ?? '') === '1' ? 'selected' : '' ?>>Disponible</option>
                    <option value="0" <?= ($filters['is_available'] ?? '') === '0' ? 'selected' : '' ?>>No Disponible</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?= BASE_URL ?>/dishes" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($dishes)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Servicio</th>
                            <th>Disponible</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dishes as $dish): ?>
                            <tr>
                                <td><strong><?= e($dish['name']) ?></strong></td>
                                <td><?= ucfirst(str_replace('_', ' ', $dish['category'])) ?></td>
                                <td><strong><?= formatCurrency($dish['price']) ?></strong></td>
                                <td><?= ucfirst(str_replace('_', ' ', $dish['service_time'])) ?></td>
                                <td><?= $dish['is_available'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                                <td class="action-buttons">
                                    <?php if (hasRole(['admin', 'manager'])): ?>
                                        <a href="<?= BASE_URL ?>/dishes/edit/<?= $dish['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="<?= BASE_URL ?>/dishes/delete/<?= $dish['id'] ?>" style="display: inline-block;">
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
                <i class="bi bi-egg-fried"></i>
                <h4>No hay platillos en el menú</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
