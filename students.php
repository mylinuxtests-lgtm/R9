<?php
session_start();
if (!isset($_SESSION["usuario"])) {
  header("Location: login.php");
  exit();
}

require_once 'conn.php';

$id_usuario = $_SESSION["id_perfil"] ?? 0;
$permiso_editar = ($id_usuario == 1 || $id_usuario == 2);
$permiso_borrar = ($id_usuario == 1);
$permiso_exportar = ($id_usuario == 1 || $id_usuario == 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css" media="screen" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>Base de datos de Estudiantes</title>
</head>
<body>
  <div class="user-header">
    <h2>Usuario: <strong><?php echo htmlspecialchars($_SESSION["usuario"]); ?></strong></h2>
    <a href="login.php?action=logout">Cerrar sesión</a>
  </div>

  <div style='text-align: center; margin-bottom: 20px;'>
    <a href='Registro.php' class='new-student-btn'>Registrar nuevo estudiante</a>
    <?php if ($permiso_exportar): ?>
      <a href='gestion.php?export=all_csv' class='export-csv-btn'>Exportar a CSV</a>
      <a href='estadisticas.php' class='stats-btn'>Ver Estadísticas</a>
    <?php endif; ?>
  </div>
  

  <div class="container-fluid overflow-auto rounded-lg shadow">
    <div id="toolbar">
      <button id="refreshBtn" class="refresh-btn">
        <i class="bi bi-arrow-clockwise"></i> Actualizar Tabla
      </button>
    </div>
    <table id="studentsTable" data-toggle="table" data-url="gestion.php?action=obtener_datos" 
           data-pagination="true" data-page-size="10" data-search="true" 
           data-show-refresh="false" data-show-columns="true" data-toolbar="#toolbar"
           data-query-params="queryParams" data-side-pagination="server" 
           data-response-handler="responseHandler" data-sort-name="ID" data-sort-order="asc">
      <thead>
        <tr>
          <th class whitespace-nowrap data-field="ID" data-sortable="true">ID</th>
          <th class whitespace-nowrap data-field="Nombre" data-sortable="true">Nombre</th>
          <th class whitespace-nowrap data-field="Fecha_Registro" data-sortable="true">Fecha Registro</th>
          <th class whitespace-nowrap data-field="Fecha_Edicion" data-sortable="true">Fecha Edición</th>
          <th class whitespace-nowrap data-field="ver" data-formatter="verFormatter" data-width="100" data-sortable="false">Ver</th>
          <th class whitespace-nowrap data-field="editar" data-formatter="editarFormatter" data-width="100" data-sortable="false">Editar</th>
          <th class whitespace-nowrap data-field="eliminar" data-formatter="eliminarFormatter" data-width="100" data-sortable="false">Eliminar</th>
        </tr>
      </thead>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
  <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/locale/bootstrap-table-es-ES.min.js"></script>
  
  <script>
    // Configuración de la tabla con AJAX
    function queryParams(params) {
      return {
        limit: params.limit,
        offset: params.offset,
        search: params.search,
        sort: params.sort,
        order: params.order
      };
    }

    function responseHandler(res) {
      if (res.error) {
        Swal.fire('Error', res.error, 'error');
        return { total: 0, rows: [] };
      }
      return {
        total: res.total,
        rows: res.rows
      };
    }

    // Formatters para los botones de acción
    function verFormatter(value, row, index) {
      return `<button class="btn-table view-btn" data-id="${row.ID}" title="Ver detalles">
                 <img src="./templates/magnifying.png" width="20" alt="Ver detalles">
               </button>`;
    }

    function editarFormatter(value, row, index) {
      const permisoEditar = <?php echo $permiso_editar ? 'true' : 'false'; ?>;
      if (permisoEditar) {
        return `<a href="editar_registro.php?id=${row.ID}" class="btn-table" title="Editar registro">
                   <img src="./templates/edit.svg" width="20" alt="Editar registro">
                 </a>`;
      }
      return `<span class="btn-table disabled" title="Sin permisos para editar">
                 <img src="./templates/edit.svg" width="20" alt="Sin permisos">
               </span>`;
    }

    function eliminarFormatter(value, row, index) {
      const permisoBorrar = <?php echo $permiso_borrar ? 'true' : 'false'; ?>;
      if (permisoBorrar) {
        return `<button class="btn-table delete-btn" data-id="${row.ID}" title="Eliminar registro">
                   <img src="./templates/delete.svg" width="20" alt="Eliminar registro">
                 </button>`;
      }
      return `<span class="btn-table disabled" title="Sin permisos para eliminar">
                 <img src="./templates/delete.svg" width="20" alt="Sin permisos">
               </span>`;
    }

    // Funciones AJAX para las acciones
    function eliminarRegistro(id) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `gestion.php?action=eliminar&id=${id}&confirm=true`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              if (data.success) {
                Swal.fire('Eliminado', 'Registro eliminado correctamente', 'success');
                $('#studentsTable').bootstrapTable('refresh');
              } else {
                Swal.fire('Error', data.error || 'Error al eliminar', 'error');
              }
            },
            error: function() {
              Swal.fire('Error', 'Error de conexión', 'error');
            }
          });
        }
      });
    }

