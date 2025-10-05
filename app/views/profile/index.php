<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="bi bi-person-circle"></i> Mi Perfil</h1>
            
            <?php if ($flash = flash('success')): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($flash = flash('error')): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Personal Information -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-person"></i> Información Personal</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/profile/update">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?= e($user['first_name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= e($user['last_name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= e($user['email']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= e($user['phone']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Rol</label>
                                    <input type="text" class="form-control" value="<?= getRoleLabel($user['role']) ?>" disabled>
                                </div>
                                
                                <?php if ($user['hotel_name']): ?>
                                <div class="mb-3">
                                    <label class="form-label">Hotel</label>
                                    <input type="text" class="form-control" value="<?= e($user['hotel_name']) ?>" disabled>
                                </div>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Actualizar Información
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Cambiar Contraseña</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/profile/changePassword">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="current_password" 
                                           name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" required minlength="6">
                                    <small class="text-muted">Mínimo 6 caracteres</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key"></i> Cambiar Contraseña
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Subscription Info (Admin only) -->
                <?php if (($user['role'] === 'admin' || $user['role'] === 'superadmin') && $subscription): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card"></i> Plan Activo</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Plan:</strong> <?= e($subscription['plan_name']) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Precio:</strong> <?= formatCurrency($subscription['price']) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Fecha Inicio:</strong> <?= formatDate($subscription['start_date']) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Fecha Fin:</strong> <?= formatDate($subscription['end_date']) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Días Restantes:</strong> 
                                <span class="badge bg-<?= $daysRemaining > 7 ? 'success' : ($daysRemaining > 0 ? 'warning' : 'danger') ?>">
                                    <?= $daysRemaining ?> días
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Estado:</strong> 
                                <?= getStatusBadge($subscription['status']) ?>
                            </div>
                            
                            <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/subscription" class="btn btn-primary">
                                <i class="bi bi-arrow-up-circle"></i> Actualizar Plan
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Referral Program -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-star"></i> Programa de Lealtad</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($referralInfo): ?>
                                <div class="mb-3">
                                    <strong>Código de Referido:</strong>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="referralCode" 
                                               value="<?= e($referralInfo['referral_code']) ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="copyReferralCode()">
                                            <i class="bi bi-clipboard"></i> Copiar
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Enlace de Referido:</strong>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="referralLink" 
                                               value="<?= BASE_URL ?>/auth/register?ref=<?= e($referralInfo['referral_code']) ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="copyReferralLink()">
                                            <i class="bi bi-clipboard"></i> Copiar
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border rounded p-2">
                                            <h4><?= number_format($referralInfo['total_referrals']) ?></h4>
                                            <small>Referencias</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border rounded p-2">
                                            <h4><?= formatCurrency($referralInfo['total_earnings']) ?></h4>
                                            <small>Total Ganado</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border rounded p-2">
                                            <h4><?= formatCurrency($referralInfo['available_balance']) ?></h4>
                                            <small>Disponible</small>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Únete al programa de lealtad y gana comisiones por cada referido.</p>
                                <form method="POST" action="<?= BASE_URL ?>/profile/referral">
                                    <button type="submit" class="btn btn-info">
                                        <i class="bi bi-star"></i> Activar Programa de Lealtad
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Payment History (Admin only) -->
                <?php if (($user['role'] === 'admin' || $user['role'] === 'superadmin') && !empty($payments)): ?>
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Pagos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID Transacción</th>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Método</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?= e($payment['transaction_id']) ?></td>
                                            <td><?= formatDateTime($payment['created_at']) ?></td>
                                            <td><?= formatCurrency($payment['amount']) ?></td>
                                            <td><?= strtoupper($payment['payment_method']) ?></td>
                                            <td><?= getStatusBadge($payment['status']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralCode() {
    const code = document.getElementById('referralCode');
    code.select();
    document.execCommand('copy');
    alert('Código copiado al portapapeles');
}

function copyReferralLink() {
    const link = document.getElementById('referralLink');
    link.select();
    document.execCommand('copy');
    alert('Enlace copiado al portapapeles');
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
