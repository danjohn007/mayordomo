<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Reservación</h4>
                    <a href="<?= BASE_URL ?>/reservations" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($flash = flash('error')) echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show">' . $flash['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; ?>
                
                <form method="POST" action="<?= BASE_URL ?>/reservations/store" id="reservationForm">
                    <!-- Tipo de Reservación -->
                    <div class="mb-4">
                        <label for="reservation_type" class="form-label"><strong>Tipo de Reservación *</strong></label>
                        <select class="form-select form-select-lg" id="reservation_type" name="reservation_type" required>
                            <option value="">Seleccione un tipo...</option>
                            <option value="room">🚪 Habitación</option>
                            <option value="table">🍽️ Mesa</option>
                            <option value="amenity">🏊 Amenidad</option>
                        </select>
                    </div>

                    <!-- Recurso -->
                    <div class="mb-4" id="resource_section" style="display: none;">
                        <label for="resource_id" class="form-label"><strong>Recurso *</strong></label>
                        <select class="form-select" id="resource_id" name="resource_id" required>
                            <option value="">Seleccione un recurso...</option>
                        </select>
                        <small class="text-muted" id="resource_help"></small>
                    </div>

                    <hr>

                    <!-- Información del Huésped -->
                    <h5 class="mb-3">Información del Huésped</h5>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Tipo de Huésped *</strong></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="guest_type" id="guest_existing" value="existing" checked>
                            <label class="btn btn-outline-primary" for="guest_existing">Buscar Huésped Existente</label>
                            
                            <input type="radio" class="btn-check" name="guest_type" id="guest_new" value="new">
                            <label class="btn btn-outline-primary" for="guest_new">Nuevo Huésped</label>
                        </div>
                    </div>

                    <!-- Buscar Huésped Existente -->
                    <div id="existing_guest_section">
                        <div class="mb-3">
                            <label for="guest_search" class="form-label">Buscar Huésped</label>
                            <input type="text" class="form-control" id="guest_search" placeholder="Buscar por nombre o email...">
                        </div>
                        <div id="guest_results" class="list-group mb-3" style="display: none;"></div>
                        <input type="hidden" id="guest_id" name="guest_id">
                    </div>

                    <!-- Crear Nuevo Huésped -->
                    <div id="new_guest_section" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_name" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="guest_name" name="guest_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="guest_email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="guest_email" name="guest_email">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="guest_phone" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="guest_phone" name="guest_phone" placeholder="10 dígitos">
                        </div>
                    </div>

                    <hr>

                    <!-- Fechas y Detalles -->
                    <h5 class="mb-3">Detalles de la Reservación</h5>
                    
                    <!-- Para Habitaciones -->
                    <div id="room_dates" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="check_in" class="form-label">Check-in *</label>
                                <input type="date" class="form-control" id="check_in" name="check_in">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="check_out" class="form-label">Check-out *</label>
                                <input type="date" class="form-control" id="check_out" name="check_out">
                            </div>
                        </div>
                    </div>

                    <!-- Para Mesas y Amenidades -->
                    <div id="table_amenity_dates" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reservation_date" class="form-label">Fecha *</label>
                                <input type="date" class="form-control" id="reservation_date" name="reservation_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reservation_time" class="form-label">Hora *</label>
                                <input type="time" class="form-control" id="reservation_time" name="reservation_time">
                            </div>
                        </div>
                    </div>

                    <!-- Party Size (solo para mesas) -->
                    <div id="party_size_section" style="display: none;">
                        <div class="mb-3">
                            <label for="party_size" class="form-label">Número de Personas *</label>
                            <input type="number" class="form-control" id="party_size" name="party_size" min="1" value="1">
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Estado *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pendiente</option>
                            <option value="confirmed">Confirmada</option>
                        </select>
                    </div>

                    <!-- Notas -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas / Solicitudes Especiales</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?= BASE_URL ?>/reservations" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Reservación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reservationType = document.getElementById('reservation_type');
    const resourceSection = document.getElementById('resource_section');
    const resourceSelect = document.getElementById('resource_id');
    const resourceHelp = document.getElementById('resource_help');
    const roomDates = document.getElementById('room_dates');
    const tableAmenityDates = document.getElementById('table_amenity_dates');
    const partySizeSection = document.getElementById('party_size_section');
    const guestTypeRadios = document.querySelectorAll('input[name="guest_type"]');
    const existingGuestSection = document.getElementById('existing_guest_section');
    const newGuestSection = document.getElementById('new_guest_section');
    const guestSearch = document.getElementById('guest_search');
    const guestResults = document.getElementById('guest_results');
    const guestIdInput = document.getElementById('guest_id');

    // Handle reservation type change
    reservationType.addEventListener('change', function() {
        const type = this.value;
        resourceSection.style.display = type ? 'block' : 'none';
        
        // Reset resource dropdown
        resourceSelect.innerHTML = '<option value="">Cargando recursos...</option>';
        
        // Show/hide appropriate date fields
        if (type === 'room') {
            roomDates.style.display = 'block';
            tableAmenityDates.style.display = 'none';
            partySizeSection.style.display = 'none';
            resourceHelp.textContent = 'Seleccione una habitación disponible';
            document.getElementById('check_in').required = true;
            document.getElementById('check_out').required = true;
            document.getElementById('reservation_date').required = false;
            document.getElementById('reservation_time').required = false;
        } else if (type === 'table') {
            roomDates.style.display = 'none';
            tableAmenityDates.style.display = 'block';
            partySizeSection.style.display = 'block';
            resourceHelp.textContent = 'Seleccione una mesa disponible';
            document.getElementById('check_in').required = false;
            document.getElementById('check_out').required = false;
            document.getElementById('reservation_date').required = true;
            document.getElementById('reservation_time').required = true;
            document.getElementById('party_size').required = true;
        } else if (type === 'amenity') {
            roomDates.style.display = 'none';
            tableAmenityDates.style.display = 'block';
            partySizeSection.style.display = 'none';
            resourceHelp.textContent = 'Seleccione una amenidad disponible';
            document.getElementById('check_in').required = false;
            document.getElementById('check_out').required = false;
            document.getElementById('reservation_date').required = true;
            document.getElementById('reservation_time').required = true;
        }
        
        // Load resources via AJAX
        if (type) {
            loadResources(type);
        }
    });

    // Load resources based on type
    function loadResources(type) {
        fetch('<?= BASE_URL ?>/api/get_resources.php?type=' + type)
            .then(response => response.json())
            .then(data => {
                resourceSelect.innerHTML = '<option value="">Seleccione un recurso...</option>';
                if (data.success && data.resources) {
                    data.resources.forEach(resource => {
                        const option = document.createElement('option');
                        option.value = resource.id;
                        if (type === 'room') {
                            option.textContent = `Habitación ${resource.room_number} - ${resource.type} ($${resource.price})`;
                        } else if (type === 'table') {
                            option.textContent = `Mesa ${resource.table_number} - Capacidad: ${resource.capacity}`;
                        } else if (type === 'amenity') {
                            option.textContent = `${resource.name} - ${resource.category}`;
                        }
                        resourceSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading resources:', error);
                resourceSelect.innerHTML = '<option value="">Error al cargar recursos</option>';
            });
    }

    // Handle guest type toggle
    guestTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'existing') {
                existingGuestSection.style.display = 'block';
                newGuestSection.style.display = 'none';
                document.getElementById('guest_name').required = false;
                document.getElementById('guest_email').required = false;
                document.getElementById('guest_phone').required = false;
            } else {
                existingGuestSection.style.display = 'none';
                newGuestSection.style.display = 'block';
                guestIdInput.value = '';
                document.getElementById('guest_name').required = true;
                document.getElementById('guest_email').required = true;
                document.getElementById('guest_phone').required = true;
            }
        });
    });

    // Guest search with debounce
    let searchTimeout;
    guestSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            guestResults.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchGuests(query);
        }, 300);
    });

    function searchGuests(query) {
        fetch('<?= BASE_URL ?>/api/search_guests.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.success && data.guests && data.guests.length > 0) {
                    guestResults.innerHTML = '';
                    data.guests.forEach(guest => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = `<strong>${guest.first_name} ${guest.last_name}</strong><br>
                                         <small>${guest.email} ${guest.phone ? '- ' + guest.phone : ''}</small>`;
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            selectGuest(guest);
                        });
                        guestResults.appendChild(item);
                    });
                    guestResults.style.display = 'block';
                } else {
                    guestResults.innerHTML = '<div class="list-group-item">No se encontraron huéspedes</div>';
                    guestResults.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error searching guests:', error);
            });
    }

    function selectGuest(guest) {
        guestIdInput.value = guest.id;
        guestSearch.value = `${guest.first_name} ${guest.last_name} (${guest.email})`;
        guestResults.style.display = 'none';
    }

    // Form validation before submit
    document.getElementById('reservationForm').addEventListener('submit', function(e) {
        const guestType = document.querySelector('input[name="guest_type"]:checked').value;
        
        if (guestType === 'existing' && !guestIdInput.value) {
            e.preventDefault();
            alert('Por favor seleccione un huésped de la lista de búsqueda');
            return false;
        }
        
        if (guestType === 'new') {
            const phone = document.getElementById('guest_phone').value;
            if (phone && !/^\d{10}$/.test(phone)) {
                e.preventDefault();
                alert('El teléfono debe tener exactamente 10 dígitos');
                return false;
            }
        }
    });
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
