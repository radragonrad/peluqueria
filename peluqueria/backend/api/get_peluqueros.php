<?php
// 1. Cargamos la configuración centralizada (CORS, Conexión PDO y Headers)
// __DIR__ asegura que la ruta sea relativa a la ubicación de este archivo
require_once __DIR__ . '/../../../private/config/db.php';


try {
    
    $stmt = $pdo->query("SELECT p.id, u.nombre AS nombre, p.especialidad, p.avatar FROM peluqueros p INNER JOIN usuarios u ON p.usuario_id = u.id WHERE p.activo = 1 AND u.activo = 1 ORDER BY u.nombre");
    echo json_encode($stmt->fetchAll());
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar peluqueros']);
}