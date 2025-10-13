<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-pencil"></i> Editar Código de Descuento</h1>
                <a href="<?= BASE_URL ?>/discount-codes" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <?php if ($flash = flash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/discount-codes/update/<?= $discountCode['id'] ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Código *</label>
                                <input type="text" class="form-control" id="code" name="code" required 
                                       value="<?= e($discountCode['code']) ?>" style="text-transform: uppercase;">
                                <small class="text-muted">El código será convertido a mayúsculas automáticamente</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="discount_type" class="form-label">Tipo de Descuento *</label>
                                <select class="form-select" id="discount_type" name="discount_type" required>
                                    <option value="percentage" <?= $discountCode['discount_type'] === 'percentage' ? 'selected' : '' ?>>Porcentaje (%)</option>
                                    <option value="fixed" <?= $discountCode['discount_type'] === 'fixed' ? 'selected' : '' ?>>Monto Fijo ($)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Monto del Descuento *</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" 
                                   value="<?= e($discountCode['amount']) ?>" required>
                            <small class="text-muted" id="amount_help">
                                <?= $discountCode['discount_type'] === 'percentage' 
                                    ? 'Ingrese el porcentaje de descuento (ej: 10 para 10%)'
                                    : 'Ingrese el monto fijo de descuento en pesos (ej: 50 para $50)' ?>
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="valid_from" class="form-label">Válido Desde *</label>
                                <input type="date" class="form-control" id="valid_from" name="valid_from" 
                                       value="<?= e($discountCode['valid_from']) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="valid_to" class="form-label">Válido Hasta *</label>
                                <input type="date" class="form-control" id="valid_to" name="valid_to" 
                                       value="<?= e($discountCode['valid_to']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="usage_limit" class="form-label">Límite de Uso (Opcional)</label>
                            <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" 
                                   value="<?= $discountCode['usage_limit'] ?? '' ?>"
                                   placeholder="Dejar vacío para uso ilimitado">
                            <small class="text-muted">
                                Número máximo de veces que se puede usar este código. Dejar vacío para ilimitado.
                                <br>Veces usado: <?= e($discountCode['times_used']) ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Descripción interna del código de descuento"><?= e($discountCode['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                   <?= $discountCode['active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="active">
                                Código Activo
                            </label>
                        </div>

                        <hr>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="<?= BASE_URL ?>/discount-codes" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('discount_type').addEventListener('change', function() {
    const amountHelp = document.getElementById('amount_help');
    if (this.value === 'percentage') {
        amountHelp.textContent = 'Ingrese el porcentaje de descuento (ej: 10 para 10%)';
    } else {
        amountHelp.textContent = 'Ingrese el monto fijo de descuento en pesos (ej: 50 para $50)';
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
