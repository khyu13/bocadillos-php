<?php
session_start();
date_default_timezone_set('Europe/Madrid');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header('Location: inicio.php');
    exit;
}

$userId = $_SESSION['user_id'];
$fecha = date("l"); // Obtiene el día de la semana en inglés

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=appbocatas;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Error BD: ' . $e->getMessage());
}

// Procesar cancelación
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_pedido'])) {
    $stmtCancel = $pdo->prepare("UPDATE pedido SET retirado = 1 WHERE id_usuario = ? AND retirado = 0");
    $stmtCancel->execute([$userId]);
    $stmtCancel = $pdo->prepare("UPDATE pedido SET retirado = 1 WHERE id_usuario = ? AND retirado = 0");
    $stmtCancel->execute([$userId]);

    header('Location: pedido.php'); // ← redirige después de cancelar
exit;

}

// Obtener pedido activo
$stmt = $pdo->prepare("
    SELECT p.*, b.nombre AS nombre_bocadillo, b.pvp as precio
    FROM pedido p
    JOIN bocadillo b ON p.id_bocadillo = b.id_bocadillo
    WHERE p.id_usuario = ? AND p.retirado = 0
    LIMIT 1");

$stmt->execute([$userId]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);
// Nombre del alumno
$alumnoNombre = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedido</title>
    <link rel="stylesheet" href="css/bocadillo.css">
    <title>Bocatas Campico – <?php echo $fecha ?></title>
</head>
<body>
     <div class="top-bar">
    <div class="top-bar-content">
      <span><img src="img/logoCampico.png" alt="Logo"></span>
      <h2><button1><a href="historial.php">Historial</a></button1></h2>
      <h2><button1><?php echo $alumnoNombre ?></button1></h2>
      <h2>
        <form action="logout.php" method="post">
          <button type="submit" class="logout-btn">Cerrar sesión</button>
        </form>
      </h2>
    </div>
  </div>



<?php if ($mensaje): ?>
    <p class="mensaje-exito"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>
<div id= "contenedor2">
    <h1>Gestión de Pedido</h1>
     <div class="box">
                <?php if ($pedido): ?>
                <h2>Tu pedido activo</h2>
                <input type="hidden" name="id_bocadillo" value="<?= $bocadillos_F['id_bocadillo'] ?>">
                <input type="hidden" name="cantidad" value="1">
                <div class="info">
                <p><strong>Bocadillo:</strong> <?= htmlspecialchars($pedido['nombre_bocadillo']) ?></p>
                <p><strong>Cantidad:</strong> <?= (int)$pedido['cantidad'] ?></p>
                <p><strong>Precio unitario:</strong> <?= number_format($pedido['precio'], 2) ?> €</p>
                <p><strong>Total:</strong> <?= number_format($pedido['precio'] * $pedido['cantidad'], 2) ?> €</p>
                <p><strong>Alergenos:</strong></p>
                <div class="alergenos">
                <img src="img/huevos.png" alt="Huevos">
                <img src="img/gluten.png" alt="Gluten">
                <img src="img/lactosa.png" alt="Lácteos">
              </div>
                </div>

                <form method="post">
                 <button type="submit" name="cancelar_pedido" onclick="return confirm('¿Seguro que quieres cancelar el pedido?')">Cancelar pedido</button>
                </form>
                <?php else: ?>
                <p>No tienes ningún pedido activo.</p>
                 <?php endif; ?>


                <p><a href="inicio.php" class="volver-inicio">Volver al inicio</a></p>

            </div>
        </div>
    </body>
</html>
