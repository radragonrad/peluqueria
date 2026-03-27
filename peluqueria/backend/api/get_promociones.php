<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

try {
    // Seleccionamos las promos ordenadas por las más recientes
    $sql = "SELECT *, 
            CASE 
                WHEN activa = 0 THEN 'INACTIVA'
                WHEN CURDATE() > fecha_fin THEN 'CADUCADA'
                ELSE 'ACTIVA'
            END as estado_calculado
            FROM promociones 
            ORDER BY id DESC";
    
    $stmt = $pdo->query($sql);
    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($promos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}