<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Solicitud de Servicio</h4></div>
            <div class="card-body">
                <?php if ($flash = flash('error')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>
                
                <form method="POST" action="<?= BASE_URL ?>/services/update/<?= $service['id'] ?>">
                    <div class="mb-3">
                        <label for="service_type_id" class="form-label">Tipo de Servicio *</label>
                        <select class="form-select" id="service_type_id" name="service_type_id" required>
                            <option value="">Seleccione un tipo de servicio...</option>
                            <?php if (!empty($serviceTypes)): ?>
                                <?php foreach ($serviceTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>" <?= ($service['service_type_id'] == $type['id']) ? 'selected' : '' ?>>
                                        <?= e($type['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Descripción breve</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= e($service['title']) ?>" placeholder="Opcional - descripción adicional">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Prioridad *</label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low" <?= $service['priority'] === 'low' ? 'selected' : '' ?>>Baja</option>
                                <option value="normal" <?= $service['priority'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                                <option value="high" <?= $service['priority'] === 'high' ? 'selected' : '' ?>>Alta</option>
                                <option value="urgent" <?= $service['priority'] === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="room_number" class="form-label">Número de Habitación</label>
                            <input type="text" class="form-control" id="room_number" name="room_number" value="<?= e($service['room_number']) ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= e($service['description']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Estado *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?= $service['status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="assigned" <?= $service['status'] === 'assigned' ? 'selected' : '' ?>>Asignado</option>
                                <option value="in_progress" <?= $service['status'] === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                                <option value="completed" <?= $service['status'] === 'completed' ? 'selected' : '' ?>>Completado</option>
                                <option value="cancelled" <?= $service['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Actualizar</button>
                        <a href="<?= BASE_URL ?>/services" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
