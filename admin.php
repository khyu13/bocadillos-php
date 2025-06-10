<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: inicio.php');
    exit;
}

$error = '';

// Parámetros
$total_rows = 10;
$curso  = trim($_GET['curso'] ?? '');
$nombre = trim($_GET['nombre'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=appbocatas;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Consulta de conteo
    $sql_count = "SELECT COUNT(*) FROM alumno a WHERE 1=1";
    $params = [];

    if ($curso !== '') {
        $sql_count .= " AND a.curso = :curso";
        $params[':curso'] = $curso;
    }
    if ($nombre !== '') {
        $sql_count .= " AND (a.nombre LIKE :nombre OR a.apellidos LIKE :nombre)";
        $params[':nombre'] = '%' . $nombre . '%';
    }

    $stmt = $pdo->prepare($sql_count);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
    $total_pages = $total > 0 ? (int)ceil($total / $total_rows) : 1;

    if ($page > $total_pages) {
        $page = $total_pages;
    }

    // Consulta de datos
    $offset = ($page - 1) * $total_rows;
    $sql = "
        SELECT 
            p.id_pedido,
            p.hora_pedido,
            p.cantidad,
            p.retirado,
            a.nombre AS alumno_nombre,
            a.apellidos AS alumno_apellidos,
            a.curso AS alumno_curso,
            b.nombre AS bocadillo,
            b.tipo AS bocadillo_tipo
        FROM pedido p
        JOIN alumno a ON a.id_usuario = p.id_usuario
        JOIN bocadillo b ON b.id_bocadillo = p.id_bocadillo
        WHERE 1=1
    ";

    if ($curso !== '') {
        $sql .= " AND a.curso = :curso";
    }
    if ($nombre !== '') {
        $sql .= " AND (a.nombre LIKE :nombre OR a.apellidos LIKE :nombre)";
    }

    $sql .= " ORDER BY p.hora_pedido ASC LIMIT :offset, :limit";

    $stmt = $pdo->prepare($sql);
    if ($curso !== '') {
        $stmt->bindValue(':curso', $curso, PDO::PARAM_STR);
    }
    if ($nombre !== '') {
        $stmt->bindValue(':nombre', '%' . $nombre . '%', PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $total_rows, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = 'Error en la base de datos: ' . $e->getMessage();
}

// URL base para la paginación
$baseQuery = ['curso' => $curso, 'nombre' => $nombre];
$baseUrl = 'admin.php?' . http_build_query($baseQuery);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <link rel="stylesheet" href="css/cocina.css">
  <title>Listado de Alumnos de EL CAMPICO</title>
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
      <div id="titulo"><strong>Listado de Alumnos EL CAMPICO</strong></div>
    </header>

    <!-- Filtros -->
    <form method="get" action="admin.php" id="filtro">
      <label for="curso">Filtrar por curso:</label>
      <select name="curso" id="curso">
        <option value="" <?= $curso === '' ? 'selected' : '' ?>>Todos</option>
        <option value="1 ESO" <?= $curso === '1 ESO' ? 'selected' : '' ?>>1 ESO</option>
        <option value="2 ESO" <?= $curso === '2 ESO' ? 'selected' : '' ?>>2 ESO</option>
        <option value="3 ESO" <?= $curso === '3 ESO' ? 'selected' : '' ?>>3 ESO</option>
        <option value="4 ESO" <?= $curso === '4 ESO' ? 'selected' : '' ?>>4 ESO</option>
        <option value="1 DAM" <?= $curso === '1 DAM' ? 'selected' : '' ?>>1 DAM</option>
        <option value="2 DAM" <?= $curso === '2 DAM' ? 'selected' : '' ?>>2 DAM</option>
      </select>
      <button type="submit">Filtrar</button>

      <label for="nombre">Buscar por nombre:</label>
      <input type="text" name="nombre" id="nombre" value="<?php echo $nombre ?>">

      <button type="submit">Filtrar</button>
      <button><a href="admin.php" class="btn-reset">Quitar filtros</a></button>
      
    </form>

    <div id="prin">
      <?php if ($error): ?>
        <p class="error"><?php echo $error ?></p>
      <?php elseif (empty($rows)): ?>
        <p>No hay alumnos que mostrar.</p>
      <?php else: ?>
        <?php foreach ($rows as $row): ?>
          <div class="carta">
            <p><?php echo $row['alumno_nombre'] . ' ' . $row['alumno_apellidos']?></p>
            <p>Curso: <?php echo $row['alumno_curso'] ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- paginación -->
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="<?= $baseUrl ?>&page=1">« Primera</a>
        <a href="<?= $baseUrl ?>&page=<?= $page - 1 ?>">‹ Anterior</a>
      <?php endif; ?>

      <span>Página <?= $page ?> de <?= $total_pages ?></span>

      <?php if ($page < $total_pages): ?>
        <a href="<?= $baseUrl ?>&page=<?= $page + 1 ?>">Siguiente ›</a>
        <a href="<?= $baseUrl ?>&page=<?= $total_pages ?>">Última »</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
