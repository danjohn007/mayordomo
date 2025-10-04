<?php
/**
 * Error Controller
 */

require_once APP_PATH . '/controllers/BaseController.php';

class ErrorController extends BaseController {
    
    protected function checkAuth() {
        // Override to allow public access
    }
    
    public function notFound() {
        http_response_code(404);
        $this->view('errors/404', [
            'title' => 'Página No Encontrada'
        ]);
    }
    
    public function forbidden() {
        http_response_code(403);
        $this->view('errors/forbidden', [
            'title' => 'Acceso Denegado'
        ]);
    }
}
