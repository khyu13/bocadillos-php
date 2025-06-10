<?php

date_default_timezone_set('Europe/Madrid');
session_start();
// Verifica que el usuario es un alumno
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header('Location: inicio.php');
    exit;
}

$fecha = date("l"); // Obtiene el día de la semana en inglés



// Conexión a la BD
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=appbocatas;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Error en la base de datos: '. $e->getMessage());
}
//bocadillos frios
$stmtF = $pdo->prepare(
    "SELECT id_bocadillo, nombre, descripcion, pvp, tipo
     FROM bocadillo
     WHERE dia = '$fecha' and tipo = 'Frío'
     ORDER BY id_bocadillo ASC
     LIMIT 1"
);
$stmtF->execute();
$bocadillos_F = $stmtF->fetch(PDO::FETCH_ASSOC);

// bocadillos calientes
$stmtC = $pdo->prepare(
    "SELECT id_bocadillo, nombre, descripcion, pvp, tipo
     FROM bocadillo
     WHERE dia = '$fecha' and tipo = 'Caliente'
     ORDER BY id_bocadillo ASC
     LIMIT 1"
);
$stmtC->execute();
$bocadillos_C = $stmtC->fetch(PDO::FETCH_ASSOC);
// Combina los resultados de ambos tipos de bocadillos


// Alerta tras pedido
if (isset($_GET['pedido'])) {
    if ($_GET['pedido'] === 'ok') {
        echo "<script>alert('Pedido realizado correctamente');</script>";
    } elseif ($_GET['pedido'] === 'limit') {
        echo "<script>alert('Ya tienes un pedido pendiente. Entrega el anterior antes de pedir otro.');</script>";
    }
}


// Nombre del alumno
$alumnoNombre = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  <div id="contenedor3">
    <h1><?php echo $fecha ?></h1>
    <div class="box">
      <?php if ($bocadillos_F): ?>
      <form action="pedido.php" method="post" class="bocadillo-form">
        <input type="hidden" name="id_bocadillo" value="<?= $bocadillos_F['id_bocadillo'] ?>">
        <input type="hidden" name="cantidad" value="1">
        <button type="submit" class="bocadillo-button">
          <div class="bocadillo">
            <img src="img/pavoyqueso.png">
            <div class="info">
              <p><strong>Bocadillo Frío</strong></p>
              <p><?php echo $bocadillos_F['nombre' ] ?></p>
              <p><?php echo $bocadillos_F['descripcion'] ?></p>
              <p><strong>Alergenos:</strong></p>
              <div class="alergenos">
                <img src="img/huevos.png" alt="Huevos">
                <img src="img/gluten.png" alt="Gluten">
                <img src="img/lactosa.png" alt="Lácteos">
              </div>
              <h3>PRECIO: <?php echo $bocadillos_F['pvp'] ?>€</h3>
            </div>
          </div>
        </button>
      </form>
      <?php endif; ?>
      <?php if ($bocadillos_C): ?>
      <form action="pedido.php" method="post" class="bocadillo-form">
        <input type="hidden" name="id_bocadillo" value="<?= $bocadillos_C['id_bocadillo'] ?>">
        <input type="hidden" name="cantidad" value="1">
        <button type="submit" class="bocadillo-button">
          <div class="bocadillo">
            <img src="img/baconyqueso.jpg">
            <div class="info">
              <p><strong>Bocadillo Caliente</strong></p>
              <p><?php echo $bocadillos_C['nombre'] ?></p>
              <p><?php echo $bocadillos_C['descripcion'] ?></p>
              <p><strong>Alergenos:</strong></p>
              <div class="alergenos">
                <img src="img/huevos.png" alt="Huevos">
                <img src="img/gluten.png" alt="Gluten">
                <img src="img/lactosa.png" alt="Lácteos">
              </div>
              <h3>PRECIO: <?php echo $bocadillos_C['pvp'] ?>€</h3>
            </div>
          </div>
        </button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
