<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Incluir PHPMailer
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'No autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$servicio_id = (int)($input['servicio_id'] ?? 0);
$fecha = $input['fecha'] ?? '';
$hora = $input['hora'] ?? '';

if (!$servicio_id || !$fecha || !$hora) {
    http_response_code(400);
    echo json_encode(['message' => 'Datos incompletos']);
    exit;
}

$host = 'localhost';
$dbname = 'peluqueria';
$db_user = 'root';
$db_pass = '123456';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Obtener datos del usuario
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(400);
        echo json_encode(['message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Obtener datos del servicio
    $stmt = $pdo->prepare("SELECT nombre, precio, duracion_min FROM servicios WHERE id = ? AND activo = 1");
    $stmt->execute([$servicio_id]);
    $servicio = $stmt->fetch();
    if (!$servicio) {
        http_response_code(400);
        echo json_encode(['message' => 'Servicio no válido']);
        exit;
    }

    $peluquero_id = (int)($input['peluquero_id'] ?? 0);

    // Validar peluquero
    $stmt = $pdo->prepare("SELECT id FROM peluqueros WHERE id = ? AND activo = 1");
    $stmt->execute([$peluquero_id]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['message' => 'Peluquero no válido']);
        exit;
    }
    
    // Insertar reserva
    $stmt = $pdo->prepare("INSERT INTO reservas (user_id, servicio_id, fecha, hora) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $servicio_id, $fecha, $hora]);
    
    // Formatear fecha para el correo
    $fecha_formateada = date('d/m/Y', strtotime($fecha));
    $dia_semana = date('l', strtotime($fecha));
    $dias_es = [
        'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
    ];
    $dia_nombre = $dias_es[$dia_semana] ?? $dia_semana;
    
    // Enviar correo de confirmación
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // Configuración de Gmail (ajusta según tu entorno)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'radragon.rad@gmail.com';        // ← TU EMAIL
        $mail->Password   = 'ipnylbfbnxpdmtih';   // ← CONTRASEÑA DE APLICACIÓN
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('radragon.rad@gmail.com', 'AQ Barber & Beauty');
        $mail->addAddress($user['email']);
        
        $mail->isHTML(false);
        $mail->Subject = 'Confirmación de tu reserva - AQ Barber & Beauty';
        $mail->Body = "
Hola,

Tu reserva ha sido confirmada con éxito:

• Servicio: {$servicio['nombre']}
• Fecha: {$dia_nombre} {$fecha_formateada}
• Hora: {$hora}
• Duración: {$servicio['duracion_min']} minutos
• Precio: €" . number_format($servicio['precio'], 2) . "

Si necesitas modificar o cancelar tu cita, contáctanos:
📞 657 55 33 77
✉️ rgutierrez@gmail.com

¡Te esperamos!

Equipo AQ Barber & Beauty
";
        
        $mail->send();
        
    } catch (Exception $e) {
        error_log("Error al enviar correo de confirmación: " . $mail->ErrorInfo);
        // No fallamos la reserva si el correo falla
    }
    
    echo json_encode(['message' => 'Reserva creada correctamente']);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al crear la reserva']);
}