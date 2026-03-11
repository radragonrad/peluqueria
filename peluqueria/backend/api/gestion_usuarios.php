<?php
// 1. Cargamos la configuración central (Conexión PDO, Headers CORS y Content-Type)
require_once __DIR__ . '/../../../private/config/db.php';

// 2. Verificación de sesión de administrador
require_once 'admin_check.php';

try {
  
    
    // IMPORTANTE: Si admin_check.php hace una redirección con header(), 
    // debes cambiarlo para que devuelva un json_encode y un die().
    require_once 'admin_check.php';

    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
        $sql = "SELECT u.*, p.especialidad, p.avatar 
                FROM usuarios u 
                LEFT JOIN peluqueros p ON u.id = p.usuario_id 
                ORDER BY u.id DESC";
        $usuarios = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        ob_clean(); // Limpiamos basura antes de imprimir
        echo json_encode($usuarios);
        exit;
    }

    if ($metodo === 'POST') {
        // Para recibir JSON o FormData correctamente
        $data = $_POST;
        $id = (isset($data['id']) && $data['id'] !== 'undefined' && !empty($data['id'])) ? $data['id'] : null;
        
        $pdo->beginTransaction();

        if ($id) {
            // --- MODO EDICIÓN ---
            $sql = "UPDATE usuarios SET nombre=?, email=?, telefono=?, fecha_nacimiento=?, rol=?, activo=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'], 
                $data['email'], 
                $data['telefono'], 
                $data['fecha_nacimiento'], 
                $data['rol'], 
                $data['activo'], 
                $id
            ]);
            $usuario_id = $id;
            $usuario_final = $data['usuario'] ?? ''; 
        } else {
            // --- MODO NUEVO ---
            $nombre_limpio = strtolower(trim($data['nombre']));
            $usuario_base = str_replace(' ', '.', $nombre_limpio); 
            $usuario_final = $usuario_base;
            
            $checkUser = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
            $checkUser->execute([$usuario_final]);
            if ($checkUser->fetchColumn() > 0) {
                $usuario_final = $usuario_base . rand(10, 99);
            }

            $password_plano = "123456"; 
            $passHash = password_hash($password_plano, PASSWORD_ARGON2ID);

            $sql = "INSERT INTO usuarios (usuario, nombre, email, password_hash, ip_address, activo, rol, telefono, fecha_nacimiento, fecha_activacion, generado) 
                    VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, NOW(), ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $usuario_final, 
                $data['nombre'], 
                $data['email'], 
                $passHash, 
                $_SERVER['REMOTE_ADDR'], 
                $data['rol'], 
                $data['telefono'], 
                $data['fecha_nacimiento'],
                'local'
            ]);
            $usuario_id = $pdo->lastInsertId();
        }

        // --- GESTIÓN DE PELUQUEROS ---
        if ($data['rol'] === 'admin' || $data['rol'] === 'empleado') {
            $generado = $data['generado'] ?? 'local';
            $avatar_name = ($generado === 'web') ? 'default-avatar-web.png' : 'default-avatar-local.png';
            
            $stmtAv = $pdo->prepare("SELECT avatar FROM peluqueros WHERE usuario_id = ?");
            $stmtAv->execute([$usuario_id]);
            $resAv = $stmtAv->fetch();
            if ($resAv && !empty($resAv['avatar'])) {
                $avatar_name = $resAv['avatar'];
            }

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                
                // 1. Buscamos la carpeta uploads relativa a este archivo PHP
                // Si tu PHP está en /backend/api/gestion_usuarios.php, subimos 3 niveles:
                $base_path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "avatares" . DIRECTORY_SEPARATOR;

                // 2. Crear carpeta con permisos si no existe
                if (!file_exists($base_path)) {
                    mkdir($base_path, 0777, true);
                }

                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $avatar_name = "user_" . time() . "_" . $usuario_id . "." . $ext;
                
                // Esta será la ruta real en el disco duro (C:/laragon/www/tu-proyecto/uploads/...)
                $target_file = $base_path . $avatar_name;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                    // 3. La URL que guardamos en la DB para que el Navegador la encuentre
                    // Debe ser relativa a la raíz de tu web
                    $avatar_url = "/uploads/avatares/" . $avatar_name;

                    // $stmt = $pdo->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
                    // $stmt->execute([$avatar_url, $usuario_id]);
                    
                    // Opcional: devuelve éxito al frontend
                    // echo json_encode(["success" => true, "url" => $avatar_url]);
                } else {
                    error_log("Error crítico: No se pudo mover a " . $target_file);
                }
            }

            $sqlP = "INSERT INTO peluqueros (usuario_id, especialidad, activo, avatar) 
                    VALUES (?, ?, 1, ?) 
                    ON DUPLICATE KEY UPDATE especialidad = VALUES(especialidad), avatar = VALUES(avatar)";
            $stmtP = $pdo->prepare($sqlP);
            $stmtP->execute([$usuario_id, $data['especialidad'] ?? '', $avatar_name]);
        } else {
            $pdo->prepare("DELETE FROM peluqueros WHERE usuario_id = ?")->execute([$usuario_id]);
        }

        $pdo->commit();
        
        ob_clean();
        echo json_encode([
            "success" => true, 
            "usuario_generado" => $usuario_final,
            "mensaje" => $id ? "Actualizado correctamente" : "Usuario creado: $usuario_final"
        ]);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}