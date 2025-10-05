<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-cash-stack"></i> Registro de Pagos</h1>
                <div>
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="bi bi-plus-circle"></i> Registrar Pago Manual
                    </button>
                    <a href="<?= BASE_URL ?>/superadmin" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Buscar</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Usuario, hotel o ID transacción..." 
                                   value="<?= e($search ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="<?= e($startDate ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="<?= e($endDate ?? '') ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="<?= BASE_URL ?>/superadmin/payments" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Hotel</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>ID Transacción</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay pagos registrados</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= e($payment['id']) ?></td>
                                        <td>
                                            <strong><?= e($payment['user_name']) ?></strong><br>
                                            <small class="text-muted"><?= e($payment['user_email']) ?></small>
                                        </td>
                                        <td><?= e($payment['hotel_name'] ?? '-') ?></td>
                                        <td><strong><?= formatCurrency($payment['amount']) ?></strong></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= strtoupper($payment['payment_method']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?= e($payment['transaction_id'] ?? '-') ?></code>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($payment['status']) ?>
                                        </td>
                                        <td><?= formatDateTime($payment['created_at']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Pago Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/superadmin/addPayment" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Usuario *</label>
                        <input type="number" class="form-control" id="user_id" name="user_id" required>
                        <small class="text-muted">ID del usuario</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Monto *</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago *</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash">Efectivo</option>
                            <option value="bank_transfer">Transferencia Bancaria</option>
                            <option value="credit_card">Tarjeta de Crédito</option>
                            <option value="debit_card">Tarjeta de Débito</option>
                            <option value="paypal">PayPal</option>
                            <option value="stripe">Stripe</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">ID de Transacción</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
