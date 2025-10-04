<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Restablecer Contraseña</h2>
                        <p class="text-muted">Ingresa tu nueva contraseña</p>
                    </div>
                    
                    <?php if ($flash = flash('error')): ?>
                        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                            <?= $flash['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($flash = flash('success')): ?>
                        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                            <?= $flash['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($valid) && $valid): ?>
                    <form action="<?= BASE_URL ?>/auth/processResetPassword" method="POST">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-check-circle"></i> Restablecer Contraseña
                        </button>
                        
                        <div class="text-center">
                            <a href="<?= BASE_URL ?>/auth/login" class="text-muted">
                                <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                            </a>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Este enlace de recuperación es inválido o ha expirado.
                    </div>
                    <div class="text-center">
                        <a href="<?= BASE_URL ?>/auth/forgotPassword" class="btn btn-primary">
                            Solicitar Nuevo Enlace
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
