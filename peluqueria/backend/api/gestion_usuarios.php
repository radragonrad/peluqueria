<?php
// 1. Configuración y PHPMailer
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../addons/php/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- FUNCIÓN DE ENVÍO DE CORREO (Formato Premium) ---
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
        $mail->Body    = $mensaje;
        $mail->AltBody = strip_tags($mensaje);

        return $mail->send();
    } catch (Exception $e) {
        error_log("Error PHPMailer: " . $mail->ErrorInfo); 
        return false;
    }
}

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    // --- MODO LECTURA (GET) ---
    if ($metodo === 'GET') {
        $sql = "SELECT u.*, p.especialidad, p.avatar,
                (SELECT GROUP_CONCAT(CONCAT(e.nombre, ':', e.color, ':', e.id)) 
                 FROM usuarios_etiquetas ce 
                 JOIN etiquetas e ON ce.etiqueta_id = e.id 
                 WHERE ce.usuario_id = u.id) as etiquetas_info
                FROM usuarios u 
                LEFT JOIN peluqueros p ON u.id = p.usuario_id 
                ORDER BY u.id DESC";
        
        $stmt = $pdo->query($sql);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usuarios as &$user) {
            $user['etiquetas'] = [];
            if (!empty($user['etiquetas_info'])) {
                $tagsRaw = explode(',', $user['etiquetas_info']);
                foreach ($tagsRaw as $tag) {
                    list($nom, $col, $tid) = explode(':', $tag);
                    $user['etiquetas'][] = ['id' => $tid, 'nombre' => $nom, 'color' => $col];
                }
            }
            unset($user['etiquetas_info']);
        }
        ob_clean();
        echo json_encode($usuarios);
        exit;
    }

    // --- MODO CREACIÓN / EDICIÓN (POST) ---
    if ($metodo === 'POST') {
        $data = $_POST; // FormData enviado desde Vue
        $id = (isset($data['id']) && $data['id'] !== 'undefined' && !empty($data['id'])) ? $data['id'] : null;
        
        // Procesar etiquetas (vienen como string separado por comas)
        $etiquetas_ids = !empty($data['etiquetas_ids']) ? explode(',', $data['etiquetas_ids']) : [];

        $pdo->beginTransaction();

        if ($id) {
            // EDICIÓN
            $sql = "UPDATE usuarios SET nombre=?, email=?, telefono=?, fecha_nacimiento=?, rol=?, activo=? WHERE id=?";
            $pdo->prepare($sql)->execute([
                $data['nombre'], $data['email'], $data['telefono'], 
                $data['fecha_nacimiento'], $data['rol'], $data['activo'], $id
            ]);
            $usuario_id = $id;
            $usuario_final = $data['usuario'] ?? '';
            $es_nuevo = false;
        } else {
            // NUEVO REGISTRO
            $nombre_limpio = strtolower(trim($data['nombre']));
            $usuario_base = str_replace(' ', '.', $nombre_limpio); 
            $usuario_final = $usuario_base;
            
            $checkUser = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
            $checkUser->execute([$usuario_final]);
            if ($checkUser->fetchColumn() > 0) { $usuario_final = $usuario_base . rand(10, 99); }

            $password_plano = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
            $passHash = password_hash($password_plano, PASSWORD_ARGON2ID);

            $sql = "INSERT INTO usuarios (usuario, nombre, email, password_hash, ip_address, activo, rol, telefono, fecha_nacimiento, fecha_activacion, generado) 
                    VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, NOW(), 'local')";
            $pdo->prepare($sql)->execute([
                $usuario_final, $data['nombre'], $data['email'], $passHash, 
                $_SERVER['REMOTE_ADDR'], $data['rol'], $data['telefono'], $data['fecha_nacimiento']
            ]);
            $usuario_id = $pdo->lastInsertId();
            $es_nuevo = true;
        }

        // --- GESTIÓN DE ETIQUETAS (ELIMINAR Y VOLVER A INSERTAR) ---
        // $pdo->prepare("DELETE FROM usuarios_etiquetas WHERE usuario_id = ?")->execute([$usuario_id]);
        // if (!empty($etiquetas_ids)) {
        //     $stmtEt = $pdo->prepare("INSERT INTO usuarios_etiquetas (usuario_id, etiqueta_id) VALUES (?, ?)");
        //     foreach ($etiquetas_ids as $et_id) {
        //         if (!empty($et_id)) $stmtEt->execute([$usuario_id, (int)$et_id]);
        //     }
        // }

        // --- GESTIÓN DE PELUQUEROS (Avatar) ---
        if ($data['rol'] === 'admin' || $data['rol'] === 'empleado') {
            // Caso: Es Staff. Aseguramos que exista y esté activo.
            $avatar_name = 'default-avatar-local.png'; 
            
            // Usamos el campo 'activo' del usuario para sincronizar al peluquero también
            $estado_peluquero = isset($data['activo']) ? (int)$data['activo'] : 1;

            $sqlP = "INSERT INTO peluqueros (usuario_id, especialidad, activo, avatar) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        especialidad = VALUES(especialidad), 
                        activo = VALUES(activo)";
            $pdo->prepare($sqlP)->execute([
                $usuario_id, 
                $data['especialidad'] ?? '', 
                $estado_peluquero, 
                $avatar_name
            ]);
        } else {
            // Caso: El rol es 'usuario' (Cliente). 
            // Si antes era empleado, debemos desactivar su perfil de peluquero para que no aparezca en las reservas.
            $sqlDesactivar = "UPDATE peluqueros SET activo = 0 WHERE usuario_id = ?";
            $pdo->prepare($sqlDesactivar)->execute([$usuario_id]);
        }

        $pdo->commit();

        // --- ENVÍO DE EMAIL SI ES NUEVO ---
        $email_enviado = false;
        if ($es_nuevo && !empty($data['email'])) {
            $nombre_cliente = htmlspecialchars($data['nombre']);
            $mensajeHtml = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                <div style='background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px; letter-spacing: 1px;'>R. Gutiérrez Hair Studio</h1>
                </div>
                <div style='padding: 30px; line-height: 1.6; color: #333;'>
                    <p style='font-size: 18px;'>Hola <strong>$nombre_cliente</strong>,</p>
                    <p>Bienvenido/a a nuestro estudio. Hemos habilitado tu acceso para que gestiones tus citas y servicios.</p>
                    <div style='background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin: 25px 0; border-left: 4px solid #bc9667;'>
                        <p style='margin: 0 0 10px 0; font-weight: bold; color: #1a1a1a;'>Tus credenciales:</p>
                        <p style='margin: 5px 0;'><strong>Usuario:</strong> <span style='color: #bc9667;'>$usuario_final</span></p>
                        <p style='margin: 5px 0;'><strong>Contraseña temporal:</strong> <span style='color: #bc9667;'>$password_plano</span></p>
                    </div>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='https://rgutierrezhairstudio.com/login' style='background-color: #bc9667; color: #ffffff; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Acceder ahora</a>
                    </div>
                </div>
            </div>";

            $email_enviado = enviar_notificacion_acceso(
                'R. Gutiérrez Hair Studio', 
                $mensajeHtml, 
                'Tus datos de acceso - R. Gutiérrez Hair Studio', 
                [$data['email']]
            );
        }

        ob_clean();
        echo json_encode([
            "success" => true, 
            "email_enviado" => $email_enviado,
            "mensaje" => $es_nuevo ? "Usuario creado y email enviado" : "Actualizado correctamente"
        ]);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}