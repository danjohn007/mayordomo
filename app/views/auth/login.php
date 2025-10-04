<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-building text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">MajorBot</h2>
                        <p class="text-muted">Sistema de Mayordomía Online</p>
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
                    
                    <form action="<?= BASE_URL ?>/auth/processLogin" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 text-end">
                            <a href="<?= BASE_URL ?>/auth/forgotPassword" class="text-muted small">
                                <i class="bi bi-question-circle"></i> ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="accept_terms" name="accept_terms" required>
                            <label class="form-check-label small" for="accept_terms">
                                Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">términos y condiciones</a>
                            </label>
                        </div>
                        
                        <?php 
                        $trialDaysDisplay = $trialDays ?? 30;
                        if ($trialDaysDisplay > 0): 
                        ?>
                        <div class="alert alert-success mb-3 py-2">
                            <small>
                                <i class="bi bi-gift"></i> 
                                <strong>¡Prueba gratis por <?= $trialDaysDisplay ?> días!</strong><br>
                                Puedes usar MajorBot completamente gratis durante tu período de prueba.
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">¿No tienes cuenta? 
                                <a href="<?= BASE_URL ?>/auth/register">Regístrate aquí</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php 
                $terms = getSetting('terms_and_conditions', '');
                if (!empty($terms)): 
                    echo nl2br(e($terms));
                else: 
                ?>
                <p>Al utilizar MajorBot, aceptas los siguientes términos y condiciones:</p>
                <ol>
                    <li>El uso del sistema está sujeto a las políticas de privacidad y términos de servicio.</li>
                    <li>Los datos proporcionados serán utilizados únicamente para la gestión del servicio.</li>
                    <li>El período de prueba gratuito está sujeto a los términos establecidos.</li>
                    <li>El usuario es responsable de mantener la confidencialidad de su cuenta.</li>
                    <li>MajorBot se reserva el derecho de modificar estos términos en cualquier momento.</li>
                </ol>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
