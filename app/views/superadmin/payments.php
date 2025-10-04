<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-cash-stack"></i> Registro de Pagos</h1>
                <a href="<?= BASE_URL ?>/superadmin" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
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

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
