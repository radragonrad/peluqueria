<?php
// 1. Cargamos la configuración central y PHPMailer
require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../private/includes/functions.php';
require_once 'admin_check.php'; // Asegura que solo el admin use este script

require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- FUNCIÓN DE ENVÍO DE CORREO (Adaptada de tu código) ---
function enviar_notificacion_acceso($desde, $mensaje, $asunto, $para = array()) {
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

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('no-reply@rgutierrezhairstudio.com', $desde);
        
        foreach ($para as $email) { 
            $email = trim($email);
            if (!empty($email)) { $mail->addAddress($email); }
        }

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje; // No usamos nl2br porque ya enviamos un HTML completo
        $mail->AltBody = strip_tags($mensaje);

        return $mail->send();
    } catch (Exception $e) {
        error_log("Error PHPMailer: " . $mail->ErrorInfo); 
        return false;
    }
}

// --- LÓGICA DE REGISTRO ---
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'Nombre son obligatorios']);
    exit;
}

try {
    // 1. Generación de Usuario (slug)
    $nombre_limpio = strtolower(trim($data['nombre']));
    $usuario_base = str_replace(' ', '.', $nombre_limpio); 
    $usuario_final = $usuario_base;

    $nombre    = trim($data['nombre']);
    $telefono  = !empty($data['telefono']) ? trim($data['telefono']) : '999999999';
    $correo    = !empty($data['email']) ? trim($data['email']) : 'sinemail@'.$usuario_final;
    $fecha_nac = !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : '1900-01-01';


    
    $checkUser = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
    $checkUser->execute([$usuario_final]);
    if ($checkUser->fetchColumn() > 0) {
        $usuario_final = $usuario_base . rand(10, 99);
    }

    // 2. Generación de Contraseña Dinámica
    $password_plano = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
    $passHash = password_hash($password_plano, PASSWORD_ARGON2ID);

    // 3. Inserción en DB
    $sql = "INSERT INTO usuarios (usuario, nombre, email, password_hash, ip_address, activo, rol, telefono, fecha_nacimiento, fecha_activacion, generado) 
            VALUES (?, ?, ?, ?, ?, 1, 'usuario', ?, ?, NOW(), 'local')";
            
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        $usuario_final, 
        $data['nombre'], 
        $correo, 
        $passHash, 
        $_SERVER['REMOTE_ADDR'], 
        $telefono, 
        $fecha_nac
    ]);

    if ($success) {
        $usuario_id = $pdo->lastInsertId();
        $email_enviado = false;

        // 4. Preparación y Envío del Correo Estilo Premium
        if (!empty($correo) && $correo !== 'sinemail@sindominio.es') {
            $nombre_cliente = htmlspecialchars($data['nombre']);
            
            $mensajeHtml = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                <div style='background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px; letter-spacing: 1px;'>R. Gutiérrez Hair Studio</h1>
                </div>
                <div style='padding: 30px; line-height: 1.6; color: #333;'>
                    <p style='font-size: 18px;'>Hola <strong>$nombre_cliente</strong>,</p>
                    <p>Es un placer darte la bienvenida. Hemos creado tu cuenta desde nuestro estudio para que puedas gestionar tus citas y disfrutar de una experiencia personalizada.</p>
                    
                    <div style='background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin: 25px 0; border-left: 4px solid #bc9667;'>
                        <p style='margin: 0 0 10px 0; font-weight: bold; color: #1a1a1a;'>Tus credenciales de acceso:</p>
                        <p style='margin: 5px 0;'><strong>Usuario:</strong> <span style='color: #bc9667;'>$correo</span></p>
                        <p style='margin: 5px 0;'><strong>Contraseña temporal:</strong> <span style='color: #bc9667;'>$password_plano</span></p>
                    </div>

                    <p>Ya puedes acceder a tu área privada para ver tus próximas reservas:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='https://rgutierrezhairstudio.com/login' style='background-color: #bc9667; color: #ffffff; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Acceder a mi Cuenta</a>
                    </div>
                    
                    <p style='font-size: 13px; color: #666; font-style: italic;'>* Te recomendamos cambiar tu contraseña al acceder por primera vez.</p>
                </div>
                <div style='background-color: #f9f9f9; color: #999; padding: 20px; text-align: center; font-size: 12px; border-top: 1px solid #eee;'>
                    <p style='margin: 5px 0;'>&copy; " . date('Y') . " R. Gutiérrez Hair Studio. C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla</p>
                </div>
            </div>
            ";

            $email_enviado = enviar_notificacion_acceso(
                'R. Gutiérrez Hair Studio', 
                $mensajeHtml, 
                'Tus datos de acceso - R. Gutiérrez Hair Studio', 
                [$correo]
            );
        }

        $id_admin_ejecutor = $_SESSION['user_id'] ?? 0;
        $datos_log = [
            'cliente_creado_id' => $usuario_id,
            'nombre_cliente'    => $nombre,
            'telefono'          => $telefono,
            'correo_usado'      => $correo,
            'fecha_nac'         => $fecha_nac
        ];

        registrarLog(
            $pdo, 
            $id_admin_ejecutor, 
            'ALTA_CLIENTE_ADMIN', // Tipo de registro
            $datos_log,           // Datos registrados
            $email_enviado        // Si se envió el email
        );

        echo json_encode([
            'success' => true, 
            'id' => $usuario_id,
            'nombre_cliente' => $nombre,
            'email_enviado' => $email_enviado,
            'message' => 'Cliente registrado correctamente'
        ]);
    }

} catch (PDOException $e) {
   
    $id_admin_ejecutor = $_SESSION['user_id'] ?? 0;
    registrarLog($pdo, $id_admin_ejecutor, 'ALTA_CLIENTE_ERROR', [
        'nombre_intentado' => $data['nombre'] ?? 'Desconocido',
        'correo_intentado' => $data['email'] ?? 'Desconocido',
        'error_mensaje'    => $e->getMessage()
    ], 0);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}