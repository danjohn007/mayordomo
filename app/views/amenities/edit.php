<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Amenidad</h4></div>
            <div class="card-body">
                <?php if ($flash = flash('error')): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= BASE_URL ?>/amenities/update/<?= $amenity['id'] ?>" enctype="multipart/form-data">
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
                    
                    <?php
                    // Get existing images
                    $imageModel = getModel('ResourceImage', $db ?? null);
                    $images = $imageModel ? $imageModel->getByResource('amenity', $amenity['id']) : [];
                    ?>
                    
                    <?php if (!empty($images)): ?>
                    <div class="mb-3">
                        <label class="form-label">Imágenes Actuales</label>
                        <div class="row g-2">
                            <?php foreach ($images as $img): ?>
                                <div class="col-md-3">
                                    <div class="card">
                                        <img src="<?= BASE_URL ?>/<?= e($img['image_path']) ?>" class="card-img-top" alt="Imagen" style="height: 100px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            <form method="POST" action="<?= BASE_URL ?>/amenities/setPrimaryImage/<?= $img['id'] ?>" style="display: inline;">
                                                <?php if ($img['is_primary']): ?>
                                                    <span class="badge bg-success w-100 mb-1">Principal</span>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100 mb-1">
                                                        <i class="bi bi-star"></i> Hacer Principal
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            <form method="POST" action="<?= BASE_URL ?>/amenities/deleteImage/<?= $img['id'] ?>" style="display: inline;" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                                <button type="submit" class="btn btn-sm btn-danger w-100"><i class="bi bi-trash"></i> Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="images" class="form-label">Agregar Imágenes (opcional)</label>
                        <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                        <small class="text-muted">Puedes agregar más imágenes (JPG, PNG, GIF)</small>
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
