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
        require_once '../controller/reporteProductividadController.php';
        $controller = new ReporteProductividadController();
    }
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Adm | Reportes de Productividad</title>
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
                            <li class="breadcrumb-item active">Reportes de Productividad</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <div class="container-main">
            <h2>Reportes de Productividad</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            <div class="row justify-content-center">
                <div class="col-md-8 report-card">
                    <div class="card-header text-center">
                        Reporte de Evaluador - Tabla: Evaluación Adolescentes.
                    </div>
                    <div class="card-body">
                        <form action="../controller/reporteProductividadController.php?action=generarReportePDF" method="POST" target="_blank">
                            <select name="id" class="form-control mb-3" required>
                                <option value="">Selecciona el Evaluador</option>
                                <?php if (!empty($_SESSION['evaluadores_adolescentes'])): ?>
                                    <?php foreach ($_SESSION['evaluadores_adolescentes'] as $evaluador): ?>
                                        <option value="<?php echo htmlspecialchars($evaluador->id); ?>">
                                            <?php echo htmlspecialchars($evaluador->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <!-- Campos de fecha -->
                            <div class="row">
                                <div class="col">
                                    <label for="fechaInicio">Fecha Inicio</label>
                                    <input type="date" name="fechaInicio" id="fechaInicio" class="form-control" max="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col">
                                    <label for="fechaFin">Fecha Fin</label>
                                    <input type="date" name="fechaFin" id="fechaFin" class="form-control" max="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Generar Reporte</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Formulario para Reevaluación Adolescentes -->
            <div class="row justify-content-center">
                <div class="col-md-8 report-card">
                    <div class="card-header text-center">
                        Reporte de Evaluador - Tabla: Reevaluación Adolescentes.
                    </div>
                    <div class="card-body">
                        <form action="../controller/reporteProductividadController.php?action=generarReportePDFR" method="POST" target="_blank">
                            <select name="id" class="form-control mb-3" required>
                                <option value="">Selecciona el Evaluador</option>
                                <?php if (!empty($_SESSION['evaluadores_reevaluadores'])): ?>
                                    <?php foreach ($_SESSION['evaluadores_reevaluadores'] as $evaluador): ?>
                                        <option value="<?php echo htmlspecialchars($evaluador->id); ?>">
                                            <?php echo htmlspecialchars($evaluador->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <!-- Campos de fecha -->
                            <div class="row">
                                <div class="col">
                                    <label for="fechaInicio">Fecha Inicio</label>
                                    <input type="date" name="fechaInicio" id="fechaInicio" class="form-control" max="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col">
                                    <label for="fechaFin">Fecha Fin</label>
                                    <input type="date" name="fechaFin" id="fechaFin" class="form-control" max="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Generar Reporte</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>
<?php
    include_once 'layouts/footer.php';
} else {
    echo '<div class="alert alert-danger text-center">No tienes permisos para acceder a esta página.</div>';
}
?>