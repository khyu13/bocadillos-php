<?php
session_start();
// Protege sólo a usuarios de cocina
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'cocina') {
    header('Location: inicio.php');
    exit;
}

$error = '';
// Conexión común para todo el script
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=appbocatas;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Error en la base de datos: '. $e->getMessage());
}

// Si se envía un POST de entrega, actualizamos y recargamos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'])) {
    $idPedido = (int) $_POST['id_pedido'];
    $up = $pdo->prepare('UPDATE pedido SET retirado = 1 WHERE id_pedido = ?');
    $up->execute([$idPedido]);
    header('Location: cocina.php');
    exit;
}

// Paginación
$total_rows = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Contar total de pedidos pendientes
$countSql = 'SELECT COUNT(*) FROM pedido WHERE retirado = 0';
$total = (int) $pdo->query($countSql)->fetchColumn();
$total_pages = $total ? (int) ceil($total / $total_rows) : 1;
if ($page > $total_pages) $page = $total_pages;

// Consulta de pedidos pendientes con límite
$offset = ($page - 1) * $total_rows;
$sql = "
    SELECT
        p.id_pedido,
        p.hora_pedido,
        CONCAT(a.nombre,' ',a.apellidos) AS alumno_nombre,
        b.nombre AS bocadillo_nombre,
        b.tipo   AS bocadillo_tipo
    FROM pedido p
    JOIN alumno a ON a.id_usuario = p.id_usuario
    JOIN bocadillo b ON b.id_bocadillo = p.id_bocadillo
    WHERE p.retirado = 0
    ORDER BY p.hora_pedido ASC
    LIMIT :offset, :limit
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $total_rows, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <link rel="stylesheet" href="css/cocina.css">
  <title>Cocina – Pedidos</title>
</head>
<body>
  <div class="top-bar">
      <div class="top-bar-content">
        <span><img src="img/logoCampico.png" alt="Logo"></span>
        <h2>
          <form action="logout.php" method="post">
            <button type="submit" class="logout-btn">Cerrar sesión</button>
          </form>
        </h2>
      </div>
    </div>
  <div id="cont">


<header>
  <div id="titulo"><strong>Pedidos Recibidos</strong></div>
  <form action="logout.php" method="post" style="position:absolute; top:1rem; right:1rem;">
    <button type="submit" class="logout-btn">Cerrar sesión</button>
  </form>
</header>

<div id="prin">
  <?php if ($error): ?>
    <p class="error"><?php echo $error ?></p>
  <?php elseif (empty($rows)): ?>
    <p>No hay pedidos realizados aún.</p>
  <?php else: ?>
    <?php foreach ($rows as $row): ?>
      <div class="carta">
        <p><strong><?php echo $row['alumno_nombre'] ?></strong></p>
        <p>
          <?php echo $row['bocadillo_nombre'] ?>
          (<?= $row['bocadillo_tipo'] === 'caliente' ? 'Caliente' : 'Frío' ?>)
        </p>
        <p class="hora">Pedido a las <?= date('H:i', strtotime($row['hora_pedido'])) ?></p>
        <form method="post" class="entregar-form">
          <input type="hidden" name="id_pedido" value="<?= (int)$row['id_pedido'] ?>">
          <button type="submit" class="done">ENTREGAR</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="cocina.php?page=1">« Primera</a>
    <a href="cocina.php?page=<?= $page - 1 ?>">‹ Anterior</a>
  <?php endif; ?>
  <span>Página <?= $page ?> de <?= $total_pages ?></span>
  <?php if ($page < $total_pages): ?>
    <a href="cocina.php?page=<?= $page + 1 ?>">Siguiente ›</a>
    <a href="cocina.php?page=<?= $total_pages ?>">Última »</a>
  <?php endif; ?>
</div>
```

  </div>
</body>
</html>
