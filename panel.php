<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Reservas - Rúben Peluqueros</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h1>Bienvenido a tu panel</h1>
    <p>Aquí podrás reservar cita, ver tu historial, etc.</p>
    <!-- Aquí irá tu calendario, formulario de reserva, etc. -->
  </div>
</body>
</html>