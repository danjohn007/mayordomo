<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Mesa</h4></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/tables/update/<?= $table['id'] ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="table_number" class="form-label">Número de Mesa *</label>
                            <input type="text" class="form-control" id="table_number" name="table_number" value="<?= e($table['table_number']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="capacity" class="form-label">Capacidad *</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" value="<?= e($table['capacity']) ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?= e($table['location']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="available" <?= $table['status'] === 'available' ? 'selected' : '' ?>>Disponible</option>
                                <option value="occupied" <?= $table['status'] === 'occupied' ? 'selected' : '' ?>>Ocupada</option>
                                <option value="reserved" <?= $table['status'] === 'reserved' ? 'selected' : '' ?>>Reservada</option>
                                <option value="blocked" <?= $table['status'] === 'blocked' ? 'selected' : '' ?>>Bloqueada</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="2"><?= e($table['description']) ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Actualizar</button>
                        <a href="<?= BASE_URL ?>/tables" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
