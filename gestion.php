<?php
session_start();
require_once 'conn.php';

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $db_password, $dbname, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en la conexión: ' . $conn->connect_error]);
    exit();
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$id_usuario = $_SESSION["id_perfil"] ?? 0;
$permiso_editar = ($id_usuario == 1 || $id_usuario == 2);
$permiso_borrar = ($id_usuario == 1);
$permiso_exportar = ($id_usuario == 1 || $id_usuario == 2);
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos para Bootstrap Table
if ($action == 'obtener_datos') {
    if (!isset($_SESSION["usuario"])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No autorizado']);
        exit();
    }

    // Parámetros de paginación, búsqueda y ordenamiento
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 's.id_students';
    $order = isset($_GET['order']) ? trim($_GET['order']) : 'asc';

    // Validar y mapear campos de ordenamiento
    $allowed_sorts = [
        'ID' => 's.id_students',
        'Nombre' => 's.nombre',
        'Fecha_Registro' => 's.fecha_registro',
        'Fecha_Edicion' => 's.fecha_edicion'
    ];
    
    $sort_field = $allowed_sorts[$sort] ?? 's.id_students';
    $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';

    // Construir consulta base
    $where_condition = "WHERE s.visible = 1";

    // Aplicar búsqueda si existe
    if (!empty($search)) {
        $sanitized_search = $conn->real_escape_string($search);
        $where_condition .= " AND (s.nombre LIKE '%$sanitized_search%' OR 
                                sex.descripcion LIKE '%$sanitized_search%' OR 
                                s.especifique LIKE '%$sanitized_search%' OR 
                                s.edad LIKE '%$sanitized_search%' OR 
                                p.pais LIKE '%$sanitized_search%' OR 
                                s.telefono LIKE '%$sanitized_search%' OR 
                                s.correo LIKE '%$sanitized_search%' OR 
                                s.domicilio LIKE '%$sanitized_search%')";
    }

    // Obtener total de registros
    $count_sql = "SELECT COUNT(*) as total FROM student s 
                  JOIN sexo sex ON s.id_sexo = sex.id_sexo 
                  JOIN paises p ON s.id_paises = p.id_paises 
                  $where_condition";
    $count_result = $conn->query($count_sql);
    $total = $count_result->fetch_assoc()['total'];

    // Obtener datos paginados y ordenados
    $sql = "SELECT
              s.id_students AS ID,
              s.nombre AS Nombre,
              DATE_FORMAT(s.fecha_registro, '%d-%m-%Y %H:%i') AS Fecha_Registro,
              DATE_FORMAT(s.fecha_edicion, '%d-%m-%Y %H:%i') AS Fecha_Edicion
            FROM student s
            JOIN sexo sex ON s.id_sexo = sex.id_sexo
            JOIN paises p ON s.id_paises = p.id_paises
            $where_condition
            ORDER BY $sort_field $order
            LIMIT $offset, $limit";

    $result = $conn->query($sql);
    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    // Devolver datos en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'total' => $total,
        'rows' => $rows
    ]);
    exit();
}

// Obtener detalles de un estudiante específico
elseif ($action == 'obtener_detalles') {
    if (!isset($_SESSION["usuario"])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No autorizado']);
        exit();
    }

    if ($student_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID inválido']);
        exit();
    }

    $sql = "
    SELECT
      s.id_students   AS ID,
      s.nombre        AS Nombre,
      sex.descripcion AS Sexo,
      s.especifique   AS Especifique,  
      s.edad          AS Edad,
      DATE_FORMAT(s.nacimiento, '%d-%m-%Y') AS Fecha_Nacimiento,
      p.pais          AS Pais,
      s.telefono      AS Telefono,
      s.correo        AS Correo,
      s.domicilio     AS Domicilio,
      s.foto          AS Foto,
      s.lista         AS Lista,
      s.excel         AS Excel,
      DATE_FORMAT(s.fecha_registro, '%d-%m-%Y %H:%i') AS Fecha_Registro
    FROM student s
    JOIN sexo sex ON s.id_sexo = sex.id_sexo
    JOIN paises p ON s.id_paises = p.id_paises
    WHERE s.id_students = $student_id AND s.visible = 1
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Preparar datos para JSON
        $data = [
            'ID' => $row['ID'],
            'Nombre' => $row['Nombre'],
            'Sexo' => $row['Sexo'],
            'Especifique' => $row['Especifique'],
            'Edad' => $row['Edad'],
            'Fecha_Nacimiento' => $row['Fecha_Nacimiento'],
            'Pais' => $row['Pais'],
            'Telefono' => $row['Telefono'],
            'Correo' => $row['Correo'],
            'Domicilio' => $row['Domicilio'],
            'Foto' => $row['Foto'],
            'Lista' => $row['Lista'],
            'Excel' => $row['Excel'],
            'Fecha_Registro' => $row['Fecha_Registro']
        ];

        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Estudiante no encontrado']);
    }
    exit();
}

