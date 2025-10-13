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
                    <!-- Información del Huésped -->
                    <h5 class="mb-3"><i class="bi bi-person-circle"></i> Información del Huésped</h5>
                    
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
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="guest_search" placeholder="Buscar por nombre, email o teléfono (10 dígitos)...">
                            </div>
                            <small class="text-muted">Ingrese al menos 2 caracteres para buscar</small>
                        </div>
                        <div id="guest_results" class="list-group mb-3" style="display: none;"></div>
                        <input type="hidden" id="guest_id" name="guest_id">
                    </div>

                    <!-- Crear Nuevo Huésped -->
                    <div id="new_guest_section" style="display: none;">
                        <div class="mb-3">
                            <label for="guest_phone" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="guest_phone" name="guest_phone" placeholder="10 dígitos">
                            <small class="text-muted">Ingrese el teléfono para verificar si el huésped ya existe</small>
                        </div>
                        <div id="phone_validation_message" class="alert" style="display: none;"></div>
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
                            <label for="guest_birthday" class="form-label">Fecha de Cumpleaños (Opcional)</label>
                            <input type="date" class="form-control" id="guest_birthday" name="guest_birthday">
                            <small class="text-muted">Esta información ayuda a personalizar la experiencia del huésped</small>
                        </div>
                    </div>

                    <hr>

                    <!-- Tipo de Reservación -->
                    <h5 class="mb-3"><i class="bi bi-calendar-check"></i> Detalles de Reservación</h5>
                    <div class="mb-4">
                        <label for="reservation_type" class="form-label"><strong>Tipo de Reservación *</strong></label>
                        <select class="form-select form-select-lg" id="reservation_type" name="reservation_type" required>
                            <option value="">Seleccione un tipo...</option>
                            <option value="room">🚪 Habitación</option>
                            <option value="table">🍽️ Mesa</option>
                            <option value="amenity">🏊 Amenidad</option>
                        </select>
                    </div>

                    <!-- Recurso - Single select for tables and amenities -->
                    <div class="mb-4" id="resource_section_single" style="display: none;">
                        <label for="resource_id" class="form-label"><strong>Recurso *</strong></label>
                        <select class="form-select" id="resource_id" name="resource_id" required>
                            <option value="">Seleccione un recurso...</option>
                        </select>
                        <small class="text-muted" id="resource_help"></small>
                    </div>

                    <!-- Recurso - Multiple select for rooms -->
                    <div class="mb-4" id="resource_section_multiple" style="display: none;">
                        <label class="form-label"><strong>Habitaciones *</strong></label>
                        <div id="rooms_checkboxes" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <p class="text-muted">Cargando habitaciones...</p>
                        </div>
                        <small class="text-muted">Seleccione una o más habitaciones para la reservación</small>
                    </div>

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
                        
                        <!-- Código de Descuento -->
                        <div class="mb-3">
                            <label for="discount_code" class="form-label">Código de Descuento (Opcional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="discount_code" name="discount_code" placeholder="Ingrese código promocional">
                                <button type="button" class="btn btn-outline-primary" id="apply_discount_btn">
                                    <i class="bi bi-check-circle"></i> Aplicar
                                </button>
                            </div>
                            <small class="form-text" id="discount_feedback"></small>
                            <input type="hidden" id="discount_code_id" name="discount_code_id">
                            <input type="hidden" id="discount_amount" name="discount_amount" value="0">
                            <input type="hidden" id="original_price" name="original_price">
                        </div>
                        
                        <!-- Resumen de Precio -->
                        <div id="price_summary" class="alert alert-info" style="display: none;">
                            <h6 class="mb-2">Resumen de Precio</h6>
                            <div class="d-flex justify-content-between">
                                <span>Precio original:</span>
                                <span id="display_original_price">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between text-success">
                                <span>Descuento:</span>
                                <span id="display_discount">-$0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total a pagar:</span>
                                <span id="display_final_price">$0.00</span>
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

                    <!-- Party Size (para mesas y amenidades) -->
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

    // Get references to both resource sections
    const resourceSectionSingle = document.getElementById('resource_section_single');
    const resourceSectionMultiple = document.getElementById('resource_section_multiple');
    const roomsCheckboxes = document.getElementById('rooms_checkboxes');
    
    // Handle reservation type change
    reservationType.addEventListener('change', function() {
        const type = this.value;
        
        // Show/hide appropriate resource section
        if (type === 'room') {
            resourceSectionSingle.style.display = 'none';
            resourceSectionMultiple.style.display = 'block';
            roomDates.style.display = 'block';
            tableAmenityDates.style.display = 'none';
            partySizeSection.style.display = 'none';
            document.getElementById('check_in').required = true;
            document.getElementById('check_out').required = true;
            document.getElementById('reservation_date').required = false;
            document.getElementById('reservation_time').required = false;
        } else if (type === 'table') {
            resourceSectionSingle.style.display = 'block';
            resourceSectionMultiple.style.display = 'none';
            roomDates.style.display = 'none';
            tableAmenityDates.style.display = 'block';
            partySizeSection.style.display = 'block';
            resourceHelp.textContent = 'Seleccione una mesa disponible';
            document.getElementById('check_in').required = false;
            document.getElementById('check_out').required = false;
            document.getElementById('reservation_date').required = true;
            document.getElementById('reservation_time').required = true;
            document.getElementById('party_size').required = true;
            resourceSelect.innerHTML = '<option value="">Cargando recursos...</option>';
        } else if (type === 'amenity') {
            resourceSectionSingle.style.display = 'block';
            resourceSectionMultiple.style.display = 'none';
            roomDates.style.display = 'none';
            tableAmenityDates.style.display = 'block';
            partySizeSection.style.display = 'block';
            resourceHelp.textContent = 'Seleccione una amenidad disponible';
            document.getElementById('check_in').required = false;
            document.getElementById('check_out').required = false;
            document.getElementById('reservation_date').required = true;
            document.getElementById('reservation_time').required = true;
            document.getElementById('party_size').required = true;
            resourceSelect.innerHTML = '<option value="">Cargando recursos...</option>';
        } else {
            resourceSectionSingle.style.display = 'none';
            resourceSectionMultiple.style.display = 'none';
        }
        
        // Load resources via AJAX
        if (type) {
            loadResources(type);
        }
    });

    // Load resources based on type
    function loadResources(type) {
        fetch('<?= BASE_URL ?>/public/api/get_resources.php?type=' + type)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data); // Debug logging
                
                // Check if API call was successful
                if (data.success) {
                    // Check if there are resources available
                    if (data.resources && data.resources.length > 0) {
                        if (type === 'room') {
                            // Display rooms as styled cards with checkboxes
                            let html = '';
                            data.resources.forEach(resource => {
                                const statusBadge = resource.status === 'available' ? 
                                    '<span class="badge bg-success ms-2">Disponible</span>' : 
                                    '<span class="badge bg-warning ms-2">Reservada</span>';
                                    
                                html += `
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card resource-card h-100" style="cursor: pointer;" data-resource-id="${resource.id}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">
                                                        <i class="bi bi-door-closed text-primary"></i>
                                                        Habitación ${resource.room_number}
                                                    </h6>
                                                    <div class="form-check">
                                                        <input class="form-check-input room-checkbox" type="checkbox" 
                                                               name="room_ids[]" value="${resource.id}" 
                                                               id="room_${resource.id}"
                                                               data-price="${resource.price}">
                                                    </div>
                                                </div>
                                                <p class="card-text text-muted small mb-2">
                                                    <i class="bi bi-tag"></i> ${resource.type}
                                                </p>
                                                <p class="card-text text-muted small mb-2">
                                                    <i class="bi bi-people"></i> Capacidad: ${resource.capacity || 'N/A'}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-primary fs-6">$${parseFloat(resource.price).toFixed(2)}</span>
                                                    ${statusBadge}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            roomsCheckboxes.innerHTML = `<div class="row">${html}</div>`;
                            
                            // Add event listeners to cards and checkboxes
                            document.querySelectorAll('.resource-card').forEach(card => {
                                card.addEventListener('click', function(e) {
                                    if (e.target.type !== 'checkbox') {
                                        const checkbox = this.querySelector('.room-checkbox');
                                        checkbox.checked = !checkbox.checked;
                                        checkbox.dispatchEvent(new Event('change'));
                                    }
                                });
                            });
                            
                            document.querySelectorAll('.room-checkbox').forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    const card = this.closest('.resource-card');
                                    if (this.checked) {
                                        card.classList.add('border-primary', 'bg-light');
                                    } else {
                                        card.classList.remove('border-primary', 'bg-light');
                                    }
                                    updateRoomPrices();
                                });
                            });
                        } else {
                            // Display tables and amenities as styled cards with radio buttons
                            let html = '';
                            data.resources.forEach(resource => {
                                let cardContent = '';
                                let icon = '';
                                let details = '';
                                
                                if (type === 'table') {
                                    icon = '<i class="bi bi-table text-warning"></i>';
                                    cardContent = `Mesa ${resource.table_number}`;
                                    details = `
                                        <p class="card-text text-muted small mb-2">
                                            <i class="bi bi-people"></i> Capacidad: ${resource.capacity} personas
                                        </p>
                                        <p class="card-text text-muted small mb-2">
                                            <i class="bi bi-geo-alt"></i> Ubicación: ${resource.location || 'Principal'}
                                        </p>
                                    `;
                                } else if (type === 'amenity') {
                                    icon = '<i class="bi bi-star text-info"></i>';
                                    cardContent = resource.name;
                                    details = `
                                        <p class="card-text text-muted small mb-2">
                                            <i class="bi bi-tag"></i> ${resource.category}
                                        </p>
                                        <p class="card-text text-muted small mb-2">
                                            <i class="bi bi-people"></i> Capacidad: ${resource.capacity || 'N/A'}
                                        </p>
                                        ${resource.opening_time && resource.closing_time ? 
                                            `<p class="card-text text-muted small mb-2">
                                                <i class="bi bi-clock"></i> ${resource.opening_time} - ${resource.closing_time}
                                            </p>` : ''}
                                    `;
                                }
                                
                                const price = resource.price ? `<span class="badge bg-primary fs-6">$${parseFloat(resource.price).toFixed(2)}</span>` : '';
                                
                                html += `
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card resource-card h-100" style="cursor: pointer;" data-resource-id="${resource.id}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">
                                                        ${icon} ${cardContent}
                                                    </h6>
                                                    <div class="form-check">
                                                        <input class="form-check-input resource-radio" type="radio" 
                                                               name="resource_id" value="${resource.id}" 
                                                               id="${type}_${resource.id}">
                                                    </div>
                                                </div>
                                                ${details}
                                                <div class="d-flex justify-content-between align-items-center">
                                                    ${price}
                                                    <span class="badge bg-success">Disponible</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            resourceSectionSingle.innerHTML = `
                                <label class="form-label"><strong>${type === 'table' ? 'Mesas' : 'Amenidades'} *</strong></label>
                                <div class="row">${html}</div>
                                <small class="text-muted">Seleccione ${type === 'table' ? 'una mesa' : 'una amenidad'} para la reservación</small>
                            `;
                            
                            // Add event listeners to cards and radio buttons
                            document.querySelectorAll('.resource-card').forEach(card => {
                                card.addEventListener('click', function(e) {
                                    if (e.target.type !== 'radio') {
                                        const radio = this.querySelector('.resource-radio');
                                        radio.checked = true;
                                        radio.dispatchEvent(new Event('change'));
                                    }
                                });
                            });
                            
                            document.querySelectorAll('.resource-radio').forEach(radio => {
                                radio.addEventListener('change', function() {
                                    // Remove selection from all cards
                                    document.querySelectorAll('.resource-card').forEach(c => {
                                        c.classList.remove('border-primary', 'bg-light');
                                    });
                                    
                                    // Add selection to current card
                                    if (this.checked) {
                                        const card = this.closest('.resource-card');
                                        card.classList.add('border-primary', 'bg-light');
                                    }
                                });
                            });
                        }
                    } else {
                        // No resources available - show specific message with consistent styling
                        let message = 'No hay recursos disponibles';
                        let icon = 'bi-exclamation-circle';
                        if (type === 'room') {
                            message = 'No hay habitaciones disponibles';
                            roomsCheckboxes.innerHTML = `
                                <div class="text-center py-4">
                                    <i class="bi ${icon} text-muted fs-1"></i>
                                    <p class="text-muted mt-2">${message}</p>
                                </div>
                            `;
                        } else {
                            if (type === 'table') message = 'No hay mesas disponibles';
                            else if (type === 'amenity') message = 'No hay amenidades disponibles';
                            resourceSectionSingle.innerHTML = `
                                <label class="form-label"><strong>${type === 'table' ? 'Mesas' : 'Amenidades'} *</strong></label>
                                <div class="text-center py-4">
                                    <i class="bi ${icon} text-muted fs-1"></i>
                                    <p class="text-muted mt-2">${message}</p>
                                </div>
                            `;
                        }
                    }
                } else {
                    // API returned error - show error message with consistent styling
                    console.error('API Error:', data.message || 'Unknown error');
                    const errorMessage = `Error: ${data.message || 'Error al cargar recursos'}`;
                    if (type === 'room') {
                        roomsCheckboxes.innerHTML = `
                            <div class="text-center py-4">
                                <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                                <p class="text-danger mt-2">${errorMessage}</p>
                            </div>
                        `;
                    } else {
                        resourceSectionSingle.innerHTML = `
                            <label class="form-label"><strong>${type === 'table' ? 'Mesas' : 'Amenidades'} *</strong></label>
                            <div class="text-center py-4">
                                <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                                <p class="text-danger mt-2">${errorMessage}</p>
                            </div>
                        `;
                    }
                }
            })
            .catch(error => {
                console.error('Error loading resources:', error);
                const errorMessage = 'Error de conexión al cargar recursos';
                if (type === 'room') {
                    roomsCheckboxes.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-wifi-off text-danger fs-1"></i>
                            <p class="text-danger mt-2">${errorMessage}</p>
                        </div>
                    `;
                } else {
                    resourceSectionSingle.innerHTML = `
                        <label class="form-label"><strong>${type === 'table' ? 'Mesas' : 'Amenidades'} *</strong></label>
                        <div class="text-center py-4">
                            <i class="bi bi-wifi-off text-danger fs-1"></i>
                            <p class="text-danger mt-2">${errorMessage}</p>
                        </div>
                    `;
                }
            });
    }
    
    // Function to update room prices when checkboxes change
    function updateRoomPrices() {
        const checkedBoxes = document.querySelectorAll('.room-checkbox:checked');
        if (checkedBoxes.length === 0) {
            // No rooms selected, hide price summary
            if (priceSummary) {
                priceSummary.style.display = 'none';
            }
            return;
        }
        
        // Calculate total price
        let totalPrice = 0;
        checkedBoxes.forEach(box => {
            totalPrice += parseFloat(box.dataset.price);
        });
        
        // Update the original price input (used for discount calculations)
        if (originalPriceInput) {
            originalPriceInput.value = totalPrice;
        }
        
        // Note: Discount functionality would need to be updated to work with multiple rooms
        // For now, we'll just show the total price
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
        fetch('<?= BASE_URL ?>/public/api/search_guests.php?q=' + encodeURIComponent(query))
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

    // Phone validation for new guest
    let phoneCheckTimeout;
    const guestPhoneInput = document.getElementById('guest_phone');
    const guestNameInput = document.getElementById('guest_name');
    const guestEmailInput = document.getElementById('guest_email');
    const phoneValidationMessage = document.getElementById('phone_validation_message');
    
    guestPhoneInput.addEventListener('input', function() {
        clearTimeout(phoneCheckTimeout);
        const phone = this.value.trim();
        
        // Reset validation message
        phoneValidationMessage.style.display = 'none';
        
        // Only check if phone is 10 digits
        if (!/^\d{10}$/.test(phone)) {
            if (phone.length > 0) {
                phoneValidationMessage.className = 'alert alert-warning';
                phoneValidationMessage.textContent = 'El teléfono debe tener exactamente 10 dígitos';
                phoneValidationMessage.style.display = 'block';
            }
            return;
        }
        
        // Check if phone exists
        phoneCheckTimeout = setTimeout(() => {
            checkPhoneExists(phone);
        }, 500);
    });
    
    function checkPhoneExists(phone) {
        fetch('<?= BASE_URL ?>/public/api/check_phone.php?phone=' + encodeURIComponent(phone))
            .then(response => response.json())
            .then(data => {
                if (data.success && data.exists) {
                    // Phone exists, preload data
                    const guest = data.guest;
                    guestIdInput.value = guest.id;
                    guestNameInput.value = (guest.first_name + ' ' + guest.last_name).trim();
                    guestEmailInput.value = guest.email;
                    
                    phoneValidationMessage.className = 'alert alert-info';
                    phoneValidationMessage.textContent = 'Huésped encontrado. Puede modificar la información si es necesario.';
                    phoneValidationMessage.style.display = 'block';
                } else {
                    // Phone doesn't exist, clear fields
                    guestIdInput.value = '';
                    phoneValidationMessage.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error checking phone:', error);
            });
    }
    
    // Handle discount code validation
    const applyDiscountBtn = document.getElementById('apply_discount_btn');
    const discountCodeInput = document.getElementById('discount_code');
    const discountFeedback = document.getElementById('discount_feedback');
    const discountCodeIdInput = document.getElementById('discount_code_id');
    const discountAmountInput = document.getElementById('discount_amount');
    const originalPriceInput = document.getElementById('original_price');
    const priceSummary = document.getElementById('price_summary');
    
    if (applyDiscountBtn) {
        applyDiscountBtn.addEventListener('click', function() {
            const code = discountCodeInput.value.trim();
            const resourceId = resourceSelect.value;
            
            if (!code) {
                showDiscountFeedback('Por favor ingrese un código de descuento', 'danger');
                return;
            }
            
            if (!resourceId) {
                showDiscountFeedback('Por favor seleccione una habitación primero', 'warning');
                return;
            }
            
            // Get room price from selected option
            const selectedOption = resourceSelect.options[resourceSelect.selectedIndex];
            const optionText = selectedOption.textContent;
            const priceMatch = optionText.match(/\$(\d+(?:\.\d{2})?)/);
            
            if (!priceMatch) {
                showDiscountFeedback('No se pudo obtener el precio de la habitación', 'danger');
                return;
            }
            
            const roomPrice = parseFloat(priceMatch[1]);
            
            // Show loading state
            applyDiscountBtn.disabled = true;
            applyDiscountBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Validando...';
            discountFeedback.textContent = '';
            
            // Validate discount code via API
            const formData = new FormData();
            formData.append('code', code);
            formData.append('room_price', roomPrice);
            
            fetch('<?= BASE_URL ?>/public/api/validate_discount_code.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Discount is valid
                    const discount = data.discount;
                    
                    // Store discount information in hidden fields
                    discountCodeIdInput.value = discount.id;
                    discountAmountInput.value = discount.discount_amount;
                    originalPriceInput.value = discount.original_price;
                    
                    // Show success feedback
                    let feedbackMsg = `✓ Código válido: `;
                    if (discount.type === 'percentage') {
                        feedbackMsg += `${discount.amount}% de descuento`;
                    } else {
                        feedbackMsg += `$${discount.amount} de descuento`;
                    }
                    showDiscountFeedback(feedbackMsg, 'success');
                    
                    // Show price summary
                    document.getElementById('display_original_price').textContent = `$${discount.original_price.toFixed(2)}`;
                    document.getElementById('display_discount').textContent = `-$${discount.discount_amount.toFixed(2)}`;
                    document.getElementById('display_final_price').textContent = `$${discount.final_price.toFixed(2)}`;
                    priceSummary.style.display = 'block';
                    
                    // Disable the input and button after successful application
                    discountCodeInput.disabled = true;
                    applyDiscountBtn.disabled = true;
                    applyDiscountBtn.innerHTML = '<i class="bi bi-check-circle"></i> Aplicado';
                } else {
                    // Discount is invalid
                    showDiscountFeedback(data.message, 'danger');
                    clearDiscountData();
                }
            })
            .catch(error => {
                console.error('Error validating discount code:', error);
                showDiscountFeedback('Error de conexión al validar código', 'danger');
                clearDiscountData();
            })
            .finally(() => {
                if (!discountCodeInput.disabled) {
                    applyDiscountBtn.disabled = false;
                    applyDiscountBtn.innerHTML = '<i class="bi bi-check-circle"></i> Aplicar';
                }
            });
        });
    }
    
    function showDiscountFeedback(message, type) {
        discountFeedback.textContent = message;
        discountFeedback.className = `form-text text-${type}`;
    }
    
    function clearDiscountData() {
        discountCodeIdInput.value = '';
        discountAmountInput.value = '0';
        originalPriceInput.value = '';
        priceSummary.style.display = 'none';
    }
    
    // Reset discount when room changes
    resourceSelect.addEventListener('change', function() {
        if (discountCodeInput) {
            discountCodeInput.value = '';
            discountCodeInput.disabled = false;
            applyDiscountBtn.disabled = false;
            applyDiscountBtn.innerHTML = '<i class="bi bi-check-circle"></i> Aplicar';
            discountFeedback.textContent = '';
            clearDiscountData();
        }
    });

    // Form validation before submit
    document.getElementById('reservationForm').addEventListener('submit', function(e) {
        const guestType = document.querySelector('input[name="guest_type"]:checked').value;
        const resType = reservationType.value;
        
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
        
        // Validate resource selection
        if (resType === 'room') {
            const checkedRooms = document.querySelectorAll('.room-checkbox:checked');
            if (checkedRooms.length === 0) {
                e.preventDefault();
                alert('Por favor seleccione al menos una habitación');
                return false;
            }
        } else if (resType === 'table' || resType === 'amenity') {
            const selectedResource = document.querySelector('.resource-radio:checked');
            if (!selectedResource) {
                const resourceType = resType === 'table' ? 'una mesa' : 'una amenidad';
                e.preventDefault();
                alert(`Por favor seleccione ${resourceType}`);
                return false;
            }
        }
    });
});
</script>

<style>
/* Custom styles for resource cards */
.resource-card {
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.resource-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.resource-card.border-primary {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.1rem rgba(13, 110, 253, 0.25);
}

.resource-card.bg-light {
    background-color: rgba(13, 110, 253, 0.05) !important;
}

.resource-card .card-body {
    padding: 1rem;
}

.resource-card .card-title {
    font-size: 1rem;
    font-weight: 600;
}

.resource-card .form-check-input {
    width: 1.25em;
    height: 1.25em;
}

.resource-card .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Animation for card selection */
@keyframes cardSelect {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.resource-card.border-primary {
    animation: cardSelect 0.3s ease-out;
}

/* Responsive grid adjustments */
@media (max-width: 768px) {
    .resource-card .card-body {
        padding: 0.75rem;
    }
    
    .resource-card .card-title {
        font-size: 0.9rem;
    }
}
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
