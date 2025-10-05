<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="bi bi-arrow-up-circle"></i> Actualizar Plan</h1>
            
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
            
            <?php if ($flash = flash('warning')): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Current Subscription -->
                <?php if ($currentSubscription): ?>
                <div class="col-12 mb-4">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Plan Actual</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Plan:</strong><br>
                                    <?= e($currentSubscription['plan_name']) ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Precio:</strong><br>
                                    <?= formatCurrency($currentSubscription['price']) ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Fecha Fin:</strong><br>
                                    <?= formatDate($currentSubscription['end_date']) ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Días Restantes:</strong><br>
                                    <span class="badge bg-<?= ($currentSubscription['days_remaining'] ?? 0) > 7 ? 'success' : (($currentSubscription['days_remaining'] ?? 0) > 0 ? 'warning' : 'danger') ?> fs-6">
                                        <?= $currentSubscription['days_remaining'] ?? 0 ?> días
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Available Plans -->
                <div class="col-12 mb-4">
                    <h3 class="mb-3"><i class="bi bi-card-list"></i> Planes Disponibles</h3>
                    <div class="row">
                        <?php foreach ($plans as $plan): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 <?= ($currentSubscription && $currentSubscription['subscription_id'] == $plan['id']) ? 'border-success' : '' ?>">
                                <div class="card-header text-center bg-primary text-white">
                                    <h4><?= e($plan['name']) ?></h4>
                                    <?php if ($currentSubscription && $currentSubscription['subscription_id'] == $plan['id']): ?>
                                        <span class="badge bg-success">Plan Actual</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="mb-3"><?= formatCurrency($plan['price']) ?></h2>
                                    <p class="text-muted"><?= $plan['duration_days'] ?> días</p>
                                    
                                    <?php if ($plan['description']): ?>
                                        <div class="mb-3 text-start">
                                            <?= nl2br(e($plan['description'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!$currentSubscription || $currentSubscription['subscription_id'] != $plan['id']): ?>
                                        <button type="button" class="btn btn-primary w-100 mb-2" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#paymentModal"
                                                data-plan-id="<?= $plan['id'] ?>"
                                                data-plan-name="<?= e($plan['name']) ?>"
                                                data-plan-price="<?= formatCurrency($plan['price']) ?>">
                                            <i class="bi bi-arrow-up-circle"></i> Seleccionar Plan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-credit-card"></i> Seleccionar Método de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Plan Seleccionado:</strong> <span id="selectedPlanName"></span><br>
                    <strong>Precio:</strong> <span id="selectedPlanPrice"></span>
                </div>
                
                <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="proof-tab" data-bs-toggle="tab" 
                                data-bs-target="#proof" type="button" role="tab">
                            <i class="bi bi-file-earmark-arrow-up"></i> Comprobante de Pago
                        </button>
                    </li>
                    <?php if ($paypalEnabled && $paypalClientId): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="paypal-tab" data-bs-toggle="tab" 
                                data-bs-target="#paypal" type="button" role="tab">
                            <i class="bi bi-paypal"></i> PayPal
                        </button>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <div class="tab-content mt-3" id="paymentTabContent">
                    <!-- Upload Payment Proof Tab -->
                    <div class="tab-pane fade show active" id="proof" role="tabpanel">
                        <form method="POST" action="<?= BASE_URL ?>/subscription/uploadProof" enctype="multipart/form-data">
                            <input type="hidden" name="plan_id" id="proofPlanId">
                            
                            <?php if (!empty($bankAccounts)): ?>
                            <div class="alert alert-info">
                                <h6><i class="bi bi-bank"></i> Información Bancaria para Transferencia:</h6>
                                <?php foreach ($bankAccounts as $account): ?>
                                <div class="mb-2">
                                    <strong><?= e($account['bank_name']) ?></strong><br>
                                    Titular: <?= e($account['account_holder']) ?><br>
                                    Número de Cuenta: <?= e($account['account_number']) ?><br>
                                    <?php if ($account['clabe']): ?>
                                    CLABE: <?= e($account['clabe']) ?><br>
                                    <?php endif; ?>
                                    <?php if ($account['swift']): ?>
                                    SWIFT: <?= e($account['swift']) ?><br>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Método de Pago *</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Selecciona un método</option>
                                    <option value="transfer">Transferencia Bancaria</option>
                                    <option value="deposit">Depósito Bancario</option>
                                    <option value="oxxo">OXXO</option>
                                    <option value="other">Otro</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="transaction_reference" class="form-label">Referencia de Transacción *</label>
                                <input type="text" class="form-control" id="transaction_reference" 
                                       name="transaction_reference" required 
                                       placeholder="Ej: Número de referencia, folio, etc.">
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_proof" class="form-label">Comprobante de Pago *</label>
                                <input type="file" class="form-control" id="payment_proof" 
                                       name="payment_proof" required 
                                       accept="image/*,.pdf">
                                <small class="text-muted">Formatos aceptados: Imágenes (JPG, PNG) o PDF</small>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle"></i> 
                                Tu comprobante será revisado por un administrador. 
                                Recibirás una notificación cuando tu plan sea activado.
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-upload"></i> Enviar Comprobante
                            </button>
                        </form>
                    </div>
                    
                    <!-- PayPal Tab -->
                    <?php if ($paypalEnabled && $paypalClientId): ?>
                    <div class="tab-pane fade" id="paypal" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Serás redirigido a PayPal para completar el pago de forma segura.
                        </div>
                        
                        <input type="hidden" id="paypalPlanId">
                        <input type="hidden" id="paypalAmount">
                        
                        <div id="paypal-button-container"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle payment modal
document.addEventListener('DOMContentLoaded', function() {
    const paymentModal = document.getElementById('paymentModal');
    
    paymentModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const planId = button.getAttribute('data-plan-id');
        const planName = button.getAttribute('data-plan-name');
        const planPrice = button.getAttribute('data-plan-price');
        
        document.getElementById('selectedPlanName').textContent = planName;
        document.getElementById('selectedPlanPrice').textContent = planPrice;
        document.getElementById('proofPlanId').value = planId;
        document.getElementById('paypalPlanId').value = planId;
        
        // Extract numeric price for PayPal
        const numericPrice = parseFloat(planPrice.replace(/[^\d.]/g, ''));
        document.getElementById('paypalAmount').value = numericPrice;
    });
});

<?php if ($paypalEnabled && $paypalClientId): ?>
// PayPal Integration
if (document.getElementById('paypal-button-container')) {
    const script = document.createElement('script');
    script.src = 'https://www.paypal.com/sdk/js?client-id=<?= e($paypalClientId) ?>&currency=MXN';
    script.onload = function() {
        paypal.Buttons({
            createOrder: function(data, actions) {
                const amount = document.getElementById('paypalAmount').value;
                const planId = document.getElementById('paypalPlanId').value;
                
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: amount
                        },
                        description: 'Actualización de Plan - MajorBot'
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    const planId = document.getElementById('paypalPlanId').value;
                    window.location.href = '<?= BASE_URL ?>/subscription/paypalSuccess?plan_id=' + planId + '&order_id=' + data.orderID;
                });
            },
            onCancel: function(data) {
                window.location.href = '<?= BASE_URL ?>/subscription/paypalCancel';
            },
            onError: function(err) {
                alert('Error al procesar el pago. Por favor intenta nuevamente.');
                console.error(err);
            }
        }).render('#paypal-button-container');
    };
    document.head.appendChild(script);
}
<?php endif; ?>
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
