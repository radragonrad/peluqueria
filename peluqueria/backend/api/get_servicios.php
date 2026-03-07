<?php
// 1. Cargamos la configuración centralizada (CORS, Conexión PDO y Headers)
// __DIR__ asegura que la ruta sea relativa a la ubicación de este archivo
require_once __DIR__ . '/../../../private/config/db.php'; 

// 2. Verificación de sesión de administrador
// require_once 'admin_check.php';


try {
   
    
    $stmt = $pdo->query("SELECT id, nombre, precio, duracion_min, descripcion, icono FROM servicios WHERE activo = 1 ORDER BY nombre");
    echo json_encode($stmt->fetchAll());
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar servicios']);
}