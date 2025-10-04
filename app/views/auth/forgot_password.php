<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-key text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Recuperar Contraseña</h2>
                        <p class="text-muted">Ingresa tu email para recibir instrucciones</p>
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
                    
                    <form action="<?= BASE_URL ?>/auth/processForgotPassword" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                            <small class="text-muted">Te enviaremos un enlace para restablecer tu contraseña</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-send"></i> Enviar Enlace de Recuperación
                        </button>
                        
                        <div class="text-center">
                            <a href="<?= BASE_URL ?>/auth/login" class="text-muted">
                                <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
