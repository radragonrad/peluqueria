<?php
// admin_check.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

if (!esAdmin()) {
    // Si no es admin, limpiamos cualquier salida previa y enviamos JSON
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode([
        "success" => false, 
        "message" => "Acceso denegado. Rol actual: " . ($_SESSION['rol'] ?? 'Ninguno')
    ]);
    exit;
}