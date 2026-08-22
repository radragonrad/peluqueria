<?php
/**
 * enviar_resena.php
 *
 * Endpoint del panel admin para pedir reseñas por email.
 *   POST { "mode": "todos" }                      → envía a todos los pendientes
 *   POST { "mode": "seleccionados", "ids": [1,2] } → envía a IDs concretos (permite reenviar)
 */

require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

const RESENA_URL = 'https://g.page/r/CaOgDoX1ryEDEBM/review';

function enviar_email_resena(string $nombre, string $email): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.servidor-correo.net';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@rgutierrezhairstudio.com';
        $mail->Password   = '3K796Xy*8#5_s34@';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->Hostname   = 'rgutierrezhairstudio.com';
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
        ];

        $mail->setFrom('no-reply@rgutierrezhairstudio.com', 'Essencia Barber Study');
        $mail->addAddress(trim($email));
        $mail->isHTML(true);

        $nombreHtml = htmlspecialchars($nombre);
        $mail->Subject = "¿Qué tal tu visita, $nombreHtml? Cuéntanoslo";
        $mail->Body = "
        <div style='font-family:sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
          <div style='background:#1a1a1a;color:#fff;padding:30px;text-align:center;'>
            <h1 style='margin:0;font-size:24px;letter-spacing:1px;'>Essencia Barber Study</h1>
          </div>
          <div style='padding:36px 30px;line-height:1.7;color:#333;'>
            <p style='font-size:20px;margin:0 0 16px;'>Hola <strong>$nombreHtml</strong>,</p>
            <p style='margin:0 0 16px;'>Gracias por confiar en <strong>Essencia Barber Study</strong>. Esperamos que tu última visita haya sido de tu agrado.</p>
            <p style='margin:0 0 16px;'>Nos ayudaría muchísimo que compartieras tu experiencia dejándonos una reseña en Google. Solo te llevará un minuto y es la mejor forma de apoyarnos.</p>
            <div style='background:#faf6f0;border-radius:8px;padding:22px;margin:26px 0;border-left:4px solid #bc9667;text-align:center;'>
              <p style='margin:0;font-weight:bold;color:#1a1a1a;font-size:16px;'>¿Nos dejas tu opinión?</p>
              <p style='margin:10px 0 0;color:#666;'>Tu reseña ayuda a otros clientes a confiar en nosotros.</p>
            </div>
            <div style='text-align:center;margin:30px 0;'>
              <a href='" . RESENA_URL . "' style='background:#bc9667;color:#fff;padding:14px 28px;text-decoration:none;border-radius:6px;font-weight:bold;display:inline-block;letter-spacing:.5px;'>Dejar mi reseña</a>
            </div>
            <p style='color:#999;font-size:13px;text-align:center;margin:0;'>Con cariño, el equipo de Essencia Barber Study</p>
          </div>
          <div style='background:#f8f8f8;padding:14px;text-align:center;font-size:12px;color:#aaa;border-top:1px solid #eee;'>
            Essencia Barber Study &mdash;
            <a href='https://rgutierrezhairstudio.com' style='color:#bc9667;text-decoration:none;'>rgutierrezhairstudio.com</a>
          </div>
        </div>";
        $mail->AltBody = "Hola $nombre, gracias por confiar en Essencia Barber Study. Déjanos tu reseña en " . RESENA_URL;

        return $mail->send();
    } catch (PHPMailerException $e) {
        error_log("Reseñas PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}

$body         = json_decode(file_get_contents('php://input'), true) ?? [];
$mode         = $body['mode'] ?? 'todos';
$ids_forzados = [];
if ($mode === 'seleccionados') {
    $ids_forzados = array_map('intval', $body['ids'] ?? []);
}

try {
    if ($mode === 'seleccionados' && !empty($ids_forzados)) {
        $placeholders = implode(',', array_fill(0, count($ids_forzados), '?'));
        $stmt = $pdo->prepare("
            SELECT id, nombre, email FROM usuarios
            WHERE id IN ($placeholders) AND activo = 1
              AND email IS NOT NULL AND email != ''
        ");
        $stmt->execute($ids_forzados);
    } else {
        $stmt = $pdo->query("
            SELECT u.id, u.nombre, u.email
            FROM usuarios u
            JOIN reservas r ON r.user_id = u.id AND r.estado = 'COMPLETADA'
            WHERE u.activo = 1 AND u.rol = 'usuario'
              AND u.resena_email_enviado = 0
              AND u.email IS NOT NULL AND u.email != ''
            GROUP BY u.id
        ");
    }

    $usuarios = $stmt->fetchAll();
    $enviados = 0;
    $errores  = 0;
    $detalle  = [];

    foreach ($usuarios as $u) {
        $ok = enviar_email_resena($u['nombre'], $u['email']);
        if ($ok) {
            $pdo->prepare("UPDATE usuarios SET resena_email_enviado = 1 WHERE id = ?")
                ->execute([$u['id']]);
            $enviados++;
        } else {
            $errores++;
        }
        $detalle[] = ['id' => $u['id'], 'nombre' => $u['nombre'], 'ok' => $ok];
    }

    ob_clean();
    echo json_encode(['success' => true, 'enviados' => $enviados, 'errores' => $errores, 'detalle' => $detalle]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
