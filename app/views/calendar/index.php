<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-calendar3"></i> Calendario de Reservaciones</h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary" id="todayBtn">
                <i class="bi bi-calendar-day"></i> Hoy
            </button>
            <button type="button" class="btn btn-outline-primary" id="prevBtn">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="btn btn-outline-primary" id="nextBtn">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="mb-2">Leyenda:</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <span><i class="bi bi-circle-fill" style="color: #ffc107;"></i> Pendiente</span>
                        <span><i class="bi bi-circle-fill" style="color: #28a745;"></i> Confirmado</span>
                        <span><i class="bi bi-circle-fill" style="color: #17a2b8;"></i> En Curso</span>
                        <span><i class="bi bi-circle-fill" style="color: #6c757d;"></i> Completado</span>
                        <span><i class="bi bi-circle-fill" style="color: #dc3545;"></i> Cancelado</span>
                        <span class="ms-4">🚪 Habitaciones</span>
                        <span>🍽️ Mesas</span>
                        <span>⭐ Amenidades</span>
                        <span>🔔 Servicios</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Detalles del Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Event details will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="eventViewLink" class="btn btn-primary" target="_blank">Ver Detalles</a>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/es.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: '',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        events: function(info, successCallback, failureCallback) {
            fetch('<?= BASE_URL ?>/calendar/getEvents?start=' + info.startStr + '&end=' + info.endStr)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error loading events:', data.error);
                        failureCallback(data.error);
                    } else {
                        successCallback(data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = info.event.title;
        },
        height: 'auto',
        nowIndicator: true,
        navLinks: true,
        editable: false,
        dayMaxEvents: true
    });
    
    calendar.render();
    
    // Navigation buttons
    document.getElementById('todayBtn').addEventListener('click', function() {
        calendar.today();
    });
    
    document.getElementById('prevBtn').addEventListener('click', function() {
        calendar.prev();
    });
    
    document.getElementById('nextBtn').addEventListener('click', function() {
        calendar.next();
    });
    
    function showEventDetails(event) {
        const props = event.extendedProps;
        const modalTitle = document.getElementById('eventModalTitle');
        const modalBody = document.getElementById('eventModalBody');
        const viewLink = document.getElementById('eventViewLink');
        
        let detailsHtml = '<dl class="row">';
        
        // Common details
        detailsHtml += `<dt class="col-sm-4">Tipo:</dt>`;
        detailsHtml += `<dd class="col-sm-8">`;
        switch(props.type) {
            case 'room':
                detailsHtml += '<i class="bi bi-door-closed"></i> Habitación';
                break;
            case 'table':
                detailsHtml += '<i class="bi bi-table"></i> Mesa';
                break;
            case 'amenity':
                detailsHtml += '<i class="bi bi-spa"></i> Amenidad';
                break;
            case 'service':
                detailsHtml += '<i class="bi bi-bell"></i> Servicio';
                break;
        }
        detailsHtml += `</dd>`;
        
        // Guest/User
        if (props.guest) {
            detailsHtml += `<dt class="col-sm-4">Huésped:</dt>`;
            detailsHtml += `<dd class="col-sm-8">${props.guest}</dd>`;
        }
        
        // Resource specific details
        if (props.room) {
            detailsHtml += `<dt class="col-sm-4">Habitación:</dt>`;
            detailsHtml += `<dd class="col-sm-8">${props.room}</dd>`;
        }
        
        if (props.table) {
            detailsHtml += `<dt class="col-sm-4">Mesa:</dt>`;
            detailsHtml += `<dd class="col-sm-8">${props.table}</dd>`;
        }
        
        if (props.amenity) {
            detailsHtml += `<dt class="col-sm-4">Amenidad:</dt>`;
            detailsHtml += `<dd class="col-sm-8">${props.amenity}</dd>`;
        }
        
        if (props.time) {
            detailsHtml += `<dt class="col-sm-4">Hora:</dt>`;
            detailsHtml += `<dd class="col-sm-8">${props.time}</dd>`;
        }
        
        if (props.description) {
            detailsHtml += `<dt class="col-sm-4">Descripción:</dt>`;
            detailsHtml += `<dd class="col-sm-8">${props.description}</dd>`;
        }
        
        // Status
        detailsHtml += `<dt class="col-sm-4">Estado:</dt>`;
        detailsHtml += `<dd class="col-sm-8">`;
        detailsHtml += getStatusBadge(props.status);
        detailsHtml += `</dd>`;
        
        // Priority (for services)
        if (props.priority) {
            detailsHtml += `<dt class="col-sm-4">Prioridad:</dt>`;
            detailsHtml += `<dd class="col-sm-8">`;
            detailsHtml += getPriorityBadge(props.priority);
            detailsHtml += `</dd>`;
        }
        
        // Dates
        detailsHtml += `<dt class="col-sm-4">Fecha:</dt>`;
        detailsHtml += `<dd class="col-sm-8">${formatDate(event.start)}`;
        if (event.end && props.type === 'room') {
            detailsHtml += ` - ${formatDate(event.end)}`;
        }
        detailsHtml += `</dd>`;
        
        detailsHtml += '</dl>';
        
        modalTitle.textContent = event.title;
        modalBody.innerHTML = detailsHtml;
        
        // Set view link based on type
        const id = event.id.split('_')[1];
        viewLink.href = '<?= BASE_URL ?>/reservations';
        viewLink.style.display = 'inline-block';
        
        eventModal.show();
    }
    
    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning">Pendiente</span>',
            'confirmed': '<span class="badge bg-success">Confirmado</span>',
            'checked_in': '<span class="badge bg-info">Check-in</span>',
            'seated': '<span class="badge bg-info">En Mesa</span>',
            'in_use': '<span class="badge bg-info">En Uso</span>',
            'checked_out': '<span class="badge bg-secondary">Check-out</span>',
            'completed': '<span class="badge bg-secondary">Completado</span>',
            'cancelled': '<span class="badge bg-danger">Cancelado</span>',
            'no_show': '<span class="badge bg-danger">No Show</span>',
            'in_progress': '<span class="badge bg-primary">En Progreso</span>'
        };
        
        return badges[status] || '<span class="badge bg-secondary">' + status + '</span>';
    }
    
    function getPriorityBadge(priority) {
        const badges = {
            'low': '<span class="badge bg-info">Baja</span>',
            'normal': '<span class="badge bg-primary">Normal</span>',
            'high': '<span class="badge bg-warning">Alta</span>',
            'urgent': '<span class="badge bg-danger">Urgente</span>'
        };
        
        return badges[priority] || '<span class="badge bg-secondary">' + priority + '</span>';
    }
    
    function formatDate(date) {
        if (!date) return '';
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
