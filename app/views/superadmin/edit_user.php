<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-pencil"></i> Editar Usuario</h1>
                <a href="<?= BASE_URL ?>/superadmin/users" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
            
            <?php if ($flash = flash('error')): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?= BASE_URL ?>/superadmin/editUser/<?= $user['id'] ?>">
                <div class="row">
                    <!-- User Information -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Información del Usuario</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?= e($user['first_name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Apellido *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= e($user['last_name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= e($user['email']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rol *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="manager" <?= $user['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                                        <option value="hostess" <?= $user['role'] === 'hostess' ? 'selected' : '' ?>>Hostess</option>
                                        <option value="collaborator" <?= $user['role'] === 'collaborator' ? 'selected' : '' ?>>Colaborador</option>
                                        <option value="guest" <?= $user['role'] === 'guest' ? 'selected' : '' ?>>Huésped</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                           <?= $user['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        Usuario Activo
                                    </label>
                                </div>
                                
                                <div class="alert alert-info">
                                    <small><i class="bi bi-info-circle"></i> Hotel asociado: <strong><?= e($user['hotel_name'] ?? 'Ninguno') ?></strong></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Subscription Management -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Asignar Suscripción</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($activeSubscription): ?>
                                    <div class="alert alert-success">
                                        <strong>Suscripción Activa:</strong> <?= e($activeSubscription['plan_name']) ?><br>
                                        <?php if ($activeSubscription['is_unlimited']): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-infinity"></i> Plan Ilimitado (Sin vencimiento)
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">Vence: <?= formatDate($activeSubscription['end_date']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Este usuario no tiene una suscripción activa
                                    </div>
                                <?php endif; ?>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="assign_plan" name="assign_plan" 
                                           onchange="togglePlanSelection()">
                                    <label class="form-check-label" for="assign_plan">
                                        <strong>Asignar o Cambiar Plan</strong>
                                    </label>
                                </div>
                                
                                <div id="planSelection" style="display: none;">
                                    <div class="mb-3">
                                        <label for="plan_id" class="form-label">Seleccionar Plan *</label>
                                        <select class="form-select" id="plan_id" name="plan_id">
                                            <option value="">-- Seleccionar Plan --</option>
                                            <?php foreach ($plans as $plan): ?>
                                                <option value="<?= $plan['id'] ?>">
                                                    <?= e($plan['name']) ?> - <?= formatCurrency($plan['price']) ?> / <?= ucfirst($plan['billing_cycle']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">El plan actual será cancelado y se asignará el nuevo</small>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_unlimited" name="is_unlimited">
                                        <label class="form-check-label" for="is_unlimited">
                                            <strong><i class="bi bi-infinity"></i> Plan Ilimitado (Sin vigencia)</strong>
                                        </label>
                                        <br>
                                        <small class="text-muted">
                                            Al marcar esta opción, el plan no tendrá fecha de vencimiento
                                        </small>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <small>
                                            <i class="bi bi-info-circle"></i> 
                                            <strong>Nota:</strong> Si no marca "Plan Ilimitado", el plan tendrá vigencia según su ciclo de facturación (mensual o anual).
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                            <a href="<?= BASE_URL ?>/superadmin/viewUser/<?= $user['id'] ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a>
                            <a href="<?= BASE_URL ?>/superadmin/users" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePlanSelection() {
    const checkbox = document.getElementById('assign_plan');
    const planSelection = document.getElementById('planSelection');
    
    if (checkbox.checked) {
        planSelection.style.display = 'block';
        document.getElementById('plan_id').required = true;
    } else {
        planSelection.style.display = 'none';
        document.getElementById('plan_id').required = false;
    }
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
