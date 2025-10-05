<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-shield-check"></i> Gestión de Roles y Permisos</h1>
</div>

<?php if ($flash = flash('success')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>
<?php if ($flash = flash('error')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    <strong>Gestión de Áreas por Rol:</strong> Asigna permisos específicos a cada colaborador para recibir notificaciones 
    de las áreas que le corresponden (Habitaciones, Mesas, Menú, Amenidades específicas y Servicios específicos).
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($users)): ?>
            <div class="accordion" id="rolesAccordion">
                <?php foreach ($users as $index => $user): 
                    $userFullName = e($user['first_name'] . ' ' . $user['last_name']);
                    $collapseId = 'collapse' . $user['id'];
                    $amenityIdsArray = $user['amenity_ids'] ? json_decode($user['amenity_ids'], true) : [];
                    $serviceTypesArray = $user['service_types'] ? json_decode($user['service_types'], true) : [];
                ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $user['id'] ?>">
                            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" 
                                    aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" 
                                    aria-controls="<?= $collapseId ?>">
                                <div class="d-flex align-items-center w-100">
                                    <div class="flex-grow-1">
                                        <strong><?= $userFullName ?></strong>
                                        <span class="badge bg-primary ms-2"><?= ucfirst($user['role']) ?></span>
                                    </div>
                                    <small class="text-muted me-3"><?= e($user['email']) ?></small>
                                </div>
                            </button>
                        </h2>
                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                             aria-labelledby="heading<?= $user['id'] ?>" data-bs-parent="#rolesAccordion">
                            <div class="accordion-body">
                                <form method="POST" action="<?= BASE_URL ?>/roles/update/<?= $user['id'] ?>">
                                    
                                    <h6 class="mb-3"><i class="bi bi-briefcase"></i> Permisos de Áreas Generales</h6>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="rooms_<?= $user['id'] ?>" 
                                                       name="can_manage_rooms" 
                                                       value="1"
                                                       <?= $user['can_manage_rooms'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="rooms_<?= $user['id'] ?>">
                                                    <i class="bi bi-door-closed text-primary"></i> 
                                                    <strong>Habitaciones</strong>
                                                    <br><small class="text-muted">Notificaciones de reservas y servicios de habitaciones</small>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="tables_<?= $user['id'] ?>" 
                                                       name="can_manage_tables" 
                                                       value="1"
                                                       <?= $user['can_manage_tables'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="tables_<?= $user['id'] ?>">
                                                    <i class="bi bi-table text-success"></i> 
                                                    <strong>Mesas</strong>
                                                    <br><small class="text-muted">Notificaciones de reservas de mesas</small>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="menu_<?= $user['id'] ?>" 
                                                       name="can_manage_menu" 
                                                       value="1"
                                                       <?= $user['can_manage_menu'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="menu_<?= $user['id'] ?>">
                                                    <i class="bi bi-egg-fried text-warning"></i> 
                                                    <strong>Menú</strong>
                                                    <br><small class="text-muted">Notificaciones de pedidos de platillos</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="mb-3"><i class="bi bi-spa"></i> Amenidades Específicas</h6>
                                    <div class="mb-4">
                                        <p class="text-muted small">Selecciona las amenidades para las cuales este usuario recibirá notificaciones:</p>
                                        <?php if (!empty($amenities)): ?>
                                            <div class="row">
                                                <?php foreach ($amenities as $amenity): ?>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   id="amenity_<?= $user['id'] ?>_<?= $amenity['id'] ?>" 
                                                                   name="amenity_ids[]" 
                                                                   value="<?= $amenity['id'] ?>"
                                                                   <?= in_array($amenity['id'], $amenityIdsArray) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="amenity_<?= $user['id'] ?>_<?= $amenity['id'] ?>">
                                                                <?= e($amenity['name']) ?>
                                                                <small class="text-muted">(<?= ucfirst($amenity['category']) ?>)</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">No hay amenidades configuradas</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="mb-3"><i class="bi bi-bell"></i> Tipos de Servicios Específicos</h6>
                                    <div class="mb-4">
                                        <p class="text-muted small">Selecciona los tipos de servicios que este usuario atenderá:</p>
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="service_cleaning_<?= $user['id'] ?>" 
                                                           name="service_types[]" 
                                                           value="cleaning"
                                                           <?= in_array('cleaning', $serviceTypesArray) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="service_cleaning_<?= $user['id'] ?>">
                                                        Limpieza
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="service_maintenance_<?= $user['id'] ?>" 
                                                           name="service_types[]" 
                                                           value="maintenance"
                                                           <?= in_array('maintenance', $serviceTypesArray) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="service_maintenance_<?= $user['id'] ?>">
                                                        Mantenimiento
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="service_room_<?= $user['id'] ?>" 
                                                           name="service_types[]" 
                                                           value="room_service"
                                                           <?= in_array('room_service', $serviceTypesArray) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="service_room_<?= $user['id'] ?>">
                                                        Servicio a habitación
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="service_concierge_<?= $user['id'] ?>" 
                                                           name="service_types[]" 
                                                           value="concierge"
                                                           <?= in_array('concierge', $serviceTypesArray) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="service_concierge_<?= $user['id'] ?>">
                                                        Conserjería
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Guardar Permisos
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">No hay colaboradores</h4>
                <p class="text-muted">No hay usuarios colaboradores registrados en este hotel.</p>
                <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Agregar Usuario
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
