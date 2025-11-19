<?php
/**
 * Configuración Avanzada de Email   email_advanced.php
 * Configuraciones adicionales para mejorar la entregabilidad y evitar SPAM
 */

/**
 * Configuraciones para evitar SPAM
 */
function getAdvancedEmailConfig() {
    return [
        // Headers adicionales para evitar SPAM
        'additional_headers' => [
            'X-Priority' => '3', // Normal priority
            'X-MSMail-Priority' => 'Normal',
            'Importance' => 'Normal',
            'X-Mailer' => 'Rancho Paraiso Real v1.0',
            'Organization' => 'Rancho Paraíso Real',
        ],
        
        // Configuraciones de autenticación
        'authentication' => [
            'dkim_enable' => false, // Habilitar si tienes DKIM configurado
            'spf_record' => 'v=spf1 include:ranchoparaisoreal.com ~all',
        ],
        
        // Límites de envío para evitar ser marcado como spam
        'rate_limits' => [
            'max_emails_per_hour' => 100,
            'max_emails_per_batch' => 10,
            'delay_between_emails' => 1, // segundos
        ],
        
        // Lista de dominios confiables para whitelist
        'trusted_domains' => [
            'gmail.com',
            'hotmail.com',
            'outlook.com',
            'yahoo.com',
            'live.com',
            'msn.com'
        ]
    ];
}

/**
 * Validar si un email está en dominio confiable
 */
function isEmailFromTrustedDomain($email) {
    $config = getAdvancedEmailConfig();
    $domain = substr(strrchr($email, "@"), 1);
    return in_array($domain, $config['trusted_domains']);
}

/**
 * Aplicar headers anti-spam a PHPMailer
 */
function applyAntiSpamHeaders($mailer) {
    $config = getAdvancedEmailConfig();
    
    foreach ($config['additional_headers'] as $header => $value) {
        $mailer->addCustomHeader($header, $value);
    }
    
    // Headers específicos para mejorar deliverability
    $mailer->addCustomHeader('Return-Path', $mailer->From);
    $mailer->addCustomHeader('Sender', $mailer->From);
    $mailer->addCustomHeader('Errors-To', $mailer->From);
    
    // Configuraciones adicionales
    $mailer->WordWrap = 70;
    $mailer->isHTML(true);
    
    return $mailer;
}