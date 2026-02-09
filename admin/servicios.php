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
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $precio = (float)$_POST['precio'];
    $duracion = (int)$_POST['duracion'];
    $activo = isset($_POST['activo']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE servicios SET precio = ?, duracion_min = ?, activo = ? WHERE id = ?");
    $stmt->execute([$precio, $duracion, $activo, $id]);
    $success = "Servicio actualizado correctamente.";
}

// Obtener servicios
$stmt = $pdo->query("SELECT * FROM servicios ORDER BY nombre");
$servicios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gestión de Servicios - Admin</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
    .service-form {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin: 0.5rem 0;
      padding: 0.8rem;
      background: rgba(255,255,255,0.03);
      border-radius: 8px;
    }
    .service-form input[type="number"] {
      width: 100px;
      padding: 0.4rem;
      background: #1a1a1a;
      color: white;
      border: 1px solid #333;
      border-radius: 4px;
    }
    .service-form label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
    }
    .btn-save {
      background: #e75480;
      color: white;
      border: none;
      padding: 0.4rem 0.8rem;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
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
      <li><a href="servicios.php" class="active">✂️ Servicios</a></li>
      <li><a href="horario.php">🕒 Horario</a></li>
      <li><a href="reservas.php">📅 Reservas</a></li>
      <li><a href="../logout.php">🚪 Cerrar sesión</a></li>
    </ul>
  </aside>

  <!-- Contenido principal -->
  <main class="main-content">
    <h1>Gestión de Servicios</h1>
    
    <?php if (isset($success)): ?>
      <div class="message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" id="servicesForm">
      <input type="hidden" name="action" value="update">
      
      <?php foreach ($servicios as $s): ?>
        <div class="service-form">
          <strong style="flex: 1;"><?= htmlspecialchars($s['nombre']) ?></strong>
          
          <div>
            <label>Precio: €</label>
            <input type="number" step="0.01" name="precio[<?= $s['id'] ?>]" 
                   value="<?= number_format($s['precio'], 2, '.', '') ?>" required>
          </div>
          
          <div>
            <label>Duración (min):</label>
            <input type="number" name="duracion[<?= $s['id'] ?>]" 
                   value="<?= $s['duracion_min'] ?>" min="1" required>
          </div>
          
          <div>
            <label>
              <input type="checkbox" name="activo[<?= $s['id'] ?>]" 
                     <?= $s['activo'] ? 'checked' : '' ?>>
              Activo
            </label>
          </div>
          
          <input type="hidden" name="id" value="<?= $s['id'] ?>">
          <button type="submit" class="btn-save" formaction="?id=<?= $s['id'] ?>">Guardar</button>
        </div>
      <?php endforeach; ?>
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