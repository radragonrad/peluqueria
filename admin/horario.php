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

// Procesar actualización
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_horario') {
    $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    
    $updates = [];
    $params = [];
    
    foreach ($dias as $dia) {
        $horario = trim($_POST["horario_$dia"] ?? '');
        $activo = isset($_POST["activo_$dia"]) ? 1 : 0;
        
        $updates[] = "$dia = ?, activo_$dia = ?";
        $params[] = $horario;
        $params[] = $activo;
    }
    
    $sql = "UPDATE horario SET " . implode(', ', $updates) . " WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $success = "Horario actualizado correctamente.";
}

// Obtener horario actual
$stmt = $pdo->query("SELECT * FROM horario WHERE id = 1");
$horario = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Horario - Admin</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
    .day-row {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.8rem;
      margin: 0.5rem 0;
      background: rgba(255,255,255,0.03);
      border-radius: 8px;
    }
    .day-row label {
      width: 120px;
      font-weight: bold;
    }
    .day-row input[type="text"] {
      flex: 1;
      padding: 0.4rem;
      background: #1a1a1a;
      color: white;
      border: 1px solid #333;
      border-radius: 4px;
    }
    .day-row input[type="checkbox"] {
      width: 20px;
      height: 20px;
    }
    .btn-save-all {
      background: #e75480;
      color: white;
      border: none;
      padding: 0.6rem 1.5rem;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      margin-top: 1rem;
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
      <li><a href="horario.php" class="active">🕒 Horario</a></li>
      <li><a href="reservas.php">📅 Reservas</a></li>
      <li><a href="../logout.php">🚪 Cerrar sesión</a></li>
    </ul>
  </aside>

  <!-- Contenido principal -->
  <main class="main-content">
    <h1>Configuración de Horario</h1>
    
    <?php if (isset($success)): ?>
      <div class="message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="action" value="update_horario">
      
      <?php
      $dias_semana = [
          'lunes' => 'Lunes',
          'martes' => 'Martes',
          'miercoles' => 'Miércoles',
          'jueves' => 'Jueves',
          'viernes' => 'Viernes',
          'sabado' => 'Sábado',
          'domingo' => 'Domingo'
      ];
      
      foreach ($dias_semana as $clave => $nombre): 
        $horario_dia = $horario[$clave] ?? '';
        $activo = $horario["activo_$clave"] ?? 0;
      ?>
        <div class="day-row">
          <label><?= $nombre ?></label>
          <input type="text" name="horario_<?= $clave ?>" value="<?= htmlspecialchars($horario_dia) ?>" placeholder="Ej: 9:30 - 20:00">
          <label style="display:flex;align-items:center;gap:0.5rem;">
            <input type="checkbox" name="activo_<?= $clave ?>" <?= $activo ? 'checked' : '' ?>>
            Activo
          </label>
        </div>
      <?php endforeach; ?>
      
      <button type="submit" class="btn-save-all">Guardar Todo</button>
    </form>
  </main>

  <script>
    // Toggle menu en móvil
    document.getElementById('menuToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('open');
    });
  </script>
</body>
</html>