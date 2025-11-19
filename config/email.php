<?php
/**
 * Email Configuration
 * SMTP settings for sending system emails
 */

// Load settings from database if available
function getEmailSettings($hotelId = null) {
    static $settings = [];
    
    // If no hotelId provided, try to get from current user
    if ($hotelId === null) {
        // Check if we have a logged-in user with hotel_id
        if (function_exists('currentUser')) {
            $user = currentUser();
            if ($user && isset($user['hotel_id'])) {
                $hotelId = $user['hotel_id'];
            }
        }
    }
    
    // Return cached settings if already loaded for this hotel
    $cacheKey = $hotelId ?? 'default';
    if (isset($settings[$cacheKey])) {
        return $settings[$cacheKey];
    }
    
    // Default configuration (fallback)
    $settings[$cacheKey] = [
        'enabled' => true,
        'host' => 'mail.ranchoparaisoreal.com',
        'port' => 465,
        'username' => 'reservaciones@ranchoparaisoreal.com',
        'password' => 'Danjohn007',
        'from_email' => 'reservaciones@ranchoparaisoreal.com',
        'from_name' => 'Rancho Paraíso Real - Reservaciones',
        'encryption' => 'ssl'
    ];
    
    // Try to load settings from hotel_settings table
    if ($hotelId !== null) {
        try {
            require_once __DIR__ . '/database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT setting_key, setting_value 
                FROM hotel_settings 
                WHERE hotel_id = ? AND category = 'email' AND setting_key LIKE 'smtp_%'
            ");
            $stmt->execute([$hotelId]);
            
            $hasSettings = false;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hasSettings = true;
                $key = str_replace('smtp_', '', $row['setting_key']);
                $value = $row['setting_value'];
                
                // Convert boolean strings
                if ($key === 'enabled' && ($value === '0' || $value === '1')) {
                    $value = ($value === '1');
                }
                // Convert port to integer
                if ($key === 'port') {
                    $value = (int)$value;
                }
                
                $settings[$cacheKey][$key] = $value;
            }
            
            // If we found settings, log success
            if ($hasSettings) {
                error_log("SMTP settings loaded from hotel_settings for hotel_id: " . $hotelId);
            }
            
        } catch (Exception $e) {
            // Fallback to default settings if database query fails
            error_log("Email settings error: " . $e->getMessage());
        }
    }
    
    return $settings[$cacheKey];
}

// Email configuration constants (can be overridden by database settings)
define('SMTP_ENABLED', getEmailSettings()['enabled'] ?? false);
define('SMTP_HOST', getEmailSettings()['host'] ?? 'mail.ranchoparaisoreal.com');
define('SMTP_PORT', getEmailSettings()['port'] ?? 465);
define('SMTP_USERNAME', getEmailSettings()['username'] ?? '');
define('SMTP_PASSWORD', getEmailSettings()['password'] ?? '');
define('SMTP_FROM_EMAIL', getEmailSettings()['from_email'] ?? 'reservaciones@ranchoparaisoreal.com');
define('SMTP_FROM_NAME', getEmailSettings()['from_name'] ?? 'Rancho Paraíso Real');
define('SMTP_ENCRYPTION', getEmailSettings()['encryption'] ?? 'ssl'); // tls or ssl
