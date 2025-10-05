<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Habitación</h4>
            </div>
            <div class="card-body">
                <?php if ($flash = flash('error')): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= BASE_URL ?>/rooms/store" class="needs-validation" enctype="multipart/form-data" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="room_number" class="form-label">Número de Habitación *</label>
                            <input type="text" class="form-control" id="room_number" name="room_number" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipo *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Seleccionar...</option>
                                <option value="single">Individual</option>
                                <option value="double">Doble</option>
                                <option value="suite">Suite</option>
                                <option value="deluxe">Deluxe</option>
                                <option value="presidential">Presidencial</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="floor" class="form-label">Piso</label>
                            <input type="number" class="form-control" id="floor" name="floor" min="1">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="capacity" class="form-label">Capacidad *</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" value="2" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="available">Disponible</option>
                            <option value="occupied">Ocupado</option>
                            <option value="maintenance">Mantenimiento</option>
                            <option value="reserved">Reservado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amenities" class="form-label">Amenidades</label>
                        <textarea class="form-control" id="amenities" name="amenities" rows="2" 
                                  placeholder="Ej: TV, WiFi, Aire acondicionado, Mini-bar"></textarea>
                        <small class="text-muted">Separa las amenidades con comas</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="images" class="form-label">Imágenes (opcional)</label>
                        <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                        <small class="text-muted">Puedes seleccionar una o más imágenes (JPG, PNG, GIF). Primera imagen será la principal.</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar
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
