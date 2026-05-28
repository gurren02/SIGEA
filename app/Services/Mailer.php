<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once BASE_PATH . '/PHPMailer/src/Exception.php';
require_once BASE_PATH . '/PHPMailer/src/PHPMailer.php';
require_once BASE_PATH . '/PHPMailer/src/SMTP.php';

class Mailer
{
    public static function sendVerificationCode(string $toEmail, string $toName, string $code): bool
    {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jesusmutul15@gmail.com';
            $mail->Password   = 'wypqbrdraohjqsat';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Recipients
            $mail->setFrom('jesusmutul15@gmail.com', 'SIGEA');
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Codigo de verificacion - SIGEA';

            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e4e4e7; border-radius: 8px;'>
                    <h2 style='color: #1a3560; text-align: center;'>Recuperacion de Contrasena - SIGEA</h2>
                    <p>Hola, <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en SIGEA.</p>
                    <p>Tu código de verificación de un solo uso es:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='background-color: #f1f5f9; color: #1a3560; font-size: 32px; font-weight: 900; letter-spacing: 6px; padding: 12px 30px; border-radius: 8px; border: 1.5px solid #cbd5e1; display: inline-block;'>" . htmlspecialchars($code) . "</span>
                    </div>
                    <p style='color: #64748b; font-size: 13px; text-align: center;'>Este código expirará en 15 minutos.</p>
                    <hr style='border: 0; border-top: 1px solid #e4e4e7; margin: 20px 0;'>
                    <p style='color: #94a3b8; font-size: 12px;'>Si no solicitaste este cambio, puedes ignorar este correo de forma segura.</p>
                </div>
            ";
            $mail->AltBody = "Hola {$toName},\n\nHemos recibido una solicitud para restablecer la contraseña de tu cuenta en SIGEA.\n\nTu código de verificación es: {$code}\n\nEste código expirará en 15 minutos.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