// Exportación completa a csv
elseif (isset($_GET['export']) && $_GET['export'] == 'all_csv' && $permiso_exportar) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=estudiantes_completo_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    
    // Encabezados completos
    fputcsv($output, [
        'ID',
        'Nombre', 
        'Sexo',
        'Especifique',
        'Edad',
        'Fecha de Nacimiento',
        'País',
        'Teléfono',
        'Correo',
        'Domicilio',
        'Foto',
        'Lista',
        'Excel',
        'Fecha de Registro',
        'Fecha de Edición'
    ]);

    $sql = "SELECT
              s.id_students AS ID,
              s.nombre AS Nombre,
              sex.descripcion AS Sexo,
              s.especifique AS Especifique,
              s.edad AS Edad,
              DATE_FORMAT(s.nacimiento, '%d-%m-%Y') AS Fecha_Nacimiento,
              p.pais AS Pais,
              s.telefono AS Telefono,
              s.correo AS Correo,
              s.domicilio AS Domicilio,
              s.foto AS Foto,
              s.lista AS Lista,
              s.excel AS Excel,
              DATE_FORMAT(s.fecha_registro, '%d-%m-%Y %H:%i') AS Fecha_Registro,
              DATE_FORMAT(s.fecha_edicion, '%d-%m-%Y %H:%i') AS Fecha_Edicion
            FROM student s
            JOIN sexo sex ON s.id_sexo = sex.id_sexo
            JOIN paises p ON s.id_paises = p.id_paises
            WHERE s.visible = 1
            ORDER BY s.id_students";

    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['ID'],
            $row['Nombre'],
            $row['Sexo'],
            $row['Especifique'] ?? '',
            $row['Edad'],
            $row['Fecha_Nacimiento'],
            $row['Pais'],
            $row['Telefono'],
            $row['Correo'],
            $row['Domicilio'],
            $row['Foto'],
            $row['Lista'],
            $row['Excel'],
            $row['Fecha_Registro'],
            $row['Fecha_Edicion']
        ]);
    }
    
    fclose($output);
    exit();
}

// Eliminación de registro
elseif ($action == 'eliminar') {
    if (!$permiso_borrar) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No tienes permisos para eliminar registros.']);
        exit();
    }

    if ($student_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID inválido']);
        exit();
    }

    // Verificar confirmación
    if (!isset($_GET['confirm']) || $_GET['confirm'] != 'true') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Confirmación requerida']);
        exit();
    }

    $id_usuario_borrador = $_SESSION["id_usuario"] ?? null;
    $sql = "UPDATE student SET visible = 0, fecha_borrado = NOW(), id_usuario_borrador = $id_usuario_borrador WHERE id_students = $student_id";

    if ($conn->query($sql) === TRUE) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al eliminar: ' . $conn->error]);
    }
    exit();
}

// Procesar actualización de registro
elseif ($action == 'actualizar') {
    if (!$permiso_editar) {
        die("No tienes permisos para editar registros.");
    }

    $student_id = intval($_POST['id']);
    if ($student_id <= 0) {
        die("ID inválido");
    }

    // Obtener los datos del formulario
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $sexo = $conn->real_escape_string($_POST['sexo']);
    $especifique = ($sexo == 'Otro' && isset($_POST['especifique'])) ? $conn->real_escape_string($_POST['especifique']) : "";
    $edad = intval($_POST['age']);
    $nacimiento = $conn->real_escape_string($_POST['bday']);
    $pais = $conn->real_escape_string($_POST['country']);
    $telefono = $conn->real_escape_string($_POST['phone']);
    $correo = $conn->real_escape_string($_POST['correo']);
    $domicilio = $conn->real_escape_string($_POST['domicilio']);

    // Relacionar sexo a ID
    $id_sexo = match ($sexo) {
        'Masculino' => 1,
        'Femenino' => 2,
        'Otro' => 3,
        default => 1
    };

    // Relacionar país a ID
    $paises_map = [
        'Alemania' => 1,
        'Brazil' => 2,
        'Canada' => 3,
        'China' => 4,
        'Estados Unidos' => 5,
        'India' => 6,
        'Indonesia' => 7,
        'Japon' => 8,
        'Mexico' => 9,
        'Rusia' => 10
    ];
    $id_paises = $paises_map[$pais] ?? 9;

    // Procesar archivos
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Obtener información actual
    $sql_actual = "SELECT foto, lista, excel FROM student WHERE id_students = $student_id";
    $result_actual = $conn->query($sql_actual);
    $actual = $result_actual->fetch_assoc();

    $foto_path = $actual['foto'];
    $lista_path = $actual['lista'];
    $excel_path = $actual['excel'];

    // Procesar archivos subidos
    $archivos = [
        'photo' => ['prefix' => 'foto', 'max_size' => 5 * 1024 * 1024],
        'excel' => ['prefix' => 'excel']
    ];

    foreach ($archivos as $fileInput => $config) {
        if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] == 0) {
            // Validar tamaño máximo para la foto
            if (isset($config['max_size']) && $_FILES[$fileInput]['size'] > $config['max_size']) {
                die("Error: La imagen es demasiado grande. El tamaño máximo permitido es 5MB.");
            }

            $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
            $name = "{$config['prefix']}_" . time() . ".$ext";
            move_uploaded_file($_FILES[$fileInput]['tmp_name'], $upload_dir . $name);
            ${$config['prefix'] . '_path'} = $upload_dir . $name;
        } elseif (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] == UPLOAD_ERR_INI_SIZE) {
            die("Error: El archivo es demasiado grande. El tamaño máximo permitido es 5MB.");
        }
    }

    // Actualizar en la base de datos
    $id_usuario_editor = $_SESSION["id_usuario"] ?? null;
    $sql = "UPDATE student SET 
            nombre = '$nombre', 
            id_sexo = $id_sexo,
            especifique = '$especifique', 
            edad = $edad, 
            nacimiento = '$nacimiento', 
            id_paises = $id_paises, 
            telefono = '$telefono', 
            correo = '$correo', 
            domicilio = '$domicilio', 
            foto = '$foto_path', 
            lista = '$lista_path', 
            excel = '$excel_path',
            fecha_edicion = NOW(),
            id_usuario_editor = $id_usuario_editor
            WHERE id_students = $student_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: students.php");
        exit();
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
    exit();
}

