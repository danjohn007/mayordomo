<?php
/**
 * Password Reset Email Service
 * Servicio para envío de correos de recuperación de contraseña usando PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class PasswordResetEmailService {
    
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setupSMTP();
    }
    
    /**
     * Configurar servidor SMTP - Cuenta dedicada para recuperación de contraseña
     */
    private function setupSMTP() {
        try {
            // NOTA: Cuenta específica para reset: ressetpassword@ (con doble 's')
            $this->mailer->isSMTP();
            $this->mailer->Host = 'ranchoparaisoreal.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'ressetpassword@ranchoparaisoreal.com';
            $this->mailer->Password = 'Danjohn007';
            $this->mailer->SMTPSecure = 'ssl';
            $this->mailer->Port = 465;
            
            // Configuración adicional
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';
            
            // Remitente
            $this->mailer->setFrom('ressetpassword@ranchoparaisoreal.com', 'Rancho Paraíso Real - Recuperación');
            
        } catch (Exception $e) {
            error_log("Error al configurar SMTP para reset password: " . $e->getMessage());
            throw new Exception("Error al configurar el servicio de correo");
        }
    }
    
    /**
     * Enviar correo de recuperación de contraseña
     * 
     * @param string $email Email del usuario
     * @param string $firstName Nombre del usuario
     * @param string $resetLink Enlace de recuperación
     * @return bool
     */
    public function sendPasswordResetEmail($email, $firstName, $resetLink) {
        try {
            // Cargar helper de logging
            require_once APP_PATH . '/helpers/email_logger.php';
            
            logEmail("=== INICIO envío de correo de recuperación de contraseña ===");
            logEmail("Email: $email, Name: $firstName");
            logEmail("Reset Link: $resetLink");
            
            // Limpiar destinatarios previos
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Destinatario
            $this->mailer->addAddress($email, $firstName);
            
            // Asunto
            $this->mailer->Subject = 'Recuperación de Contraseña - Rancho Paraíso Real';
            
            // Contenido HTML
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->getPasswordResetTemplate($firstName, $resetLink);
            
            // Contenido alternativo en texto plano
            $this->mailer->AltBody = $this->getPasswordResetPlainText($firstName, $resetLink);
            
            // Enviar correo
            $result = $this->mailer->send();
            
            if ($result) {
                logEmail("✅ Correo de recuperación enviado exitosamente a: $email");
            } else {
                logEmail("❌ No se pudo enviar el correo de recuperación a: $email");
            }
            
            logEmail("=== FIN envío de correo de recuperación ===");
            return $result;
            
        } catch (Exception $e) {
            logEmail("❌ ERROR al enviar correo de recuperación: " . $this->mailer->ErrorInfo);
            logEmail("Exception: " . $e->getMessage());
            error_log("Error al enviar correo de recuperación: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Obtener plantilla HTML del correo de recuperación
     */
    private function getPasswordResetTemplate($firstName, $resetLink) {
        $firstName = htmlspecialchars($firstName);
        $resetLink = htmlspecialchars($resetLink);
        
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperación de Contraseña</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f4f4; padding: 20px;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                            <!-- Header -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;'>
                                    <h1 style='color: #ffffff; margin: 0; font-size: 28px;'>Rancho Paraíso Real</h1>
                                    <p style='color: #ffffff; margin: 10px 0 0 0; font-size: 16px;'>Recuperación de Contraseña</p>
                                </td>
                            </tr>
                            
                            <!-- Body -->
                            <tr>
                                <td style='padding: 40px 30px;'>
                                    <h2 style='color: #333333; margin: 0 0 20px 0; font-size: 24px;'>🔒 Restablecer Contraseña</h2>
                                    
                                    <p style='color: #666666; margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;'>
                                        Hola <strong>$firstName</strong>,
                                    </p>
                                    
                                    <p style='color: #666666; margin: 0 0 30px 0; font-size: 16px; line-height: 1.6;'>
                                        Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. 
                                        Haz clic en el siguiente botón para crear una nueva contraseña:
                                    </p>
                                    
                                    <!-- Button -->
                                    <table width='100%' cellpadding='0' cellspacing='0' style='margin: 30px 0;'>
                                        <tr>
                                            <td align='center'>
                                                <a href='$resetLink' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: bold; font-size: 16px;'>
                                                    Restablecer Contraseña
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <p style='color: #666666; margin: 0 0 10px 0; font-size: 14px; line-height: 1.6;'>
                                        O copia y pega este enlace en tu navegador:
                                    </p>
                                    <p style='color: #0d6efd; margin: 0 0 30px 0; font-size: 13px; word-break: break-all; background-color: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #0d6efd;'>
                                        $resetLink
                                    </p>
                                    
                                    <div style='background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 30px; border-radius: 4px;'>
                                        <p style='color: #856404; margin: 0; font-size: 14px; line-height: 1.6;'>
                                            <strong>⏱️ Importante:</strong> Este enlace expirará en <strong>1 hora</strong> por seguridad.
                                        </p>
                                    </div>
                                    
                                    <div style='background-color: #e8f4fd; border-left: 4px solid #2196F3; padding: 15px; margin-bottom: 30px; border-radius: 4px;'>
                                        <p style='color: #1976D2; margin: 0; font-size: 14px; line-height: 1.6;'>
                                            <strong>🔐 Nota de Seguridad:</strong> Si no solicitaste este cambio de contraseña, 
                                            puedes ignorar este correo de forma segura. Tu contraseña actual permanecerá sin cambios.
                                        </p>
                                    </div>
                                    
                                    <p style='color: #666666; margin: 0; font-size: 16px; line-height: 1.6;'>
                                        Saludos cordiales,<br>
                                        <strong>Equipo de Rancho Paraíso Real</strong>
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;'>
                                    <p style='color: #999999; margin: 0 0 10px 0; font-size: 14px;'>
                                        Rancho Paraíso Real
                                    </p>
                                    <p style='color: #999999; margin: 0 0 10px 0; font-size: 14px;'>
                                        Contacto: ressetpassword@ranchoparaisoreal.com
                                    </p>
                                    <p style='color: #999999; margin: 0; font-size: 12px;'>
                                        Este es un correo automático, por favor no responda a este mensaje.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Obtener versión en texto plano del correo de recuperación
     */
    private function getPasswordResetPlainText($firstName, $resetLink) {
        $text = "RANCHO PARAÍSO REAL\n";
        $text .= "Recuperación de Contraseña\n\n";
        $text .= "Restablecer Contraseña\n\n";
        $text .= "Hola $firstName,\n\n";
        $text .= "Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.\n\n";
        $text .= "Haz clic en el siguiente enlace para crear una nueva contraseña:\n\n";
        $text .= "$resetLink\n\n";
        $text .= "⏱️ IMPORTANTE: Este enlace expirará en 1 hora por seguridad.\n\n";
        $text .= "🔐 NOTA DE SEGURIDAD: Si no solicitaste este cambio de contraseña, puedes ignorar este correo de forma segura.\n";
        $text .= "Tu contraseña actual permanecerá sin cambios.\n\n";
        $text .= "Saludos cordiales,\n";
        $text .= "Equipo de Rancho Paraíso Real\n\n";
        $text .= "Contacto: ressetpassword@ranchoparaisoreal.com\n";
        
        return $text;
    }
}
