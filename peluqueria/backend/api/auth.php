<?php
// 1. Cargamos la configuración central (CORS, Conexión PDO, Headers)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
require_once __DIR__ . '/../../../private/config/db.php';

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
        $mail->Username   = 'no-reply@rgutierrezhairstudio.com'; // Usuario correcto
        $mail->Password   = '3K796Xy*8#5_s34@'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->Hostname   = 'rgutierrezhairstudio.com';
        $mail->CharSet    = 'UTF-8'; // Asegura que las tildes se vean bien

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // REMITENTE: Ahora coincide con el Username
        $mail->setFrom('no-reply@rgutierrezhairstudio.com', $desde);
        
        foreach ($para as $email) { 
            $email = trim($email); // Limpia espacios accidentales
            if (!empty($email)) {
                $mail->addAddress($email); 
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = nl2br($mensaje); // Convierte saltos de línea a <br> para HTML
        $mail->AltBody = strip_tags($mensaje);

        return $mail->send();
    } catch (Exception $e) {
        // Esto guardará el error real en el log del servidor si vuelve a fallar
        error_log("Error PHPMailer: " . $mail->ErrorInfo); 
        return false;
    }
}

function validatePassword($password) {
    if (strlen($password) < 8) return 'La contraseña debe tener al menos 8 caracteres.';
    if (!preg_match('/[A-Z]/', $password)) return 'Debe contener una mayúscula.';
    if (!preg_match('/[a-z]/', $password)) return 'Debe contener una minúscula.';
    if (!preg_match('/[0-9]/', $password)) return 'Debe contener un número.';
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return 'Debe contener un carácter especial.';
    return null;
}

function generarTokenUnico($pdo) {
    do {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_activacion = ?");
        $stmt->execute([$token]);
    } while ($stmt->fetch());
    return $token;
}

// --- LÓGICA PRINCIPAL ---

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Datos inválidos'
    ]);   
    exit;
}

$action = $input['action'] ?? '';

// REGISTRO
if ($action === 'register') {
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Email inválido'
        ]);
        exit;
    }

    $validationError = validatePassword($password);
    if ($validationError) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => $validationError
        ]);
        echo json_encode(['message' => $validationError]);
        exit;
    }

    // Usamos la tabla 'usuarios' para ser consistentes
    $stmt = $pdo->prepare("SELECT id, activo FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['activo']) {
            http_response_code(409);
            echo json_encode([
                'success' => false, 
                'message' => 'Este correo ya está registrado'
            ]);  
            
            exit;
        } else {
            $token = generarTokenUnico($pdo);
            $stmt = $pdo->prepare("UPDATE usuarios SET token_activacion = ? WHERE email = ?");
            $stmt->execute([$token, $email]);
        }
    } else {
        // Generar username automático basado en el email
        $username = str_replace(' ', '.', strtolower(explode('@', $email)[0]));
        
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $check->execute([$username]);
        if ($check->fetch()) { $username .= rand(10, 99); }

        $password_hash = password_hash($password, PASSWORD_ARGON2ID);
        $token = generarTokenUnico($pdo);

        $sql = "INSERT INTO usuarios (usuario, nombre, email, password_hash, ip_address, activo, rol, token_activacion, telefono, fecha_nacimiento, generado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $input['nombre'], $email, $password_hash, $_SERVER['REMOTE_ADDR'], 0, 'usuario', $token, $input['telefono'], $input['fecha_nacimiento'], 'web']);
    }

    // Configura el enlace según tu entorno (Local o Pro)
    $is_local = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');
    $base_url = $is_local ? "http://localhost:5173" : "https://rgutierrezhairstudio.com";
    $enlace = $base_url . "/activar.php?token=" . urlencode($token);

    $nombre_cliente = htmlspecialchars($input['nombre']);

    // Diseño del mensaje en HTML
    $mensaje = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;'>
            <h1 style='margin: 0; font-size: 24px;'>R. Gutiérrez Hair Studio</h1>
        </div>
        <div style='padding: 30px; line-height: 1.6; color: #333;'>
            <p style='font-size: 18px;'>Hola <strong>$nombre_cliente</strong>,</p>
            <p>Es un placer darte la bienvenida a un espacio diseñado para tu imagen. Confirma tu cuenta para que podamos empezar a cuidar de tu estilo como te mereces. Para comenzar a gestionar tus citas y disfrutar de nuestros servicios, solo necesitas confirmar tu cuenta pulsando el siguiente botón:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='$enlace' style='background-color: #bc9667; color: #ffffff; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Confirmar mi Cuenta</a>
            </div>
            
            <p style='font-size: 14px; color: #666;'>Si el botón no funciona, puedes copiar y pegar este enlace en tu navegador:<br>
            <a href='$enlace' style='color: #bc9667;'>$enlace</a></p>
        </div>
        <div style='background-color: #f9f9f9; color: #999; padding: 20px; text-align: center; font-size: 12px; border-top: 1px solid #eee;'>
            <p style='margin: 5px 0;'>Este es un correo automático, por favor no respondas a este mensaje.</p>
            <p style='margin: 5px 0;'>&copy; " . date('Y') . " R. Gutiérrez Hair Studio. C. San Jose, 9, 41808 Villanueva del Ariscal, Sevilla</p>
        </div>
    </div>
    ";
    
    if (enviar_notificacion_correo('Activación de cuenta', $mensaje, 'Activa tu cuenta', [$email])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Revisa tu correo para activar tu cuenta.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error al enviar el correo.'
        ]);
      
    }

// LOGIN
} elseif ($action === 'login') {
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, usuario, nombre, password_hash, activo, rol, email FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Email o contraseña incorrectos'
        ]);        
        exit;
    }

    if (!$user['activo']) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Cuenta no activada. Revisa tu email.'
        ]);    
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['nombre'] = $user['nombre'];
    
    echo json_encode([
        'success' => true,
        'rol' => $user['rol'],
        'nombre' => $user['nombre'],
        'id' => $user['id']
    ]);

} else {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' =>  'Acción no válida'
    ]);   
    
}