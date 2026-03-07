<?php
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'peluqueria';
        $db_user = 'root';
        $db_pass = '123456';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    return $pdo;
}

function getUserById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, email, rol FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}