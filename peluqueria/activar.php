<?php
while (ob_get_level()) { ob_end_clean(); }

header("Content-Type: text/html; charset=utf-8");
header("X-Content-Type-Options: nosniff");

require_once __DIR__ . '/../private/config/db.php';
require_once __DIR__ . '/../private/includes/functions.php';

$mensaje    = "";
$tipo_alerta = "info";

if (isset($_GET['token'])) {
    // Limpiamos el token por si viene con espacios o encoding raro
    $token = trim(urldecode($_GET['token']));

    try {
        $stmt = $pdo->prepare("SELECT id, activo FROM usuarios WHERE token_activacion = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['activo'] == 0) {
                // ✅ Usamos prepared statement para la fecha también
                $update = $pdo->prepare("UPDATE usuarios SET activo = 1, token_activacion = NULL, fecha_activacion = ? WHERE id = ?");
                $update->execute([date("Y-m-d H:i:s"), $user['id']]);

                registrarLog($pdo, $user['id'], 'ACTIVACION_CUENTA', [
                    'motivo'     => 'Cuenta activada mediante enlace de correo',
                    'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
                ], 0, 'activar.php');

                $mensaje     = "¡Cuenta activada con éxito! Ya puedes iniciar sesión.";
                $tipo_alerta = "success";

            } else {
                registrarLog($pdo, $user['id'], 'ACTIVACION_DUPLICADA', [
                    'motivo' => 'Intento de activar una cuenta ya activa'
                ], 0, 'activar.php');

                $mensaje     = "Esta cuenta ya había sido activada anteriormente.";
                $tipo_alerta = "warning";
            }
        } else {
            // Intentamos loguear aunque no sepamos el usuario_id
            registrarLog($pdo, null, 'ACTIVACION_FALLIDA', [
                'motivo' => 'Token inválido o expirado',
                'token'  => substr($token, 0, 8) . '...'
            ], 0, 'activar.php');

            $mensaje     = "El enlace de activación no es válido o ha expirado.";
            $tipo_alerta = "danger";
        }
    } catch (PDOException $e) {
        error_log("Error en activar.php: " . $e->getMessage());
        $mensaje     = "Error en el servidor. Por favor, inténtalo más tarde.";
        $tipo_alerta = "danger";
    }
} else {
    $mensaje     = "No se ha proporcionado un token de activación.";
    $tipo_alerta = "danger";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación | Essencia Barber Study</title>
    <style>
        body { background-color: #1a1a1a; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: sans-serif; color: white; }
        .container { text-align: center; background: #2a2a2a; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); max-width: 400px; width: 90%; border: 1px solid #bc9667; }
        .icon { font-size: 56px; margin-bottom: 12px; }
        h2 { color: #bc9667; margin-top: 0; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; line-height: 1.5; }
        .success { background: rgba(22, 101, 52, 0.3); color: #dcfce7; border: 1px solid #166534; }
        .danger  { background: rgba(153, 27, 27, 0.3);  color: #fee2e2; border: 1px solid #991b1b; }
        .warning { background: rgba(133, 77, 14, 0.3);  color: #fef9c3; border: 1px solid #854d0e; }
        .btn { display: inline-block; padding: 12px 28px; background: #bc9667; color: #1a1a1a; text-decoration: none; border-radius: 6px; font-weight: bold; transition: opacity .2s; }
        .btn:hover { opacity: .85; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <?php
                if ($tipo_alerta === 'success')  echo '✅';
                elseif ($tipo_alerta === 'warning') echo '⚠️';
                else echo '❌';
            ?>
        </div>
        <h2>Essencia Barber Study</h2>
        <div class="alert <?php echo htmlspecialchars($tipo_alerta); ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
        <a href="/login" class="btn">Ir al Inicio de Sesión</a>
    </div>
</body>
</html>