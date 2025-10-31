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
    
    // Configuración principal (hardcoded para Rancho Paraíso Real)
    // TEMPORAL: Usando ressetpassword@ porque reservaciones@ no autentica
    $settings = [
        'enabled' => true,
        'host' => 'ranchoparaisoreal.com',
        'port' => 465,
        'username' => 'ressetpassword@ranchoparaisoreal.com',
        'password' => 'Danjohn007',
        'from_email' => 'ressetpassword@ranchoparaisoreal.com',
        'from_name' => 'Rancho Paraíso Real - Reservaciones',
        'encryption' => 'ssl'
    ];
    
    // NOTA: Comentado para usar solo la configuración de arriba
    // Si quieres usar configuración desde la base de datos, descomenta este bloque:
    /*
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
    */
    
    return $settings;
}

// Email configuration constants (can be overridden by database settings)
define('SMTP_ENABLED', getEmailSettings()['enabled'] ?? false);
define('SMTP_HOST', getEmailSettings()['host'] ?? 'ranchoparaisoreal.com');
define('SMTP_PORT', getEmailSettings()['port'] ?? 465);
define('SMTP_USERNAME', getEmailSettings()['username'] ?? '');
define('SMTP_PASSWORD', getEmailSettings()['password'] ?? '');
define('SMTP_FROM_EMAIL', getEmailSettings()['from_email'] ?? 'reservaciones@ranchoparaisoreal.com');
define('SMTP_FROM_NAME', getEmailSettings()['from_name'] ?? 'Rancho Paraíso Real');
define('SMTP_ENCRYPTION', getEmailSettings()['encryption'] ?? 'ssl'); // tls or ssl