function verDetalles(id) {
  $.ajax({
    url: `gestion.php?action=obtener_detalles&id=${id}`,
    type: 'GET',
    dataType: 'json',
    success: function(data) {
      if (data.error) {
        Swal.fire('Error', data.error, 'error');
        return;
      }

      let especifique = data.Especifique ? `<br><strong>Especifique:</strong> ${data.Especifique}` : '';
      const permisoExportar = <?php echo $permiso_exportar ? 'true' : 'false'; ?>;
      
      let exportButton = '';
      if (permisoExportar) {
        exportButton = `<a href="exportar_pdf.php?id=${data.ID}" target="_blank" class="action-btn">Exportar PDF</a>`;
      }
      
      Swal.fire({
        title: `Detalles del estudiante: ${data.Nombre}`,
        html: `
          <div style="text-align: left;">
            <p><strong>ID:</strong> ${data.ID}</p>
            <p><strong>Nombre:</strong> ${data.Nombre}</p>
            <p><strong>Sexo:</strong> ${data.Sexo}${especifique}</p>
            <p><strong>Edad:</strong> ${data.Edad}</p>
            <p><strong>Fecha Nacimiento:</strong> ${data.Fecha_Nacimiento}</p>
            <p><strong>País:</strong> ${data.Pais}</p>
            <p><strong>Teléfono:</strong> ${data.Telefono}</p>
            <p><strong>Correo:</strong> ${data.Correo}</p>
            <p><strong>Domicilio:</strong> ${data.Domicilio}</p>
            <p><strong>Fecha Registro:</strong> ${data.Fecha_Registro}</p>
            <div style="text-align: center; margin-top: 15px;">
              <img src="${data.Foto}" width="120" height="150" style="object-fit: cover; border-radius: 4px;">
              <div style="margin-top: 10px;">
                <a href="${data.Lista}" download class="action-btn">Descargar Lista</a>
                <a href="${data.Excel}" download class="action-btn">Descargar Excel</a>
                ${exportButton}
              </div>
            </div>
          </div>
        `,
        width: 600,
        showConfirmButton: false,
        showCloseButton: true
      });
    },
    error: function() {
      Swal.fire('Error', 'Error al cargar detalles', 'error');
    }
  });
}

    // Event listeners
    $(document).ready(function() {
      $('#studentsTable').bootstrapTable({
        locale: 'es-ES',
        formatLoadingMessage: function () {
            return 'Cargando, por favor espere...';
        },
        formatRecordsPerPage: function (pageNumber) {
            return pageNumber + ' registros por página';
        },
        formatShowingRows: function (pageFrom, pageTo, totalRows, totalNotFiltered) {
            if (totalNotFiltered !== undefined && totalNotFiltered > 0 && totalNotFiltered > totalRows) {
                return 'Mostrando ' + pageFrom + ' a ' + pageTo + ' de ' + totalRows + ' registros (filtrado de ' + totalNotFiltered + ' registros totales)';
            }
            return 'Mostrando ' + pageFrom + ' a ' + pageTo + ' de ' + totalRows + ' registros';
        },
        formatSearch: function () {
            return 'Buscar';
        },
        formatNoMatches: function () {
            return 'No se encontraron registros coincidentes';
        },
        formatPaginationSwitch: function () {
            return 'Ocultar/Mostrar paginación';
        },
        formatRefresh: function () {
            return 'Actualizar';
        },
        formatToggle: function () {
            return 'Alternar';
        },
        formatColumns: function () {
            return 'Columnas';
        },
        formatAllRows: function () {
            return 'Todos';
        }
      });

      $('#refreshBtn').on('click', function() {
        $('#studentsTable').bootstrapTable('refresh');
        Swal.fire({ title: 'Tabla actualizada', icon: 'success', timer: 1000, showConfirmButton: false });
      });

      $(document).on('click', '.delete-btn', function() {
        eliminarRegistro($(this).data('id'));
      });

      $(document).on('click', '.view-btn', function() {
        verDetalles($(this).data('id'));
      });
    });
  </script>
</body>
</html>