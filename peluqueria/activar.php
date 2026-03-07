<?php
// Limpia cualquier salida anterior del servidor
while (ob_get_level()) { ob_end_clean(); }

// Fuerza la cabecera antes de cualquier otra cosa
header("Content-Type: text/html; charset=utf-8");
header("X-Content-Type-Options: nosniff");

require_once __DIR__ . '/../private/config/db.php';

$mensaje = "";
$tipo_alerta = "info";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    try {
        $stmt = $pdo->prepare("SELECT id, activo FROM usuarios WHERE token_activacion = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['activo'] == 0) {
                $update = $pdo->prepare("UPDATE usuarios SET activo = 1, token_activacion = NULL, fecha_activacion = now() WHERE id = ?");
                $update->execute([$user['id']]);
                $mensaje = "¡Cuenta activada con éxito! Ya puedes iniciar sesión.";
                $tipo_alerta = "success";
            } else {
                $mensaje = "Esta cuenta ya había sido activada anteriormente.";
                $tipo_alerta = "warning";
            }
        } else {
            $mensaje = "El enlace de activación no es válido o ha expirado.";
            $tipo_alerta = "danger";
        }
    } catch (PDOException $e) {
        $mensaje = "Error en el servidor. Por favor, inténtalo más tarde.";
        $tipo_alerta = "danger";
    }
} else {
    $mensaje = "No se ha proporcionado un token de activación.";
    $tipo_alerta = "danger";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación | R. Gutiérrez Studio</title>
    <style>
        body { background-color: #1a1a1a; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: sans-serif; color: white; }
        .container { text-align: center; background: #2a2a2a; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); max-width: 400px; width: 90%; border: 1px solid #bc9667; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; }
        .success { background: rgba(22, 101, 52, 0.3); color: #dcfce7; border: 1px solid #166534; }
        .danger { background: rgba(153, 27, 27, 0.3); color: #fee2e2; border: 1px solid #991b1b; }
        .warning { background: rgba(133, 77, 14, 0.3); color: #fef9c3; border: 1px solid #854d0e; }
        .btn { display: inline-block; padding: 12px 24px; background: #bc9667; color: #1a1a1a; text-decoration: none; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color: #bc9667;">R. Gutiérrez Studio</h2>
        <div class="alert <?php echo $tipo_alerta; ?>">
            <?php echo $mensaje; ?>
        </div>
        <a href="/login" class="btn">Ir al Inicio de Sesión</a>
    </div>
</body>
</html>