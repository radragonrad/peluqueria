<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

header('Content-Type: application/json');
$metodo = $_SERVER['REQUEST_METHOD'];

// ASIGNAR ETIQUETA (POST)
if ($metodo === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios_etiquetas (usuario_id, etiqueta_id) VALUES (?, ?)");
    $stmt->execute([$data['cliente_id'], $data['etiqueta_id']]);
    echo json_encode(['status' => 'ok']);
    exit;
}

// QUITAR ETIQUETA (DELETE)
if ($metodo === 'DELETE') {
    $usuario_id = $_GET['cliente_id'];
    $etiqueta_id = $_GET['etiqueta_id'];
    $stmt = $pdo->prepare("DELETE FROM usuarios_etiquetas WHERE usuario_id = ? AND etiqueta_id = ?");
    $stmt->execute([$usuario_id, $etiqueta_id]);
    echo json_encode(['status' => 'ok']);
    exit;
}