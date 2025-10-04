<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-star"></i> Programa de Lealtad</h1>
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
                                    <th>Rol</th>
                                    <th>Código Referido</th>
                                    <th>Total Referencias</th>
                                    <th>Total Ganado</th>
                                    <th>Disponible</th>
                                    <th>Retirado</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No hay miembros en el programa de lealtad</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?= e($member['id']) ?></td>
                                        <td>
                                            <strong><?= e($member['user_name']) ?></strong><br>
                                            <small class="text-muted"><?= e($member['user_email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $member['user_role'] === 'superadmin' ? 'danger' : ($member['user_role'] === 'admin' ? 'primary' : 'secondary') ?>">
                                                <?= getRoleLabel($member['user_role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?= e($member['referral_code']) ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= number_format($member['total_referrals']) ?></span>
                                        </td>
                                        <td><strong><?= formatCurrency($member['total_earnings']) ?></strong></td>
                                        <td><?= formatCurrency($member['available_balance']) ?></td>
                                        <td><?= formatCurrency($member['withdrawn_balance']) ?></td>
                                        <td>
                                            <?php if ($member['is_active']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($member['created_at']) ?></td>
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
