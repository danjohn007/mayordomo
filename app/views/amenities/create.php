<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Amenidad</h4></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/amenities/store" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Categoría *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Seleccionar...</option>
                                <option value="wellness">Wellness</option>
                                <option value="fitness">Fitness</option>
                                <option value="entertainment">Entretenimiento</option>
                                <option value="transport">Transporte</option>
                                <option value="business">Negocios</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Precio</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="capacity" class="form-label">Capacidad</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="opening_time" class="form-label">Hora de Apertura</label>
                            <input type="time" class="form-control" id="opening_time" name="opening_time">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="closing_time" class="form-label">Hora de Cierre</label>
                            <input type="time" class="form-control" id="closing_time" name="closing_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_available" name="is_available" checked>
                        <label class="form-check-label" for="is_available">Disponible</label>
                    </div>
                    <div class="mb-3">
                        <label for="images" class="form-label">Imágenes (opcional)</label>
                        <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                        <small class="text-muted">Puedes seleccionar una o más imágenes (JPG, PNG, GIF)</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar</button>
                        <a href="<?= BASE_URL ?>/amenities" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
