<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-lock"></i> Sistema de Bloqueos</h1>
    <a href="<?= BASE_URL ?>/blocks/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Bloqueo</a>
</div>

<?php if ($flash = flash('success')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>

<div class="card">
    <div class="card-body">
        <?php if (!empty($blocks)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Recurso ID</th>
                            <th>Motivo</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Bloqueado Por</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blocks as $block): ?>
                            <tr>
                                <td><?= ucfirst($block['resource_type']) ?></td>
                                <td><?= e($block['resource_id']) ?></td>
                                <td><?= e($block['reason']) ?></td>
                                <td><?= formatDate($block['start_date']) ?></td>
                                <td><?= formatDate($block['end_date']) ?></td>
                                <td><?= e($block['first_name']) ?> <?= e($block['last_name']) ?></td>
                                <td><?= getStatusBadge($block['status']) ?></td>
                                <td class="action-buttons">
                                    <?php if ($block['status'] === 'active'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/blocks/release/<?= $block['id'] ?>" style="display: inline-block;">
                                            <button type="submit" class="btn btn-sm btn-success" title="Liberar">
                                                <i class="bi bi-unlock"></i> Liberar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-lock"></i>
                <h4>No hay bloqueos registrados</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
