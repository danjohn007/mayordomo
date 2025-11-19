<?php
/**
 * API Endpoint: Get Resources
 * Returns available rooms, tables, or amenities based on type
 */

// Prevent any HTML output from errors
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// Set JSON header as early as possible
header('Content-Type: application/json');

// Ensure no output before JSON
ob_start();

// Session is already started in config.php, so we don't need to start it again
// Check if user is logged in (using the same logic as helpers.php)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    ob_clean(); // Clear any buffered output
    error_log('API get_resources.php: Session user_id not found. Session data: ' . print_r($_SESSION, true));
    echo json_encode(['success' => false, 'message' => 'No autorizado', 'debug' => 'Session user_id not found']);
    exit;
}

// Get user data (same format as currentUser() helper function)
$user = [
    'id' => $_SESSION['user_id'] ?? null,
    'email' => $_SESSION['email'] ?? null,
    'first_name' => $_SESSION['first_name'] ?? null,
    'last_name' => $_SESSION['last_name'] ?? null,
    'role' => $_SESSION['role'] ?? null,
    'hotel_id' => $_SESSION['hotel_id'] ?? null
];
$type = $_GET['type'] ?? '';

if (empty($type) || !in_array($type, ['room', 'table', 'amenity'])) {
    ob_clean(); // Clear any buffered output
    echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $hotelId = $user['hotel_id'];
    
    // Get optional date parameters for availability checking
    $checkIn = $_GET['check_in'] ?? null;
    $checkOut = $_GET['check_out'] ?? null;
    
    if ($type === 'room') {
        $stmt = $db->prepare("
            SELECT id, room_number, type, capacity, price, status 
            FROM rooms 
            WHERE hotel_id = ? AND status IN ('available', 'reserved')
            ORDER BY room_number
        ");
        $stmt->execute([$hotelId]);
        
        // If dates are provided, check availability for each room
        if ($checkIn && $checkOut) {
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rooms as &$room) {
                // Check if room is available for the date range
                $availStmt = $db->prepare("
                    SELECT COUNT(*) as count
                    FROM room_reservations
                    WHERE room_id = ?
                    AND hotel_id = ?
                    AND status NOT IN ('cancelled', 'completed', 'checked_out')
                    AND (
                        (check_in <= ? AND check_out > ?)
                        OR (check_in < ? AND check_out >= ?)
                        OR (check_in >= ? AND check_out <= ?)
                    )
                ");
                $availStmt->execute([
                    $room['id'],
                    $hotelId,
                    $checkIn, $checkIn,
                    $checkOut, $checkOut,
                    $checkIn, $checkOut
                ]);
                $conflictCount = $availStmt->fetch(PDO::FETCH_ASSOC)['count'];
                $room['available'] = ($conflictCount == 0);
            }
            $resources = $rooms;
        } else {
            // No dates provided, return all rooms as available
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } elseif ($type === 'table') {
        $stmt = $db->prepare("
            SELECT id, table_number, capacity, location, status 
            FROM restaurant_tables 
            WHERE hotel_id = ? AND status IN ('available', 'reserved')
            ORDER BY table_number
        ");
        $stmt->execute([$hotelId]);
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'amenity') {
        $stmt = $db->prepare("
            SELECT id, name, category, price, capacity, opening_time, closing_time 
            FROM amenities 
            WHERE hotel_id = ? AND is_available = 1
            ORDER BY name
        ");
        $stmt->execute([$hotelId]);
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Ensure resources is always an array
    if ($resources === false) {
        $resources = [];
    }
    
    ob_clean(); // Clear any buffered output
    echo json_encode([
        'success' => true,
        'resources' => $resources,
        'count' => count($resources)
    ]);
} catch (Exception $e) {
    ob_clean(); // Clear any buffered output
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar recursos: ' . $e->getMessage()
    ]);
}
