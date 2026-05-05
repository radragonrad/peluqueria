<?php
// 1. Cargamos la configuración central (CORS, Conexión PDO, Headers)
require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../private/includes/functions.php';
// 2. Incluir PHPMailer
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- FUNCIONES DE APOYO ---

function enviar_notificacion_correo($desde, $mensaje, $asunto, $para = array()) {
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
            if (!empty($email)) {
                $mail->addAddress($email); 
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = nl2br($mensaje); 
        $mail->AltBody = strip_tags($mensaje);

        return $mail->send();
    } catch (Exception $e) {
        error_log("Error PHPMailer: " . $mail->ErrorInfo); 
        return false;
    }
}

function validatePassword($password) {
    if (strlen($password) < 8) return 'La contraseña debe tener al menos 8 caracteres.';
    // if (!preg_match('/[A-Z]/', $password)) return 'Debe contener una mayúscula.';
    // if (!preg_match('/[a-z]/', $password)) return 'Debe contener una minúscula.';
    // if (!preg_match('/[0-9]/', $password)) return 'Debe contener un número.';
    // if (!preg_match('/[^A-Za-z0-9]/', $password)) return 'Debe contener un carácter especial.';
    return null;
}

function generarTokenUnico($pdo) {
    do {
        $token = bin2hex(random_bytes(32));
        // Verificamos en token_activacion por defecto, 
        // pero para recuperar usaremos una lógica similar
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_activacion = ?");
        $stmt->execute([$token]);
    } while ($stmt->fetch());
    return $token;
}

// --- LÓGICA PRINCIPAL ---

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);   
    exit;
}

$action = $input['action'] ?? '';

// REGISTRO
if ($action === 'register') {
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }

    $validationError = validatePassword($password);
    if ($validationError) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $validationError]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, activo FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['activo']) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Este correo ya está registrado']);   
            exit;
        } else {
            $token = generarTokenUnico($pdo);
            $stmt = $pdo->prepare("UPDATE usuarios SET token_activacion = ? WHERE email = ?");
            $stmt->execute([$token, $email]);
        }
    } else {
        $username = str_replace(' ', '.', strtolower(explode('@', $email)[0]));
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $check->execute([$username]);
        if ($check->fetch()) { $username .= rand(10, 99); }

        $password_hash = password_hash($password, PASSWORD_ARGON2ID);
        $token = generarTokenUnico($pdo);

        $sql = "INSERT INTO usuarios (usuario, nombre, email, password_hash, ip_address, activo, rol, token_activacion, telefono, fecha_nacimiento, generado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        // Anterior con tocken & email
        // $stmt->execute([$username, $input['nombre'], $email, $password_hash, $_SERVER['REMOTE_ADDR'], 0, 'usuario', $token, $input['telefono'], $input['fecha_nacimiento'], 'web']);
        $stmt->execute([$username, $input['nombre'], $email, $password_hash, $_SERVER['REMOTE_ADDR'], 1, 'usuario', null, $input['telefono'], $input['fecha_nacimiento'], 'web']);
    }

    $is_local = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');
    $base_url = $is_local ? "http://localhost/ruben-peluqueria/peluqueria" : "https://rgutierrezhairstudio.com";
    $enlace = $base_url . "/activar.php?token=" . urlencode($token);
    $nombre_cliente = htmlspecialchars($input['nombre']);

    $mensaje = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;'>
            <h1 style='margin: 0; font-size: 24px;'>R. Gutiérrez Hair Studio</h1>
        </div>
        <div style='padding: 30px; line-height: 1.6; color: #333;'>
            <p style='font-size: 18px;'>Hola <strong>$nombre_cliente</strong>,</p>
            <p>Confirma tu cuenta para que podamos empezar a cuidar de tu estilo. Pulsa el siguiente botón:</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='$enlace' style='background-color: #bc9667; color: #ffffff; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Confirmar mi Cuenta</a>
            </div>
            <p style='font-size: 14px; color: #666;'>Si el botón no funciona, copia este enlace:<br>$enlace</p>
        </div>
    </div>";
    // Anterior con tocken & email
    // $email_ok = enviar_notificacion_correo('Activación de cuenta', $mensaje, 'Activa tu cuenta', [$email]);

    // Obtenemos el id del usuario recién creado (o el existente que reenvió token)
    $stmt_id = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_id->execute([$email]);
    $nuevo_id = $stmt_id->fetchColumn();
    
    // Anterior con tocken & email
    // registrarLog($pdo, $nuevo_id, 'REGISTRO', [
    //     'email'          => $email,
    //     'nombre'         => $input['nombre'],
    //     'telefono'       => $input['telefono'],
    //     'email_enviado'  => $email_ok ? 'sí' : 'no'
    // ], $email_ok ? 1 : 0);

    // if ($email_ok) {
    //     echo json_encode(['success' => true, 'message' => 'Revisa tu correo para activar tu cuenta.']);
    // } else {
    //     http_response_code(500);
    //     echo json_encode(['success' => false, 'message' => 'Error al enviar el correo.']);
    // }

     registrarLog($pdo, $nuevo_id, 'REGISTRO', [
        'email'          => $email,
        'nombre'         => $input['nombre'],
        'telefono'       => $input['telefono'],
        'email_enviado'  => 'no'
    ], 0);

    
    echo json_encode(['success' => true, 'message' => 'Registro realizado correctamente.']);
    

