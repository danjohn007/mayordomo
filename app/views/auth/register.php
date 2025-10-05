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
                    
                    <?php if (!empty($referrerName)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-person-check"></i>
                            <strong>¡Has sido recomendado!</strong><br>
                            <?= e($referrerName) ?> te ha recomendado usar MajorBot.
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= BASE_URL ?>/auth/processRegister" method="POST" enctype="multipart/form-data">
                        <?php if (!empty($referralCode)): ?>
                            <input type="hidden" name="referral_code" value="<?= e($referralCode) ?>">
                        <?php endif; ?>
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
                            <label for="phone" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9]{10}" maxlength="10" placeholder="10 dígitos">
                            <small class="text-muted">Debe contener exactamente 10 dígitos</small>
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
                            <select class="form-select" id="subscription_id" name="subscription_id" required onchange="handlePlanChange()">
                                <option value="">Selecciona un plan</option>
                                <?php foreach ($subscriptions as $sub): ?>
                                    <option value="<?= $sub['id'] ?>" 
                                            data-price="<?= $sub['price'] ?>"
                                            data-type="<?= $sub['type'] ?? 'paid' ?>"
                                            data-name="<?= e($sub['name']) ?>">
                                        <?= e($sub['name']) ?> - <?= formatCurrency($sub['price']) ?> 
                                        (<?= $sub['duration_days'] ?> días)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Payment Options (shown for paid plans) -->
                        <div id="paymentOptions" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Opciones de Pago Disponibles:</strong><br>
                                Puedes pagar con PayPal o subir un comprobante de pago para validación manual.
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Método de Pago</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_option" 
                                           id="payment_later" value="later" checked onchange="togglePaymentForms()">
                                    <label class="form-check-label" for="payment_later">
                                        <i class="bi bi-clock"></i> Pagar después (acceso inmediato al período de prueba)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_option" 
                                           id="payment_proof" value="proof" onchange="togglePaymentForms()">
                                    <label class="form-check-label" for="payment_proof">
                                        <i class="bi bi-file-earmark-arrow-up"></i> Subir comprobante de pago
                                    </label>
                                </div>
                                <?php if ($paypalEnabled ?? false): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_option" 
                                           id="payment_paypal" value="paypal" onchange="togglePaymentForms()">
                                    <label class="form-check-label" for="payment_paypal">
                                        <i class="bi bi-paypal"></i> Pagar con PayPal
                                    </label>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Payment Proof Form -->
                            <div id="proofForm" style="display: none;">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-file-earmark-arrow-up"></i> Comprobante de Pago</h6>
                                        
                                        <?php if (!empty($bankAccounts ?? [])): ?>
                                        <div class="alert alert-secondary">
                                            <h6><i class="bi bi-bank"></i> Información Bancaria:</h6>
                                            <?php foreach ($bankAccounts as $account): ?>
                                            <div class="mb-2">
                                                <strong><?= e($account['bank_name']) ?></strong><br>
                                                Titular: <?= e($account['account_holder']) ?><br>
                                                Cuenta: <?= e($account['account_number']) ?>
                                                <?php if ($account['clabe']): ?><br>CLABE: <?= e($account['clabe']) ?><?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="mb-2">
                                            <label for="reg_payment_method" class="form-label">Método de Pago</label>
                                            <select class="form-select form-select-sm" id="reg_payment_method" name="reg_payment_method">
                                                <option value="transfer">Transferencia Bancaria</option>
                                                <option value="deposit">Depósito Bancario</option>
                                                <option value="oxxo">OXXO</option>
                                                <option value="other">Otro</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <label for="reg_transaction_reference" class="form-label">Referencia</label>
                                            <input type="text" class="form-control form-control-sm" 
                                                   id="reg_transaction_reference" name="reg_transaction_reference"
                                                   placeholder="Número de referencia o folio">
                                        </div>
                                        
                                        <div class="mb-2">
                                            <label for="reg_payment_proof" class="form-label">Archivo</label>
                                            <input type="file" class="form-control form-control-sm" 
                                                   id="reg_payment_proof" name="reg_payment_proof"
                                                   accept="image/*,.pdf">
                                            <small class="text-muted">JPG, PNG o PDF</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PayPal Form -->
                            <?php if ($paypalEnabled ?? false): ?>
                            <div id="paypalForm" style="display: none;">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-paypal"></i> Pagar con PayPal</h6>
                                        <p class="text-muted small">Después de registrarte, serás redirigido a PayPal para completar el pago.</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
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

<script>
function handlePlanChange() {
    const select = document.getElementById('subscription_id');
    const selectedOption = select.options[select.selectedIndex];
    const paymentOptions = document.getElementById('paymentOptions');
    
    if (selectedOption.value) {
        const planType = selectedOption.getAttribute('data-type');
        const price = parseFloat(selectedOption.getAttribute('data-price'));
        
        // Show payment options only for paid plans (price > 0)
        if (price > 0) {
            paymentOptions.style.display = 'block';
        } else {
            paymentOptions.style.display = 'none';
        }
    } else {
        paymentOptions.style.display = 'none';
    }
}

function togglePaymentForms() {
    const proofForm = document.getElementById('proofForm');
    const paypalForm = document.getElementById('paypalForm');
    
    if (document.getElementById('payment_proof').checked) {
        proofForm.style.display = 'block';
        if (paypalForm) paypalForm.style.display = 'none';
        
        // Make proof fields required
        document.getElementById('reg_payment_method').required = true;
        document.getElementById('reg_transaction_reference').required = true;
        document.getElementById('reg_payment_proof').required = true;
    } else if (document.getElementById('payment_paypal') && document.getElementById('payment_paypal').checked) {
        proofForm.style.display = 'none';
        if (paypalForm) paypalForm.style.display = 'block';
        
        // Make proof fields not required
        document.getElementById('reg_payment_method').required = false;
        document.getElementById('reg_transaction_reference').required = false;
        document.getElementById('reg_payment_proof').required = false;
    } else {
        proofForm.style.display = 'none';
        if (paypalForm) paypalForm.style.display = 'none';
        
        // Make proof fields not required
        document.getElementById('reg_payment_method').required = false;
        document.getElementById('reg_transaction_reference').required = false;
        document.getElementById('reg_payment_proof').required = false;
    }
}
</script>

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
