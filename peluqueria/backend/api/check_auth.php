<?php
// 1. Cargamos la configuración central (CORS, Conexión PDO, Headers)
require_once __DIR__ . '/../private/config/db.php';

session_start();


// Verificar si hay una sesión activa
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT email, rol FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode([
                'logged_in' => true,
                'rol' => $user['rol'],
                'email' => $user['email']
            ]);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener usuario en check_auth: " . $e->getMessage());
    }
}

// Si no hay sesión o hay error, devolver false
echo json_encode(['logged_in' => false]);