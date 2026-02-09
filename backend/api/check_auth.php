<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user_id'])) {
    $host = 'localhost';
    $dbname = 'peluqueria';
    $db_user = 'root';
    $db_pass = '123456';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // ¡Importante! Seleccionar email y rol
        $stmt = $pdo->prepare("SELECT email, rol FROM users WHERE id = ? AND activo = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode([
                'logged_in' => true,
                'email' => $user['email'],  // ← Añadido
                'rol' => $user['rol']       // ← Añadido
            ]);
        } else {
            session_destroy();
            echo json_encode(['logged_in' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['logged_in' => false]);
    }
} else {
    echo json_encode(['logged_in' => false]);
}