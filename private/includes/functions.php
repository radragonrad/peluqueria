<?php
// /private/includes/functions.php

/**
 * Genera una cadena SQL con los parámetros insertados para depuración.
 */
function debugQuery($sql, $params) {
    // --- AQUÍ USAS TU FUNCIÓN DE DEBUG ---
    // $params_reservas = [$peluquero_id, $fecha];
    // echo "<pre>";
    // echo "SQL DEPURADO: " . debugQuery($queryText, $params_reservas);
    // echo "</pre>";
    // // -------------------------------------
    if (!$params) return $sql;
    
    foreach ($params as $param) {
        // Manejar valores nulos para que no rompan el string
        if (is_null($param)) {
            $value = "NULL";
        } else {
            $value = is_numeric($param) ? $param : "'" . addslashes($param) . "'";
        }
        $sql = preg_replace('/\?/', $value, $sql, 1);
    }
    return $sql;
}

/**
 * Puedes añadir más funciones genéricas aquí, como formateo de fechas,
 * validaciones de entrada, o respuestas JSON estándar.
 */
function jsonResponse($data, $code = 200) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    exit;
}


/**
 * Registra un movimiento en la tabla de auditoría logs_sistema.
 */
/**
 * /private/includes/functions.php
 */
function registrarLog($pdo, $usuario_id, $accion, $detalle, $email_enviado = 0, $archivo = null) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Si no se pasa archivo, intentamos capturarlo del backtrace
        if ($archivo === null) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $archivo = basename($trace[0]['file'] ?? 'desconocido');
        }

        $sql = "INSERT INTO logs_sistema (usuario_id, accion, archivo, detalle, email_enviado, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $usuario_id, 
            $accion,
            $archivo,
            json_encode($detalle, JSON_UNESCAPED_UNICODE), 
            $email_enviado ? 1 : 0, 
            $ip
        ]);
    } catch (Exception $e) {
        error_log("Error en Log: " . $e->getMessage());
    }
}