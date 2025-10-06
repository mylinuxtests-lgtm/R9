<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION["id_perfil"] ?? 0;
$permiso_exportar = ($id_usuario == 1 || $id_usuario == 2);

if (!$permiso_exportar) {
    die("No tienes permisos para editar registros.");
}

require_once 'conn.php';

$conn = new mysqli($servername, $username, $db_password, $dbname, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

// Estadísticas por país
$sql_paises = "
SELECT 
    p.pais AS Pais,
    COUNT(*) AS Cantidad
FROM student s
JOIN paises p ON s.id_paises = p.id_paises
WHERE s.visible = 1
GROUP BY p.pais
ORDER BY Cantidad DESC
";

// Estadísticas por sexo
$sql_sexo = "
SELECT 
    sex.descripcion AS Sexo,
    COUNT(*) AS Cantidad
FROM student s
JOIN sexo sex ON s.id_sexo = sex.id_sexo
WHERE s.visible = 1
GROUP BY sex.descripcion
ORDER BY Cantidad DESC
";

// Estadísticas por rango de edad
$sql_edad = "
SELECT 
    CASE 
        WHEN edad BETWEEN 1 AND 17 THEN '1-17 años'
        WHEN edad BETWEEN 18 AND 25 THEN '18-25 años'
        WHEN edad BETWEEN 26 AND 35 THEN '26-35 años'
        WHEN edad BETWEEN 36 AND 50 THEN '36-50 años'
        ELSE '51+ años'
    END AS Rango_Edad,
    COUNT(*) AS Cantidad
FROM student 
WHERE visible = 1
GROUP BY Rango_Edad
ORDER BY MIN(edad)
";

$paises_result = $conn->query($sql_paises);
$sexo_result = $conn->query($sql_sexo);
$edad_result = $conn->query($sql_edad);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas Generales</title>
    <link rel="stylesheet" href="style.css" media="screen" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="user-header">
        <h2>Usuario: <strong><?php echo htmlspecialchars($_SESSION["usuario"]); ?></strong></h2>
        <a href="students.php">Volver a Estudiantes</a>
        <a href="login.php?action=logout">Cerrar sesión</a>
    </div>

    <div class="container-fluid">
        <div class="form-container">
            <h2>Estadísticas Generales de la base de datos </h2>
        </div>

        <div class="row justify-content-center">
            <!-- Estadísticas por País -->
            <div class="col-md-6">
                <div class="form-box">
                    <h3>Estudiantes por País</h3>
                    <?php if ($paises_result->num_rows > 0): ?>
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>País</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $paises_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['Pais']); ?></td>
                                        <td><?php echo $row['Cantidad']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay datos disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estadísticas por Sexo -->
            <div class="col-md-6">
                <div class="form-box">
                    <h3>Estudiantes por Sexo</h3>
                    <?php if ($sexo_result->num_rows > 0): ?>
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>Sexo</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $sexo_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['Sexo']); ?></td>
                                        <td><?php echo $row['Cantidad']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay datos disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estadísticas por Edad -->
            <div class="col-md-6">
                <div class="form-box">
                    <h3>Estudiantes por Rango de Edad</h3>
                    <?php if ($edad_result->num_rows > 0): ?>
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>Rango de Edad</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $edad_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['Rango_Edad']); ?></td>
                                        <td><?php echo $row['Cantidad']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay datos disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>