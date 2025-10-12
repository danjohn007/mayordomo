<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-tag"></i> Códigos de Descuento</h1>
        <div>
            <a href="<?= BASE_URL ?>/discount-codes/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Código
            </a>
            <a href="<?= BASE_URL ?>/settings" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Configuraciones
            </a>
        </div>
    </div>

    <?php if ($flash = flash('success')): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flash = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (!empty($discountCodes)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Descuento</th>
                                <th>Validez</th>
                                <th>Uso</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($discountCodes as $code): ?>
                                <?php
                                $today = date('Y-m-d');
                                $isExpired = $code['valid_to'] < $today;
                                $isNotStarted = $code['valid_from'] > $today;
                                $isLimitReached = $code['usage_limit'] !== null && $code['times_used'] >= $code['usage_limit'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= e($code['code']) ?></strong>
                                        <?php if (!empty($code['description'])): ?>
                                            <br><small class="text-muted"><?= e($code['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($code['discount_type'] === 'percentage'): ?>
                                            <span class="badge bg-info">Porcentaje</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Monto Fijo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($code['discount_type'] === 'percentage'): ?>
                                            <strong><?= e($code['amount']) ?>%</strong>
                                        <?php else: ?>
                                            <strong>$<?= number_format($code['amount'], 2) ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            Del: <?= date('d/m/Y', strtotime($code['valid_from'])) ?><br>
                                            Al: <?= date('d/m/Y', strtotime($code['valid_to'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= e($code['times_used']) ?> 
                                        <?php if ($code['usage_limit'] !== null): ?>
                                            / <?= e($code['usage_limit']) ?>
                                        <?php else: ?>
                                            / <span class="text-muted">∞</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$code['active']): ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php elseif ($isExpired): ?>
                                            <span class="badge bg-danger">Expirado</span>
                                        <?php elseif ($isNotStarted): ?>
                                            <span class="badge bg-warning">No Iniciado</span>
                                        <?php elseif ($isLimitReached): ?>
                                            <span class="badge bg-danger">Límite Alcanzado</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= BASE_URL ?>/discount-codes/edit/<?= $code['id'] ?>" class="btn btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $code['id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay códigos de descuento creados. 
                    <a href="<?= BASE_URL ?>/discount-codes/create">Crear el primero</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este código de descuento?</p>
                <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = '<?= BASE_URL ?>/discount-codes/delete/' + id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
