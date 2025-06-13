<?php
session_start();
date_default_timezone_set('Europe/Madrid');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header('Location: inicio.php');
    exit;
}

$userId = $_SESSION['user_id'];
$alumnoNombre = $_SESSION['user_name'];

try {
    $pdo = new PDO('mysql:host=localhost;dbname=appbocatas;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Filtros
$where = "p.id_usuario = :id AND p.retirado = 1";
$params = ['id' => $userId];

if (!empty($_GET['mes'])) {
    $where .= " AND MONTH(p.hora_pedido) = :mes";
    $params['mes'] = $_GET['mes'];
}

if (!empty($_GET['anio'])) {
    $where .= " AND YEAR(p.hora_pedido) = :anio";
    $params['anio'] = $_GET['anio'];
}

if (!empty($_GET['bocadillo'])) {
    $where .= " AND b.nombre LIKE :bocadillo";
    $params['bocadillo'] = '%' . $_GET['bocadillo'] . '%';
}

$sql = "
    SELECT p.id_pedido, p.hora_pedido, b.nombre AS bocadillo_nombre, b.tipo AS bocadillo_tipo,
           p.cantidad, b.pvp, (p.cantidad * b.pvp) AS total
    FROM pedido p
    JOIN bocadillo b ON b.id_bocadillo = p.id_bocadillo
    WHERE $where
    ORDER BY p.hora_pedido DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculamos total
$total = 0;
foreach ($pedidos as $p) {
    $total += $p['total'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Pedidos</title>
  <link rel="stylesheet" href="css/historial.css">
</head>
<body>

<div class="top-bar">
  <div class="top-bar-content">
    <span><img src="img/logoCampico.png" alt="Logo"></span>
    <h2><button1><a href="bocadillo.php">Volver</a></button1></h2>
    <h2><button1><?= $alumnoNombre ?></button1></h2>
    <h2>
      <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">Cerrar sesión</button>
      </form>
    </h2>
  </div>
</div>

<div id="contenedor3">
  <h1>Historial de Pedidos</h1>

  <!-- Filtros -->
  <form method="get" class="filtros">
    <label>Mes:
      <select name="mes">
        <option value="">Todos</option>
        <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>" <?= ($_GET['mes'] ?? '') == $m ? 'selected' : '' ?>><?= $m ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label>Año:
      <select name="anio">
        <option value="">Todos</option>
        <?php for ($a = 2023; $a <= date('Y'); $a++): ?>
          <option value="<?= $a ?>" <?= ($_GET['anio'] ?? '') == $a ? 'selected' : '' ?>><?= $a ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label>Bocadillo:
      <input type="text" name="bocadillo" value="<?= htmlspecialchars($_GET['bocadillo'] ?? '') ?>">
    </label>
    <button type="submit">Filtrar</button>
  </form>

  <?php if (count($pedidos) > 0): ?>
    <div class="total-final">
      Total gastado: <?= number_format($total, 2) ?> €
    </div>
  <?php endif; ?>

  <?php if (count($pedidos) > 0): ?>
    <?php foreach ($pedidos as $p): ?>
      <div class="pedido-card">
        <h4><?= date('d/m/Y H:i', strtotime($p['hora_pedido'])) ?></h4>
        <p><strong>Bocadillo:</strong> <?= $p['bocadillo_nombre'] ?> (<?= $p['bocadillo_tipo'] ?>)</p>
        <p><strong>Cantidad:</strong> <?= $p['cantidad'] ?></p>
        <p><strong>Precio:</strong> <?= number_format($p['pvp'], 2) ?> €</p>
        <p><strong>Total:</strong> <?= number_format($p['total'], 2) ?> €</p>
        <span class="status entregado">Entregado</span>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="margin: 2em;">No se encontraron pedidos con los filtros seleccionados.</p>
  <?php endif; ?>
</div>

</body>
</html>
