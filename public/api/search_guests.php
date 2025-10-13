<?php
/**
 * API Endpoint: Search Guests
 * Returns guests matching the search query
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
$query = $_GET['q'] ?? '';

// Allow shorter search for phone numbers (at least 3 digits for phone search)
$minLength = 2;
if (preg_match('/^\d+$/', $query)) {
    // If only digits, allow searching with at least 3 characters
    $minLength = 3;
}

if (strlen($query) < $minLength) {
    ob_clean(); // Clear any buffered output
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
    
    ob_clean(); // Clear any buffered output
    echo json_encode([
        'success' => true,
        'guests' => $guests
    ]);
} catch (Exception $e) {
    ob_clean(); // Clear any buffered output
    echo json_encode([
        'success' => false,
        'message' => 'Error al buscar huéspedes: ' . $e->getMessage()
    ]);
}
