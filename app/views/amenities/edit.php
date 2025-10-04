<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Amenidad</h4></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/amenities/update/<?= $amenity['id'] ?>">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= e($amenity['name']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Categoría *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="wellness" <?= $amenity['category'] === 'wellness' ? 'selected' : '' ?>>Wellness</option>
                                <option value="fitness" <?= $amenity['category'] === 'fitness' ? 'selected' : '' ?>>Fitness</option>
                                <option value="entertainment" <?= $amenity['category'] === 'entertainment' ? 'selected' : '' ?>>Entretenimiento</option>
                                <option value="transport" <?= $amenity['category'] === 'transport' ? 'selected' : '' ?>>Transporte</option>
                                <option value="business" <?= $amenity['category'] === 'business' ? 'selected' : '' ?>>Negocios</option>
                                <option value="other" <?= $amenity['category'] === 'other' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Precio</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?= e($amenity['price']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="capacity" class="form-label">Capacidad</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" value="<?= e($amenity['capacity']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="opening_time" class="form-label">Hora de Apertura</label>
                            <input type="time" class="form-control" id="opening_time" name="opening_time" value="<?= e($amenity['opening_time']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="closing_time" class="form-label">Hora de Cierre</label>
                            <input type="time" class="form-control" id="closing_time" name="closing_time" value="<?= e($amenity['closing_time']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= e($amenity['description']) ?></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_available" name="is_available" <?= $amenity['is_available'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_available">Disponible</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Actualizar</button>
                        <a href="<?= BASE_URL ?>/amenities" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
