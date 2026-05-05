<?php
header('Content-Type: application/json');

// 1. Configuración de rutas y carga de librerías
// Usamos la misma estructura que tu ejemplo para localizar el autoload y PHPMailer
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. Capturar los datos enviados desde Vue (axios/fetch)
$data = json_decode(file_get_contents("php://input"), true);

/**
 * FUNCIÓN PARA ENVIAR CORREO DE SOPORTE POR SMTP
 */
function enviarIncidenciaSoporte($datos) {
    $mail = new PHPMailer(true);
    try {
        // Configuración del servidor (según tus datos de ejemplo)
        $mail->isSMTP();
        $mail->Host       = 'smtp.servidor-correo.net'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@rgutierrezhairstudio.com'; 
        $mail->Password   = '3K796Xy*8#5_s34@'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Opciones SSL para evitar bloqueos en servidores locales/Laragon
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Remitente y Destinatario
        $mail->setFrom('no-reply@rgutierrezhairstudio.com', 'Sistema de Incidencias Essencia');
        $mail->addAddress('soporte@rgutierrezhairstudio.com', 'Soporte Técnico');
        
        // El cliente que reporta se pone en "Responder a" para facilitar el contacto
        $mail->addReplyTo($datos['email'], $datos['nombre']);

        // Contenido del correo (Estilo similar al de confirmación de cita)
        $mensaje_html = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #d93025; color: #ffffff; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; font-size: 22px;'>Nueva Incidencia Reportada</h1>
            </div>
            <div style='padding: 30px; line-height: 1.6; color: #333;'>
                <p>Se ha recibido un nuevo reporte de error desde el formulario de acceso:</p>
                <div style='background-color: #f8f9fa; border-left: 4px solid #d93025; padding: 15px; margin: 20px 0;'>
                    <p><strong>Nombre:</strong> {$datos['nombre']}</p>
                    <p><strong>Email de contacto:</strong> {$datos['email']}</p>
                    <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
                </div>
                <p><strong>Observaciones:</strong></p>
                <p style='background: #eee; padding: 10px; border-radius: 5px;'>{$datos['observaciones']}</p>
            </div>
            <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                Este es un mensaje automático generado por el sistema de soporte de Essencia.
            </div>
        </div>";

        $mail->isHTML(true);
        $mail->Subject = 'AVISO: Incidencia técnica de ' . $datos['nombre'];
        $mail->Body    = $mensaje_html;
        $mail->AltBody = "Nueva incidencia de {$datos['nombre']} ({$datos['email']}): {$datos['observaciones']}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error PHPMailer Incidencias: " . $mail->ErrorInfo);
        return false;
    }
}

// --- PROCESO DE ENVÍO ---

// Validamos que lleguen los campos necesarios que definimos en ReportarIncidencia.vue
if (isset($data['nombre'], $data['email'], $data['observaciones'])) {
    
    $exito = enviarIncidenciaSoporte($data);

    if ($exito) {
        echo json_encode([
            "success" => true, 
            "message" => "El reporte ha sido enviado con éxito a soporte."
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "El servidor no pudo procesar el envío del correo."
        ]);
    }

} else {
    echo json_encode([
        "success" => false, 
        "message" => "Datos de formulario incompletos."
    ]);
}
?>