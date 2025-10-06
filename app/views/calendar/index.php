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
    <div class="card mb-3 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Leyenda</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong class="text-muted small">ESTADOS:</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge bg-warning text-dark"><i class="bi bi-circle-fill"></i> Pendiente</span>
                        <span class="badge" style="background-color: #28a745;"><i class="bi bi-circle-fill"></i> Confirmado</span>
                        <span class="badge" style="background-color: #17a2b8;"><i class="bi bi-circle-fill"></i> En Curso</span>
                        <span class="badge bg-secondary"><i class="bi bi-circle-fill"></i> Completado</span>
                        <span class="badge bg-danger"><i class="bi bi-circle-fill"></i> Cancelado</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <strong class="text-muted small">TIPOS DE RESERVACIÓN:</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge bg-info"><i class="bi bi-door-closed"></i> Habitaciones</span>
                        <span class="badge bg-success"><i class="bi bi-table"></i> Mesas</span>
                        <span class="badge bg-primary"><i class="bi bi-spa"></i> Amenidades</span>
                        <span class="badge bg-warning text-dark"><i class="bi bi-bell"></i> Servicios</span>
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

<style>
/* Ensure calendar events are clearly visible */
.fc-event {
    cursor: pointer;
    font-weight: 500;
    border-width: 2px !important;
}

.fc-event:hover {
    opacity: 0.85;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.fc-daygrid-event {
    white-space: normal !important;
    align-items: flex-start !important;
    padding: 2px 4px !important;
}

.fc-event-title {
    font-weight: 600;
}

/* Make calendar more readable */
.fc-daygrid-day-number {
    font-size: 1.1em;
    font-weight: 600;
    padding: 8px;
}

.fc-col-header-cell-cushion {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9em;
}

/* Modal improvements */
#eventModalBody dl dt {
    font-weight: 600;
    color: #495057;
}

#eventModalBody dl dd {
    color: #212529;
}
</style>

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
                        alert('Error al cargar eventos: ' + data.error);
                        failureCallback(data.error);
                    } else {
                        console.log('Loaded ' + data.length + ' events from server');
                        if (data.length === 0) {
                            console.log('No hay reservaciones en este período');
                        }
                        successCallback(data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al cargar eventos');
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
        
        let detailsHtml = '<dl class="row mb-0">';
        
        // Type - REQUIRED
        detailsHtml += `<dt class="col-sm-4"><strong>Tipo:</strong></dt>`;
        detailsHtml += `<dd class="col-sm-8">`;
        switch(props.type) {
            case 'room':
                detailsHtml += '<span class="badge bg-info"><i class="bi bi-door-closed"></i> Habitación</span>';
                break;
            case 'table':
                detailsHtml += '<span class="badge bg-success"><i class="bi bi-table"></i> Mesa</span>';
                break;
            case 'amenity':
                detailsHtml += '<span class="badge bg-primary"><i class="bi bi-spa"></i> Amenidad</span>';
                break;
            case 'service':
                detailsHtml += '<span class="badge bg-warning"><i class="bi bi-bell"></i> Servicio</span>';
                break;
        }
        detailsHtml += `</dd>`;
        
        // Status - REQUIRED
        detailsHtml += `<dt class="col-sm-4"><strong>Estado:</strong></dt>`;
        detailsHtml += `<dd class="col-sm-8">`;
        detailsHtml += getStatusBadge(props.status);
        detailsHtml += `</dd>`;
        
        // Guest/User - REQUIRED
        if (props.guest) {
            detailsHtml += `<dt class="col-sm-4"><strong>Huésped:</strong></dt>`;
            detailsHtml += `<dd class="col-sm-8"><i class="bi bi-person"></i> ${props.guest}</dd>`;
        }
        
        // Resource specific details - REQUIRED
        if (props.room) {
            detailsHtml += `<dt class="col-sm-4"><strong>Recurso:</strong></dt>`;
            detailsHtml += `<dd class="col-sm-8"><i class="bi bi-door-closed"></i> Habitación ${props.room}</dd>`;
        }
        
        if (props.table) {
            detailsHtml += `<dt class="col-sm-4"><strong>Recurso:</strong></dt>`;
            detailsHtml += `<dd class="col-sm-8"><i class="bi bi-table"></i> Mesa ${props.table}</dd>`;
        }
        
        if (props.amenity) {
            detailsHtml += `<dt class="col-sm-4"><strong>Recurso:</strong></dt>`;
            detailsHtml += `<dd class="col-sm-8"><i class="bi bi-spa"></i> ${props.amenity}</dd>`;
        }
        
        // Dates - REQUIRED
        detailsHtml += `<dt class="col-sm-4"><strong>Fecha:</strong></dt>`;
        detailsHtml += `<dd class="col-sm-8"><i class="bi bi-calendar"></i> ${formatDate(event.start)}`;
        if (event.end && props.type === 'room') {
            detailsHtml += ` al ${formatDate(event.end)}`;
        }
        detailsHtml += `</dd>`;
        
        // Time (for tables and amenities)
        if (props.time) {
            detailsHtml += `<dt class="col-sm-4"><strong>Hora:</strong></dt>`;
            detailsHtml += `<dd class="col-sm-8"><i class="bi bi-clock"></i> ${props.time}</dd>`;
        }
        
        // Description (for services)
        if (props.description) {
            detailsHtml += `<dt class="col-sm-4"><strong>Descripción:</strong></dt>`;
            detailsHtml += `<dd class="col-sm-8">${props.description}</dd>`;
        }
        
        // Priority (for services)
        if (props.priority) {
            detailsHtml += `<dt class="col-sm-4"><strong>Prioridad:</strong></dt>`;
            detailsHtml += `<dd class="col-sm-8">`;
            detailsHtml += getPriorityBadge(props.priority);
            detailsHtml += `</dd>`;
        }
        
        detailsHtml += '</dl>';
        
        modalTitle.innerHTML = '<i class="bi bi-info-circle"></i> ' + event.title;
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
