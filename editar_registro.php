<?php
session_start();

$id_usuario = $_SESSION["id_perfil"] ?? 0;
$permiso_editar = ($id_usuario == 1 || $id_usuario == 2);

if (!$permiso_editar) {
    die("No tienes permisos para editar registros.");
}

require_once 'conn.php';

$conn = new mysqli($servername, $username, $db_password, $dbname, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id <= 0) {
    die("ID inválido");
}

// Obtiene los datos del estudiante
$sql = "SELECT * FROM student WHERE id_students = $student_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Estudiante no encontrado");
}

$student = $result->fetch_assoc();

// Obtiene el sexo y país
$sexo_map = [
    1 => 'Masculino',
    2 => 'Femenino',
    3 => 'Otro'
];

$paises_map = [
    1 => 'Alemania',
    2 => 'Brazil',
    3 => 'Canada',
    4 => 'China',
    5 => 'Estados Unidos',
    6 => 'India',
    7 => 'Indonesia',
    8 => 'Japon',
    9 => 'Mexico',
    10 => 'Rusia'
];

$sexo_valor = $sexo_map[$student['id_sexo']] ?? 'Masculino';
$pais_valor = $paises_map[$student['id_paises']] ?? 'Mexico';
$especifique_valor = $student['especifique'] ?? "";

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" media="screen" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Editar Registro</title>
</head>

