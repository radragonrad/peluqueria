<?php
session_start();
require_once '../backend/api/common.php';

// Verificar acceso
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

$user = getUserById($_SESSION['user_id']);
if (!$user || $user['rol'] !== 'admin') {
    die("Acceso denegado.");
}

// Obtener lista de usuarios
$pdo = getDB();
$stmt = $pdo->query("
    SELECT id, email, username, activo, 
           DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as registrado,
           DATE_FORMAT(fecha_activacion, '%d/%m/%Y %H:%i') as activado
    FROM users 
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
  <!-- Botón de menú para móvil -->
  <button class="menu-toggle" id="menuToggle">☰</button>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h2>Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
      <li><a href="dashboard.php" class="active">📊 Usuarios</a></li>
      <li><a href="servicios.php">✂️ Servicios</a></li>
      <li><a href="horario.php">🕒 Horario</a></li>
      <li><a href="reservas.php">📅 Reservas</a></li>
      <li><a href="../logout.php">🚪 Cerrar sesión</a></li>
    </ul>
  </aside>

  <!-- Contenido principal -->
  <main class="main-content">
    <h1>Usuarios Registrados</h1>
    <p>Total: <?= count($users) ?> usuarios</p>

    <table class="users-table">
      <thead>
        <tr>
          <th>Email</th>
          <th>Usuario</th>
          <th>Estado</th>
          <th>Registrado</th>
          <th>Activado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td>
            <?php if ($u['activo']): ?>
              <span class="status-active">✅ Activo</span>
            <?php else: ?>
              <span class="status-inactive">❌ Inactivo</span>
            <?php endif; ?>
          </td>
          <td><?= $u['registrado'] ?></td>
          <td><?= $u['activado'] ?? '—' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>

  <script>
    // Toggle menu en móvil
    document.getElementById('menuToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('open');
    });
  </script>
</body>
</html>