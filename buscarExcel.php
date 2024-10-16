<?php
require 'conexion.php';

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
    'ministerio_publico',
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
    'catalogo1',
    'catalogo2',
    'catalogo3',
    'catalogo4',
    'subdireccion',
    'distrito_judicial',
    'evaluador',
    'tipo_atencion',
    'tutor',
    'fecha_entrevista',
    'hora_entrevista',
    'defensor',
    'tipo_riesgo',
    'riesgo_168',
    'riesgo_169',
    'riesgo_170',
    'fecha_envio',
    'hora_envio',
    'estado',
    'verificado',
    'tipo_verificacion',
    'observaciones'
];

$columnsWhere = ['evaluacion', 'nuc', 'nic', 'paterno', 'materno', 'nombre', 'curp'];
$tables = "eval_adolescentes";

$id = "evaluacion";

$campo = isset($_POST['campo']) ? $mysqli->real_escape_string($_POST['campo']) : null;

/* FILTRADO */
$where = '';

if ($campo != null) {
    $where = "WHERE (";
    $cont = count($columnsWhere);
    for ($i = 0; $i < $cont; $i++) {
        $where .= $columnsWhere[$i] . " LIKE '%" . $campo . "%' OR ";
    }
    $where = substr_replace($where, "", -4); // Para eliminar el último " OR "
    $where .= ")";
}

/* LIMIT*/

$limit = isset($_POST['registros']) ? $mysqli->real_escape_string($_POST['registros']) : 10;
$pagina = isset($_POST['pagina']) ? $mysqli->real_escape_string($_POST['pagina']) : 0;

if (!$pagina) {
    $inicio = 0;
    $pagina = 1;
} else {
    $inicio = ($pagina - 1) * $limit;
}

$sLimit = "LIMIT $inicio, $limit";


/*Consulta sql */
$sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " FROM $tables $where $sLimit";
$resultado = $mysqli->query($sql);
$num_rows = $resultado->num_rows;

/* Total registros filtrados */
$sqlFiltro = "SELECT FOUND_ROWS()";
$resFiltro = $mysqli->query($sqlFiltro);
$row_filtro = $resFiltro->fetch_array();
$totalFiltro = $row_filtro[0];


/* Total registros filtrados */
$sqlTotal = "SELECT count($id) FROM $tables";
$resTotal = $mysqli->query($sqlTotal);
$row_total = $resTotal->fetch_array();
$totalRegistros = $row_total[0];

$output = [];
$output['totalRegistros'] = $totalRegistros;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

if ($num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $output['data'] .= '<tr>';
        $output['data'] .= '<td>' . $row['evaluacion'] . '</td>';
        $output['data'] .= '<td>' . $row['reevaluacion'] . '</td>';
        $output['data'] .= '<td>' . $row['carpeta_administrativa'] . '</td>';
        $output['data'] .= '<td>' . $row['juzgado'] . '</td>';
        $output['data'] .= '<td>' . $row['fecha_recepcion'] . '</td>';
        $output['data'] .= '<td>' . $row['hora_recepcion'] . '</td>';
        $output['data'] .= '<td>' . $row['fuero'] . '</td>';
        $output['data'] .= '<td>' . $row['fiscalia'] . '</td>';
        $output['data'] .= '<td>' . $row['agencia'] . '</td>';
        $output['data'] .= '<td>' . $row['ministerio_publico'] . '</td>';
        $output['data'] .= '<td>' . $row['turno'] . '</td>';
        $output['data'] .= '<td>' . $row['telefono'] . '</td>';
        $output['data'] .= '<td>' . $row['email'] . '</td>';
        $output['data'] .= '<td>' . $row['carpeta_investigacion'] . '</td>';
        $output['data'] .= '<td>' . $row['falla_tecnica'] . '</td>';
        $output['data'] .= '<td>' . $row['nuc'] . '</td>';
        $output['data'] .= '<td>' . $row['nic'] . '</td>';
        $output['data'] .= '<td>' . $row['fecha_disposicion'] . '</td>';
        $output['data'] .= '<td>' . $row['hora_disposicion'] . '</td>';
        $output['data'] .= '<td>' . $row['puesta_disposicion'] . '</td>';
        $output['data'] .= '<td>' . $row['paterno'] . '</td>';
        $output['data'] .= '<td>' . $row['materno'] . '</td>';
        $output['data'] .= '<td>' . $row['nombre'] . '</td>';
        $output['data'] .= '<td>' . $row['curp'] . '</td>';
        $output['data'] .= '<td>' . $row['edad'] . '</td>';
        $output['data'] .= '<td>' . $row['genero'] . '</td>';
        $output['data'] .= '<td>' . $row['municipio'] . '</td>';
        $output['data'] .= '<td>' . $row['colonia'] . '</td>';
        $output['data'] .= '<td>' . $row['calle'] . '</td>';
        $output['data'] .= '<td>' . $row['numero'] . '</td>';
        $output['data'] .= '<td>' . $row['municipio_delito'] . '</td>';
        $output['data'] .= '<td>' . $row['colonia_delito'] . '</td>';
        $output['data'] .= '<td>' . $row['descripcion_delito'] . '</td>';
        $output['data'] .= '<td>' . $row['catalogo1'] . '</td>';
        $output['data'] .= '<td>' . $row['catalogo2'] . '</td>';
        $output['data'] .= '<td>' . $row['catalogo3'] . '</td>';
        $output['data'] .= '<td>' . $row['catalogo4'] . '</td>';
        $output['data'] .= '<td>' . $row['subdireccion'] . '</td>';
        $output['data'] .= '<td>' . $row['distrito_judicial'] . '</td>';
        $output['data'] .= '<td>' . $row['evaluador'] . '</td>';
        $output['data'] .= '<td>' . $row['tipo_atencion'] . '</td>';
        $output['data'] .= '<td>' . $row['tutor'] . '</td>';
        $output['data'] .= '<td>' . $row['fecha_entrevista'] . '</td>';
        $output['data'] .= '<td>' . $row['hora_entrevista'] . '</td>';
        $output['data'] .= '<td>' . $row['defensor'] . '</td>';
        $output['data'] .= '<td>' . $row['tipo_riesgo'] . '</td>';
        $output['data'] .= '<td>' . $row['riesgo_168'] . '</td>';
        $output['data'] .= '<td>' . $row['riesgo_169'] . '</td>';
        $output['data'] .= '<td>' . $row['riesgo_170'] . '</td>';
        $output['data'] .= '<td>' . $row['fecha_envio'] . '</td>';
        $output['data'] .= '<td>' . $row['hora_envio'] . '</td>';
        $output['data'] .= '<td>' . $row['estado'] . '</td>';
        $output['data'] .= '<td>' . $row['verificado'] . '</td>';
        $output['data'] .= '<td>' . $row['tipo_verificacion'] . '</td>';
        $output['data'] .= '<td>' . $row['observaciones'] . '</td>';
        $output['data'] .= '</tr>';
    }
} else {
    $output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="7">Sin resultados</td>';
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
