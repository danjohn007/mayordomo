<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-gear"></i> Configuraciones del Hotel</h1>
    </div>

    <?php if ($flash = flash('success')): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flash = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="<?= BASE_URL ?>/settings/save">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Configuración de Reservaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="allow_reservation_overlap" 
                                name="allow_reservation_overlap"
                                value="1"
                                <?= isset($settings['allow_reservation_overlap']) && $settings['allow_reservation_overlap'] ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="allow_reservation_overlap">
                                <strong>Permitir empalmar reservaciones con mismo horario y fecha</strong>
                            </label>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Información sobre esta configuración:</h6>
                            <p class="mb-2"><strong>Cuando está activada:</strong></p>
                            <ul class="mb-2">
                                <li>Se permite que múltiples huéspedes reserven el mismo recurso (habitación, mesa o amenidad) en el mismo horario y fecha.</li>
                                <li>No se validará la disponibilidad del recurso.</li>
                            </ul>
                            
                            <p class="mb-2"><strong>Cuando está desactivada (recomendado):</strong></p>
                            <ul class="mb-0">
                                <li><strong>Habitaciones:</strong> Se bloquean hasta las 15:00 hrs del día siguiente al check-in.</li>
                                <li><strong>Mesas y Amenidades:</strong> Se bloquean únicamente por 2 horas desde la hora de reservación.</li>
                                <li>El sistema validará que el recurso esté disponible antes de confirmar la reservación.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Configuraciones
                        </button>
                        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-question-circle"></i> Ayuda</h5>
                </div>
                <div class="card-body">
                    <h6>¿Cuándo usar esta configuración?</h6>
                    <p class="small">
                        Activa la opción de "Permitir empalmar reservaciones" solo en casos especiales como:
                    </p>
                    <ul class="small">
                        <li>Eventos especiales donde varios huéspedes pueden compartir el mismo espacio</li>
                        <li>Amenidades que permiten uso simultáneo (piscinas, gimnasios, etc.)</li>
                        <li>Pruebas o demostraciones del sistema</li>
                    </ul>
                    
                    <div class="alert alert-warning small mb-0">
                        <strong>⚠️ Precaución:</strong> Mantener esta opción activada permanentemente puede causar conflictos en las reservaciones y afectar la experiencia del huésped.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
