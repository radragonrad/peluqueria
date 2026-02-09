<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$dbname = 'peluqueria';
$db_user = 'root';
$db_pass = '123456';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $stmt = $pdo->query("SELECT id, nombre, precio, duracion_min, descripcion, icono FROM servicios WHERE activo = 1 ORDER BY nombre");
    echo json_encode($stmt->fetchAll());
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar servicios']);
}