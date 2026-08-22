<?php
// gestion_horarios_peluquero.php
// CRUD del horario semanal individual de cada peluquero (tabla horarios_peluquero).
// Si un peluquero no tiene fila para un día, se considera cerrado (abierto = 0).

require_once __DIR__ . '/../../../private/config/db.php';
require_once 'admin_check.php';

const DIAS_SEMANA = [
    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
    5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
];

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
        $peluquero_id = intval($_GET['peluquero_id'] ?? 0);
        if (!$peluquero_id) {
            http_response_code(400);
            echo json_encode(["error" => "Falta peluquero_id"]);
            exit;
        }

        // Aseguramos que existan las 7 filas (cerrado por defecto) antes de leer.
        $stmtIns = $pdo->prepare(
            "INSERT IGNORE INTO horarios_peluquero (peluquero_id, id_dia, dia_semana, abierto)
             VALUES (?, ?, ?, 0)"
        );
        foreach (DIAS_SEMANA as $id_dia => $nombre) {
            $stmtIns->execute([$peluquero_id, $id_dia, $nombre]);
        }

        $stmt = $pdo->prepare("SELECT * FROM horarios_peluquero WHERE peluquero_id = ? ORDER BY id_dia ASC");
        $stmt->execute([$peluquero_id]);
        echo json_encode(["horarios" => $stmt->fetchAll()]);
        exit;
    }

    if ($metodo === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "UPDATE horarios_peluquero SET
                abierto = ?,
                h_apertura_1 = ?, h_cierre_1 = ?,
                h_apertura_2 = ?, h_cierre_2 = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['abierto'],
            $data['h_apertura_1'] ?: null,
            $data['h_cierre_1'] ?: null,
            $data['h_apertura_2'] ?: null,
            $data['h_cierre_2'] ?: null,
            $data['id'],
        ]);

        echo json_encode(["success" => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
