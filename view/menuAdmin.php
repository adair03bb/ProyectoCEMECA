<?php
session_start();
if ($_SESSION['tipo_usuario_id'] == 1 || $_SESSION['tipo_usuario_id'] == 3 || $_SESSION['tipo_usuario_id'] == 2) {
    include_once 'layouts/header.php';
?>
<head>
    <title>Gráfico de Edades por Municipio</title>
    
</head>
<!-- Contenido principal -->
<?php
include_once 'layouts/nav.php';
?>
<div class="content-wrapper">
    <!-- Encabezado de la página -->
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
            <div class="row">
                <div class="col-md-4">
                    <select id="municipio" class="form-control">
                        <option value="">Seleccione un Municipio</option>
                        <!-- Los municipios se cargarán dinámicamente -->
                    </select>
                </div>
            </div>
        </div>
    </section>
    <!-- Contenido principal -->
    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Gráfico de Edades por Municipio</h3>
            </div>
            <div class="card-body">
                <!-- Contenedor del gráfico -->
                <div id="chart_div" style="height: 650px; width: 100%;"></div>
                <style>
                    
                </style>
            </div>
            <div class="card-footer">
                Seleccione un municipio para visualizar el reporte o vea las edades más comunes.
            </div>
        </div>
    </section>
</div>


<?php
include_once 'layouts/footer.php';
} else {
    header('Location: ../index.php');
}
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="../js/edades.js"></script>