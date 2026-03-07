<?php
// 1. Cargamos la configuración centralizada (CORS, Conexión PDO y Headers)
// __DIR__ asegura que la ruta sea relativa a la ubicación de este archivo
require_once __DIR__ . '/../../../private/config/db.php';

// 2. Verificación de sesión de administrador
require_once 'admin_check.php';


try {
   
    // Total de reservas hoy
    $hoy = date('Y-m-d');
    $res_hoy = $pdo->query("SELECT COUNT(*) FROM reservas WHERE fecha = '$hoy'")->fetchColumn();
    
    // Total usuarios
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE rol = 'usuario'")->fetchColumn();

    // Próximas reservas
    $stmt = $pdo->query("SELECT r.fecha, r.hora, u.username as cliente, s.nombre as servicio 
                         FROM reservas r 
                         JOIN users u ON r.user_id = u.id 
                         JOIN servicios s ON r.servicio_id = s.id 
                         WHERE r.fecha >= '$hoy' 
                         ORDER BY r.fecha ASC LIMIT 10");
    $proximas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "stats" => [
            "hoy" => $res_hoy,
            "clientes" => $total_users
        ],
        "proximas_reservas" => $proximas
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}