<?php
// 1. Cargamos la configuración central
// Esto ya inicia la sesión con los parámetros de 30 días y maneja los CORS
require_once __DIR__ . '/../private/config/db.php';

// 2. Limpiar todas las variables de sesión en el servidor
$_SESSION = array();

// 3. Destruir la cookie en el navegador del cliente
// Usamos los mismos parámetros que definimos en db.php para que coincidan
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // Fecha en el pasado para que el navegador la borre ya
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destruir la sesión en el servidor
session_destroy();

// 5. Responder con éxito
// db.php ya configuró el header Content-Type: application/json
echo json_encode(['success' => true]);
exit;