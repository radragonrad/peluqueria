<?php
header('Content-Type: application/json; charset=utf-8');

// Incluir la función de envío (ajusta la ruta si es necesario)
require_once __DIR__ . '/../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../addons/php/PHPMailer-master/src/SMTP.php';

function enviar_notificacion($desde, $mensaje, $asunto, $para = array(), $cc_arr = array()) {
    $correoDesde = 'peluqueria8mb4_general_ci';

    if (count($para) == 0) {
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.rgutierrez.es';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'peluqueria8mb4_general_ci';
        $mail->Password   = '';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom($correoDesde, $desde);

        foreach ($para as $email) {
            $mail->addAddress($email);
        }

        foreach ($cc_arr as $cc) {
            $mail->addCC($cc);
        }

        $mail->isHTML(false); // Usamos texto plano para evitar problemas
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;
        $mail->AltBody = '';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $e->getMessage());
        return false;
    }
}

// Recoger datos del formulario
$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$email = $_POST['email'] ?? '';
$servicio = $_POST['servicio'] ?? '';
$mensaje = $_POST['mensaje'] ?? '';

// Validación básica
if (empty($nombre) || empty($telefono) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Por favor, rellena todos los campos correctamente.']);
    exit;
}

// Construir el cuerpo del mensaje
$body = "Nuevo mensaje de contacto:\n\n";
$body .= "Nombre: $nombre\n";
$body .= "Teléfono: $telefono\n";
$body .= "Email: $email\n";
$body .= "Servicio: $servicio\n";
$body .= "Mensaje:\n$mensaje";

$asunto = "Nuevo mensaje desde tu web - Contacto";
$destinatarios = ['aqbarberbeauty@gmail.com'];

// Enviar correo
if (enviar_notificacion('Contacto Web', $body, $asunto, $destinatarios)) {
    echo json_encode(['status' => 'success', 'message' => '¡Gracias! Tu mensaje ha sido enviado. Te responderemos pronto.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Lo sentimos, hubo un error al enviar el mensaje.']);
}