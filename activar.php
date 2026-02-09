<?php
$host = 'localhost';
$dbname = 'peluqueria';
$db_user = 'root';
$db_pass = '123456';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión");
}

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Token inválido.");
}

$token = $_GET['token'];
$stmt = $pdo->prepare("SELECT id, activo FROM users WHERE token_activacion = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Token no válido.");
}

if ($user['activo']) {
    echo "<h2 style='color:green;'>✅ Tu cuenta ya está activada.</h2>";
} else {
    $stmt = $pdo->prepare("UPDATE users SET activo = 1, token_activacion = NULL, fecha_activacion = NOW()  WHERE id = ?");
    $stmt->execute([$user['id']]);
    echo "<h2 style='color:green;'>✅ ¡Cuenta activada! Ya puedes <a href='login.html'>iniciar sesión</a>.</h2>";
}
?>