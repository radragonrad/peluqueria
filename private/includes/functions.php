<?php
// /private/includes/functions.php

/**
 * Genera una cadena SQL con los parámetros insertados para depuración.
 */
function debugQuery($sql, $params) {
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