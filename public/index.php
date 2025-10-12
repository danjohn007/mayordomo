<?php
/**
 * Front Controller
 * Routes all requests through the application
 */

// Load configuration
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../app/helpers/helpers.php';

// Parse the URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Determine controller, method, and parameters
// Convert hyphenated URLs to PascalCase for controller names
$controllerPart = !empty($url[0]) ? $url[0] : 'dashboard';
$controllerPart = str_replace('-', ' ', $controllerPart);
$controllerPart = ucwords($controllerPart);
$controllerPart = str_replace(' ', '', $controllerPart);
$controllerName = $controllerPart . 'Controller';

$method = isset($url[1]) && !empty($url[1]) ? $url[1] : 'index';
$params = array_slice($url, 2);

// Controller file path
$controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

// Check if controller exists
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Instantiate controller
    $controller = new $controllerName();
    
    // Check if method exists
    if (method_exists($controller, $method)) {
        // Call method with parameters
        call_user_func_array([$controller, $method], $params);
    } else {
        // Method not found
        http_response_code(404);
        require_once APP_PATH . '/controllers/ErrorController.php';
        $errorController = new ErrorController();
        $errorController->notFound();
    }
} else {
    // Controller not found
    http_response_code(404);
    require_once APP_PATH . '/controllers/ErrorController.php';
    $errorController = new ErrorController();
    $errorController->notFound();
}
