<?php
/**
 * API Endpoint: Validate Discount Code
 * Validates discount codes for room reservations
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
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    ob_clean(); // Clear any buffered output
    error_log('API validate_discount_code.php: Session user_id not found. Session data: ' . print_r($_SESSION, true));
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
$code = trim($_POST['code'] ?? '');
$roomPrice = floatval($_POST['room_price'] ?? 0);

// Validate input
if (empty($code)) {
    ob_clean(); // Clear any buffered output
    echo json_encode(['success' => false, 'message' => 'Código de descuento requerido']);
    exit;
}

if ($roomPrice <= 0) {
    ob_clean(); // Clear any buffered output
    echo json_encode(['success' => false, 'message' => 'Precio de habitación inválido']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $hotelId = $user['hotel_id'];
    $today = date('Y-m-d');
    
    // Find the discount code
    $stmt = $db->prepare("
        SELECT 
            id, 
            code, 
            discount_type, 
            amount, 
            usage_limit, 
            times_used,
            valid_from,
            valid_to,
            description
        FROM discount_codes 
        WHERE code = ? 
          AND hotel_id = ? 
          AND active = 1 
          AND valid_from <= ? 
          AND valid_to >= ?
    ");
    $stmt->execute([$code, $hotelId, $today, $today]);
    $discountCode = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if code exists and is valid
    if (!$discountCode) {
        ob_clean(); // Clear any buffered output
        echo json_encode([
            'success' => false, 
            'message' => 'Código de descuento inválido o expirado'
        ]);
        exit;
    }
    
    // Check usage limit
    if ($discountCode['usage_limit'] !== null && $discountCode['times_used'] >= $discountCode['usage_limit']) {
        ob_clean(); // Clear any buffered output
        echo json_encode([
            'success' => false, 
            'message' => 'Este código de descuento ha alcanzado su límite de uso'
        ]);
        exit;
    }
    
    // Calculate discount
    $discountAmount = 0;
    if ($discountCode['discount_type'] === 'percentage') {
        // Percentage discount
        $discountAmount = ($roomPrice * $discountCode['amount']) / 100;
        $discountAmount = round($discountAmount, 2);
    } else {
        // Fixed amount discount
        $discountAmount = $discountCode['amount'];
        // Don't allow discount to exceed price
        if ($discountAmount > $roomPrice) {
            $discountAmount = $roomPrice;
        }
    }
    
    $finalPrice = $roomPrice - $discountAmount;
    $finalPrice = round($finalPrice, 2);
    
    // Ensure final price is not negative
    if ($finalPrice < 0) {
        $finalPrice = 0;
    }
    
    // Return success with discount details
    ob_clean(); // Clear any buffered output
    echo json_encode([
        'success' => true,
        'message' => 'Código válido aplicado correctamente',
        'discount' => [
            'id' => $discountCode['id'],
            'code' => $discountCode['code'],
            'type' => $discountCode['discount_type'],
            'amount' => floatval($discountCode['amount']),
            'discount_amount' => $discountAmount,
            'original_price' => $roomPrice,
            'final_price' => $finalPrice,
            'description' => $discountCode['description']
        ]
    ]);
    
} catch (Exception $e) {
    ob_clean(); // Clear any buffered output
    echo json_encode([
        'success' => false,
        'message' => 'Error al validar código: ' . $e->getMessage()
    ]);
}
