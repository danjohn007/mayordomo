<?php
/**
 * API Endpoint: Check Phone
 * Checks if a phone number already exists and returns guest data if found
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

// Check if user is logged in (using the same logic as helpers.php)
if (!isset($_SESSION['user_id'])) {
    ob_clean(); // Clear any buffered output
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
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
$phone = $_GET['phone'] ?? '';

if (empty($phone)) {
    ob_clean(); // Clear any buffered output
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
    
    ob_clean(); // Clear any buffered output
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
    ob_clean(); // Clear any buffered output
    echo json_encode([
        'success' => false,
        'message' => 'Error al verificar teléfono: ' . $e->getMessage()
    ]);
}
