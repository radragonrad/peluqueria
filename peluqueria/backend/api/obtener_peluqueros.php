<?php
// 1. Cargamos configuración y control de acceso
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

try {
    // 2. Consultamos los peluqueros (puedes añadir un WHERE activo = 1 si tienes ese campo)
    // Seleccionamos id, nombre y la ruta de la foto
    $stmt = $pdo->prepare("SELECT id, nombre, foto FROM peluqueros ORDER BY nombre ASC");
    $stmt->execute();
    $peluqueros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Devolvemos el JSON
    header('Content-Type: application/json');
    echo json_encode($peluqueros);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}