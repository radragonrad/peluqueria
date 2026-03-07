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

$pdo = getDB();

// Procesar cancelación
if (isset($_POST['action']) && $_POST['action'] === 'cancelar') {
    $reserva_id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
    $stmt->execute([$reserva_id]);
    $success = "Reserva cancelada correctamente.";
}

// Obtener reservas
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT r.id, r.fecha, r.hora, 
           u.email as cliente_email, 
           s.nombre as servicio_nombre, s.precio
    FROM reservas r
    JOIN users u ON r.user_id = u.id
    JOIN servicios s ON r.servicio_id = s.id
    WHERE r.fecha >= ?
    ORDER BY r.fecha, r.hora
");
$stmt->execute([$fecha_filtro]);
$reservas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reservas - Admin</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
    .filters {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
      align-items: center;
    }
    .filters input[type="date"] {
      padding: 0.4rem;
      background: #1a1a1a;
      color: white;
      border: 1px solid #333;
      border-radius: 4px;
    }
    .btn-filter {
      background: #e75480;
      color: white;
      border: none;
      padding: 0.4rem 0.8rem;
      border-radius: 4px;
      cursor: pointer;
    }
    .reservation-card {
      background: rgba(255,255,255,0.03);
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .reservation-info {
      flex: 1;
    }
    .reservation-info h3 {
      color: #e75480;
      margin-bottom: 0.5rem;
    }
    .btn-cancel {
      background: #f44336;
      color: white;
      border: none;
      padding: 0.4rem 0.8rem;
      border-radius: 4px;
      cursor: pointer;
    }
    .message {
      padding: 0.75rem;
      margin: 1rem 0;
      border-radius: 8px;
      background: rgba(76, 175, 80, 0.2);
      color: #4caf50;
    }
  </style>
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
      <li><a href="dashboard.php">📊 Usuarios</a></li>
      <li><a href="servicios.php">✂️ Servicios</a></li>
      <li><a href="horario.php">🕒 Horario</a></li>
      <li><a href="reservas.php" class="active">📅 Reservas</a></li>
      <li><a href="../logout.php">🚪 Cerrar sesión</a></li>
    </ul>
  </aside>

  <!-- Contenido principal -->
  <main class="main-content">
    <h1>Gestión de Reservas</h1>
    
    <?php if (isset($success)): ?>
      <div class="message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="filters">
      <label for="filter-date">Filtrar desde:</label>
      <input type="date" id="filter-date" value="<?= htmlspecialchars($fecha_filtro) ?>">
      <button class="btn-filter" onclick="filtrar()">Filtrar</button>
    </div>

    <!-- Lista de reservas -->
    <?php if (empty($reservas)): ?>
      <p>No hay reservas en esta fecha.</p>
    <?php else: ?>
      <?php foreach ($reservas as $r): ?>
        <div class="reservation-card">
          <div class="reservation-info">
            <h3><?= htmlspecialchars($r['servicio_nombre']) ?> - €<?= number_format($r['precio'], 2) ?></h3>
            <p><strong>Cliente:</strong> <?= htmlspecialchars($r['cliente_email']) ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($r['fecha'])) ?> a las <?= $r['hora'] ?></p>
          </div>
          <form method="POST" style="margin:0;">
            <input type="hidden" name="action" value="cancelar">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button type="submit" class="btn-cancel" onclick="return confirm('¿Cancelar esta reserva?')">Cancelar</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <script>
    // Toggle menu en móvil
    document.getElementById('menuToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('open');
    });
    
    // Filtrar por fecha
    function filtrar() {
      const fecha = document.getElementById('filter-date').value;
      window.location.href = `reservas.php?fecha=${fecha}`;
    }
  </script>
</body>
</html>