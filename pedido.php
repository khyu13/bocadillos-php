<?php
date_default_timezone_set('Europe/Madrid');
session_start();
// Verifica que el usuario es un alumno
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header('Location: inicio.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Conexión a la BD
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=appbocatas;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Error BD: ' . $e->getMessage());
}

// Comprueba si ya tiene un pedido pendiente
$stmtChk = $pdo->prepare(
    'SELECT COUNT(*) FROM pedido WHERE id_usuario = ? AND retirado = 0');
    $stmtChk->execute([$userId]);
    $pendientes = (int)$stmtChk->fetchColumn();

if ($pendientes > 0) {
    // Ya tiene un pedido activo
    header('Location: gestion_pedido.php');
    exit;
}

// Procesa nuevo pedido
if (isset($_POST['id_bocadillo'])) {
    $idB = (int) $_POST['id_bocadillo'];
    $qty = isset($_POST['cantidad']) ? (int) $_POST['cantidad'] : 1;

    $stmt = $pdo->prepare(
        'INSERT INTO pedido (id_usuario, id_bocadillo, cantidad, hora_pedido, retirado)
         VALUES (?, ?, ?, NOW(), 0)'
    );
    $stmt->execute([$userId, $idB, $qty]);

    header('Location: gestion_pedido.php');
    exit;
}

// Si no hay POST válido
header('Location: bocadillo.php');
exit;
