<?php
/**
 * Email Configuration
 * SMTP settings for sending system emails
 */

// Load settings from database if available
function getEmailSettings() {
    static $settings = null;
    
    if ($settings !== null) {
        return $settings;
    }
    
    $settings = [
        'enabled' => false,
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'from_email' => 'noreply@mayorbot.com',
        'from_name' => 'MajorBot'
    ];
    
    try {
        require_once __DIR__ . '/database.php';
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("
            SELECT setting_key, setting_value 
            FROM global_settings 
            WHERE category = 'email' AND setting_key LIKE 'smtp_%'
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = str_replace('smtp_', '', $row['setting_key']);
            $settings[$key] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // Fallback to default settings if database query fails
        error_log("Email settings error: " . $e->getMessage());
    }
    
    return $settings;
}

// Email configuration constants (can be overridden by database settings)
define('SMTP_ENABLED', getEmailSettings()['enabled'] ?? false);
define('SMTP_HOST', getEmailSettings()['host'] ?? 'smtp.gmail.com');
define('SMTP_PORT', getEmailSettings()['port'] ?? 587);
define('SMTP_USERNAME', getEmailSettings()['username'] ?? '');
define('SMTP_PASSWORD', getEmailSettings()['password'] ?? '');
define('SMTP_FROM_EMAIL', getEmailSettings()['from_email'] ?? 'noreply@mayorbot.com');
define('SMTP_FROM_NAME', getEmailSettings()['from_name'] ?? 'MajorBot');
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl
