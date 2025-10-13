<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-pencil"></i> Editar Reservación</h1>
                <a href="<?= BASE_URL ?>/reservations" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/reservations/update/<?= $reservation['id'] ?>">
                        <input type="hidden" name="type" value="<?= e($type) ?>">
                        
                        <!-- Información del recurso -->
                        <div class="mb-3">
                            <label class="form-label"><strong>Recurso:</strong></label>
                            <div class="form-control-plaintext">
                                <?php if ($type === 'room'): ?>
                                    <span class="badge bg-info"><i class="bi bi-door-closed"></i> Habitación <?= e($reservation['room_number']) ?></span>
                                    <?php if (isset($reservation['room_type'])): ?>
                                        - <?= ucfirst($reservation['room_type']) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-success"><i class="bi bi-table"></i> Mesa <?= e($reservation['table_number']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr>

                        <!-- Información del huésped -->
                        <h5 class="mb-3">Información del Huésped</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_name" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="guest_name" name="guest_name" 
                                       value="<?= e($reservation['guest_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="guest_email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="guest_email" name="guest_email" 
                                       value="<?= e($reservation['guest_email'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_phone" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="guest_phone" name="guest_phone" 
                                       value="<?= e($reservation['guest_phone'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="guest_birthday" class="form-label">Fecha de Cumpleaños</label>
                                <input type="date" class="form-control" id="guest_birthday" name="guest_birthday" 
                                       value="<?= e($reservation['guest_birthday'] ?? '') ?>">
                            </div>
                        </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Estado *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <?php if ($type === 'room'): ?>
                                        <option value="pending" <?= ($reservation['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="confirmed" <?= ($reservation['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmada</option>
                                        <option value="checked_in" <?= ($reservation['status'] ?? '') === 'checked_in' ? 'selected' : '' ?>>Check-in Realizado</option>
                                        <option value="checked_out" <?= ($reservation['status'] ?? '') === 'checked_out' ? 'selected' : '' ?>>Check-out Realizado</option>
                                        <option value="cancelled" <?= ($reservation['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                                    <?php else: ?>
                                        <option value="pending" <?= ($reservation['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="confirmed" <?= ($reservation['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmada</option>
                                        <option value="seated" <?= ($reservation['status'] ?? '') === 'seated' ? 'selected' : '' ?>>Cliente Sentado</option>
                                        <option value="completed" <?= ($reservation['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completada</option>
                                        <option value="cancelled" <?= ($reservation['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <!-- Fechas y detalles -->
                        <h5 class="mb-3">Detalles de la Reservación</h5>

                        <?php if ($type === 'room'): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="check_in" class="form-label">Fecha de Check-in *</label>
                                    <input type="date" class="form-control" id="check_in" name="check_in" 
                                           value="<?= e($reservation['check_in'] ?? '') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="check_out" class="form-label">Fecha de Check-out *</label>
                                    <input type="date" class="form-control" id="check_out" name="check_out" 
                                           value="<?= e($reservation['check_out'] ?? '') ?>" required>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="reservation_date" class="form-label">Fecha *</label>
                                    <input type="date" class="form-control" id="reservation_date" name="reservation_date" 
                                           value="<?= e($reservation['reservation_date'] ?? '') ?>" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="reservation_time" class="form-label">Hora *</label>
                                    <input type="time" class="form-control" id="reservation_time" name="reservation_time" 
                                           value="<?= e($reservation['reservation_time'] ?? '') ?>" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="party_size" class="form-label">Personas *</label>
                                    <input type="number" class="form-control" id="party_size" name="party_size" 
                                           value="<?= e($reservation['party_size'] ?? 1) ?>" min="1" required>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas / Solicitudes Especiales</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?= e($reservation['notes'] ?? '') ?></textarea>
                        </div>

                        <hr>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= BASE_URL ?>/reservations" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
