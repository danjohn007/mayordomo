<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Chatbot de Reservaciones' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .chat-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .chat-body {
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 10px;
            max-width: 80%;
        }
        .message.bot {
            background: #f0f0f0;
            margin-right: auto;
        }
        .message.user {
            background: #667eea;
            color: white;
            margin-left: auto;
        }
        .resource-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .resource-card:hover {
            background: #f8f9fa;
            border-color: #667eea;
        }
        .resource-card.selected {
            background: #e7f3ff;
            border-color: #667eea;
        }
        .resource-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body style="background: #f5f7fa;">
    <div class="chat-container">
        <div class="chat-header">
            <h4 class="mb-0"><i class="bi bi-chat-dots"></i> <?= e($hotel['name']) ?></h4>
            <small>Reservaciones en línea</small>
        </div>
        <div class="chat-body" id="chatBody">
            <div class="message bot">
                ¡Hola! 👋 Soy el asistente de reservaciones de <?= e($hotel['name']) ?>. ¿Qué te gustaría reservar?
            </div>
            <div class="mt-3">
                <button class="btn btn-outline-primary btn-sm me-2" onclick="selectType('room')">
                    <i class="bi bi-door-closed"></i> Habitación
                </button>
                <button class="btn btn-outline-primary btn-sm me-2" onclick="selectType('table')">
                    <i class="bi bi-table"></i> Mesa
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="selectType('amenity')">
                    <i class="bi bi-stars"></i> Amenidad
                </button>
            </div>
        </div>
        <div class="p-3 border-top" id="inputArea" style="display: none;">
            <form id="reservationForm" onsubmit="submitReservation(event)">
                <input type="hidden" id="hotel_id" value="<?= $hotelId ?>">
                <input type="hidden" id="resource_type" name="resource_type">
                <input type="hidden" id="resource_id" name="resource_id">
                
                <div id="dateSection" style="display: none;">
                    <div class="mb-2">
                        <label class="form-label">Fecha de entrada/reservación</label>
                        <input type="date" class="form-control" id="check_in_date" name="check_in_date" required>
                    </div>
                    <div class="mb-2" id="checkOutField" style="display: none;">
                        <label class="form-label">Fecha de salida</label>
                        <input type="date" class="form-control" id="check_out_date" name="check_out_date">
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="checkAvailability()">
                        Buscar disponibilidad
                    </button>
                </div>
                
                <div id="resourcesSection" style="display: none;"></div>
                
                <div id="contactSection" style="display: none;">
                    <div class="mb-2">
                        <label class="form-label">Nombre completo *</label>
                        <input type="text" class="form-control" id="guest_name" name="guest_name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" id="guest_email" name="guest_email" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Teléfono (10 dígitos) *</label>
                        <input type="tel" class="form-control" id="guest_phone" name="guest_phone" 
                               pattern="[0-9]{10}" maxlength="10" placeholder="10 dígitos" required>
                        <small class="text-muted">Debe contener exactamente 10 dígitos</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Notas adicionales</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-circle"></i> Confirmar Reservación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const baseUrl = '<?= BASE_URL ?>';
        const hotelId = <?= $hotelId ?>;
        let selectedResourceType = '';

        function addMessage(text, isUser = false) {
            const chatBody = document.getElementById('chatBody');
            const message = document.createElement('div');
            message.className = 'message ' + (isUser ? 'user' : 'bot');
            message.textContent = text;
            chatBody.appendChild(message);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function selectType(type) {
            selectedResourceType = type;
            document.getElementById('resource_type').value = type;
            
            let typeName = type === 'room' ? 'habitación' : (type === 'table' ? 'mesa' : 'amenidad');
            addMessage('Quiero reservar una ' + typeName, true);
            addMessage('Perfecto! ¿Para qué fecha?');
            
            document.getElementById('inputArea').style.display = 'block';
            document.getElementById('dateSection').style.display = 'block';
            
            if (type === 'room') {
                document.getElementById('checkOutField').style.display = 'block';
                document.getElementById('check_out_date').required = true;
            } else {
                document.getElementById('checkOutField').style.display = 'none';
                document.getElementById('check_out_date').required = false;
            }
        }

        function checkAvailability() {
            const checkIn = document.getElementById('check_in_date').value;
            const checkOut = document.getElementById('check_out_date').value;
            
            if (!checkIn) {
                alert('Por favor selecciona una fecha');
                return;
            }
            
            if (selectedResourceType === 'room' && !checkOut) {
                alert('Por favor selecciona la fecha de salida');
                return;
            }
            
            addMessage(checkIn + (checkOut ? ' - ' + checkOut : ''), true);
            addMessage('Buscando disponibilidad...');
            
            const formData = new FormData();
            formData.append('hotel_id', hotelId);
            formData.append('resource_type', selectedResourceType);
            formData.append('check_in', checkIn);
            formData.append('check_out', checkOut || checkIn);
            
            fetch(baseUrl + '/chatbot/checkAvailability', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.resources && data.resources.length > 0) {
                    displayResources(data.resources);
                } else {
                    addMessage('Lo siento, no hay disponibilidad para esas fechas. Por favor intenta con otras fechas.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                addMessage('Error al buscar disponibilidad. Por favor intenta de nuevo.');
            });
        }

        function displayResources(resources) {
            const resourcesSection = document.getElementById('resourcesSection');
            resourcesSection.innerHTML = '<div class="message bot">Estas son las opciones disponibles:</div>';
            
            resources.forEach(resource => {
                const card = document.createElement('div');
                card.className = 'resource-card';
                card.onclick = () => selectResource(resource.id, card);
                
                let content = '';
                if (resource.image) {
                    content += `<img src="${baseUrl}/${resource.image}" alt="${resource.name || resource.room_number || resource.table_number}">`;
                }
                
                if (selectedResourceType === 'room') {
                    content += `<h6 class="mt-2">Habitación ${resource.room_number}</h6>`;
                    content += `<p class="mb-0 small">Tipo: ${resource.type} | Capacidad: ${resource.capacity} | $${resource.price}/noche</p>`;
                } else if (selectedResourceType === 'table') {
                    content += `<h6 class="mt-2">Mesa ${resource.table_number}</h6>`;
                    content += `<p class="mb-0 small">Capacidad: ${resource.capacity} personas | ${resource.location || ''}</p>`;
                } else {
                    content += `<h6 class="mt-2">${resource.name}</h6>`;
                    content += `<p class="mb-0 small">${resource.description || ''} | $${resource.price}</p>`;
                }
                
                card.innerHTML = content;
                resourcesSection.appendChild(card);
            });
            
            resourcesSection.style.display = 'block';
            document.getElementById('dateSection').style.display = 'none';
        }

        function selectResource(resourceId, cardElement) {
            document.querySelectorAll('.resource-card').forEach(c => c.classList.remove('selected'));
            cardElement.classList.add('selected');
            document.getElementById('resource_id').value = resourceId;
            
            addMessage('He seleccionado esta opción', true);
            addMessage('Por favor proporciona tus datos de contacto para confirmar la reservación:');
            
            document.getElementById('resourcesSection').style.display = 'none';
            document.getElementById('contactSection').style.display = 'block';
        }

        function submitReservation(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('reservationForm'));
            formData.append('hotel_id', hotelId);
            formData.append('check_in_date', document.getElementById('check_in_date').value);
            formData.append('check_out_date', document.getElementById('check_out_date').value);
            
            fetch(baseUrl + '/chatbot/createReservation', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addMessage(data.message);
                    document.getElementById('contactSection').style.display = 'none';
                    setTimeout(() => {
                        alert('¡Reservación creada! Te contactaremos pronto para confirmar.');
                        location.reload();
                    }, 2000);
                } else {
                    alert(data.message || 'Error al crear la reservación');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear la reservación. Por favor intenta de nuevo.');
            });
        }
    </script>
</body>
</html>
