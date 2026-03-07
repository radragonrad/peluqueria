<?php
// 1. Cargamos la configuración central (CORS, Conexión PDO, Headers)
require_once __DIR__ . '/../../../private/config/db.php'; 

// 2. Incluir PHPMailer usando rutas relativas limpias
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3. Verificación de sesión (Si no usas admin_check porque es para clientes)
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Sesión expirada o no autorizada']);
    exit;
}

// 4. Captura de datos
$input = json_decode(file_get_contents('php://input'), true);
$servicio_id = (int)($input['servicio_id'] ?? 0);
$peluquero_id = (int)($input['peluquero_id'] ?? 0);
$fecha = $input['fecha'] ?? '';
$hora = $input['hora'] ?? '';

if (!$servicio_id || !$fecha || !$hora || !$peluquero_id) {
    http_response_code(400);
    echo json_encode(['message' => 'Datos incompletos para procesar la reserva']);
    exit;
}

try {
    // La variable $pdo ya viene de db.php
    
    // Obtener datos del usuario (Asegúrate que la tabla sea 'usuarios' o 'users')
    $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Usuario no encontrado');
    }
    
    // Obtener datos del servicio
    $stmt = $pdo->prepare("SELECT nombre, precio, duracion_min FROM servicios WHERE id = ? AND activo = 1");
    $stmt->execute([$servicio_id]);
    $servicio = $stmt->fetch();
    
    if (!$servicio) {
        throw new Exception('El servicio seleccionado ya no está disponible');
    }

    // Validar peluquero
    $stmt = $pdo->prepare("SELECT id FROM peluqueros WHERE id = ? AND activo = 1");
    $stmt->execute([$peluquero_id]);
    if (!$stmt->fetch()) {
        throw new Exception('El barbero seleccionado no está disponible');
    }
    
    // 5. Insertar reserva (He añadido peluquero_id a tu INSERT)
    $stmt = $pdo->prepare("INSERT INTO reservas (user_id, servicio_id, peluquero_id, fecha, hora, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
    $stmt->execute([$_SESSION['user_id'], $servicio_id, $peluquero_id, $fecha, $hora]);

    // 6. Preparar datos para el correo
    $fecha_formateada = date('d/m/Y', strtotime($fecha));
    $dias_es = [
        'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
    ];
    $dia_nombre = $dias_es[date('l', strtotime($fecha))] ?? date('l', strtotime($fecha));
    
    // 7. Enviar correo de confirmación
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP (Esto también podrías moverlo a un config/mail.php en el futuro)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'radragon.rad@gmail.com';        
        $mail->Password   = 'ipnylbfbnxpdmtih'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('radragon.rad@gmail.com', 'rgutierrezhairstudio');
        $mail->addAddress($user['email'], $user['nombre']);
        
        $mail->isHTML(true); // Cambiado a true para que se vea mejor
        $mail->Subject = 'Confirmación de tu Cita - rgutierrezhairstudio';
        
        // Un diseño un poco más limpio para el body
        $mail->Body = "
            <h2>¡Hola, {$user['nombre']}!</h2>
            <p>Tu reserva en <strong>rgutierrezhairstudio</strong> ha sido confirmada:</p>
            <ul>
                <li><strong>Servicio:</strong> {$servicio['nombre']}</li>
                <li><strong>Fecha:</strong> {$dia_nombre}, {$fecha_formateada}</li>
                <li><strong>Hora:</strong> {$hora}</li>
                <li><strong>Duración:</strong> {$servicio['duracion_min']} min</li>
                <li><strong>Precio:</strong> " . number_format($servicio['precio'], 2) . "€</li>
            </ul>
            <p>Si necesitas cancelar, llámanos al 657 55 33 77.</p>
            <p>¡Te esperamos!</p>
        ";
        
        $mail->send();
    } catch (Exception $e) {
        error_log("Error PHPMailer: " . $mail->ErrorInfo);
    }
    
    echo json_encode(['success' => true, 'message' => 'Reserva creada correctamente']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}