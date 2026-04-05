<?php
require_once __DIR__ . '/../../../private/config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once 'admin_check.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
        $sql = "SELECT 
                    r.*, 
                    u_cliente.usuario AS cliente_nombre, 
                    u_cliente.telefono AS cliente_telefono, 
                    u_peluquero.nombre AS peluquero_nombre, 
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
        
        if (isset($data['id'], $data['estado'])) {
            $id = $data['id'];
            $estado = strtoupper($data['estado']);
            $metodo_pago = $data['metodo_pago'] ?? null;
            $usuario_id = $data['user_id'] ?? null;
            $promocion_id = $data['promocion_id'] ?? null;
            $tipo_promo = $data['tipo_promo'] ?? null;
            $revertir = isset($data['revertir_cupon']) && $data['revertir_cupon'] === true;

            $pdo->beginTransaction();

            // 1. Actualizar el estado de la reserva
            $stmt = $pdo->prepare("UPDATE reservas SET estado = ?, metodo_pago = ? WHERE id = ?");
            $stmt->execute([$estado, $metodo_pago, $id]);

            // 2. Lógica de Fidelidad
            if ($usuario_id && $promocion_id) {
                
                if ($estado === 'COMPLETADA') {
                    $log_detalle = "";

                    if ($tipo_promo === 'ETIQUETA') {
                        $sql = "INSERT INTO cupones_usuario (usuario_id, promocion_id, cupones_actuales, total_historico, premios_canjeados) 
                                VALUES (?, ?, 0, 1, 1) 
                                ON DUPLICATE KEY UPDATE 
                                premios_canjeados = premios_canjeados + 1, 
                                total_historico = total_historico + 1";
                        $pdo->prepare($sql)->execute([$usuario_id, $promocion_id]);
                        $log_detalle = "Premio de etiqueta otorgado.";
                    } 
                    else {
                        // Obtener el límite de la promoción (ej. 5)
                        $stmtP = $pdo->prepare("SELECT cupones_necesarios FROM promociones WHERE id = ?");
                        $stmtP->execute([$promocion_id]);
                        $necesarios = (int)$stmtP->fetchColumn();

                        /**
                         * NUEVA LÓGICA SOLICITADA:
                         * - Al llegar a la 6ª visita (cuando ya tiene 5 cupones):
                         * Mantenemos cupones_actuales en 5, subimos histórico a 6 y sumamos 1 a premios.
                         */
                        $sql = "INSERT INTO cupones_usuario (usuario_id, promocion_id, cupones_actuales, total_historico, premios_canjeados) 
                                VALUES (?, ?, 1, 1, 0) 
                                ON DUPLICATE KEY UPDATE 
                                total_historico = total_historico + 1,
                                premios_canjeados = CASE 
                                    WHEN cupones_actuales >= ? THEN premios_canjeados + 1 
                                    ELSE premios_canjeados 
                                END,
                                cupones_actuales = CASE 
                                    WHEN cupones_actuales >= ? THEN ? 
                                    ELSE cupones_actuales + 1 
                                END";
                        
                        // Enviamos $necesarios tres veces para cubrir las condiciones del CASE
                        $pdo->prepare($sql)->execute([$usuario_id, $promocion_id, $necesarios, $necesarios, $necesarios]);
                        $log_detalle = "Sello de visita añadido o premio canjeado.";
                    }

                    $stmtLog = $pdo->prepare("INSERT INTO logs_promociones (reserva_id, usuario_id, promocion_id, tipo_movimiento, tipo_promo, detalles) VALUES (?, ?, ?, 'SUMA', ?, ?)");
                    $stmtLog->execute([$id, $usuario_id, $promocion_id, $tipo_promo, $log_detalle]);
                }
                
                else if ($estado === 'PENDIENTE' && $revertir) {
                    $log_detalle = "Reversión de estado de reserva.";

                    if ($tipo_promo === 'ETIQUETA') {
                        $sql = "UPDATE cupones_usuario 
                                SET premios_canjeados = GREATEST(0, premios_canjeados - 1),
                                    total_historico = GREATEST(0, total_historico - 1)
                                WHERE usuario_id = ? AND promocion_id = ?";
                        $pdo->prepare($sql)->execute([$usuario_id, $promocion_id]);
                    } 
                    else {
                        $stmtP = $pdo->prepare("SELECT cupones_necesarios FROM promociones WHERE id = ?");
                        $stmtP->execute([$promocion_id]);
                        $necesarios = (int)$stmtP->fetchColumn();

                        $sql = "UPDATE cupones_usuario 
                                SET premios_canjeados = CASE 
                                        WHEN cupones_actuales = ? AND premios_canjeados > 0 THEN premios_canjeados - 1 
                                        ELSE premios_canjeados 
                                    END,
                                    cupones_actuales = GREATEST(0, cupones_actuales - 1),
                                    total_historico = GREATEST(0, total_historico - 1)
                                WHERE usuario_id = ? AND promocion_id = ?";
                        
                        $pdo->prepare($sql)->execute([$necesarios, $usuario_id, $promocion_id]);
                    }

                    $stmtLog = $pdo->prepare("INSERT INTO logs_promociones (reserva_id, usuario_id, promocion_id, tipo_movimiento, tipo_promo, detalles) VALUES (?, ?, ?, 'REVERSION', ?, ?)");
                    $stmtLog->execute([$id, $usuario_id, $promocion_id, $tipo_promo, $log_detalle]);
                }
            }

            $pdo->commit();
            ob_clean();
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}