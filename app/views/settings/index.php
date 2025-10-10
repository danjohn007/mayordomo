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
                                id="allow_table_overlap" 
                                name="allow_table_overlap"
                                value="1"
                                <?= isset($settings['allow_table_overlap']) && $settings['allow_table_overlap'] ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="allow_table_overlap">
                                <strong>Permitir empalmar reservaciones de mesas con mismo horario y fecha</strong>
                            </label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="allow_room_overlap" 
                                name="allow_room_overlap"
                                value="1"
                                <?= isset($settings['allow_room_overlap']) && $settings['allow_room_overlap'] ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="allow_room_overlap">
                                <strong>Permitir empalmar reservaciones de habitaciones con mismo horario y fecha</strong>
                            </label>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Información sobre estas configuraciones:</h6>
                            <p class="mb-2"><strong>Mesas:</strong></p>
                            <ul class="mb-2">
                                <li><strong>Activada (por defecto):</strong> Múltiples huéspedes pueden reservar la misma mesa.</li>
                                <li><strong>Desactivada:</strong> Se bloquean por 2 horas desde la hora de reservación.</li>
                            </ul>
                            
                            <p class="mb-2"><strong>Habitaciones:</strong></p>
                            <ul class="mb-2">
                                <li><strong>Activada:</strong> Múltiples huéspedes pueden reservar la misma habitación.</li>
                                <li><strong>Desactivada (por defecto):</strong> Se bloquean por 21 horas de 15:00 a 12:00 del día siguiente.</li>
                            </ul>
                            
                            <p class="mb-2"><strong>Amenidades:</strong></p>
                            <ul class="mb-0">
                                <li>Las amenidades tienen su propia configuración individual.</li>
                                <li>Cada amenidad puede configurarse para permitir o no empalme.</li>
                                <li>Cuando no se permite empalme, se puede definir capacidad máxima y tiempo de bloqueo.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Catálogo de Tipos de Servicio -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Catálogo de Tipos de Servicio</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestiona los tipos de servicio disponibles para las solicitudes de los huéspedes.</p>
                        
                        <button type="button" class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#addServiceTypeModal">
                            <i class="bi bi-plus-circle"></i> Agregar Tipo de Servicio
                        </button>
                        
                        <?php if (!empty($serviceTypes)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Icono</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Orden</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($serviceTypes as $type): ?>
                                            <tr>
                                                <td><i class="<?= e($type['icon']) ?>"></i></td>
                                                <td><strong><?= e($type['name']) ?></strong></td>
                                                <td><small><?= e($type['description']) ?></small></td>
                                                <td><?= e($type['sort_order']) ?></td>
                                                <td>
                                                    <?php if ($type['is_active']): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="editServiceType(<?= $type['id'] ?>, '<?= e($type['name']) ?>', '<?= e($type['description']) ?>', '<?= e($type['icon']) ?>', <?= $type['sort_order'] ?>, <?= $type['is_active'] ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No hay tipos de servicio configurados.</div>
                        <?php endif; ?>
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

<!-- Modal: Agregar Tipo de Servicio -->
<div class="modal fade" id="addServiceTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= BASE_URL ?>/settings/addServiceType">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Tipo de Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icono Bootstrap (ej: bi-wrench)</label>
                        <input type="text" class="form-control" name="icon" value="bi-wrench" placeholder="bi-wrench">
                        <small class="text-muted">Ver iconos en: <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <input type="number" class="form-control" name="sort_order" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Tipo de Servicio -->
<div class="modal fade" id="editServiceTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editServiceTypeForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Tipo de Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icono Bootstrap</label>
                        <input type="text" class="form-control" name="icon" id="edit_icon" placeholder="bi-wrench">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <input type="number" class="form-control" name="sort_order" id="edit_sort_order">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function editServiceType(id, name, description, icon, sortOrder, isActive) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_icon').value = icon;
    document.getElementById('edit_sort_order').value = sortOrder;
    document.getElementById('edit_is_active').checked = isActive == 1;
    document.getElementById('editServiceTypeForm').action = '<?= BASE_URL ?>/settings/editServiceType/' + id;
    
    var modal = new bootstrap.Modal(document.getElementById('editServiceTypeModal'));
    modal.show();
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
