<?php
session_start();

// Cerrar sesion
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_SESSION["usuario"])) {
    header("Location: students.php");
    exit();
}

require_once 'conn.php';

// Inicio de sesión
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["usuario"])) {
    $usuario = trim($_POST["usuario"]);
    $password_form = trim($_POST["password"]);

    if (empty($usuario) || empty($password_form)) {
        $error = "Por favor, rellene todos los campos.";
    } else {
        // Conectar a la base de datos
        $conn = new mysqli($servername, $username, $db_password, $dbname, $port);
        $conn->set_charset("utf8mb4");

        if ($conn->connect_error) {
            die("Error en la conexión: " . $conn->connect_error);
        }

        // Verificar credenciales
        $stmt = $conn->prepare("SELECT l.id_usuario, l.nombre_usuario, l.contrasena, l.id_perfil, p.perfil 
                               FROM id_usuario l 
                               JOIN id_perfil p ON l.id_perfil = p.id_perfil 
                               WHERE l.nombre_usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($password_form === $row['contrasena']) {
                $_SESSION["usuario"] = $row['nombre_usuario'];
                $_SESSION["id_usuario"] = $row['id_usuario'];
                $_SESSION["id_perfil"] = $row['id_perfil'];
                $_SESSION["perfil"] = $row['perfil'];

                header("Location: students.php");
                exit();
            } else {
                $error = "Los datos son incorrectos.";
            }
        } else {
            $error = "Los datos son incorrectos.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="style.css" media="screen" />
</head>

<body>
    <?php if (isset($_GET['message']) && $_GET['message'] == 'logout'): ?>
        <div class="login-success">Has cerrado sesión correctamente.</div>
    <?php endif; ?>

    <div class="login-container">
        <h2>Iniciar Sesión</h2>

        <?php if (!empty($error)): ?>
            <div class="login-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="post" autocomplete="off">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required autocomplete="off"
                    value="<?php echo isset($usuario) ? htmlspecialchars($usuario) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required autocomplete="off">
            </div>
            <div class="form-group">
                <input type="submit" value="Ingresar">
            </div>
        </form>
    </div>
</body>

</html>