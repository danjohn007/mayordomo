<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><i class="bi bi-plus-circle"></i> Nuevo Bloqueo</h4></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/blocks/store">
                    <div class="mb-3">
                        <label for="resource_type" class="form-label">Tipo de Recurso *</label>
                        <select class="form-select" id="resource_type" name="resource_type" required onchange="updateResourceOptions(this.value)">
                            <option value="">Seleccionar...</option>
                            <option value="room">Habitación</option>
                            <option value="table">Mesa</option>
                            <option value="amenity">Amenidad</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="resource_id" class="form-label">Recurso *</label>
                        <select class="form-select" id="resource_id" name="resource_id" required>
                            <option value="">Seleccione primero el tipo</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Fecha Inicio *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Fecha Fin *</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Motivo del Bloqueo *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Crear Bloqueo</button>
                        <a href="<?= BASE_URL ?>/blocks" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const rooms = <?= json_encode($rooms) ?>;
const tables = <?= json_encode($tables) ?>;

function updateResourceOptions(type) {
    const select = document.getElementById('resource_id');
    select.innerHTML = '<option value="">Seleccionar...</option>';
    
    let items = [];
    if (type === 'room') items = rooms;
    else if (type === 'table') items = tables;
    
    items.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = type === 'room' ? `Habitación ${item.room_number}` : `Mesa ${item.table_number}`;
        select.appendChild(option);
    });
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
