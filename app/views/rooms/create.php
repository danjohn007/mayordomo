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
                            <label for="price" class="form-label">Precio Base *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                            </div>
                            <small class="text-muted">Este será el precio por defecto para todos los días</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <strong>Precios por Día de la Semana</strong>
                            <small class="text-muted">(Opcional - si no se especifica, se usa el precio base)</small>
                        </label>
                        <div class="row g-2">
                            <div class="col-md-3 col-6">
                                <label for="price_monday" class="form-label small">Lunes</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price_monday" name="price_monday" min="0" step="0.01" placeholder="Precio base">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="price_tuesday" class="form-label small">Martes</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price_tuesday" name="price_tuesday" min="0" step="0.01" placeholder="Precio base">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="price_wednesday" class="form-label small">Miércoles</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price_wednesday" name="price_wednesday" min="0" step="0.01" placeholder="Precio base">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="price_thursday" class="form-label small">Jueves</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price_thursday" name="price_thursday" min="0" step="0.01" placeholder="Precio base">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="price_friday" class="form-label small">Viernes</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price_friday" name="price_friday" min="0" step="0.01" placeholder="Precio base">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="price_saturday" class="form-label small">Sábado</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price_saturday" name="price_saturday" min="0" step="0.01" placeholder="Precio base">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="price_sunday" class="form-label small">Domingo</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price_sunday" name="price_sunday" min="0" step="0.01" placeholder="Precio base">
                                </div>
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
