<?php
require '../model/conexion.php';

$conexion = new conexion();
$pdo = $conexion->pdo;

$columns = [
    'evaluacion',
    'reevaluacion',
    'carpeta_administrativa',
    'juzgado',
    'fecha_recepcion',
    'hora_recepcion',
    'fuero',
    'fiscalia',
    'agencia',
    'juez_control',
    'turno',
    'telefono',
    'email',
    'carpeta_investigacion',
    'falla_tecnica',
    'nuc',
    'nic',
    'fecha_disposicion',
    'hora_disposicion',
    'puesta_disposicion',
    'paterno',
    'materno',
    'nombre',
    'curp',
    'edad',
    'genero',
    'municipio',
    'colonia',
    'calle',
    'numero',
    'municipio_delito',
    'colonia_delito',
    'descripcion_delito',
    'delito1',
    'delito2',
    'delito3',
    'delito4',
    'subdireccion',
    'distrito_judicial',
    'evaluador',
    'tipo_atencion',
    'fecha_entrevista',
    'hora_entrevista',
    'defensor',
    'tipo_riesgo',
    'nuevo_tipo_riesgo',
    'riesgo168',
    'riesgo169',
    'riesgo170',
    'fecha_envio',
    'hora_envio',
    'estado',
    'verificada',
    'tipo_verificacion',
    'observaciones'
];

$columnsWhere = ['evaluacion', 'nuc', 'nic', 'paterno', 'materno', 'nombre', 'curp'];
$tables = "reevaluaciones";

$id = "evaluacion";

$campo = isset($_POST['campo']) ? $_POST['campo'] : null;

/* FILTRADO */
$where = '';

if ($campo != null) {
    $where = "WHERE (";
    $cont = count($columnsWhere);
    for ($i = 0; $i < $cont; $i++) {
        $where .= $columnsWhere[$i] . " LIKE :campo OR ";
    }
    $where = substr_replace($where, "", -4); // Para eliminar el último " OR "
    $where .= ")";
}

/* LIMIT */
$limit = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
$pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 0;

if (!$pagina) {
    $inicio = 0;
    $pagina = 1;
} else {
    $inicio = ($pagina - 1) * $limit;
}

$sLimit = "LIMIT :inicio, :limit";

/* Consulta SQL */
$sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM $tables $where $sLimit";
$stmt = $pdo->prepare($sql);

if ($campo != null) {
    $stmt->bindValue(':campo', '%' . $campo . '%', PDO::PARAM_STR);
}
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
$num_rows = count($resultado);

/* Total registros filtrados */
$sqlFiltro = "SELECT FOUND_ROWS()";
$stmtFiltro = $pdo->query($sqlFiltro);
$row_filtro = $stmtFiltro->fetch(PDO::FETCH_NUM);
$totalFiltro = $row_filtro[0];

/* Total registros */
$sqlTotal = "SELECT COUNT($id) FROM $tables";
$stmtTotal = $pdo->query($sqlTotal);
$row_total = $stmtTotal->fetch(PDO::FETCH_NUM);
$totalRegistros = $row_total[0];

$output = [];
$output['totalRegistros'] = $totalRegistros;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

if ($num_rows > 0) {
    foreach ($resultado as $row) {
        $output['data'] .= '<tr>';
        foreach ($columns as $column) {
            $output['data'] .= '<td>' . $row[$column] . '</td>';
        }
        $output['data'] .= '</tr>';
    }
} else {
    $output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="' . count($columns) . '">Sin resultados</td>';
    $output['data'] .= '</tr>';
}

if ($output['totalRegistros'] > 0) {
    $totalPaginas = ceil($output['totalRegistros'] / $limit);
    $output['paginacion'] .= '<nav>';
    $output['paginacion'] .= '<ul class="pagination">';
    $numeroInicio = 1;

    if (($pagina - 4) > 1) {
        $numeroInicio = $pagina - 4;
    }
    $numeroFin = $numeroInicio + 9;
    if ($numeroFin > $totalPaginas) {
        $numeroFin = $totalPaginas;
    }

    for ($i = $numeroInicio; $i <= $numeroFin; $i++) {
        if ($pagina == $i) {
            $output['paginacion'] .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $output['paginacion'] .= '<li class="page-item"><a class="page-link" href="#" 
            onclick="getData(' . $i . ')">' . $i . '</a></li>';
        }
    }

    $output['paginacion'] .= '</ul>';
    $output['paginacion'] .= '</nav>';
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);