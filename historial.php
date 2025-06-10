<?php
date_default_timezone_set('Europe/Madrid');
session_start();

// Verifica que el usuario es un alumno
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header('Location: inicio.php');
    exit;
}

$userId = $_SESSION['user_id'];
$alumnoNombre = $_SESSION['user_name'];

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

// Pedidos pendientes
$stmtPend = $pdo->prepare(
    "SELECT p.id_pedido, p.hora_pedido, b.nombre AS bocadillo_nombre, b.tipo AS bocadillo_tipo, p.cantidad
     FROM pedido p
     JOIN bocadillo b ON b.id_bocadillo = p.id_bocadillo
     WHERE p.id_usuario = ? AND p.retirado = 0
     ORDER BY p.hora_pedido DESC"
);
$stmtPend->execute([$userId]);
$pendientes = $stmtPend->fetchAll(PDO::FETCH_ASSOC);

// Pedidos entregados
$stmtEnt = $pdo->prepare(
    "SELECT p.id_pedido, p.hora_pedido, b.nombre AS bocadillo_nombre, b.tipo AS bocadillo_tipo, p.cantidad
     FROM pedido p
     JOIN bocadillo b ON b.id_bocadillo = p.id_bocadillo
     WHERE p.id_usuario = ? AND p.retirado = 1
     ORDER BY p.hora_pedido DESC"
);
$stmtEnt->execute([$userId]);
$entregados = $stmtEnt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <link rel="stylesheet" href="css/historial.css">
  <title>Historial de Pedidos</title>
 

</head>
<body>
  <div class="top-bar">
    <div class="top-bar-content">
      <span><img src="img/logoCampico.png" alt="Logo"></span>
      <h2><button1><a href="bocadillo.php">Menú</a></button1></h2>
      <h2><button1><?php echo $alumnoNombre?></button1></h2>
      <h2>
        <form action="logout.php" method="post">
          <button type="submit" class="logout-btn">Cerrar sesión</button>
        </form>
      </h2>
    </div>
  </div>

  <div id="historial">
    <div class="historial-sections">
      <div class="historial-section">
        <h1>Pedidos Pendientes</h1>
        <?php if (empty($pendientes)): ?>
          <p>No tienes pedidos pendientes.</p>
        <?php else: ?>
          <?php foreach ($pendientes as $p): ?>
            <div class="pedido-card">
              <h4><?= date('d/m/Y H:i', strtotime($p['hora_pedido'])) ?></h4>
              <p><strong>Bocadillo:</strong> <?php echo $p['bocadillo_nombre'] ?> <em>(<?= $p['bocadillo_tipo']==='caliente'?'Caliente':'Frío' ?>)</em></p>
              <p><strong>Cantidad:</strong> <?= (int)$p['cantidad'] ?></p>
              <span class="status pendiente">Pendiente</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="historial-section">
        <h1>Pedidos Entregados</h1>
        <?php if (empty($entregados)): ?>
          <p>No tienes pedidos entregados.</p>
        <?php else: ?>
          <?php foreach ($entregados as $p): ?>
            <div class="pedido-card">
              <h4><?= date('d/m/Y H:i', strtotime($p['hora_pedido'])) ?></h4>
              <p><strong>Bocadillo:</strong> <?php echo $p['bocadillo_nombre']?> <em>(<?= $p['bocadillo_tipo']==='caliente'?'Caliente':'Frío' ?>)</em></p>
              <p><strong>Cantidad:</strong> <?= (int)$p['cantidad'] ?></p>
              <span class="status entregado">Entregado</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>