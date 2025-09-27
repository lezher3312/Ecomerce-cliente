<?php
namespace Classes;

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    public $email;
    public $nombre;
    public $token;

    public function __construct($email, $nombre, $token) {
        $this->email  = $email;
        $this->nombre = $nombre;
        $this->token  = $token;
    }

    private function configurarSMTP(PHPMailer $mail) {
        $mail->isSMTP();
        $mail->Host       = $_ENV['EMAIL_HOST'] ?? "smtp.hostinger.com";
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'] ?? "admin@gtis.tech";
        $mail->Password   = $_ENV['EMAIL_PASS'] ?? "Email2025%";
        $mail->Port       = $_ENV['EMAIL_PORT'] ?? 465;
        $mail->SMTPSecure = ($mail->Port == 465) 
            ? PHPMailer::ENCRYPTION_SMTPS 
            : PHPMailer::ENCRYPTION_STARTTLS;

        // Always match SMTP username with From
        $mail->setFrom($mail->Username, 'Global-Import');
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
    }

    public function enviarConfirmacion() {
        $mail = new PHPMailer(true);
        try {
            $this->configurarSMTP($mail);

            $mail->addAddress($this->email, $this->nombre);
            $mail->Subject = 'Confirma tu Cuenta';

            $contenido  = '<html>';
            $contenido .= "<p><strong>Hola {$this->nombre}</strong>, has registrado correctamente tu cuenta en Global-Import; pero es necesario confirmarla.</p>";
           //Modificar el puerto en donde corren el proyecto :)
            $contenido .= "<p>Presiona aquí: <a href='" . ($_ENV['HOST'] ?? "https://gtis.tech/Global-client") . "/confirmar-cuenta?token={$this->token}'>Confirmar Cuenta</a></p>";
            $contenido .= "<p>Si tú no creaste esta cuenta, puedes ignorar este mensaje.</p>";
            $contenido .= '</html>';

            $mail->Body = $contenido;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
            return false;
        }
    }

    public function enviarInstrucciones() {
        $mail = new PHPMailer(true);
        try {
            $this->configurarSMTP($mail);

            $mail->addAddress($this->email, $this->nombre);
            $mail->Subject = 'Reestablece tu password';

            $contenido  = '<html>';
            $contenido .= "<p><strong>Hola {$this->nombre}</strong>, has solicitado reestablecer tu password. Sigue el siguiente enlace para hacerlo:</p>";
            $contenido .= "<p><a href='" . ($_ENV['HOST'] ?? "https://gtis.tech/Global-client") . "/reestablecer?token={$this->token}'>Reestablecer Password</a></p>";
            $contenido .= "<p>Si tú no solicitaste este cambio, puedes ignorar este mensaje.</p>";
            $contenido .= '</html>';

            $mail->Body = $contenido;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar instrucciones: {$mail->ErrorInfo}");
            return false;
        }
    }
}
