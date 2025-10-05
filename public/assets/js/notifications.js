/**
 * Sistema de Notificaciones con Sonido
 * Realiza polling periódico para verificar nuevas notificaciones
 */

(function() {
    'use strict';
    
    // Configuración
    const POLL_INTERVAL = 15000; // 15 segundos
    const SOUND_FILE = BASE_URL + '/assets/sounds/notification.wav'; // Changed to WAV format
    const SOUND_REPEAT_INTERVAL = 10000; // Repetir sonido cada 10 segundos
    
    // Estado
    let lastNotificationId = 0;
    let notificationSound = null;
    let isFirstCheck = true;
    let soundIntervalId = null;
    let activeNotifications = new Set(); // Track active notifications requiring sound
    
    /**
     * Inicializar sistema de notificaciones
     */
    function init() {
        // Crear elemento de audio para notificaciones
        notificationSound = new Audio(SOUND_FILE);
        notificationSound.volume = 0.7;
        
        // Verificar notificaciones inmediatamente
        checkNotifications();
        
        // Configurar polling periódico
        setInterval(checkNotifications, POLL_INTERVAL);
        
        console.log('Sistema de notificaciones iniciado');
    }
    
    /**
     * Verificar nuevas notificaciones
     */
    function checkNotifications() {
        fetch(BASE_URL + '/notifications/check', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications && data.notifications.length > 0) {
                processNotifications(data.notifications);
            }
        })
        .catch(error => {
            console.error('Error al verificar notificaciones:', error);
        });
    }
    
    /**
     * Procesar notificaciones recibidas
     */
    function processNotifications(notifications) {
        let hasNewNotifications = false;
        let hasPendingNotifications = false;
        
        notifications.forEach(notification => {
            // Solo procesar notificaciones nuevas
            if (notification.id > lastNotificationId) {
                lastNotificationId = notification.id;
                
                // No mostrar ni reproducir sonido en la primera carga
                if (!isFirstCheck) {
                    hasNewNotifications = true;
                    showNotification(notification);
                }
            }
            
            // Check if notification requires sound (pending status only)
            // Sound should play persistently until admin confirms or cancels
            if (notification.requires_sound && (
                (notification.related_type === 'service_request' && notification.status !== 'completed') ||
                (notification.related_type === 'room_reservation' && notification.status === 'pending') ||
                (notification.related_type === 'table_reservation' && notification.status === 'pending') ||
                (notification.related_type === 'amenity_reservation' && notification.status === 'pending'))) {
                activeNotifications.add(notification.id);
                hasPendingNotifications = true;
            } else {
                activeNotifications.delete(notification.id);
            }
        });
        
        // Start or stop persistent sound based on active notifications
        if (hasPendingNotifications && activeNotifications.size > 0) {
            startPersistentSound();
        } else {
            stopPersistentSound();
        }
        
        // Play immediate sound for new notifications
        if (hasNewNotifications && !isFirstCheck) {
            playNotificationSound();
        }
        
        // Actualizar badge de contador
        updateNotificationBadge(notifications.length);
        
        // Marcar que ya no es la primera verificación
        isFirstCheck = false;
    }
    
    /**
     * Mostrar notificación visual
     */
    function showNotification(notification) {
        // Crear elemento de notificación
        const notifElement = document.createElement('div');
        notifElement.className = 'toast align-items-center text-white bg-primary border-0';
        notifElement.setAttribute('role', 'alert');
        notifElement.setAttribute('aria-live', 'assertive');
        notifElement.setAttribute('aria-atomic', 'true');
        
        // Determinar color según prioridad
        let bgClass = 'bg-primary';
        if (notification.priority === 'urgent') {
            bgClass = 'bg-danger';
        } else if (notification.priority === 'high') {
            bgClass = 'bg-warning';
        }
        
        notifElement.className = `toast align-items-center text-white ${bgClass} border-0`;
        
        notifElement.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${escapeHtml(notification.title)}</strong>
                    <br>
                    <small>${escapeHtml(notification.message || '')}</small>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        // Agregar al contenedor de toasts
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        container.appendChild(notifElement);
        
        // Mostrar toast
        const toast = new bootstrap.Toast(notifElement, {
            autohide: true,
            delay: 8000
        });
        toast.show();
        
        // Eliminar del DOM después de ocultarse
        notifElement.addEventListener('hidden.bs.toast', function() {
            notifElement.remove();
        });
    }
    
    /**
     * Reproducir sonido de notificación
     */
    function playNotificationSound() {
        if (notificationSound) {
            notificationSound.currentTime = 0;
            notificationSound.play().catch(error => {
                console.log('No se pudo reproducir el sonido:', error);
                // El navegador puede bloquear la reproducción automática
                // En ese caso, se requiere interacción del usuario
            });
        }
    }
    
    /**
     * Start persistent sound for pending notifications
     */
    function startPersistentSound() {
        if (!soundIntervalId) {
            // Play sound immediately
            playNotificationSound();
            
            // Set up interval to repeat sound
            soundIntervalId = setInterval(() => {
                if (activeNotifications.size > 0) {
                    playNotificationSound();
                } else {
                    stopPersistentSound();
                }
            }, SOUND_REPEAT_INTERVAL);
            
            console.log('Sonido persistente iniciado para notificaciones pendientes');
        }
    }
    
    /**
     * Stop persistent sound
     */
    function stopPersistentSound() {
        if (soundIntervalId) {
            clearInterval(soundIntervalId);
            soundIntervalId = null;
            console.log('Sonido persistente detenido');
        }
    }
    
    /**
     * Actualizar badge de contador de notificaciones
     */
    function updateNotificationBadge(count) {
        let badge = document.getElementById('notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    /**
     * Escapar HTML para prevenir XSS
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    /**
     * Marcar notificación como leída
     */
    window.markNotificationAsRead = function(notificationId) {
        // Remove from active notifications to stop sound
        activeNotifications.delete(notificationId);
        
        // Stop sound if no more active notifications
        if (activeNotifications.size === 0) {
            stopPersistentSound();
        }
        
        fetch(BASE_URL + '/notifications/markAsRead/' + notificationId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                checkNotifications(); // Actualizar contador
            }
        })
        .catch(error => {
            console.error('Error al marcar notificación:', error);
        });
    };
    
    /**
     * Marcar todas las notificaciones como leídas
     */
    window.markAllNotificationsAsRead = function() {
        // Clear all active notifications to stop sound
        activeNotifications.clear();
        stopPersistentSound();
        
        fetch(BASE_URL + '/notifications/markAllAsRead', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                checkNotifications(); // Actualizar contador
            }
        })
        .catch(error => {
            console.error('Error al marcar notificaciones:', error);
        });
    };
    
    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
