<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        // Verificamos si el teléfono ya existe
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE telefono = ?");
        $check->execute([$data['telefono']]);
        if ($check->fetch()) {
            echo json_encode(["success" => false, "error" => "El teléfono ya existe"]);
            exit;
        }

        // Insertar nuevo cliente (Contraseña provisional = teléfono)
        $pass = password_hash($data['telefono'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, telefono, password, rol) VALUES (?, ?, ?, 'cliente')");
        $stmt->execute([$data['nombre'], $data['telefono'], $pass]);

        echo json_encode([
            "success" => true,
            "id" => $pdo->lastInsertId(),
            "nombre" => $data['nombre'],
            "telefono" => $data['telefono']
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}