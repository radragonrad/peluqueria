<?php
// backend/api/get_horario_tienda.php


require_once __DIR__ . '/../../../private/config/db.php';
try {
    // 1. Obtener horario semanal normal
    $stmt = $pdo->query("SELECT id_dia, dia_semana, abierto, h_apertura_1, h_cierre_1, h_apertura_2, h_cierre_2 FROM horarios ORDER BY id_dia ASC");
    $horarioNormal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Comprobar excepciones para HOY y MAÑANA
    // Asumo que la tabla tiene columnas 'fecha' y 'cerrado' (1 para cerrado)
    $stmtEx = $pdo->prepare("SELECT fecha, descripcion, solo_tramo, cerrado,
                         h_inicio, h_fin
                         FROM horario_excepciones 
                         WHERE fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");
    $stmtEx->execute();
    $excepciones = $stmtEx->fetchAll(PDO::FETCH_ASSOC);

    // Enviamos ambos datos en un solo JSON
    echo json_encode([
        'semanal' => $horarioNormal,
        'excepciones' => $excepciones
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}