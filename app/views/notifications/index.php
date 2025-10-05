<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-bell"></i> Notificaciones</h1>
        <?php if (!empty($notifications) && count(array_filter($notifications, fn($n) => !$n['is_read'])) > 0): ?>
        <button class="btn btn-outline-primary" onclick="markAllAsRead()">
            <i class="bi bi-check-all"></i> Marcar todas como leídas
        </button>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (!empty($notifications)): ?>
                <div class="list-group">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item list-group-item-action <?= $notification['is_read'] ? '' : 'list-group-item-light border-start border-primary border-3' ?>" 
                             style="cursor: pointer;" 
                             onclick="markAsRead(<?= $notification['id'] ?>)">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <?php
                                        // Icon based on notification type
                                        $icon = 'bell';
                                        switch($notification['notification_type']) {
                                            case 'room_reservation':
                                                $icon = 'door-closed';
                                                break;
                                            case 'table_reservation':
                                                $icon = 'table';
                                                break;
                                            case 'amenity_reservation':
                                                $icon = 'spa';
                                                break;
                                            case 'service_request':
                                                $icon = 'bell';
                                                break;
                                            case 'order_placed':
                                                $icon = 'cart';
                                                break;
                                        }
                                        ?>
                                        <i class="bi bi-<?= $icon ?>"></i>
                                        <?= e($notification['title']) ?>
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="badge bg-primary ms-2">Nueva</span>
                                        <?php endif; ?>
                                    </h6>
                                    <p class="mb-1"><?= e($notification['message']) ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i>
                                        <?php
                                        $date = new DateTime($notification['created_at']);
                                        echo $date->format('d/m/Y H:i');
                                        ?>
                                        
                                        <?php if ($notification['priority'] !== 'normal'): ?>
                                            <span class="badge bg-<?= $notification['priority'] === 'urgent' ? 'danger' : ($notification['priority'] === 'high' ? 'warning' : 'info') ?> ms-2">
                                                <?= ucfirst($notification['priority']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state py-5">
                    <i class="bi bi-bell" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">No hay notificaciones</h4>
                    <p class="text-muted">Cuando recibas notificaciones, aparecerán aquí</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch('<?= BASE_URL ?>/notifications/markAsRead/' + notificationId, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markAllAsRead() {
    fetch('<?= BASE_URL ?>/notifications/markAllAsRead', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
