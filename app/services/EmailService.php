<?php
/**
 * Email Service
 * Servicio para envío de correos electrónicos usando PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailService {
    
    private $mailer;
    private $config;
    
    public function __construct($hotelId = null) {
        require_once CONFIG_PATH . '/email.php';
        
        $this->config = getEmailSettings($hotelId);
        $this->mailer = new PHPMailer(true);
        
        // Configurar SMTP
        $this->setupSMTP();
    }
    
    /**
     * Configurar servidor SMTP
     */
    private function setupSMTP() {
        try {
            // Configuración del servidor usando valores del config
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            
            // Configuración adicional
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';
            
            // Para debugging (desactivar en producción)
            // $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            // $this->mailer->Debugoutput = 'html';
            
            // Remitente por defecto
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            
        } catch (Exception $e) {
            error_log("Error al configurar SMTP: " . $e->getMessage());
            throw new Exception("Error al configurar el servicio de correo");
        }
    }
    
    /**
     * Enviar correo de confirmación de reservación
     * 
     * @param array $reservationData Datos de la reservación
     * @return bool
     */
    public function sendReservationConfirmation($reservationData) {
        if (!$this->config['enabled']) {
            error_log("Email no enviado - SMTP está deshabilitado");
            return false;
        }
        
        try {
            // Limpiar destinatarios previos
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Destinatario
            $this->mailer->addAddress($reservationData['guest_email'], $reservationData['guest_name']);
            
            // Asunto
            $this->mailer->Subject = 'Confirmación de Reservación - Rancho Paraíso Real';
            
            // Contenido HTML
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->getReservationEmailTemplate($reservationData);
            
            // Contenido alternativo en texto plano
            $this->mailer->AltBody = $this->getReservationEmailPlainText($reservationData);
            
            // Enviar correo
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Correo de confirmación enviado exitosamente a: " . $reservationData['guest_email']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error al enviar correo de confirmación: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Obtener plantilla HTML del correo de reservación
     */
    private function getReservationEmailTemplate($data) {
        $type = $data['type'];
        $guestName = htmlspecialchars($data['guest_name']);
        $reservationId = htmlspecialchars($data['reservation_id'] ?? 'N/A');
        $confirmationCode = htmlspecialchars($data['confirmation_code'] ?? 'N/A');
        $includePin = isset($data['include_pin']) && $data['include_pin'] === true;
        
        // Información específica del tipo de reservación
        $details = '';
        if ($type === 'room') {
            $roomNumber = htmlspecialchars($data['room_number'] ?? 'N/A');
            $checkIn = date('d/m/Y', strtotime($data['check_in']));
            $checkOut = date('d/m/Y', strtotime($data['check_out']));
            $price = number_format($data['total_price'], 2);
            
            $details = "
                <tr>
                    <td style='padding: 5px 0;'><strong>Habitación:</strong></td>
                    <td style='padding: 5px 0;'>$roomNumber</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Check-in:</strong></td>
                    <td style='padding: 5px 0;'>$checkIn</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Check-out:</strong></td>
                    <td style='padding: 5px 0;'>$checkOut</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Precio Total:</strong></td>
                    <td style='padding: 5px 0;'>$" . $price . " MXN</td>
                </tr>
            ";
        } elseif ($type === 'table') {
            $tableNumber = htmlspecialchars($data['table_number'] ?? 'N/A');
            $reservationDate = date('d/m/Y', strtotime($data['reservation_date']));
            $reservationTime = date('H:i', strtotime($data['reservation_time']));
            $partySize = htmlspecialchars($data['party_size']);
            
            $details = "
                <tr>
                    <td style='padding: 5px 0;'><strong>Mesa:</strong></td>
                    <td style='padding: 5px 0;'>$tableNumber</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Fecha:</strong></td>
                    <td style='padding: 5px 0;'>$reservationDate</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Hora:</strong></td>
                    <td style='padding: 5px 0;'>$reservationTime</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Personas:</strong></td>
                    <td style='padding: 5px 0;'>$partySize</td>
                </tr>
            ";
        } elseif ($type === 'amenity') {
            $amenityName = htmlspecialchars($data['amenity_name'] ?? 'N/A');
            $reservationDate = date('d/m/Y', strtotime($data['reservation_date']));
            $reservationTime = date('H:i', strtotime($data['reservation_time']));
            $partySize = htmlspecialchars($data['party_size']);
            
            $details = "
                <tr>
                    <td style='padding: 5px 0;'><strong>Amenidad:</strong></td>
                    <td style='padding: 5px 0;'>$amenityName</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Fecha:</strong></td>
                    <td style='padding: 5px 0;'>$reservationDate</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Hora:</strong></td>
                    <td style='padding: 5px 0;'>$reservationTime</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0;'><strong>Personas:</strong></td>
                    <td style='padding: 5px 0;'>$partySize</td>
                </tr>
            ";
        }
        
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmación de Reservación</title>
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
                                    <p style='color: #ffffff; margin: 10px 0 0 0; font-size: 16px;'>" . ($includePin ? 'Reservación Confirmada' : 'Reservación Recibida') . "</p>
                                </td>
                            </tr>
                            
                            <!-- Body -->
                            <tr>
                                <td style='padding: 40px 30px;'>
                                    <h2 style='color: #333333; margin: 0 0 20px 0; font-size: 24px;'>¡Reservación " . ($includePin ? 'Confirmada' : 'Recibida') . "!</h2>
                                    <p style='color: #666666; margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;'>
                                        Estimado/a <strong>$guestName</strong>,
                                    </p>
                                    <p style='color: #666666; margin: 0 0 30px 0; font-size: 16px; line-height: 1.6;'>
                                        " . ($includePin 
                                            ? 'Su reservación ha sido <strong>CONFIRMADA</strong> exitosamente. A continuación encontrará los detalles y su PIN de confirmación:' 
                                            : 'Hemos recibido su solicitud de reservación. Nuestro equipo la revisará y le enviaremos un correo de confirmación con su PIN una vez aprobada.') . "
                                    </p>                                    <!-- Reservation Details -->
                                    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 20px;'>
                                        <tr>
                                            <td style='padding: 5px 0;'><strong>ID de Reservación:</strong></td>
                                            <td style='padding: 5px 0;'>$reservationId</td>
                                        </tr>
                                        $details
                                    </table>
                                    " . ($includePin ? "
                                    <!-- Confirmation PIN -->
                                    <div style='background-color: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center;'>
                                        <p style='color: #856404; margin: 0 0 10px 0; font-size: 14px; font-weight: bold;'>
                                            📌 PIN DE CONFIRMACIÓN
                                        </p>
                                        <p style='color: #212529; margin: 0; font-size: 32px; font-weight: bold; letter-spacing: 3px; font-family: monospace;'>
                                            $confirmationCode
                                        </p>
                                        <p style='color: #856404; margin: 10px 0 0 0; font-size: 12px;'>
                                            Presente este PIN al momento de su llegada
                                        </p>
                                    </div>
                                    " : "") . "
                                    
                                    <div style='background-color: #e8f4fd; border-left: 4px solid #2196F3; padding: 15px; margin-bottom: 30px; border-radius: 4px;'>
                                        <p style='color: #1976D2; margin: 0; font-size: 14px; line-height: 1.6;'>
                                            <strong>Nota Importante:</strong> " . ($includePin 
                                                ? 'Por favor, llegue 15 minutos antes de su hora de reservación y presente su PIN de confirmación.' 
                                                : 'Su reservación está pendiente de confirmación. Le enviaremos un correo con su PIN cuando sea aprobada.') . "
                                            Si necesita cancelar o modificar su reservación, contáctenos lo antes posible.
                                        </p>
                                    </div>
                                    
                                    <p style='color: #666666; margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;'>
                                        " . ($includePin 
                                            ? 'Esperamos con ansias recibirle en Rancho Paraíso Real.' 
                                            : 'Nos pondremos en contacto con usted pronto.') . "
                                    </p>
                                    
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
                                        Contacto: reservaciones@ranchoparaisoreal.com
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
     * Obtener versión en texto plano del correo de reservación
     */
    private function getReservationEmailPlainText($data) {
        $type = $data['type'];
        $guestName = $data['guest_name'];
        $reservationId = $data['reservation_id'] ?? 'N/A';
        $confirmationCode = $data['confirmation_code'] ?? 'N/A';
        $includePin = isset($data['include_pin']) && $data['include_pin'] === true;
        
        $text = "RANCHO PARAÍSO REAL\n";
        $text .= ($includePin ? "Reservación Confirmada\n\n" : "Reservación Recibida\n\n");
        $text .= ($includePin ? "¡Reservación Confirmada!\n\n" : "¡Reservación Recibida!\n\n");
        $text .= "Estimado/a $guestName,\n\n";
        
        if ($includePin) {
            $text .= "Su reservación ha sido CONFIRMADA exitosamente. A continuación los detalles:\n\n";
        } else {
            $text .= "Hemos recibido su solicitud de reservación. Nuestro equipo la revisará y le enviaremos un correo de confirmación con su PIN una vez aprobada.\n\n";
        }
        
        $text .= "ID de Reservación: $reservationId\n";
        
        if ($includePin) {
            $text .= "PIN de Confirmación: $confirmationCode\n";
        }
        
        if ($type === 'room') {
            $text .= "Habitación: " . ($data['room_number'] ?? 'N/A') . "\n";
            $text .= "Check-in: " . date('d/m/Y', strtotime($data['check_in'])) . "\n";
            $text .= "Check-out: " . date('d/m/Y', strtotime($data['check_out'])) . "\n";
            $text .= "Precio Total: $" . number_format($data['total_price'], 2) . " MXN\n";
        } elseif ($type === 'table') {
            $text .= "Mesa: " . ($data['table_number'] ?? 'N/A') . "\n";
            $text .= "Fecha: " . date('d/m/Y', strtotime($data['reservation_date'])) . "\n";
            $text .= "Hora: " . date('H:i', strtotime($data['reservation_time'])) . "\n";
            $text .= "Personas: " . $data['party_size'] . "\n";
        } elseif ($type === 'amenity') {
            $text .= "Amenidad: " . ($data['amenity_name'] ?? 'N/A') . "\n";
            $text .= "Fecha: " . date('d/m/Y', strtotime($data['reservation_date'])) . "\n";
            $text .= "Hora: " . date('H:i', strtotime($data['reservation_time'])) . "\n";
            $text .= "Personas: " . $data['party_size'] . "\n";
        }
        
        if ($includePin) {
            $text .= "\nNota Importante: Por favor, llegue 15 minutos antes de su hora de reservación y presente su PIN de confirmación.\n";
            $text .= "Si necesita cancelar o modificar su reservación, contáctenos lo antes posible.\n\n";
            $text .= "Esperamos con ansias recibirle en Rancho Paraíso Real.\n\n";
        } else {
            $text .= "\nNota Importante: Su reservación está pendiente de confirmación. Le enviaremos un correo con su PIN cuando sea aprobada.\n";
            $text .= "Si necesita cancelar o modificar su reservación, contáctenos lo antes posible.\n\n";
            $text .= "Nos pondremos en contacto con usted pronto.\n\n";
        }
        
        $text .= "Saludos cordiales,\n";
        $text .= "Equipo de Rancho Paraíso Real\n\n";
        $text .= "Contacto: reservaciones@ranchoparaisoreal.com\n";
        
        return $text;
    }
    
    /**
     * Enviar correo genérico
     * 
     * @param string $to Email destinatario
     * @param string $subject Asunto
     * @param string $body Cuerpo del mensaje (HTML)
     * @param string $altBody Cuerpo alternativo (texto plano)
     * @return bool
     */
    public function sendEmail($to, $subject, $body, $altBody = '') {
        if (!$this->config['enabled']) {
            error_log("Email no enviado - SMTP está deshabilitado");
            return false;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
