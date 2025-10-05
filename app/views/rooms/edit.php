<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Habitación</h4>
            </div>
            <div class="card-body">
                <?php if ($flash = flash('error')): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= BASE_URL ?>/rooms/update/<?= $room['id'] ?>" class="needs-validation" enctype="multipart/form-data" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="room_number" class="form-label">Número de Habitación *</label>
                            <input type="text" class="form-control" id="room_number" name="room_number" 
                                   value="<?= e($room['room_number']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipo *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Seleccionar...</option>
                                <option value="single" <?= $room['type'] === 'single' ? 'selected' : '' ?>>Individual</option>
                                <option value="double" <?= $room['type'] === 'double' ? 'selected' : '' ?>>Doble</option>
                                <option value="suite" <?= $room['type'] === 'suite' ? 'selected' : '' ?>>Suite</option>
                                <option value="deluxe" <?= $room['type'] === 'deluxe' ? 'selected' : '' ?>>Deluxe</option>
                                <option value="presidential" <?= $room['type'] === 'presidential' ? 'selected' : '' ?>>Presidencial</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="floor" class="form-label">Piso</label>
                            <input type="number" class="form-control" id="floor" name="floor" min="1" 
                                   value="<?= e($room['floor']) ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="capacity" class="form-label">Capacidad *</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" 
                                   value="<?= e($room['capacity']) ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" 
                                       value="<?= e($room['price']) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="available" <?= $room['status'] === 'available' ? 'selected' : '' ?>>Disponible</option>
                            <option value="occupied" <?= $room['status'] === 'occupied' ? 'selected' : '' ?>>Ocupado</option>
                            <option value="maintenance" <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>Mantenimiento</option>
                            <option value="reserved" <?= $room['status'] === 'reserved' ? 'selected' : '' ?>>Reservado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= e($room['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amenities" class="form-label">Amenidades</label>
                        <textarea class="form-control" id="amenities" name="amenities" rows="2" 
                                  placeholder="Ej: TV, WiFi, Aire acondicionado, Mini-bar"><?= e($room['amenities']) ?></textarea>
                        <small class="text-muted">Separa las amenidades con comas</small>
                    </div>
                    
                    <?php
                    // Get existing images
                    $imageModel = getModel('ResourceImage', $db ?? null);
                    $images = $imageModel ? $imageModel->getByResource('room', $room['id']) : [];
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
                                            <form method="POST" action="<?= BASE_URL ?>/rooms/setPrimaryImage/<?= $img['id'] ?>" style="display: inline;">
                                                <?php if ($img['is_primary']): ?>
                                                    <span class="badge bg-success w-100 mb-1">Principal</span>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100 mb-1">
                                                        <i class="bi bi-star"></i> Hacer Principal
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            <form method="POST" action="<?= BASE_URL ?>/rooms/deleteImage/<?= $img['id'] ?>" style="display: inline;" onsubmit="return confirm('¿Eliminar esta imagen?')">
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
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Actualizar
                        </button>
                        <a href="<?= BASE_URL ?>/rooms" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
