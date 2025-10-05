<?php
/**
 * Calendar Controller
 * Manages calendar view for reservations and service requests
 */

require_once APP_PATH . '/controllers/BaseController.php';

class CalendarController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'manager', 'hostess', 'collaborator']);
    }
    
    /**
     * Display calendar view
     */
    public function index() {
        $this->view('calendar/index', [
            'title' => 'Calendario de Reservaciones'
        ]);
    }
    
    /**
     * Get events for calendar (AJAX)
     */
    public function getEvents() {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $user = currentUser();
        $hotelId = $user['hotel_id'];
        
        // Get date range from request
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        
        $events = [];
        
        try {
            // Get room reservations
            $stmt = $this->db->prepare("
                SELECT 
                    rr.id,
                    rr.check_in,
                    rr.check_out,
                    COALESCE(rr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
                    rr.status,
                    r.room_number,
                    'room' as event_type
                FROM room_reservations rr
                JOIN rooms r ON rr.room_id = r.id
                LEFT JOIN users u ON rr.guest_id = u.id
                WHERE r.hotel_id = ?
                AND (
                    (rr.check_in BETWEEN ? AND ?)
                    OR (rr.check_out BETWEEN ? AND ?)
                    OR (rr.check_in <= ? AND rr.check_out >= ?)
                )
                ORDER BY rr.check_in
            ");
            $stmt->execute([$hotelId, $start, $end, $start, $end, $start, $end]);
            $roomReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($roomReservations as $reservation) {
                $color = $this->getColorForStatus($reservation['status']);
                $events[] = [
                    'id' => 'room_' . $reservation['id'],
                    'title' => '🚪 Hab. ' . $reservation['room_number'] . ' - ' . $reservation['guest_name'],
                    'start' => $reservation['check_in'],
                    'end' => $reservation['check_out'],
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'type' => 'room',
                        'status' => $reservation['status'],
                        'guest' => $reservation['guest_name'],
                        'room' => $reservation['room_number']
                    ]
                ];
            }
            
            // Get table reservations
            $stmt = $this->db->prepare("
                SELECT 
                    tr.id,
                    tr.reservation_date,
                    tr.reservation_time,
                    COALESCE(tr.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
                    tr.party_size,
                    tr.status,
                    t.table_number,
                    'table' as event_type
                FROM table_reservations tr
                JOIN restaurant_tables t ON tr.table_id = t.id
                LEFT JOIN users u ON tr.guest_id = u.id
                WHERE t.hotel_id = ?
                AND tr.reservation_date BETWEEN ? AND ?
                ORDER BY tr.reservation_date, tr.reservation_time
            ");
            $stmt->execute([$hotelId, $start, $end]);
            $tableReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($tableReservations as $reservation) {
                $color = $this->getColorForStatus($reservation['status']);
                $datetime = $reservation['reservation_date'] . 'T' . $reservation['reservation_time'];
                $events[] = [
                    'id' => 'table_' . $reservation['id'],
                    'title' => '🍽️ Mesa ' . $reservation['table_number'] . ' - ' . $reservation['guest_name'] . ' (' . $reservation['party_size'] . ' pers.)',
                    'start' => $datetime,
                    'allDay' => false,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'type' => 'table',
                        'status' => $reservation['status'],
                        'guest' => $reservation['guest_name'],
                        'table' => $reservation['table_number'],
                        'time' => substr($reservation['reservation_time'], 0, 5)
                    ]
                ];
            }
            
            // Get amenity reservations
            $stmt = $this->db->prepare("
                SELECT 
                    ar.id,
                    ar.reservation_date,
                    ar.reservation_time,
                    COALESCE(ar.guest_name, CONCAT(u.first_name, ' ', u.last_name)) as guest_name,
                    ar.status,
                    a.name as amenity_name,
                    'amenity' as event_type
                FROM amenity_reservations ar
                JOIN amenities a ON ar.amenity_id = a.id
                LEFT JOIN users u ON ar.user_id = u.id
                WHERE ar.hotel_id = ?
                AND ar.reservation_date BETWEEN ? AND ?
                ORDER BY ar.reservation_date, ar.reservation_time
            ");
            $stmt->execute([$hotelId, $start, $end]);
            $amenityReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($amenityReservations as $reservation) {
                $color = $this->getColorForStatus($reservation['status']);
                $datetime = $reservation['reservation_date'] . 'T' . $reservation['reservation_time'];
                $events[] = [
                    'id' => 'amenity_' . $reservation['id'],
                    'title' => '⭐ ' . $reservation['amenity_name'] . ' - ' . $reservation['guest_name'],
                    'start' => $datetime,
                    'allDay' => false,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'type' => 'amenity',
                        'status' => $reservation['status'],
                        'guest' => $reservation['guest_name'],
                        'amenity' => $reservation['amenity_name'],
                        'time' => substr($reservation['reservation_time'], 0, 5)
                    ]
                ];
            }
            
            // Get service requests with dates
            $stmt = $this->db->prepare("
                SELECT 
                    sr.id,
                    sr.created_at,
                    sr.request_description,
                    sr.status,
                    sr.priority,
                    u.first_name,
                    u.last_name,
                    'service' as event_type
                FROM service_requests sr
                LEFT JOIN users u ON sr.user_id = u.id
                WHERE sr.hotel_id = ?
                AND DATE(sr.created_at) BETWEEN ? AND ?
                AND sr.status NOT IN ('completed', 'cancelled')
                ORDER BY sr.created_at
            ");
            $stmt->execute([$hotelId, $start, $end]);
            $serviceRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($serviceRequests as $request) {
                $color = $this->getColorForPriority($request['priority'] ?? 'normal');
                $guest = ($request['first_name'] ? $request['first_name'] . ' ' . $request['last_name'] : 'Huésped');
                $events[] = [
                    'id' => 'service_' . $request['id'],
                    'title' => '🔔 Servicio: ' . substr($request['request_description'], 0, 30) . '...',
                    'start' => $request['created_at'],
                    'allDay' => false,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'type' => 'service',
                        'status' => $request['status'],
                        'priority' => $request['priority'] ?? 'normal',
                        'guest' => $guest,
                        'description' => $request['request_description']
                    ]
                ];
            }
            
            echo json_encode($events);
        } catch (Exception $e) {
            error_log('Calendar error: ' . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Get color based on status
     */
    private function getColorForStatus($status) {
        $colors = [
            'pending' => '#ffc107',
            'confirmed' => '#28a745',
            'checked_in' => '#17a2b8',
            'seated' => '#17a2b8',
            'in_use' => '#17a2b8',
            'checked_out' => '#6c757d',
            'completed' => '#6c757d',
            'cancelled' => '#dc3545',
            'no_show' => '#dc3545'
        ];
        
        return $colors[$status] ?? '#007bff';
    }
    
    /**
     * Get color based on priority
     */
    private function getColorForPriority($priority) {
        $colors = [
            'low' => '#17a2b8',
            'normal' => '#007bff',
            'high' => '#ffc107',
            'urgent' => '#dc3545'
        ];
        
        return $colors[$priority] ?? '#007bff';
    }
}
