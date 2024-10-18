<?php
require 'model/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">

    <title>Mostrar Evaluación de Adolescentes</title>
</head>

<body>
    <h1>Subir archivo Excel Hoja 2</h1>
    <form action="pruebaSubir2.php" method="post" enctype="multipart/form-data">
        <label class="subir" for="archivoExcel">Selecciona el archivo Excel:</label>
        <input type="file" name="archivoExcel" id="archivoExcel" accept=".xlsx, .xls">
        <button class="button-40" role="button" type="submit" name="submit"><span class="text">Subir y procesar</span></button>
    </form>
    <br>
    <br>
    <div class="row g-4">
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

        <div class="col-5"></div>

        <div class="col-auto">
            <label for="campo" class="col-form-label">Buscar: </label>
        </div>
        <div class="col-auto">
            <input type="text" name="campo" id="campo" oninput="getData()" class="form-control">
        </div>
    </div>

    <div class="outer-wrapper">
        <div class="table-wrapper">
            <table border="1">
                <thead>
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
                </thead>

                <tbody id="content">

                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <label id="lbl-total"> </label>
        </div>
        <div class="col-6" id="nav-paginacion">

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
            let url = "buscarExcel2.php";
            let formaData = new FormData()
            formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', paginaActual)

            fetch(url, {
                    method: "POST",
                    body: formaData
                }).then(response => response.json())
                .then(data => {
                    console.log("Respuesta del servidor:", data);
                    content.innerHTML = data.data;
                    document.getElementById("lbl-total").innerHTML = 'Mostrando ' + data.totalFiltro + ' de ' + data.totalRegistros + ' registros';
                    document.getElementById("nav-paginacion").innerHTML = data.paginacion
                }).catch(err => console.log(err))
        }

    </script>
    
</body>

</html>