<?php
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'peluqueria';
$db_user = 'root';
$db_pass = '123456';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo "<br>ssss";exit;
    http_response_code(500);
    echo json_encode(['message' => 'Error de conexión a la base de datos']);
    exit;
}



function enviar_notificacion_correo($desde, $mensaje, $asunto, $para = array()) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'radragon.rad@gmail.com';        // ← TU EMAIL DE GMAIL
        $mail->Password   = 'ipnylbfbnxpdmtih';        // ← CONTRASEÑA DE APLICACIÓN (16 dígitos, sin espacios)
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;


       // Remitente
        $mail->setFrom('radragon.rad@gmail.com', $desde);   // ← MISMO EMAIL

        // Destinatarios
        foreach ($para as $email) {
            $mail->addAddress($email);
        }

        // Contenido
        $mail->isHTML(false);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $e->getMessage());
        return false;
    }
}

function getRealIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function validatePassword($password) {
    if (strlen($password) < 8) return 'La contraseña debe tener al menos 8 caracteres.';
    if (!preg_match('/[A-Z]/', $password)) return 'La contraseña debe contener al menos una letra mayúscula.';
    if (!preg_match('/[a-z]/', $password)) return 'La contraseña debe contener al menos una letra minúscula.';
    if (!preg_match('/[0-9]/', $password)) return 'La contraseña debe contener al menos un número.';
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return 'La contraseña debe contener al menos un carácter especial (ej. ! @ # $ %).';
    return null;
}

function generarTokenUnico($pdo) {
    do {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("SELECT id FROM users WHERE token_activacion = ?");
        $stmt->execute([$token]);
        $exists = $stmt->fetch();
    } while ($exists);
    return $token;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'register') {
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';

    // Validaciones
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Correo electrónico inválido']);
        exit;
    }

    $validationError = validatePassword($password);
    if ($validationError) {
        http_response_code(400);
        echo json_encode(['message' => $validationError]);
        exit;
    }

    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id, activo FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['activo']) {
            http_response_code(409);
            echo json_encode(['message' => 'Este correo ya está registrado']);
            exit;
        } else {
            // Reenviar token
            $token = generarTokenUnico($pdo);
            $stmt = $pdo->prepare("UPDATE users SET token_activacion = ? WHERE email = ?");
            $stmt->execute([$token, $email]);
        }
    } else {
        // Generar username
        $base = preg_replace('/[^a-z0-9]/', '_', strtolower(explode('@', $email)[0]));
        $username = $base;
        $counter = 0;

        do {
            if ($counter > 0) {
                $username = $base . '_' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 4);
            }
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $exists = $stmt->fetch();
            $counter++;
        } while ($exists && $counter < 10);

        if ($counter >= 10) {
            http_response_code(500);
            echo json_encode(['message' => 'No se pudo generar un nombre de usuario único']);
            exit;
        }

        // Insertar nuevo usuario
        $password_hash = password_hash($password, PASSWORD_ARGON2ID);
        $ip = getRealIP();
        $token = generarTokenUnico($pdo);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, ip_address, activo, token_activacion) VALUES (?, ?, ?, ?, 0, ?)");
        $stmt->execute([$username, $email, $password_hash, $ip, $token]);
    }

    // Enviar correo
    $enlace = "http://localhost/peluqueria/activar.php?token=" . urlencode($token);
    $mensaje = "Hola,\n\nGracias por registrarte. Haz clic aquí para activar tu cuenta:\n$enlace\n\nSi no solicitaste este registro, ignora este mensaje.";
    
    if (enviar_notificacion_correo('Activación de cuenta', $mensaje, 'Activa tu cuenta', [$email])) {
        echo json_encode(['message' => 'Registro exitoso. Revisa tu correo para activar tu cuenta.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al enviar el correo de activación.']);
    }

} elseif ($action === 'login') {
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Credenciales inválidas']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, password_hash, activo, rol, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['message' => 'Email o contraseña incorrectos']);
        exit;
    }

    if (!$user['activo']) {
        http_response_code(403);
        echo json_encode(['message' => 'Tu cuenta no está activada. Revisa tu correo.']);
        exit;
    }

    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
       
        
        
        echo json_encode([
            'success' => true,
            'rol' => $user['rol'],
            'email' => $user['email']
        ]);

    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Email o contraseña incorrectos']);
    }

} else {
    http_response_code(400);
    echo json_encode(['message' => 'Acción no válida']);
}