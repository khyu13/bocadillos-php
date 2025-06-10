<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Recogemos datos del formulario
    $user = trim($_POST['user'] ?? '');
    $pasw = trim($_POST['pasw'] ?? '');

    if ($user === '' || $pasw === '') {
        $error = 'Por favor, completa ambos campos.';
    } else {
        //Conexión a la BD
        $host   = 'localhost';
        $dbname = 'appbocatas';
        $dbuser = 'root';
        $dbpass = '';
        $dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $dbuser, $dbpass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // 3) Buscamos por correo O DNI en Usuario
            $sql ="
                SELECT 
                    u.id_usuario, 
                    u.contrasena, 
                    COALESCE(a.nombre,'') AS nombre, 
                    COALESCE(a.apellidos,'') AS apellidos,
                    u.rol
                FROM Usuario u
                LEFT JOIN Alumno a ON a.id_usuario = u.id_usuario
                WHERE u.correo = :u OR u.id_usuario = :u
                LIMIT 1
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['u' => $user]);

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // 4) Usuario encontrado → validamos contraseña
                if ($pasw === $row['contrasena']) {
                    // Credenciales OK → guardamos sesión
                    $_SESSION['user_id']   = $row['id_usuario'];
                    $_SESSION['user_name'] = trim($row['nombre'].' '.$row['apellidos']);
                    $_SESSION['user_role'] = $row['rol'];

                    // Redirige según rol (por ejemplo)
                    if ($row['rol'] === 'cocina') {
                        header('Location: cocina.php');
                    }elseif($row['rol'] === 'admin') {
                        header('Location: admin.php');
                    }
                    else {
                        header('Location: bocadillo.php');
                    } 
                    exit;
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'Usuario no encontrado.';
            }

        } catch (PDOException $e) {
            ($e->getMessage());
            $error = 'Error conectando con la base de datos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="shortcut icon" href="img/123.png"/>
</head>
<body>
    <div id="princ">
        <div id="cont">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div>
                    <img src="img/efaa.png" alt="Logo">
                    <h1>Iniciar Sesión</h1>
                </div>

                <?php if ($error): ?>
                    <div style="color:red; margin-bottom:1em;">
                        <?php echo $error ?>
                    </div>
                <?php endif; ?>

                <div class="input-cont">
                    <label for="user">
                        <input
                            type="text"
                            name="user"
                            id="user"
                            placeholder="Correo o DNI"
                            required
                        >
                        <img src="img/user.png" alt="Usuario" class="icono">
                    </label>
                </div>
                <div class="input-cont">
                    <label for="pasw">
                        <input
                            type="password"
                            name="pasw"
                            id="pasw"
                            placeholder="Contraseña"
                            required
                        >
                        <img src="img/secure.png" alt="Contraseña" class="icono">
                    </label>
                </div>

                <div id="olvidar">
                    <a href="recuperar.html"><p>¿Has olvidado la contraseña?</p></a>
                </div>
                <div id="enviar">
                    <input id="boton_inicio2" type="submit" value="Iniciar Sesión">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
