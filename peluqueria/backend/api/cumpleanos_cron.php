<?php
/**
 * cumpleanos_cron.php
 *
 * Uso como cron (CLI):
 *   php cumpleanos_cron.php
 *   Ejemplo cPanel: 0 10 * * * php /home/usuario/public_html/peluqueria/backend/api/cumpleanos_cron.php
 *
 * Uso como endpoint HTTP (panel admin):
 *   POST { "mode": "todos" }                     → envía a todos los pendientes de hoy
 *   POST { "mode": "seleccionados", "ids": [1,2] } → envía a IDs concretos
 */

$es_cli = (php_sapi_name() === 'cli');

require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if ($es_cli) {
    date_default_timezone_set('Europe/Madrid');
    // En producción el cron corre en el mismo servidor → localhost
    try {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=11281494_peluqueria;charset=utf8mb4',
            'admin_rgutierrez',
            'K4w=Xb6<5|0a7D>/',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (PDOException $e) {
        echo "Error DB: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    require_once __DIR__ . '/../../../private/config/db.php';
    require_once 'admin_check.php';
}

// ─── Función de envío ────────────────────────────────────────────────────────
function enviar_email_cumpleanos(string $nombre, string $email): bool
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
        $mail->Subject = "¡Feliz cumpleaños, $nombreHtml!";
        $mail->Body = "
        <div style='font-family:sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
          <div style='background:#1a1a1a;color:#fff;padding:30px;text-align:center;'>
            <h1 style='margin:0;font-size:24px;letter-spacing:1px;'>Essencia Barber Study</h1>
          </div>
          <div style='padding:36px 30px;line-height:1.7;color:#333;'>
            <p style='font-size:20px;margin:0 0 16px;'>Hola <strong>$nombreHtml</strong>,</p>
            <p style='margin:0 0 16px;'>Todo el equipo de <strong>Essencia Barber Study</strong> te desea un <strong>feliz cumpleaños</strong>. Esperamos que sea un día increíble rodeado de los tuyos.</p>
            <div style='background:#faf6f0;border-radius:8px;padding:22px;margin:26px 0;border-left:4px solid #bc9667;text-align:center;'>
              <p style='margin:0;font-weight:bold;color:#1a1a1a;font-size:16px;'>Celébralo con nosotros</p>
              <p style='margin:10px 0 0;color:#666;'>Reserva tu cita y disfruta de un trato especial en tu día.</p>
            </div>
            <div style='text-align:center;margin:30px 0;'>
              <a href='https://rgutierrezhairstudio.com' style='background:#bc9667;color:#fff;padding:14px 28px;text-decoration:none;border-radius:6px;font-weight:bold;display:inline-block;letter-spacing:.5px;'>Reservar cita</a>
            </div>
            <p style='color:#999;font-size:13px;text-align:center;margin:0;'>Con cariño, el equipo de Essencia Barber Study</p>
          </div>
          <div style='background:#f8f8f8;padding:14px;text-align:center;font-size:12px;color:#aaa;border-top:1px solid #eee;'>
            Essencia Barber Study &mdash;
            <a href='https://rgutierrezhairstudio.com' style='color:#bc9667;text-decoration:none;'>rgutierrezhairstudio.com</a>
          </div>
        </div>";
        $mail->AltBody = "Hola $nombre, feliz cumpleaños desde Essencia Barber Study. Reserva en https://rgutierrezhairstudio.com";

        return $mail->send();
    } catch (PHPMailerException $e) {
        error_log("Cumpleaños PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}

// ─── Lógica principal ────────────────────────────────────────────────────────
$anio    = (int) date('Y');
$mes_hoy = (int) date('n');
$dia_hoy = (int) date('j');

$mode          = 'todos';
$ids_forzados  = [];

if (!$es_cli) {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $mode = $body['mode'] ?? 'todos';
    if ($mode === 'seleccionados') {
        $ids_forzados = array_map('intval', $body['ids'] ?? []);
    }
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
        // Todos los pendientes de hoy
        $stmt = $pdo->prepare("
            SELECT id, nombre, email FROM usuarios
            WHERE MONTH(fecha_nacimiento) = ? AND DAY(fecha_nacimiento) = ?
              AND (anio_email_cumpleanos IS NULL OR anio_email_cumpleanos != ?)
              AND activo = 1 AND rol = 'usuario'
              AND email IS NOT NULL AND email != ''
        ");
        $stmt->execute([$mes_hoy, $dia_hoy, $anio]);
    }

    $usuarios = $stmt->fetchAll();
    $enviados = 0;
    $errores  = 0;
    $detalle  = [];

    foreach ($usuarios as $u) {
        $ok = enviar_email_cumpleanos($u['nombre'], $u['email']);
        if ($ok) {
            $pdo->prepare("UPDATE usuarios SET anio_email_cumpleanos = ? WHERE id = ?")
                ->execute([$anio, $u['id']]);
            $enviados++;
        } else {
            $errores++;
        }
        $detalle[] = ['id' => $u['id'], 'nombre' => $u['nombre'], 'ok' => $ok];

        if ($es_cli) {
            echo ($ok ? '[OK]   ' : '[ERROR]') . " {$u['nombre']} <{$u['email']}>\n";
        }
    }

    if ($es_cli) {
        echo "Completado: $enviados enviados, $errores errores.\n";
        exit(0);
    }

    ob_clean();
    echo json_encode(['success' => true, 'enviados' => $enviados, 'errores' => $errores, 'detalle' => $detalle]);
} catch (Exception $e) {
    if ($es_cli) {
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
