<?php
require_once __DIR__ . '/../../../private/config/db.php';

header('Content-Type: application/json');

// Recogemos el ID de la URL
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["error" => "ID de usuario no proporcionado"]);
    exit;
}

try {
    // 1. Obtener datos básicos del usuario para el saludo
    $stmtUser = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // 2. Obtener promociones filtradas por la etiqueta del usuario
    // He añadido un JOIN a 'usuario_etiquetas' para verificar la pertenencia del cliente
    $sql = "SELECT DISTINCT p.*, 
            COALESCE(c.cupones_actuales, 0) as mis_sellos,
            e.nombre as nombre_etiqueta
            FROM promociones p
            LEFT JOIN cupones_usuario c ON p.id = c.promocion_id AND c.usuario_id = ?
            LEFT JOIN etiquetas e ON p.etiqueta_id = e.id
            -- Unimos con la tabla que vincula usuarios y etiquetas
            LEFT JOIN usuarios_etiquetas ue ON ue.usuario_id = ? AND ue.etiqueta_id = p.etiqueta_id
            WHERE p.activa = 1 
            AND CURDATE() BETWEEN p.fecha_inicio AND p.fecha_fin
            AND (
                p.tipo != 'ETIQUETA'      -- Si no es por etiqueta, se muestra a todos
                OR p.etiqueta_id IS NULL  -- Si no tiene etiqueta asignada, se muestra a todos
                OR ue.etiqueta_id IS NOT NULL -- Si es tipo ETIQUETA, el usuario debe tenerla
            )";
            
           

    // Pasamos el $user_id dos veces: uno para los sellos y otro para el filtro de etiquetas
    $stmtPromo = $pdo->prepare($sql);
    $stmtPromo->execute([$user_id, $user_id]);
    $promos = $stmtPromo->fetchAll(PDO::FETCH_ASSOC);

    // Devolvemos la respuesta unificada
    echo json_encode([
        "usuario" => $usuario,
        "promociones" => $promos
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}