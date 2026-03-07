<?php
// 1. Cargamos la configuración centralizada (CORS, Conexión PDO y Headers)
// __DIR__ asegura que la ruta sea relativa a la ubicación de este archivo
require_once __DIR__ . '/../../../private/config/db.php'; 

try {
   
    // Solo traemos excepciones de hoy en adelante para no cargar datos viejos
    $stmt = $pdo->query("SELECT fecha, solo_tramo, cerrado, descripcion FROM horario_excepciones WHERE fecha >= CURDATE()");
    $excepciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($excepciones);
} catch (PDOException $e) {
    echo json_encode([]);
}