<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Solo en desarrollo; en producción, limita a tu dominio

require_once __DIR__ . '/../config/db.php';

// Leer datos JSON
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action !== 'register') {
    http_response_code(400);
    echo json_encode(['message' => 'Acción no válida']);
    exit;
}

$email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $input['password'] ?? '';

// Validaciones básicas
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['message' => 'Correo electrónico inválido']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

try {
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['message' => 'Este correo ya está registrado']);
        exit;
    }

    // Hashear contraseña
    $password_hash = password_hash($password, PASSWORD_ARGON2ID);

    // Insertar nuevo cliente
    $stmt = $pdo->prepare("INSERT INTO clients (email, password_hash) VALUES (?, ?)");
    $stmt->execute([$email, $password_hash]);

    echo json_encode(['message' => 'Usuario registrado correctamente']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error interno del servidor']);
    error_log("Registro error: " . $e->getMessage());
}