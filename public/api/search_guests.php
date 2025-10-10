<?php
/**
 * API Endpoint: Search Guests
 * Returns guests matching the search query
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
$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query muy corto']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $hotelId = $user['hotel_id'];
    
    $stmt = $db->prepare("
        SELECT id, first_name, last_name, email, phone 
        FROM users 
        WHERE hotel_id = ? 
        AND role = 'guest'
        AND (
            CONCAT(first_name, ' ', last_name) LIKE ? 
            OR email LIKE ?
            OR phone LIKE ?
        )
        ORDER BY first_name, last_name
        LIMIT 20
    ");
    
    $searchTerm = '%' . $query . '%';
    $stmt->execute([$hotelId, $searchTerm, $searchTerm, $searchTerm]);
    
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'guests' => $guests
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al buscar huéspedes: ' . $e->getMessage()
    ]);
}
