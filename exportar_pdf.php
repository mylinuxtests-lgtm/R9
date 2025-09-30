<?php
// Evitar cualquier salida de buffer
ob_start();
session_start();

require_once('tcpdf/tcpdf.php');
require_once 'conn.php';

// Verificar autenticación
if (!isset($_SESSION["usuario"])) {
    // Limpiar buffer y mostrar error
    ob_end_clean();
    die("No autorizado");
}

$id_usuario = $_SESSION["id_perfil"] ?? 0;
$permiso_exportar = ($id_usuario == 1 || $id_usuario == 2);

if (!$permiso_exportar) {
    ob_end_clean();
    die("No tienes permisos para exportar registros.");
}

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id <= 0) {
    ob_end_clean();
    die("ID inválido");
}

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $db_password, $dbname, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    ob_end_clean();
    die("Error en la conexión: " . $conn->connect_error);
}

// Obtener datos del estudiante
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
  DATE_FORMAT(s.fecha_edicion, '%d-%m-%Y %H:%i') AS Fecha_Edicion
FROM student s
JOIN sexo sex ON s.id_sexo = sex.id_sexo
JOIN paises p ON s.id_paises = p.id_paises
WHERE s.id_students = $student_id AND s.visible = 1
";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    ob_end_clean();
    die("Estudiante no encontrado");
}

$student = $result->fetch_assoc();

// Limpiar cualquier buffer de salida
while (ob_get_level()) {
    ob_end_clean();
}

// Crear nuevo PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar información del documento
$pdf->SetCreator('Sistema Estudiantes');
$pdf->SetAuthor('Sistema Estudiantes');
$pdf->SetTitle('Estudiante - ' . $student['Nombre']);
$pdf->SetSubject('Información del Estudiante');

// Configurar márgenes
$pdf->SetMargins(15, 25, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Configurar saltos de página automáticos
$pdf->SetAutoPageBreak(TRUE, 15);

// Agregar una página
$pdf->AddPage();

// Configurar fuente para el título
$pdf->SetFont('helvetica', 'B', 16);

// Título del documento
$pdf->Cell(0, 10, 'INFORMACIÓN DEL ESTUDIANTE', 0, 1, 'C');
$pdf->Ln(5);

// Cambiar a fuente normal
$pdf->SetFont('helvetica', '', 12);

// Información básica en dos columnas
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'ID del Estudiante:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $student['ID'], 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Nombre:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $student['Nombre'], 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Sexo:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$sexo_completo = $student['Sexo'];
if (!empty($student['Especifique'])) {
    $sexo_completo .= ' - ' . $student['Especifique'];
}
$pdf->Cell(0, 8, $sexo_completo, 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Edad:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $student['Edad'] . ' años', 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Fecha Nacimiento:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $student['Fecha_Nacimiento'], 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'País:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $student['Pais'], 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Teléfono:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $student['Telefono'], 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Correo:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $student['Correo'], 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 8, 'Domicilio:', 0,0);
$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, $student['Domicilio'], 0, 'L');

if (file_exists($student['Foto'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Fotografía:', 0, 1);
    
    try {
        // Insertar imagen con manejo de errores
        $pdf->Image($student['Foto'], 15, $pdf->GetY(), 40, 50, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $pdf->SetY($pdf->GetY() + 55);
    } catch (Exception $e) {
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 8, 'Error al cargar la imagen: ' . $e->getMessage(), 0, 1);
    }
}

$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(50, 8, 'Fecha de Registro:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $student['Fecha_Registro'], 0, 1);

if (!empty($student['Fecha_Edicion'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 8, 'Última Edición:', 0, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, $student['Fecha_Edicion'], 0, 1);
}

$pdf->Ln(10);

// Archivos adjuntos
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'ARCHIVOS ADJUNTOS', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Ln(5);

// Posición para los íconos de archivos adjuntos
$y_position = $pdf->GetY();

// Archivo de .txt
if (file_exists($student['Lista'])) {
    $pdf->Annotation(30, $y_position, 5, 5, 'Archivo de Lista', array(
        'Subtype' => 'FileAttachment', 
        'Name' => 'PushPin', 
        'FS' => $student['Lista']
    ));
    $pdf->SetXY(40, $y_position - 2);
    $pdf->Cell(0, 8, 'Lista de Estudiantes (.txt)', 0, 1);
    $y_position += 10;
}

// Archivo Excel
if (file_exists($student['Excel'])) {
    $pdf->Annotation(30, $y_position, 5, 5, 'Archivo Excel', array(
        'Subtype' => 'FileAttachment', 
        'Name' => 'PushPin', 
        'FS' => $student['Excel']
    ));
    $pdf->SetXY(40, $y_position - 2);
    $pdf->Cell(0, 8, 'Archivo Excel', 0, 1);
}

// Verificar si se solicita descarga o visualización
if (isset($_GET['action']) && $_GET['action'] == 'download') {
    // Forzar descarga del PDF
    $pdf->Output('estudiante_' . $student_id . '.pdf', 'D');
} else {
    // Mostrar en el navegador (previsualización)
    $pdf->Output('estudiante_' . $student_id . '.pdf', 'I');
}

$conn->close();
exit();
?>