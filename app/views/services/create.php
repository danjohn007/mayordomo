<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Solicitud de Servicio</h4></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/services/store">
                    <div class="mb-3">
                        <label for="service_type_id" class="form-label">Tipo de Servicio *</label>
                        <select class="form-select" id="service_type_id" name="service_type_id" required>
                            <option value="">Seleccione un tipo de servicio...</option>
                            <?php if (!empty($serviceTypes)): ?>
                                <?php foreach ($serviceTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>">
                                        <?= e($type['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Descripción breve</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Opcional - descripción adicional">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Prioridad</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">Baja</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="room_number" class="form-label">Número de Habitación</label>
                            <input type="text" class="form-control" id="room_number" name="room_number">
                        </div>
                    </div>
                    
                    <?php if (!empty($collaborators) && hasRole(['admin', 'manager', 'hostess'])): ?>
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Asignar a Colaborador</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">Sin asignar</option>
                            <?php foreach ($collaborators as $collaborator): ?>
                                <option value="<?= $collaborator['id'] ?>">
                                    <?= e($collaborator['first_name'] . ' ' . $collaborator['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Seleccione un colaborador para asignar esta solicitud</small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Crear Solicitud</button>
                        <a href="<?= BASE_URL ?>/services" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
