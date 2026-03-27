<?php
// 1. Cargamos la configuración central
require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
// 2. Verificación de sesión de administrador
require_once 'admin_check.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
        // Obtenemos las reservas con sus etiquetas procesadas
        $sql = "SELECT 
                    r.*, 
                    u_cliente.usuario AS cliente_nombre, 
                    u_cliente.telefono AS cliente_telefono, 
                    u_peluquero.usuario AS peluquero_nombre, 
                    s.nombre AS servicio_nombre, 
                    s.icono AS servicio_icono, 
                    s.precio AS precio,
                    s.duracion_min AS duracion,
                    (
                        SELECT GROUP_CONCAT(CONCAT(e.nombre, ':', e.color) SEPARATOR '|')
                        FROM usuarios_etiquetas ue
                        JOIN etiquetas e ON ue.etiqueta_id = e.id
                        WHERE ue.usuario_id = r.user_id
                    ) as etiquetas_raw
                FROM reservas r
                JOIN usuarios u_cliente ON r.user_id = u_cliente.id
                JOIN servicios s ON r.servicio_id = s.id
                JOIN peluqueros p ON r.peluquero_id = p.id
                JOIN usuarios u_peluquero ON p.usuario_id = u_peluquero.id 
                ORDER BY r.fecha DESC, r.hora DESC";
        
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();

        foreach ($resultados as &$reserva) {
            $reserva['etiquetas'] = [];
            if (!empty($reserva['etiquetas_raw'])) {
                $parts = explode('|', $reserva['etiquetas_raw']);
                foreach ($parts as $p) {
                    $subparts = explode(':', $p);
                    if(count($subparts) == 2){
                        $reserva['etiquetas'][] = [
                            'nombre' => $subparts[0],
                            'color' => $subparts[1]
                        ];
                    }
                }
            }
            unset($reserva['etiquetas_raw']);
        }
        
        ob_clean(); 
        echo json_encode($resultados);
        exit;
    }

    if ($metodo === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (isset($data['id']) && isset($data['estado'])) {
            $id = $data['id'];
            $estado = strtoupper($data['estado']);
            $motivo = isset($data['motivo']) ? $data['motivo'] : null;
            $metodo_pago = isset($data['metodo_pago']) ? $data['metodo_pago'] : null;

            // Iniciamos transacción para asegurar que si falla el cupón, no se marque como pagado (o viceversa)
            $pdo->beginTransaction();

            // 1. Actualización de la reserva
            $stmt = $pdo->prepare("UPDATE reservas SET estado = ?, motivo_cancelacion = ?, metodo_pago = ? WHERE id = ?");
            $stmt->execute([$estado, $motivo, $metodo_pago, $id]);

            // 2. LÓGICA DE FIDELIZACIÓN (NUEVO)
            if ($estado === 'COMPLETADA') {
                // Obtenemos el user_id de esta reserva
                $stmtUser = $pdo->prepare("SELECT user_id FROM reservas WHERE id = ?");
                $stmtUser->execute([$id]);
                $reservaData = $stmtUser->fetch();
                $usuario_id = $reservaData['user_id'];

                if ($usuario_id) {
                    // Buscamos si hay una promoción de visitas activa hoy
                    $sql_promo = "SELECT id FROM promociones 
                                  WHERE activa = 1 AND tipo = 'VISITAS' 
                                  AND CURDATE() BETWEEN fecha_inicio AND fecha_fin 
                                  LIMIT 1";
                    $stmt_promo = $pdo->query($sql_promo);
                    $promo = $stmt_promo->fetch();

                    if ($promo) {
                        $promo_id = $promo['id'];
                        // Insertamos o actualizamos el contador de cupones del usuario
                        $sql_cupen = "INSERT INTO cupones_usuario (usuario_id, promocion_id, cupones_actuales, total_historico) 
                                      VALUES (?, ?, 1, 1) 
                                      ON DUPLICATE KEY UPDATE 
                                      cupones_actuales = cupones_actuales + 1,
                                      total_historico = total_historico + 1";
                        $stmt_sello = $pdo->prepare($sql_cupen);
                        $stmt_sello->execute([$usuario_id, $promo_id]);
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['status' => 'success']);
            exit;
        }
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}