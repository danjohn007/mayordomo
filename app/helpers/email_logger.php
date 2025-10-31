<?php
/**
 * Helper function para logging personalizado
 */

if (!function_exists('logEmail')) {
    function logEmail($message) {
        $logFile = __DIR__ . '/../logs/email.log';
        $logDir = dirname($logFile);
        
        // Crear directorio de logs si no existe
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        // Formato del log
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        // Escribir en el archivo
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // También escribir en error_log estándar
        error_log($message);
    }
}
