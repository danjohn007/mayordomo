<?php
/**
 * API Endpoint: Validate Discount Code
 * Validates discount codes for room reservations
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
$code = trim($_POST['code'] ?? '');
$roomPrice = floatval($_POST['room_price'] ?? 0);

// Validate input
if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Código de descuento requerido']);
    exit;
}

if ($roomPrice <= 0) {
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
        echo json_encode([
            'success' => false, 
            'message' => 'Código de descuento inválido o expirado'
        ]);
        exit;
    }
    
    // Check usage limit
    if ($discountCode['usage_limit'] !== null && $discountCode['times_used'] >= $discountCode['usage_limit']) {
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
    echo json_encode([
        'success' => false,
        'message' => 'Error al validar código: ' . $e->getMessage()
    ]);
}
