<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['tipo_usuario_id'] == 1 || $_SESSION['tipo_usuario_id'] == 3  || $_SESSION['tipo_usuario_id'] == 2) {
    include_once 'layouts/header.php';
    include_once 'layouts/nav.php';
    $evaluadores = $_SESSION['evaluadores'] ?? null;

    // Si no tenemos evaluadores, intentamos obtenerlos
    if ($evaluadores === null) {
        require_once '../controller/reporteIndicadoresController.php';
        $controller = new ReporteIndicadoresController();
    }
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Adm | Reportes de Supervisión</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body,
            html {
                height: 100%;
                font-family: Arial, sans-serif;
                background-color: #f4f6f9;
            }

            .container-main {
                margin-left: 250px;
                padding: 20px;
                min-height: 100vh;
            }

            h2 {
                margin-bottom: 30px;
                font-weight: bold;
                color: #333;
                text-align: left;
            }

            .report-card {
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                padding: 20px;
                margin-bottom: 20px;
            }

            .btn-primary {
                background-color: #007bff;
                border: none;
                width: 100%;
            }

            .btn-primary:hover {
                background-color: #0056b3;
            }
        </style>
    </head>

    <body>
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../view/menuAdmin.php">Ir a Inicio</a></li>
                            <li class="breadcrumb-item active">Reportes de Supervisión</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <div class="container-main">
            <h2>Reportes de Supervisión</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            <!-- Formulario para Evaluación Adolescentes -->
            <div class="row justify-content-center">
                <div class="col-md-8 report-card">
                    <div class="card-header text-center">
                    </div>
                    <div class="card-body">
                        <form action="../controller/reporteIndicadoresController.php" method="POST" target="_blank">
                            <select name="tipo_usuario" id="tipo_indicador" class="form-control mb-3" required onchange="updateIndicadorAction(this)">
                                <option value="">Selecciona una opcion para generar el reporte</option>
                                <option value="Adolescentes">Adolescentes</option>
                                <option value="Adultos">Adultos</option>
                            </select>

                            <!-- Campos de fecha -->
                            <div class="row">
                                <div class="col">
                                    <label for="fechaInicio">Fecha Inicio</label>
                                    <input type="date" name="fechaInicio" id="fechaInicio"
                                        class="form-control"
                                        max="<?= date('Y-m-d') ?>"
                                        min="<?= date('Y-m-d', strtotime('-10 years')) ?>"
                                        required>
                                </div>
                                <div class="col">
                                    <label for="fechaFin">Fecha Fin</label>
                                    <input type="date" name="fechaFin" id="fechaFin"
                                        class="form-control"
                                        max="<?= date('Y-m-d') ?>"
                                        min="<?= date('Y-m-d', strtotime('-10 years')) ?>"
                                        required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Generar Reporte</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Formulario para Reevaluación -->
            <div class="row justify-content-center">
                <div class="col-md-8 report-card">
                    <div class="card-header text-center">
                    </div>
                    <div class="card-body">
                        <form action="../controller/reporteIndicadoresController.php" method="POST" target="_blank">
                            <select name="tipo_usuario" id="tipo_supervision" class="form-control mb-3" required onchange="updateSupervisionAction(this)">
                                <option value="">Selecciona una opcion para generar el reporte</option>
                                <option value="medidas_adolescentes">Medidas Adultos</option>
                                <option value="condiciones_adolescentes">Condiciones Adultos</option>
                                <option value="colab_medidas_adolescentes">Colaboraciones de Medidas de Adultos</option>
                                <option value="colab_condiciones_adolescentes">Colaboraciones de Condiciones de Adultos</option>
                            </select>

                            <!-- Campos de fecha -->
                            <div class="row">
                                <div class="col">
                                    <label for="fechaInicio">Fecha Inicio</label>
                                    <input type="date" name="fechaInicio" id="fechaInicio"
                                        class="form-control"
                                        max="<?= date('Y-m-d') ?>"
                                        min="<?= date('Y-m-d', strtotime('-10 years')) ?>"
                                        required>
                                </div>
                                <div class="col">
                                    <label for="fechaFin">Fecha Fin</label>
                                    <input type="date" name="fechaFin" id="fechaFin"
                                        class="form-control"
                                        max="<?= date('Y-m-d') ?>"
                                        min="<?= date('Y-m-d', strtotime('-10 years')) ?>"
                                        required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Generar Reporte</button>
                        </form>
                    </div>
                </div>
            </div>
            <script>
                function updateIndicadorAction(select) {
                    var form = select.form;
                    var action = '../controller/reporteIndicadoresController.php?action=';

                    if (select.value === 'Adolescentes') {
                        action += 'generarReportePDF';
                    } else if (select.value === 'Adultos') {
                        action += 'generarReportePDFR';
                    }

                    form.action = action;
                }

                function updateSupervisionAction(select) {
                    var form = select.form;
                    var action = '../controller/reporteIndicadoresController.php?action=';

                    switch (select.value) {
                        case 'medidas_adolescentes':
                            action += 'generarReportePDFM';
                            break;
                        case 'condiciones_adolescentes':
                            action += 'generarReportePDFC';
                            break;
                        case 'colab_medidas_adolescentes':
                            action += 'generarReportePDFCM';
                            break;
                        case 'colab_condiciones_adolescentes':
                            action += 'generarReportePDFCC';
                            break;
                    }

                    form.action = action;
                }
            </script>
        </div>
    </body>

    </html>
<?php
    include_once 'layouts/footer.php';
} else {
    echo '<div class="alert alert-danger text-center">No tienes permisos para acceder a esta página.</div>';
}
?>