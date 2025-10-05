<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Mesa</h4></div>
            <div class="card-body">
                <?php if ($flash = flash('error')): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= BASE_URL ?>/tables/update/<?= $table['id'] ?>" enctype="multipart/form-data">
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
                    
                    <?php
                    // Get existing images
                    $imageModel = getModel('ResourceImage', $db ?? null);
                    $images = $imageModel ? $imageModel->getByResource('table', $table['id']) : [];
                    ?>
                    
                    <?php if (!empty($images)): ?>
                    <div class="mb-3">
                        <label class="form-label">Imágenes Actuales</label>
                        <div class="row g-2">
                            <?php foreach ($images as $img): ?>
                                <div class="col-md-3">
                                    <div class="card">
                                        <img src="<?= BASE_URL ?>/public/<?= e($img['image_path']) ?>" class="card-img-top" alt="Imagen" style="height: 100px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            <form method="POST" action="<?= BASE_URL ?>/tables/setPrimaryImage/<?= $img['id'] ?>" style="display: inline;">
                                                <?php if ($img['is_primary']): ?>
                                                    <span class="badge bg-success w-100 mb-1">Principal</span>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100 mb-1">
                                                        <i class="bi bi-star"></i> Hacer Principal
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            <form method="POST" action="<?= BASE_URL ?>/tables/deleteImage/<?= $img['id'] ?>" style="display: inline;" onsubmit="return confirm('¿Eliminar esta imagen?')">
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
                        <a href="<?= BASE_URL ?>/tables" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
