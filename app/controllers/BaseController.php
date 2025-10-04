<?php
/**
 * Base Controller
 * All controllers extend from this class
 */

class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->checkAuth();
    }
    
    /**
     * Check if user is authenticated
     */
    protected function checkAuth() {
        // Public routes that don't require authentication
        $publicRoutes = ['auth', 'test'];
        
        $currentController = strtolower(str_replace('Controller', '', get_class($this)));
        
        if (!in_array($currentController, $publicRoutes) && !isLoggedIn()) {
            redirect('auth/login');
        }
    }
    
    /**
     * Load a view
     */
    protected function view($view, $data = []) {
        extract($data);
        
        // Check if view file exists
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View not found: $view");
        }
    }
    
    /**
     * Load a model
     */
    protected function model($model) {
        $modelFile = APP_PATH . '/models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model($this->db);
        } else {
            die("Model not found: $model");
        }
    }
    
    /**
     * Check if user has required role
     */
    protected function requireRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!hasRole($roles)) {
            http_response_code(403);
            $this->view('errors/forbidden', [
                'title' => 'Acceso Denegado',
                'message' => 'No tienes permisos para acceder a esta sección.'
            ]);
            exit;
        }
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
