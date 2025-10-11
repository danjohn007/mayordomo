<?php
/**
 * API Endpoint: Check Phone
 * Checks if a phone number already exists and returns guest data if found
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
$phone = $_GET['phone'] ?? '';

if (empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Teléfono no proporcionado']);
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
        AND phone = ?
        LIMIT 1
    ");
    
    $stmt->execute([$hotelId, $phone]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($guest) {
        echo json_encode([
            'success' => true,
            'exists' => true,
            'guest' => $guest
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'exists' => false
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al verificar teléfono: ' . $e->getMessage()
    ]);
}
