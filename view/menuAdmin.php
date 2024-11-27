<?php
session_start();
if ($_SESSION['tipo_usuario_id'] == 1 || $_SESSION['tipo_usuario_id'] == 3 || $_SESSION['tipo_usuario_id'] == 2) {
    include_once 'layouts/header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gráfico de Edades por Municipio</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</head>
<body>
    <?php include_once 'layouts/nav.php'; ?>
    
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Reporte por Municipio</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item active">Reporte</li>
                        </ol>
                    </div>
                </div>
                <div class="card">
                <div class="card-body">
                        Seleccione un municipio y tipo de gráfico para visualizar el reporte.
                </div>
            </div>
                <div class="row">
                    <div class="col-md-4">
                        <select id="municipio" class="form-control">
                            <option value="">Seleccione un Municipio</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="tipo-grafico" class="form-control">
                            <option value="bar">Gráfico de Barras</option>
                            <option value="pie">Gráfico de Pastel</option>
                            <option value="column">Gráfico de Columnas</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <button id="botonDescargarPdf" class="btn btn-primary">Descargar PDF</button>
                    </div>
                </div>
                
                <div id="chart_div" style="height: 650px; width: 100%;"></div>
            </div>
            

        </section>
    </div>

    <?php include_once 'layouts/footer.php'; ?>
</body>
</html>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="../js/edades.js"></script>
<?php
} else {
    header('Location: ../index.php');
}
?>