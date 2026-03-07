<?php
session_start();
require_once '../backend/api/common.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

$user = getUserById($_SESSION['user_id']);
if ($user['rol'] !== 'admin') {
    die("Acceso denegado.");
}

$pdo = getDB();
$stmt = $pdo->query("SELECT id, email, username, activo, created_at, fecha_activacion FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Usuarios - Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h2>Lista de Usuarios</h2>
    <table>
      <thead>
        <tr>
          <th>Email</th>
          <th>Usuario</th>
          <th>Activo</th>
          <th>Registrado</th>
          <th>Activado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= $u['activo'] ? '✅' : '❌' ?></td>
          <td><?= $u['created_at'] ?></td>
          <td><?= $u['fecha_activacion'] ?? '—' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>