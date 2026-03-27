<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

header('Content-Type: application/json');

// 1. Detectar el método y obtener datos
$metodo = $_SERVER['REQUEST_METHOD'];
$json = file_get_contents('php://input');
$data = json_decode($json, true);

try {
    // --- LÓGICA PARA ELIMINAR ---
    if ($metodo === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) throw new Exception("ID no proporcionado");

        // Verificación de seguridad: ¿Hay clientes con esta promo?
        $check = $pdo->prepare("SELECT COUNT(*) FROM cupones_usuario WHERE promocion_id = ? AND cupones_actuales > 0");
        $check->execute([$id]);
        
        if ($check->fetchColumn() > 0) {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: hay usuarios con esta promoción activa."]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM promociones WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success"]);
        exit;
    }

    // --- LÓGICA PARA GUARDAR (POST) ---
    if ($data) {
        $id = $data['id'] ?? null;
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'];
        $tipo = $data['tipo'];
        $fecha_inicio = $data['fecha_inicio'];
        $fecha_fin = $data['fecha_fin'];

        // Manejo de nulos según el tipo (Limpieza de datos)
        $cupones = ($tipo === 'VISITAS') ? $data['cupones_necesarios'] : null;
        $descuento = ($tipo === 'PORCENTAJE') ? $data['valor_descuento'] : null;
        $etiqueta_id = ($tipo === 'ETIQUETA') ? $data['etiqueta_id'] : null;

        if ($id) {
            // UPDATE
            $sql = "UPDATE promociones SET 
                    nombre = ?, descripcion = ?, tipo = ?, 
                    cupones_necesarios = ?, valor_descuento = ?, etiqueta_id = ?,
                    fecha_inicio = ?, fecha_fin = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $tipo, $cupones, $descuento, $etiqueta_id, $fecha_inicio, $fecha_fin, $id]);
        } else {
            // INSERT
            $sql = "INSERT INTO promociones (nombre, descripcion, tipo, cupones_necesarios, valor_descuento, etiqueta_id, fecha_inicio, fecha_fin, activa) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $tipo, $cupones, $descuento, $etiqueta_id, $fecha_inicio, $fecha_fin]);
        }

        echo json_encode(["status" => "success"]);
    } else {
        throw new Exception("No se recibieron datos válidos");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}