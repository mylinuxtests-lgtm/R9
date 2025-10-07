<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
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

// Datos para los gráficos
$paises_data = [];
$sexo_data = [];
$edad_data = [];

while ($row = $paises_result->fetch_assoc()) {
    $paises_data['labels'][] = $row['Pais'];
    $paises_data['cantidades'][] = $row['Cantidad'];
}

while ($row = $sexo_result->fetch_assoc()) {
    $sexo_data['labels'][] = $row['Sexo'];
    $sexo_data['cantidades'][] = $row['Cantidad'];
}

while ($row = $edad_result->fetch_assoc()) {
    $edad_data['labels'][] = $row['Rango_Edad'];
    $edad_data['cantidades'][] = $row['Cantidad'];
}

// Colores para los gráficos
$colores = [
    '#1982C4',
    '#6A4C93',
    '#8AC926',
    '#FF9F40',
    '#6c0e22ff',
    '#36A2EB',
    '#FFCE56',
    '#e60d0dff',
    '#9966FF',
    '#155610ff'
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas Generales</title>
    <link rel="stylesheet" href="style.css" media="screen" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="user-header">
        <h2>Usuario: <strong><?php echo htmlspecialchars($_SESSION["usuario"]); ?></strong></h2>
        <br/> 
        <div class="header-links">
            <a href="students.php" class="btn-students">Volver a registros</a>
            <a href="login.php?action=logout" class="btn-students">Cerrar sesión</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="form-container">
            <h2>Estadísticas Generales</h2>
        </div>

        <div class="row">
            <!-- Gráfico de Barras de Países -->
            <div class="col-md-8 mx-auto">
                <div class="chart-container chart-container-full">
                    <h3 class="chart-title">Distribución de Estudiantes por País</h3>
                    <div class="chart-wrapper">
                        <canvas id="paisesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Pastel de Sexo -->
            <div class="col-md-6">
                <div class="chart-container chart-container-half">
                    <h3 class="chart-title">Distribución por Sexo</h3>
                    <div class="chart-wrapper">
                        <canvas id="sexoChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Pastel de Edades -->
            <div class="col-md-6">
                <div class="chart-container chart-container-half">
                    <h3 class="chart-title">Distribución por Rango de Edad</h3>
                    <div class="chart-wrapper">
                        <canvas id="edadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Datos para los gráficos
        const paisesData = {
            labels: <?php echo json_encode($paises_data['labels'] ?? []); ?>,
            cantidades: <?php echo json_encode($paises_data['cantidades'] ?? []); ?>
        };

        const sexoData = {
            labels: <?php echo json_encode($sexo_data['labels'] ?? []); ?>,
            cantidades: <?php echo json_encode($sexo_data['cantidades'] ?? []); ?>
        };

        const edadData = {
            labels: <?php echo json_encode($edad_data['labels'] ?? []); ?>,
            cantidades: <?php echo json_encode($edad_data['cantidades'] ?? []); ?>
        };

        const colores = <?php echo json_encode($colores); ?>;

        function inicializarGraficos() {
            // Gráfico de Barras de Países
            const paisesCtx = document.getElementById('paisesChart').getContext('2d');
            const paisesChart = new Chart(paisesCtx, {
                type: 'bar',
                data: {
                    labels: paisesData.labels,
                    datasets: [{
                        label: 'Cantidad de Estudiantes',
                        data: paisesData.cantidades,
                        backgroundColor: colores,
                        borderColor: colores.map(color => color),
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `Estudiantes: ${context.parsed.x}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad de Estudiantes'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Países'
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },

                    barThickness: 20, 
                    maxBarThickness: 25, 
                    categoryPercentage: 0.8, 
                    barPercentage: 0.9 
                }
            });

            // Gráfico de Pastel de Sexo
            const sexoCtx = document.getElementById('sexoChart').getContext('2d');
            const sexoChart = new Chart(sexoCtx, {
                type: 'pie',
                data: {
                    labels: sexoData.labels,
                    datasets: [{
                        data: sexoData.cantidades,
                        backgroundColor: colores,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.parsed / total) * 100);
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Pastel de Edades
            const edadCtx = document.getElementById('edadChart').getContext('2d');
            const edadChart = new Chart(edadCtx, {
                type: 'doughnut',
                data: {
                    labels: edadData.labels,
                    datasets: [{
                        data: edadData.cantidades,
                        backgroundColor: colores.slice().reverse(),
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.parsed / total) * 100);
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            window.paisesChart = paisesChart;
            window.sexoChart = sexoChart;
            window.edadChart = edadChart;
        }

        document.addEventListener('DOMContentLoaded', function () {
            inicializarGraficos();
        });

        // Redimensionar gráficos
        window.addEventListener('resize', function () {
            if (window.paisesChart) {
                window.paisesChart.resize();
            }
            if (window.sexoChart) {
                window.sexoChart.resize();
            }
            if (window.edadChart) {
                window.edadChart.resize();
            }
        });
    </script>

    <?php $conn->close(); ?>
</body>

</html>