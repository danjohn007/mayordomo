<?php
/**
 * API Endpoint: Get Resources
 * Returns available rooms, tables, or amenities based on type
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$user = $_SESSION['user'];
$type = $_GET['type'] ?? '';

if (empty($type) || !in_array($type, ['room', 'table', 'amenity'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $hotelId = $user['hotel_id'];
    
    if ($type === 'room') {
        $stmt = $db->prepare("
            SELECT id, room_number, type, capacity, price, status 
            FROM rooms 
            WHERE hotel_id = ? AND status IN ('available', 'reserved')
            ORDER BY room_number
        ");
        $stmt->execute([$hotelId]);
    } elseif ($type === 'table') {
        $stmt = $db->prepare("
            SELECT id, table_number, capacity, location, status 
            FROM restaurant_tables 
            WHERE hotel_id = ? AND status IN ('available', 'reserved')
            ORDER BY table_number
        ");
        $stmt->execute([$hotelId]);
    } elseif ($type === 'amenity') {
        $stmt = $db->prepare("
            SELECT id, name, category, price, capacity, opening_time, closing_time 
            FROM amenities 
            WHERE hotel_id = ? AND is_available = 1
            ORDER BY name
        ");
        $stmt->execute([$hotelId]);
    }
    
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure resources is always an array
    if ($resources === false) {
        $resources = [];
    }
    
    echo json_encode([
        'success' => true,
        'resources' => $resources,
        'count' => count($resources)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar recursos: ' . $e->getMessage()
    ]);
}
