<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Calendario de Reservaciones') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            margin: 20px auto;
            max-width: 1400px;
        }
        
        .hotel-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #667eea;
        }
        
        .hotel-header h1 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .hotel-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .room-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .room-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .room-info h4 {
            color: #667eea;
            margin: 0;
            font-weight: 600;
        }
        
        .room-type-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .room-details {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            color: #6c757d;
        }
        
        .room-details span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            gap: 8px;
            margin-top: 15px;
        }
        
        .day-cell {
            text-align: center;
            padding: 10px 5px;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .day-cell:hover:not(.reserved):not(.past) {
            transform: scale(1.05);
            border-color: #667eea;
        }
        
        .day-cell.available {
            background: #d4edda;
            color: #155724;
            font-weight: 600;
        }
        
        .day-cell.available:hover {
            background: #c3e6cb;
        }
        
        .day-cell.reserved {
            background: #f8d7da;
            color: #721c24;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .day-cell.past {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .day-date {
            display: block;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .day-name {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            opacity: 0.8;
        }
        
        .day-price {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 2px;
        }
        
        .whatsapp-btn {
            background: #25D366;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 1rem;
        }
        
        .whatsapp-btn:hover {
            background: #128C7E;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.4);
        }
        
        .whatsapp-btn i {
            font-size: 1.3rem;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        .legend-box {
            width: 30px;
            height: 30px;
            border-radius: 5px;
        }
        
        .month-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .month-nav h3 {
            margin: 0;
            color: #667eea;
            font-weight: 700;
            min-width: 200px;
            text-align: center;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #667eea;
        }
        
        .loading i {
            font-size: 3rem;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .no-rooms {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-rooms i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="hotel-header">
                <h1>
                    <i class="bi bi-calendar-heart"></i>
                    <?= htmlspecialchars($hotel['name']) ?>
                </h1>
                <p>Calendario de Disponibilidad de Habitaciones</p>
            </div>
            
            <div class="filter-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="form-label"><i class="bi bi-funnel"></i> Filtrar por tipo de habitación:</label>
                        <select id="roomTypeFilter" class="form-select">
                            <option value="">Todos los tipos</option>
                            <?php foreach ($roomTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>">
                                    <?= ucfirst(htmlspecialchars($type)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="month-nav">
                            <button class="btn btn-outline-primary" id="prevMonth">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </button>
                            <h3 id="currentMonth"></h3>
                            <button class="btn btn-outline-primary" id="nextMonth">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="roomsContainer">
                <div class="loading">
                    <i class="bi bi-hourglass-split"></i>
                    <p>Cargando disponibilidad...</p>
                </div>
            </div>
            
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-box" style="background: #d4edda;"></div>
                    <span>Disponible</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box" style="background: #f8d7da;"></div>
                    <span>Reservado</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box" style="background: #e9ecef;"></div>
                    <span>Fecha pasada</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const hotelId = <?= $hotelId ?>;
        const baseUrl = '<?= BASE_URL ?>';
        const whatsappNumber = '5217206212805'; // Format: country code + number
        const whatsappMessage = 'Me interesa hacer una reservación';
        
        let currentDate = new Date();
        let availabilityData = [];
        let selectedRoomType = '';
        
        // Month names in Spanish
        const monthNames = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadAvailability();
            
            document.getElementById('prevMonth').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                loadAvailability();
            });
            
            document.getElementById('nextMonth').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                loadAvailability();
            });
            
            document.getElementById('roomTypeFilter').addEventListener('change', (e) => {
                selectedRoomType = e.target.value;
                renderRooms();
            });
        });
        
        function loadAvailability() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const start = new Date(year, month, 1).toISOString().split('T')[0];
            const end = new Date(year, month + 1, 0).toISOString().split('T')[0];
            
            // Update month display
            document.getElementById('currentMonth').textContent = 
                monthNames[month] + ' ' + year;
            
            // Show loading
            document.getElementById('roomsContainer').innerHTML = `
                <div class="loading">
                    <i class="bi bi-hourglass-split"></i>
                    <p>Cargando disponibilidad...</p>
                </div>
            `;
            
            fetch(`${baseUrl}/public-calendar/getAvailability?hotel_id=${hotelId}&start=${start}&end=${end}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        availabilityData = data.availability;
                        renderRooms();
                    } else {
                        showError('Error al cargar disponibilidad');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Error de conexión');
                });
        }
        
        function renderRooms() {
            const container = document.getElementById('roomsContainer');
            
            // Filter rooms by type
            let rooms = availabilityData;
            if (selectedRoomType) {
                rooms = rooms.filter(room => room.type === selectedRoomType);
            }
            
            if (rooms.length === 0) {
                container.innerHTML = `
                    <div class="no-rooms">
                        <i class="bi bi-inbox"></i>
                        <h4>No hay habitaciones disponibles</h4>
                        <p>Intenta con otro tipo de habitación o mes</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            rooms.forEach(room => {
                html += `
                    <div class="room-card">
                        <div class="room-header">
                            <div class="room-info">
                                <h4>
                                    <i class="bi bi-door-closed"></i> 
                                    Habitación ${room.room_number}
                                </h4>
                                <div class="room-details">
                                    <span><i class="bi bi-people"></i> ${room.capacity} personas</span>
                                    <span><i class="bi bi-currency-dollar"></i> Desde $${parseFloat(room.price).toFixed(2)}</span>
                                </div>
                            </div>
                            <span class="room-type-badge">${getRoomTypeLabel(room.type)}</span>
                        </div>
                        
                        ${room.description ? `<p class="text-muted small mb-3">${room.description}</p>` : ''}
                        
                        <div class="calendar-grid">
                            ${renderDays(room)}
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
            // Add click handlers
            container.querySelectorAll('.day-cell.available').forEach(cell => {
                cell.addEventListener('click', function() {
                    const roomNumber = this.dataset.room;
                    const date = this.dataset.date;
                    const price = this.dataset.price;
                    openWhatsApp(roomNumber, date, price);
                });
            });
        }
        
        function renderDays(room) {
            let html = '';
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const dates = Object.keys(room.dates).sort();
            
            dates.forEach(dateStr => {
                const date = new Date(dateStr + 'T00:00:00');
                const status = room.dates[dateStr];
                const dayOfWeek = date.getDay();
                const dayName = dayNames[dayOfWeek];
                const dayNumber = date.getDate();
                
                // Get price for specific day
                const dayNamesFull = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const price = room.prices[dayNamesFull[dayOfWeek]];
                
                let className = 'day-cell ';
                if (date < today) {
                    className += 'past';
                } else {
                    className += status;
                }
                
                html += `
                    <div class="${className}" 
                         data-room="${room.room_number}" 
                         data-date="${dateStr}"
                         data-price="${price}"
                         title="${status === 'available' ? 'Click para reservar' : status === 'reserved' ? 'Ocupado' : 'Fecha pasada'}">
                        <span class="day-date">${dayNumber}</span>
                        <span class="day-name">${dayName}</span>
                        ${status === 'available' && date >= today ? 
                            `<span class="day-price">$${parseFloat(price).toFixed(0)}</span>` : ''}
                    </div>
                `;
            });
            
            return html;
        }
        
        function getRoomTypeLabel(type) {
            const labels = {
                'single': 'Sencilla',
                'double': 'Doble',
                'suite': 'Suite',
                'deluxe': 'Deluxe',
                'presidential': 'Presidencial'
            };
            return labels[type] || type;
        }
        
        function openWhatsApp(roomNumber, date, price) {
            const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('es-MX', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const message = `${whatsappMessage}%0A%0AHabitación: ${roomNumber}%0AFecha: ${formattedDate}%0APrecio: $${parseFloat(price).toFixed(2)}`;
            const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${message}`;
            
            window.open(whatsappUrl, '_blank');
        }
        
        function showError(message) {
            document.getElementById('roomsContainer').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> ${message}
                </div>
            `;
        }
    </script>
</body>
</html>