<body>
    <?php
    $usuario_actual = isset($_SESSION["usuario"]) ? htmlspecialchars($_SESSION["usuario"]) : 'Usuario';
    ?>

    <div class="user-header">
        <h2>Editando como: <strong><?php echo $usuario_actual; ?></strong></h2>
    </div>

    <form action="gestion.php?action=actualizar" method="post" enctype="multipart/form-data" autocomplete="off"
        class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?php echo $student_id; ?>">
        <div class="form-container">
            <h2>Editar información</h2>
            <h2><span class="error">* Campo Obligatorio</span></h2>
        </div>
        <input type="hidden" id="fecha_respuesta" name="fecha_respuestas" value="0" />
        <div class="form-box">
            <label for="nombre">
                Nombre completo: <span class="error">*</span><br /></label>
            <input type="text" class="form-control" id="nombre" name="nombre"
                value="<?php echo htmlspecialchars($student['nombre']); ?>" required /><br /><br />
        </div>
        <div class="form-box">
            <label for="sexo"> Sexo: <span class="error">*</span><br /></label>
            <br /><br />

            <label class="radio-label">
                <input type="radio" id="S1" name="sexo" value="Masculino" <?php echo ($sexo_valor == 'Masculino') ? 'checked' : ''; ?> required />
                <span class="radio-text">Masculino</span> </label><br />

            <label class="radio-label">
                <input type="radio" id="S2" name="sexo" value="Femenino" <?php echo ($sexo_valor == 'Femenino') ? 'checked' : ''; ?> />
                <span class="radio-text">Femenino</span> </label><br />

            <label class="radio-label">
                <input type="radio" id="S3" name="sexo" value="Otro" <?php echo ($sexo_valor == 'Otro') ? 'checked' : ''; ?> />
                <span class="radio-text">Otro</span> </label><br />

            <label id="labelOtro" for="especifique" class="<?php echo ($sexo_valor == 'Otro') ? '' : 'oculto'; ?>">
                Especifique:
            </label>
            <input type="text" class="<?php echo ($sexo_valor == 'Otro') ? '' : 'oculto'; ?>" id="especifique"
                name="especifique" value="<?php echo htmlspecialchars($especifique_valor); ?>" <?php echo ($sexo_valor == 'Otro') ? 'required' : ''; ?> /><br />
        </div>
        <div class="form-box">
            <label for="age"> Edad: <span class="error">*</span><br /></label>
            <input type="number" id="age" name="age" min="1" max="99" value="<?php echo $student['edad']; ?>"
                required /><br /><br />
        </div>
        <div class="form-box">
            <label for="bday">
                Fecha de Nacimiento: <span class="error">*</span><br /></label>
            <input type="date" id="bday" name="bday" value="<?php echo $student['nacimiento']; ?>"
                required /><br /><br />
        </div>
        <div class="form-box">
            <label for="country" class="col-sm-3 control-label">Pais de residencia: <span
                    class="error">*</span><br /></label>
            <div class="col-12">
                <select id="country" name="country" class="form-control" onchange="actualizarFormatoTelefono()">
                    <option value="Alemania" <?php echo ($pais_valor == 'Alemania') ? 'selected' : ''; ?>>Alemania
                    </option>
                    <option value="Brazil" <?php echo ($pais_valor == 'Brazil') ? 'selected' : ''; ?>>Brazil</option>
                    <option value="Canada" <?php echo ($pais_valor == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                    <option value="China" <?php echo ($pais_valor == 'China') ? 'selected' : ''; ?>>China</option>
                    <option value="Estados Unidos" <?php echo ($pais_valor == 'Estados Unidos') ? 'selected' : ''; ?>>
                        Estados Unidos</option>
                    <option value="India" <?php echo ($pais_valor == 'India') ? 'selected' : ''; ?>>India</option>
                    <option value="Indonesia" <?php echo ($pais_valor == 'Indonesia') ? 'selected' : ''; ?>>Indonesia
                    </option>
                    <option value="Japon" <?php echo ($pais_valor == 'Japon') ? 'selected' : ''; ?>>Japon</option>
                    <option value="Mexico" <?php echo ($pais_valor == 'Mexico') ? 'selected' : ''; ?>>Mexico</option>
                    <option value="Rusia" <?php echo ($pais_valor == 'Rusia') ? 'selected' : ''; ?>>Rusia</option>
                </select>
            </div>
        </div>
        <div class="form-box">
            <label for="photo">
                Sube tu foto: <span class="error">*</span><br />
            </label>
            <div class="photo-container">
                <div class="photo-upload">
                    <input type="file" id="photo" name="photo" accept="image/*" />
                    <small>Tamaño máximo: 5MB</small>
                </div>
                <div class="photo-example">
                    <small>Foto actual:</small><br />
                    <img src="<?php echo $student['foto']; ?>" alt="Foto actual" width="150" height="200" />
                </div>
            </div>
        </div>
        <div class="form-box">
            <label for="phone"> Teléfono: <span class="error">*</span><br /></label>
            <input type="tel" id="phone" name="phone" placeholder="000-00000000" pattern="[0-9]{3}-[0-9]{7,11}"
                maxlength="15" value="<?php echo htmlspecialchars($student['telefono']); ?>" required /><br /><br />
            <small id="phoneHelp">Debe seguir el siguiente formato: extensión-número (ej:
                473-1234567)</small><br /><br />
        </div>
        <div class="form-box">
            <label for="correo"> Correo: <span class="error">*</span><br /></label>
            <input type="email" id="correo" name="correo" placeholder="ejemplo@hotmail.com" aria-label="correo"
                aria-describedby="basic-addon1" value="<?php echo htmlspecialchars($student['correo']); ?>"
                required /><br /><br />
        </div>
        <div class="form-box">
            <label for="domicilio">
                Domicilio: <span class="error">*</span><br />
            </label>
            <textarea id="domicilio" name="domicilio" rows="3" cols="30"
                required><?php echo htmlspecialchars($student['domicilio']); ?></textarea><br /><br />
        </div>
        <div class="form-box">
            <label for="list"> Listado <span class="error">*</span><br /> </label>
            <input type="file" id="list" name="list" accept=".txt" /><br /><br />
            <small>Archivo actual: <?php echo basename($student['lista']); ?></small>
        </div>
        <div class="form-box">
            <label for="excel">
                Archivo Excel <span class="error">*</span><br />
            </label>
            <input type="file" id="excel" name="excel" accept=".xlsx, .csv" /><br /><br />
            <small>Archivo actual: <?php echo basename($student['excel']); ?></small>
        </div>
        <div class="col-12">
            <div class="form-check">
                <label class="form-check-label" style="color:rgba(0, 0, 0, 1);" for="invalidCheck">
                    Confirmas que la informacion es veraz y correcta.
                    <span class="error">*</span>
                    <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required />
                </label>
                <small style="color:rgba(0, 0, 0, 1);">Debes aceptar antes de finalizar de edtiar</small>
            </div>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <input type="submit" value="Actualizar información" />
            <input type="reset" value="Restablecer cambios" />
            <a href="students.php" class="form-group">Cancelar edición</a>
        </div>
    </form>
    <br /><br />

    <script>
        const radioOtro = document.getElementById("S3");
        const campoEspecifique = document.getElementById("especifique");
        const labelEspecifique = document.getElementById("labelOtro");

        // Mostrar/ocultar campo 
        radioOtro.addEventListener("change", function () {
            if (radioOtro.checked) {
                campoEspecifique.classList.remove("oculto");
                labelEspecifique.classList.remove("oculto");
                campoEspecifique.setAttribute("required", "true");
            } else {
                campoEspecifique.classList.add("oculto");
                labelEspecifique.classList.add("oculto");
                campoEspecifique.removeAttribute("required");
            }
        });

        // Si ya está seleccionado "Otro", mostrar el campo
        if (radioOtro.checked) {
            campoEspecifique.classList.remove("oculto");
            labelEspecifique.classList.remove("oculto");
        }

        // Validación del formulario
        (function () {
            "use strict";
            const maxFileSize = 5 * 1024 * 1024;

            const forms = document.querySelectorAll(".needs-validation");

            Array.from(forms).forEach(function (form) {
                form.addEventListener(
                    "submit",
                    function (event) {
                        // Validar tamaño de archivo
                        const photoInput = document.getElementById('photo');
                        if (photoInput.files.length > 0) {
                            const fileSize = photoInput.files[0].size;
                            if (fileSize > maxFileSize) {
                                event.preventDefault();
                                event.stopPropagation();
                                alert('La imagen es demasiado grande. El tamaño máximo permitido es 5MB.');
                                return false;
                            }
                        }

                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        form.classList.add("was-validated");
                    },
                    false
                );
            });
        })();


        const phoneFormats = {
            Alemania: {
                pattern: "[0-9]{2,4}-[0-9]{6,13}",
                placeholder: "49-12345678",
                example: "código de área-número (ej: 49-12345678)",
            },
            Brazil: {
                pattern: "[0-9]{2}-[0-9]{10}",
                placeholder: "55-912345678",
                example: "código de área-número (ej: 55-912345678)",
            },
            Canada: {
                pattern: "[0-9]{1,3}-[0-9]{10}",
                placeholder: "1-1234567",
                example: "código de área-número (ej: 1-1234567)",
            },
            China: {
                pattern: "[0-9]{2,3}-[0-9]{5,12}",
                placeholder: "86-12345678",
                example: "código de área-número (ej: 86-12345678)",
            },
            "Estados Unidos": {
                pattern: "[0-9]{1,3}-[0-9]{10}",
                placeholder: "1-5550123",
                example: "código de área-número (ej: 1-5550123)",
            },
            India: {
                pattern: "[0-9]{2,5}-[0-9]{7,10}",
                placeholder: "91-23456789",
                example: "código STD-número (ej: 91-23456789)",
            },
            Indonesia: {
                pattern: "[0-9]{2,4}-[0-9]{5,10}",
                placeholder: "62-12345678",
                example: "código de área-número (ej: 62-1234567)",
            },
            Japon: {
                pattern: "[0-9]{2,4}-[0-9]{5,13}",
                placeholder: "81-12345678",
                example: "código de área-número (ej: 81-12345678)",
            },
            Mexico: {
                pattern: "[0-9]{2,3}-[0-9]{10}",
                placeholder: "52-12345678",
                example: "lada-número (ej: 52-12345678)",
            },
            Rusia: {
                pattern: "[0-9]{3}-[0-9]{10}",
                placeholder: "7-1234567",
                example: "código de área-número (ej: 7-1234567)",
            },
        };

        function actualizarFormatoTelefono() {
            const countrySelect = document.getElementById("country");
            const phoneInput = document.getElementById("phone");
            const phoneHelp = document.getElementById("phoneHelp");

            const selectedCountry = countrySelect.value;
            const format = phoneFormats[selectedCountry];

            if (format) {
                phoneInput.pattern = format.pattern;
                phoneInput.placeholder = format.placeholder;
                phoneHelp.textContent = `Debe seguir el siguiente formato: ${format.example}`;
            } else {
                phoneInput.pattern = "[0-9]{3}-[0-9]{7,11}";
                phoneInput.placeholder = "000-00000000";
                phoneHelp.textContent = "Debe seguir el siguiente formato: extensión-número (ej: 473-1234567)";
            }
        }


        actualizarFormatoTelefono();
    </script>
</body>

</html>