// Mostrar detalles del estudiante (vista HTML)
elseif ($student_id > 0) {
    $sql = "
    SELECT
      s.id_students   AS ID,
      s.nombre        AS Nombre,
      sex.descripcion AS Sexo,
      s.especifique   AS Especifique,  
      s.edad          AS Edad,
      DATE_FORMAT(s.nacimiento, '%d-%m-%Y') AS Fecha_Nacimiento,
      p.pais          AS Pais,
      s.telefono      AS Telefono,
      s.correo        AS Correo,
      s.domicilio     AS Domicilio,
      s.foto          AS Foto,
      s.lista         AS Lista,
      s.excel         AS Excel,
      DATE_FORMAT(s.fecha_registro, '%d-%m-%Y %H:%i') AS Fecha_Registro,
      s.visible       AS Visible,
      DATE_FORMAT(s.fecha_borrado, '%d-%m-%Y %H:%i') AS Fecha_Borrado
    FROM student s
    JOIN sexo sex ON s.id_sexo = sex.id_sexo
    JOIN paises p ON s.id_paises = p.id_paises
    WHERE s.id_students = $student_id
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        echo "<div class='user-header'>";
        echo "<h2>Detalles del Estudiante</h2>";
        echo "</div>";

        echo "<div class='table-container'>";
        echo "<table class='students-table'>";

        // Campos principales
        $fields = [
            'ID' => 'ID',
            'Nombre' => 'Nombre',
            'Sexo' => 'Sexo',
            'Edad' => 'Edad',
            'Fecha_Nacimiento' => 'Fecha de Nacimiento',
            'Pais' => 'País',
            'Telefono' => 'Teléfono',
            'Correo' => 'Correo',
            'Domicilio' => 'Domicilio',
            'Fecha_Registro' => 'Fecha de Registro'
        ];

        foreach ($fields as $key => $label) {
            echo "<tr>";
            echo "<td><strong>$label</strong></td>";

            if ($key === 'Sexo') {
                if ($row['Sexo'] == 'Otro' && !empty($row['Especifique'])) {
                    echo "<td>" . htmlspecialchars($row['Sexo'] . " - " . $row['Especifique']) . "</td>";
                } else {
                    echo "<td>" . htmlspecialchars($row[$key]) . "</td>";
                }
            } else {
                echo "<td>" . htmlspecialchars($row[$key] ?? 'N/A') . "</td>";
            }
            echo "</tr>";
        }

        // Archivos
        echo "<tr>";
        echo "<td><strong>Foto</strong></td>";
        echo "<td><img src='" . $row['Foto'] . "' width='120' height='150' style='object-fit: cover; border-radius: 4px;'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td><strong>Lista</strong></td>";
        echo "<td><a href='" . $row['Lista'] . "' download class='action-btn'>Descargar Lista</a></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td><strong>Archivo Excel</strong></td>";
        echo "<td><a href='" . $row['Excel'] . "' class='action-btn'>Descargar Excel</a></td>";
        echo "</tr>";

        echo "</table>";
        echo "</div>";

        echo "<div style='text-align: center; margin-top: 20px;'>";
        echo "<a href='students.php' class='action-btn'>Volver a Estudiantes</a>";

        if ($permiso_exportar) {
            echo "<a href='gestion.php?export=csv&id=" . $student_id . "' class='export-csv-btn'>Exportar a CSV</a>";
        }

        echo "</div>";

    } else {
        echo "<div class='user-header'>";
        echo "<h2>Error</h2>";
        echo "<a href='students.php'>Volver a estudiantes</a>";
        echo "</div>";

        echo "<div style='text-align: center; margin: 40px;'>";
        echo "<p>ID incorrecta: $student_id</p>";
        echo "</div>";
    }

    echo "</body></html>";
    exit();
}

$conn->close();

// Si no hay acción específica, mostrar error
echo "Acción no válida o parámetros incorrectos.";
?>