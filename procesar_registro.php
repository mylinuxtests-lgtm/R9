<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION["usuario"])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Verificar método
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Establecer conexión
$conn = new mysqli($servername, $username, $db_password, $dbname, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(['error' => 'Error en la conexión: ' . $conn->connect_error]);
    exit();
}

// Inicializar variables
$error_message = null;
$foto_path = $lista_path = $excel_path = "";

// Obtener datos del formulario
$nombre = $conn->real_escape_string($_POST['nombre'] ?? '');
$sexo = $conn->real_escape_string($_POST['sexo'] ?? '');
$especifique = ($sexo == 'Otro' && isset($_POST['especifique'])) ? $conn->real_escape_string($_POST['especifique']) : "";
$edad = intval($_POST['age'] ?? 0);
$nacimiento = $conn->real_escape_string($_POST['bday'] ?? '');
$pais = $conn->real_escape_string($_POST['country'] ?? '');
$telefono = $conn->real_escape_string($_POST['phone'] ?? '');
$correo = $conn->real_escape_string($_POST['correo'] ?? '');
$domicilio = $conn->real_escape_string($_POST['domicilio'] ?? '');

// Validaciones básicas
if (empty($nombre) || empty($sexo) || $edad <= 0) {
    echo json_encode(['error' => 'Datos incompletos o inválidos']);
    exit();
}

// Relacionar sexo a ID
$id_sexo = match ($sexo) {
    'Masculino' => 1,
    'Femenino' => 2,
    'Otro' => 3,
    default => 1
};

// Relacionar país a ID
$paises_map = [
    'Alemania' => 1, 'Brazil' => 2, 'Canada' => 3, 'China' => 4,
    'Estados Unidos' => 5, 'India' => 6, 'Indonesia' => 7, 
    'Japon' => 8, 'Mexico' => 9, 'Rusia' => 10
];
$id_paises = $paises_map[$pais] ?? 9;

// Procesar archivos
$upload_dir = "./uploads/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Procesar archivos subidos
$archivos = [
    'photo' => ['prefix' => 'foto', 'required' => true, 'max_size' => 5 * 1024 * 1024],
    'list' => ['prefix' => 'lista', 'required' => true],
    'excel' => ['prefix' => 'excel', 'required' => true]
];

foreach ($archivos as $fileInput => $config) {
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] == 0) {
        // Verificar tamaño máximo
        if ($fileInput === 'photo' && $_FILES[$fileInput]['size'] > $config['max_size']) {
            $error_message = "La imagen es demasiado grande. El tamaño máximo permitido es 5MB.";
            break;
        }

        $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
        $name = "{$config['prefix']}_" . time() . "_" . uniqid() . ".$ext";
        $file_path = $upload_dir . $name;
        
        if (move_uploaded_file($_FILES[$fileInput]['tmp_name'], $file_path)) {
            ${$config['prefix'] . '_path'} = $file_path;
        } else {
            $error_message = "Error al subir el archivo: " . $_FILES[$fileInput]['name'];
            break;
        }
    } elseif (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] != UPLOAD_ERR_NO_FILE) {
        $error_message = "Error al subir archivo: " . $_FILES[$fileInput]['name'];
        break;
    }
}

// Si hay error, retornar
if ($error_message) {
    echo json_encode(['error' => $error_message]);
    exit();
}

// Verificar que los archivos requeridos se subieron
if (empty($foto_path) || empty($lista_path) || empty($excel_path)) {
    echo json_encode(['error' => 'Todos los archivos son requeridos']);
    exit();
}

// Insertar en la base de datos
$id_usuario_registro = $_SESSION["id_usuario"] ?? null;
$sql = "INSERT INTO student (nombre, id_sexo, especifique, edad, nacimiento, id_paises, telefono, correo, domicilio, foto, lista, excel, fecha_registro, id_usuario_registro, visible) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 1)";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("sisissssssssi", $nombre, $id_sexo, $especifique, $edad, $nacimiento, $id_paises, $telefono, $correo, $domicilio, $foto_path, $lista_path, $excel_path, $id_usuario_registro);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Estudiante registrado correctamente']);
    } else {
        echo json_encode(['error' => 'Error en la base de datos: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Error al preparar la consulta: ' . $conn->error]);
}

$conn->close();
?>