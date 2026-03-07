<?php
session_start();
$codigo_secreto = "RBTEST2026"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_codigo = $_POST['codigo'] ?? '';
    if ($input_codigo === $codigo_secreto) {
        $_SESSION['acceso_test'] = true;
        header("Location: /");
        exit;
    } else {
        $error = "Código incorrecto";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sitio en Pruebas</title>
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #f4f4f4; }
        .box { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 80%; margin-bottom: 10px; }
        button { padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Acceso Privado</h2>
        <p>Introduce el código para ver los avances:</p>
        <form method="POST">
            <input type="password" name="codigo" placeholder="Código de acceso">
            <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
            <br>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>