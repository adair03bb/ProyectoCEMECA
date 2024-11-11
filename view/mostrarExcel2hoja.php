<?php
session_start();
if ($_SESSION['tipo_usuario_id'] == 1) {
    include_once 'layouts/header.php';
    include "../model/conexion.php";
    include_once 'layouts/nav.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <title>Adm | Reevaluación Adolescentes</title>

    <style>
        /* Estilos base */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Contenedor principal */
        .container-main {
            margin-left: 250px;
            padding: 20px;
            min-height: calc(100vh - 100px);
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        /* Contenedor de la tabla */
        .table-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 400px;
            margin-top: 20px;
        }

        /* Tabla responsiva */
        .table-responsive {
            flex: 1;
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        /* Estilos de la tabla */
        .table th, .table td {
            white-space: nowrap;
            font-size: 14px;
            padding: 8px;
            vertical-align: middle;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
        }

        /* Contenedor de paginación */
        .pagination-container {
            margin-top: auto;
            padding: 15px 0;
            background-color: #fff;
            border-top: 1px solid #dee2e6;
        }

        /* Navegación de paginación */
        #nav-paginacion {
            min-height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #nav-paginacion .pagination {
            margin-bottom: 0;
        }

        #nav-paginacion .page-link {
            padding: 0.375rem 0.75rem;
        }

        /* Formulario y controles */
        .form-controls {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .search-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>

<body>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/menuAdmin.php">Ir a Inicio</a></li>
                    <li class="breadcrumb-item active">Reevaluaciones Adolescentes</li>
                </ol>
            </div>
        </div>
    </div>
</section>
    <div class="container-main">
        <h1>Reevaluaciones Adolescentes</h1>
        <form action="../controller/pruebaSubir2.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label" for="archivoExcel">Selecciona el archivo Excel:</label>
                <input class="form-control" type="file" name="archivoExcel" id="archivoExcel" accept=".xlsx, .xls">
            </div>
            <button class="btn btn-primary" type="submit" name="submit">Subir y Procesar</button>
        </form>

        <div class="row g-4 mt-4">
            <div class="col-auto">
                <label for="num_registros" class="col-form-label">Mostrar: </label>
            </div>
            <div class="col-auto">
                <select name="num_registros" id="num_registros" class="form-select">
                    <option value="100">100</option>
                    <option value="250">250</option>
                    <option value="500">500</option>
                </select>
            </div>

            <div class="col-auto">
                <label for="num_registros" class="col-form-label">Registros</label>
            </div>

            <div class="col-auto">
                <label for="campo" class="col-form-label">Buscar: </label>
            </div>
            <div class="col-auto">
                <input type="text" name="campo" id="campo" oninput="getData()" class="form-control">
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Evaluación</th>
                        <th>Reevaluación</th>
                        <th>Carpeta Administrativa</th>
                        <th>Juzgado</th>
                        <th>Fecha Recepción</th>
                        <th>Hora Recepción</th>
                        <th>Fuero</th>
                        <th>Fiscalía</th>
                        <th>Agencia</th>
                        <th>Juez Control</th>
                        <th>Turno</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Carpeta de Investigación</th>
                        <th>Falla Técnica</th>
                        <th>NUC</th>
                        <th>NIC</th>
                        <th>Fecha Disposición</th>
                        <th>Hora Disposición</th>
                        <th>Puesta Disposición</th>
                        <th>Paterno</th>
                        <th>Materno</th>
                        <th>Nombre</th>
                        <th>CURP</th>
                        <th>Edad</th>
                        <th>Género</th>
                        <th>Municipio</th>
                        <th>Colonia</th>
                        <th>Calle</th>
                        <th>Número</th>
                        <th>Municipio Delito</th>
                        <th>Colonia Delito</th>
                        <th>Descripción Delito</th>
                        <th>Delito 1</th>
                        <th>Delito 2</th>
                        <th>Delito 3</th>
                        <th>Delito 4</th>
                        <th>Subdirección</th>
                        <th>Distrito Judicial</th>
                        <th>Evaluador</th>
                        <th>Tipo Atención</th>
                        <th>Fecha Entrevista</th>
                        <th>Hora Entrevista</th>
                        <th>Defensor</th>
                        <th>Tipo Riesgo</th>
                        <th>Nuevo Tipo Riesgo</th>
                        <th>Riesgo 168</th>
                        <th>Riesgo 169</th>
                        <th>Riesgo 170</th>
                        <th>Fecha Envío</th>
                        <th>Hora Envío</th>
                        <th>Estado</th>
                        <th>Verificada</th>
                        <th>Tipo Verificación</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>

                <tbody id="content">
                    <!-- Aquí se insertarán los datos -->
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-4">
                <label id="lbl-total"> </label>
            </div>
            <div class="col-4" id="nav-paginacion">
                <!-- Aquí se insertará la paginación -->
            </div>
        </div>
    </div>

    <script>
        let paginaActual = 1
        getData(paginaActual)

        document.getElementById("campo").addEventListener("keyup", function() {
            getData(1)
        }, false)
        document.getElementById("num_registros").addEventListener("change", function() {
            getData(paginaActual)
        }, false)

        function getData(pagina) {
            let input = document.getElementById("campo").value
            let num_registros = document.getElementById("num_registros").value
            let content = document.getElementById("content")

            if (pagina != null) {
                paginaActual = pagina
            }

            let url = "../model/buscarExcel2.php";
            let formaData = new FormData()
            formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', paginaActual)

            fetch(url, {
                    method: "POST",
                    body: formaData
                }).then(response => response.json())
                .then(data => {
                    content.innerHTML = data.data;
                    document.getElementById("lbl-total").innerHTML = 'Mostrando ' + data.totalFiltro + ' de ' + data.totalRegistros + ' registros';
                    document.getElementById("nav-paginacion").innerHTML = data.paginacion
                }).catch(err => console.log(err))
        }
    </script>

</body>

</html>
<?php 
} else {
    echo "No tienes permisos para acceder a esta página.";
}
?>
