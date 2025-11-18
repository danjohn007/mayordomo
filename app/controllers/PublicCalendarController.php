<?php
/**
 * Public Calendar Controller
 * Public view for room availability calendar - NO authentication required
 */

require_once APP_PATH . '/controllers/BaseController.php';

class PublicCalendarController extends BaseController {
    
    /**
     * Display public calendar view - NO authentication required
     */
    public function index() {
        // Get hotel_id from query parameter or default to 1
        $hotelId = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 1;
        
        // Get hotel info
        $stmt = $this->db->prepare("SELECT * FROM hotels WHERE id = ? AND is_active = 1");
        $stmt->execute([$hotelId]);
        $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$hotel) {
            die('Hotel no encontrado');
        }
        
        // Get room types for the hotel
        $stmt = $this->db->prepare("
            SELECT DISTINCT type 
            FROM rooms 
            WHERE hotel_id = ? AND status IN ('available', 'reserved', 'occupied')
            ORDER BY type
        ");
        $stmt->execute([$hotelId]);
        $roomTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Get contact phone from hotel settings
        $stmt = $this->db->prepare("
            SELECT setting_value 
            FROM hotel_settings 
            WHERE hotel_id = ? AND setting_key = 'contact_phone'
        ");
        $stmt->execute([$hotelId]);
        $contactPhone = $stmt->fetchColumn();
        
        // Default to a fallback number if not set
        if (empty($contactPhone)) {
            $contactPhone = '7206212805'; // Default fallback
        }
        
        // Render without requiring authentication
        $this->viewPublic('calendar/public', [
            'title' => 'Calendario de Reservaciones - ' . $hotel['name'],
            'hotel' => $hotel,
            'hotelId' => $hotelId,
            'roomTypes' => $roomTypes,
            'contactPhone' => $contactPhone
        ]);
    }
    
    /**
     * Get room availability data for public calendar (AJAX)
     */
    public function getAvailability() {
        header('Content-Type: application/json');
        
        $hotelId = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 1;
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t', strtotime('+3 months'));
        
        try {
            // Get all rooms for the hotel
            $stmt = $this->db->prepare("
                SELECT 
                    r.id,
                    r.room_number,
                    r.type,
                    r.capacity,
                    r.price,
                    r.price_monday,
                    r.price_tuesday,
                    r.price_wednesday,
                    r.price_thursday,
                    r.price_friday,
                    r.price_saturday,
                    r.price_sunday,
                    r.description,
                    (SELECT image_path FROM resource_images WHERE resource_type = 'room' AND resource_id = r.id AND is_primary = 1 LIMIT 1) as image
                FROM rooms r
                WHERE r.hotel_id = ? 
                AND r.status IN ('available', 'reserved', 'occupied')
                ORDER BY r.type, r.room_number
            ");
            $stmt->execute([$hotelId]);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get all reservations for the date range
            $stmt = $this->db->prepare("
                SELECT 
                    room_id,
                    check_in,
                    check_out,
                    status
                FROM room_reservations
                WHERE room_id IN (SELECT id FROM rooms WHERE hotel_id = ?)
                AND status NOT IN ('cancelled')
                AND (
                    (check_in BETWEEN ? AND ?)
                    OR (check_out BETWEEN ? AND ?)
                    OR (check_in <= ? AND check_out >= ?)
                )
            ");
            $stmt->execute([$hotelId, $start, $end, $start, $end, $start, $end]);
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Build availability calendar
            $availability = [];
            
            foreach ($rooms as $room) {
                $roomId = $room['id'];
                $availability[$roomId] = [
                    'room_number' => $room['room_number'],
                    'type' => $room['type'],
                    'capacity' => $room['capacity'],
                    'price' => $room['price'],
                    'prices' => [
                        'monday' => $room['price_monday'] ?? $room['price'],
                        'tuesday' => $room['price_tuesday'] ?? $room['price'],
                        'wednesday' => $room['price_wednesday'] ?? $room['price'],
                        'thursday' => $room['price_thursday'] ?? $room['price'],
                        'friday' => $room['price_friday'] ?? $room['price'],
                        'saturday' => $room['price_saturday'] ?? $room['price'],
                        'sunday' => $room['price_sunday'] ?? $room['price'],
                    ],
                    'description' => $room['description'],
                    'image' => $room['image'],
                    'dates' => []
                ];
                
                // Generate date range
                $currentDate = new DateTime($start);
                $endDate = new DateTime($end);
                
                while ($currentDate <= $endDate) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $availability[$roomId]['dates'][$dateStr] = 'available';
                    $currentDate->modify('+1 day');
                }
            }
            
            // Mark reserved dates
            foreach ($reservations as $reservation) {
                $roomId = $reservation['room_id'];
                if (!isset($availability[$roomId])) continue;
                
                $checkIn = new DateTime($reservation['check_in']);
                $checkOut = new DateTime($reservation['check_out']);
                $currentDate = clone $checkIn;
                
                while ($currentDate < $checkOut) {
                    $dateStr = $currentDate->format('Y-m-d');
                    if (isset($availability[$roomId]['dates'][$dateStr])) {
                        $availability[$roomId]['dates'][$dateStr] = 'reserved';
                    }
                    $currentDate->modify('+1 day');
                }
            }
            
            echo json_encode([
                'success' => true,
                'availability' => array_values($availability)
            ]);
            
        } catch (Exception $e) {
            error_log('Public calendar error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Render a view without authentication requirement
     */
    protected function viewPublic($view, $data = []) {
        extract($data);
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View not found: $view");
        }
    }
}