// LOGIN
} elseif ($action === 'login') {
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, usuario, nombre, password_hash, activo, rol, email FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        registrarLog($pdo, null, 'LOGIN_FALLIDO', [
            'email'  => $email,
            'motivo' => 'Credenciales incorrectas'
        ]);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email o contraseña incorrectos']);        
        exit;
    }

    if (!$user['activo']) {
        registrarLog($pdo, $user['id'], 'LOGIN_FALLIDO', [
            'email'  => $email,
            'motivo' => 'Cuenta no activada'
        ]);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cuenta no activada. Revisa tu email.']);    
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['usuario'] = $user['usuario'];

    registrarLog($pdo, $user['id'], 'LOGIN', [
        'email'   => $user['email'],
        'usuario' => $user['usuario'],
        'rol'     => $user['rol']
    ]);

    echo json_encode([
        'success' => true,
        'rol' => $user['rol'],
        'nombre' => $user['nombre'],
        'email' => $user['email'],
        'usuario' => $user['usuario'],
        'id' => $user['id']
    ]);

// RECUPERAR CONTRASEÑA (NUEVO)
} elseif ($action === 'forgot_password') {
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);

    $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Por seguridad, siempre respondemos "success" para no dar pistas de qué emails existen,
    // pero solo enviamos el correo si el usuario existe y está activo.
    if ($user) {
        $token = generarTokenUnico($pdo);
        // Reutilizamos la columna token_activacion o puedes usar una específica si la tienes
        $stmt = $pdo->prepare("UPDATE usuarios SET token_activacion = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);

        $is_local = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');
        $base_url = $is_local ? "http://localhost:5173" : "https://rgutierrezhairstudio.com"; // 5173 suele ser el puerto de Vue
        $enlace = $base_url . "/restablecer-password?token=" . urlencode($token);

        $nombre_user = htmlspecialchars($user['nombre']);
        $mensaje = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>Recuperar Contraseña</h1>
            </div>
            <div style='padding: 30px; line-height: 1.6; color: #333;'>
                <p>Hola <strong>$nombre_user</strong>,</p>
                <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente botón para elegir una nueva:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$enlace' style='background-color: #e75480; color: #ffffff; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Restablecer Contraseña</a>
                </div>
                <p style='font-size: 12px; color: #999;'>Si no solicitaste este cambio, puedes ignorar este correo.</p>
            </div>
        </div>";

        $email_rec = enviar_notificacion_correo('Recuperación de contraseña', $mensaje, 'Restablece tu contraseña', [$email]);

        registrarLog($pdo, $user['id'], 'RECUPERAR_PASSWORD', [
            'email'         => $email,
            'email_enviado' => $email_rec ? 'sí' : 'no'
        ], $email_rec ? 1 : 0);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Si el correo está registrado, recibirás instrucciones en breve.'
    ]);
// RESTABLECER CONTRASEÑA (DEFINITIVO)
} elseif ($action === 'reset_password') {
    $token = $input['token'] ?? '';
    $new_password = $input['password'] ?? '';

    // Validar contraseña en el servidor
    $validationError = validatePassword($new_password);
    if ($validationError) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $validationError]);
        exit;
    }

    // Buscamos al usuario por el token
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_activacion = ? AND activo = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $new_hash = password_hash($new_password, PASSWORD_ARGON2ID);
        
        // Actualizamos contraseña y LIMPIAMOS el token para que no se use dos veces
        $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ?, token_activacion = NULL WHERE id = ?");
        $stmt->execute([$new_hash, $user['id']]);

        registrarLog($pdo, $user['id'], 'RESET_PASSWORD', [
            'motivo' => 'Contraseña restablecida con éxito'
        ]);

        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada.']);
    } else {
        registrarLog($pdo, null, 'RESET_PASSWORD_FALLIDO', [
            'motivo' => 'Token inválido o expirado',
            'token'  => substr($token, 0, 8) . '...' // Solo los primeros 8 chars por seguridad
        ]);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El enlace ha expirado o es inválido.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);   
}