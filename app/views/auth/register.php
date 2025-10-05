<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-building text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Registrar Hotel</h2>
                        <p class="text-muted">Registro para Propietarios y Administradores de Hoteles</p>
                    </div>
                    
                    <?php 
                    $trialDays = $trialDays ?? 30;
                    if ($trialDays > 0): 
                    ?>
                    <div class="alert alert-success alert-permanent mb-3">
                        <i class="bi bi-gift"></i> 
                        <strong>¡Prueba gratis por <?= $trialDays ?> días!</strong><br>
                        Puedes usar MajorBot completamente gratis durante tu período de prueba.
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($flash = flash('error')): ?>
                        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                            <?= $flash['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= BASE_URL ?>/auth/processRegister" method="POST">
                        <div class="mb-3">
                            <label for="hotel_name" class="form-label">Nombre del Hotel o Alojamiento *</label>
                            <input type="text" class="form-control" id="hotel_name" name="hotel_name" required placeholder="Ej: Hotel Paradise">
                            <small class="text-muted">Este registro es exclusivo para propietarios/administradores de hoteles</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Apellido *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subscription_id" class="form-label">Plan de Suscripción *</label>
                            <select class="form-select" id="subscription_id" name="subscription_id" required>
                                <option value="">Selecciona un plan</option>
                                <?php foreach ($subscriptions as $sub): ?>
                                    <option value="<?= $sub['id'] ?>">
                                        <?= e($sub['name']) ?> - <?= formatCurrency($sub['price']) ?> 
                                        (<?= $sub['duration_days'] ?> días)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="accept_terms" name="accept_terms" required>
                            <label class="form-check-label small" for="accept_terms">
                                Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">términos y condiciones</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-check-circle"></i> Registrarse
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">¿Ya tienes cuenta? 
                                <a href="<?= BASE_URL ?>/auth/login">Inicia sesión aquí</a>
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
