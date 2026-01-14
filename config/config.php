<?php
/**
 * MajorBot - Configuration File
 * Auto-detects base URL and sets up application constants
 */

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-detect base URL
function detectBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the directory path where index.php is located
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Remove /public if present in the path
    $script = str_replace('/public', '', $script);
    
    // Remove trailing slash
    $script = rtrim($script, '/');
    
    return $protocol . '://' . $host . $script;
}

// Application configuration
define('BASE_URL', detectBaseUrl());
define('APP_NAME', 'MajorBot');
define('APP_VERSION', '1.0.0');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'majorbot_sistema');
define('DB_USER', 'majorbot_sistema');
define('DB_PASS', 'Danjohn007!');
define('DB_CHARSET', 'utf8mb4');

// Path configuration
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Timezone
date_default_timezone_set('America/Mexico_City');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Password hashing options
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12);
