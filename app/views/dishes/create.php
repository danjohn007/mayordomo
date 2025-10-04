<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> Nuevo Platillo</h4></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/dishes/store">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Nombre del Platillo *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Categoría *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Seleccionar...</option>
                                <option value="appetizer">Entrada</option>
                                <option value="main_course">Plato Principal</option>
                                <option value="dessert">Postre</option>
                                <option value="beverage">Bebida</option>
                                <option value="breakfast">Desayuno</option>
                                <option value="lunch">Comida</option>
                                <option value="dinner">Cena</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="service_time" class="form-label">Tiempo de Servicio</label>
                            <select class="form-select" id="service_time" name="service_time">
                                <option value="all_day">Todo el día</option>
                                <option value="breakfast">Desayuno</option>
                                <option value="lunch">Comida</option>
                                <option value="dinner">Cena</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_available" name="is_available" checked>
                        <label class="form-check-label" for="is_available">Disponible para ordenar</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar</button>
                        <a href="<?= BASE_URL ?>/dishes" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
