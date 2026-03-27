<?php
// 1. Cargamos la configuración centralizada (CORS, Conexión PDO y Headers)
// __DIR__ asegura que la ruta sea relativa a la ubicación de este archivo
require_once __DIR__ . '/../../../private/config/db.php'; 

try {
   

    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    if ($user_id === 0) {
        echo json_encode([]); exit;
    }

    // Consultamos las reservas uniendo con servicios y peluqueros
    $sql = "SELECT r.id, r.fecha, r.hora, s.nombre as servicio, s.precio, u.nombre as peluquero
            FROM reservas r
            JOIN servicios s ON r.servicio_id = s.id
            JOIN peluqueros p ON r.peluquero_id = p.id
            JOIN usuarios u ON p.usuario_id=u.id
            WHERE r.user_id = ?
            ORDER BY r.fecha ASC, r.hora ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($reservas);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}