<?php
require_once '../model/estadisticasMensualesModel.php';
require_once '../lib/fpdf.php';

class MYPDF extends FPDF {
    public function __construct() {
        // Constructor con orientación horizontal
        parent::__construct('L', 'mm', 'A4');
    }

    public function Header() {
        // Configuración del fondo del header
        $this->SetFillColor(139, 140, 137);
        $this->Rect(0, 0, $this->GetPageWidth(), 35, 'F');
        $this->SetTextColor(255, 255, 255);

        // Logotipo izquierdo
        $this->Image('../img/gobiernoEdoMex.png', 10, 8, 35, 20, 'PNG');

        // Título principal en el centro
        $this->SetFont('Arial', 'B', 14);
        $this->SetX(($this->GetPageWidth() - 180) / 2); // Centra el texto
        $this->Cell(180, 10, utf8_decode('CENTRO ESTATAL DE MEDIDAS CAUTELARES'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 12);
        $this->SetX(($this->GetPageWidth() - 180) / 2); // Centra el texto
        $this->Cell(180, 10, utf8_decode('Reporte de Actividades del Evaluador'), 0, 1, 'C');

        // Logotipo derecho
        $this->Image('../img/edomex.png', $this->GetPageWidth() - 45, 8, 35, 20, 'PNG');

        // Salto de línea
        $this->Ln(5);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

class EstadisticasMensualesController {
    private $model;

    public function __construct() {
        $this->model = new EstadisticasMensualesModel();
    }

    public function mostrarFormulario() {
        require_once '../view/estadisticasMensuales.php';
    } 

    public function generarReportePDF() {
        try {
            ob_clean();
    
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $fechaInicio = $_POST['fechaInicio'] ?? '';
                $fechaFin = $_POST['fechaFin'] ?? '';
    
                if (empty($fechaInicio) || empty($fechaFin)) {
                    ob_end_clean();
                    echo "Por favor, complete todos los campos.";
                    return;
                }
    
                $datos = $this->model->obtenerDatosPorSubregion($fechaInicio, $fechaFin);
    
                // Define las subregiones
                $subregiones = [
                    'Norte' => ['NORTE', 'Norte', 'norte'],
                    'Tlalnepantla' => ['Tlalnepantla', 'TLALNEPANTLA', 'tlalnepantla'],
                    'Valle de Mexico I' => ['VALLE DE MÉXICO I', 'VALLE DE MEXICO I', 'Valle de México I', 'valle de méxico i', 'VALLE MEXICO 1', 'valle mexico 1'],
                    'Sur' => ['Sur', 'SUR', 'sur'],
                    'Valle de Mexico II' => ['VALLE DE MÉXICO II', 'VALLE DE MEXICO II', 'Valle de México II', 'valle de méxico ii', 'VALLE MEXICO 2', 'valle mexico 2'],
                ];
    
                // Inicializa los totales con array_fill_keys para asegurar consistencia
                $totalesBase = [
                    'peticiones' => 0,
                    'atendidas' => 0,
                    'porcentaje_atencion' => 0,
                    'atendida_no_autorizada' => 0,
                    'no_realizada_por_libertad' => 0,
                    'no_realizada_por_prision_preventiva' => 0,
                    'no_realizada_por_circunstancias_especiales' => 0,
                    'no_realizada_por_traslado_agencia' => 0,
                    'no_realizada_por_traslado_centro_preventivo' => 0,
                    'no_realizada_por_condicion_medica' => 0,
                    'no_atendida' => 0,
                    'riesgo_alto' => 0,
                    'riesgo_medio' => 0,
                    'riesgo_bajo' => 0,
                    'sin_nivel_de_riesgo' => 0,
                    'por_informe' => 0,
                ];
    
                // Inicializa resultados usando el array base
                $resultadoPorSubregion = [];
                foreach ($subregiones as $nombre => $prefijos) {
                    $resultadoPorSubregion[$nombre] = $totalesBase;
                }
                
                // Inicializa totales generales con los mismos campos
                $totalesGenerales = $totalesBase;
    
                // Procesa los datos
                foreach ($datos as $dato) {
                    $subregionEncontrada = null;
                    foreach ($subregiones as $nombre => $variantes) {
                        if (in_array(strtolower(trim($dato->subdireccion)), array_map('strtolower', array_map('trim', $variantes)))) {
                            $subregionEncontrada = $nombre;
                            break;
                        }
                    }
    
                    if ($subregionEncontrada !== null) {
                        $resultadoPorSubregion[$subregionEncontrada]['peticiones']++;
                        $this->clasificarAtencion($resultadoPorSubregion[$subregionEncontrada], $dato->tipo_atencion);
                        $this->clasificarRiesgo($resultadoPorSubregion[$subregionEncontrada], $dato->tipo_riesgo);
                    } else {
                        error_log("Subdirección no encontrada: " . $dato->subdireccion);
                    }
                }
    
                // Calcula totales generales
                foreach ($resultadoPorSubregion as $totales) {
                    foreach ($totalesBase as $key => $_) {
                        if ($key !== 'porcentaje_atencion') {
                            $totalesGenerales[$key] += $totales[$key];
                        }
                    }
                }
    
                // Calcula porcentajes
                foreach ($resultadoPorSubregion as $nombre => &$totales) {
                    $totales['porcentaje_atencion'] = $totales['peticiones'] > 0 
                        ? round(($totales['atendidas'] / $totales['peticiones']) * 100, 2) . '%' 
                        : '0%';
                }
                
                $totalesGenerales['porcentaje_atencion'] = $totalesGenerales['peticiones'] > 0 
                    ? round(($totalesGenerales['atendidas'] / $totalesGenerales['peticiones']) * 100, 2) . '%' 
                    : '0%';
    
                // Genera el PDF
            // Crea una nueva instancia del PDF
            $pdf = new MYPDF();
            
            // Agrega la primera página
            $pdf->AddPage();
            
            // Configura la fuente después del header
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(50, 50, 50);
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');

                
            // Cambiar tamaño de la fuente para las fechas
            $pdf->SetFont('Arial', 'B', 12); // Aumentar el tamaño de la fuente
            $pdf->Cell(0, 10, utf8_decode('Desde el: ') . strftime('%d-%b-%Y', strtotime($fechaInicio)), 0, 0, 'L');
            // Fecha de fin alineada a la derecha
            $pdf->Cell(0, 10, utf8_decode('Hasta el: ') . strftime('%d-%b-%Y', strtotime($fechaFin)), 0, 1, 'R');
            $pdf->Ln(5);

            // Restaurar tamaño de fuente para la tabla
            $pdf->SetFont('Arial', 'B', 5);
            $this->addTableHeader($pdf);

    
                // Imprime las filas de datos
                $pdf->SetFont('Arial', '', 6);
                $subregionesOrdenadas = array_keys($resultadoPorSubregion);
                // Ahora procesamos todas las subregiones (sin el -1)
                for ($i = 0; $i < count($subregionesOrdenadas); $i++) {
                    $subregion = $subregionesOrdenadas[$i];
                    $this->imprimirFilaTabla($pdf, $subregion, $resultadoPorSubregion[$subregion]);
                }
    
                // Imprime la fila de totales
                $pdf->SetFont('Arial', 'B', 6);
                $this->imprimirFilaTabla($pdf, 'TOTALES', $totalesGenerales);
    
                $pdf->Output('I', "Reporte.pdf");
                exit;
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "Error generando el reporte: " . $e->getMessage();
        }
    }
    
    private function imprimirFilaTabla($pdf, $nombre, $datos) {
        $pdf->Cell(20, 6, $nombre, 1);
        $pdf->Cell(13, 6, $datos['peticiones'], 1, 0, 'C');
        $pdf->Cell(14, 6, $datos['atendidas'], 1, 0, 'C');
        $pdf->Cell(15, 6, $datos['porcentaje_atencion'], 1, 0, 'C');
        $pdf->Cell(15, 6, $datos['atendida_no_autorizada'], 1, 0, 'C');
        $pdf->Cell(18, 6, $datos['no_realizada_por_libertad'], 1, 0, 'C');
        $pdf->Cell(15, 6, $datos['no_realizada_por_prision_preventiva'], 1, 0, 'C');
        $pdf->Cell(28, 6, $datos['no_realizada_por_circunstancias_especiales'], 1, 0, 'C');
        $pdf->Cell(25, 6, $datos['no_realizada_por_traslado_agencia'], 1, 0, 'C');
        $pdf->Cell(26, 6, $datos['no_realizada_por_traslado_centro_preventivo'], 1, 0, 'C');
        $pdf->Cell(20, 6, $datos['no_realizada_por_condicion_medica'], 1, 0, 'C');
        $pdf->Cell(15, 6, $datos['no_atendida'], 1, 0, 'C');
        $pdf->Cell(9, 6, $datos['riesgo_alto'], 1, 0, 'C');
        $pdf->Cell(9, 6, $datos['riesgo_medio'], 1, 0, 'C');
        $pdf->Cell(9, 6, $datos['riesgo_bajo'], 1, 0, 'C');
        $pdf->Cell(13, 6, $datos['sin_nivel_de_riesgo'], 1, 0, 'C');
        $pdf->Cell(11, 6, $datos['por_informe'], 1, 0, 'C');
        $pdf->Ln();
    }
    
    // Métodos auxiliares para clasificación mejorados
    private function clasificarAtencion(&$subregion, $tipoAtencion) {
        $mapeoAtencion = [
            'atendidas' => [
                'SI ATENDIDA, SI AUTORIZADA',
                'ATENDIDA, SI AUTORIZADA'
            ],
            'atendida_no_autorizada' => [
                'SI ATENDIDA, PERO NO AUTORIZADA',
                'ATENDIDA, NO AUTORIZADA'
            ],
            'no_realizada_por_libertad' => [
                'SI ATENDIDA, NO REALIZADA POR LIBERTAD',
                'ATENDIDA, NO REALIZADA POR LIBERTAD'
            ],
            'no_realizada_por_prision_preventiva' => [
                'SI ATENDIDA, NO REALIZADA POR PRISION PREVENTIVA',
                'SI ATENDIDA, NO REALIZADA POR PRISIÓN PREVENTIVA',
                'SI ATENDIDA, NO AREALIZADA POR PRISION PREVENTIVA',
                'ATENDIDA, NO REALIZADA POR PRISIÓN PREVENTIVA'
            ],
            'no_realizada_por_circunstancias_especiales' => [
                'SI ATENDIDA, NO REALIZADA POR CIRCUNSTANCIAS ESPECIALES',
                'ATENDIDA, NO REALIZADA POR CIRCUNSTANCIAS ESPECIALES'
            ],
            'no_realizada_por_traslado_agencia' => [
                'SI ATENDIDA, NO REALIZADA POR TRASLADO A AGENCIA DEL MP',
                'SI ATENDIDA, NO REALIZADA POR TRASLADO DE MP',
                'ATENDIDA, NO REALIZADA POR TRASLADO A AGENCIA'
            ],
            'no_realizada_por_traslado_centro_preventivo' => [
                'SI ATENDIDA, NO REALIZADA POR TRASLADO A CENTRO PREVENTIVO',
                'ATENDIDA, NO REALIZADA POR TRASLADO A CENTRO PREVENTIVO'
            ],
            'no_realizada_por_condicion_medica' => [
                'SI ATENDIDA, NO REALIZADA POR CONDICION MEDICA/HOSPITALIZACION',
                'SI ATENDIDA, NO REALIZADA POR CONDICIÓN MÉDICA/HOSPITALIZACIÓN',
                'ATENDIDA, NO REALIZADA POR CONDICIÓN MÉDICA'
            ]
        ];
    
        foreach ($mapeoAtencion as $campo => $patrones) {
            foreach ($patrones as $patron) {
                if (stripos($tipoAtencion, $patron) !== false) {
                    $subregion[$campo]++;
                    return;
                }
            }
        }
    
        // Si no encuentra coincidencia, podría considerarse como no atendida
        $subregion['no_atendida']++;
    }
    
    private function clasificarRiesgo(&$subregion, $tipoRiesgo) {
        $mapeoRiesgo = [
            'riesgo_alto' => ['RIESGO ALTO'],
            'riesgo_medio' => ['RIESGO MEDIO'],
            'riesgo_bajo' => ['RIESGO BAJO'],
            'sin_nivel_de_riesgo' => ['SIN CATEGORÍA', 'SIN CATEGORIA'],
            'por_informe' => ['INFORME', 'informe']
        ];
    
        foreach ($mapeoRiesgo as $campo => $patrones) {
            foreach ($patrones as $patron) {
                if (stripos($tipoRiesgo, $patron) !== false) {
                    $subregion[$campo]++;
                    return;
                }
            }
        }
    }

    
    
    public function generarReportePDFR() {
        try {
            ob_start();
    
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $tipoUsuario = $_POST['tipo_usuario'] ?? '';
                $fechaInicio = $_POST['fechaInicio'] ?? '';
                $fechaFin = $_POST['fechaFin'] ?? '';
    
                if (empty($tipoUsuario) || empty($fechaInicio) || empty($fechaFin)) {
                    ob_end_clean();
                    echo "Por favor, complete todos los campos.";
                    return;
                }
    
    
                if (empty($datos)) {
                    ob_end_clean();
                    echo "No se encontraron datos para el rango de fechas seleccionado.";
                    return;
                }
    
                $pdf = new FPDF('L', 'mm', 'A4');
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 5);
    
                // Cabecera del PDF
                $pdf->Cell(0, 10, utf8_decode("Reporte de Productividad - $tipoUsuario"), 0, 1, 'C');
                $pdf->Cell(0, 10, utf8_decode("Desde: $fechaInicio Hasta: $fechaFin"), 0, 1, 'C');
                $pdf->Ln(10);
    
                // Encabezados de la tabla
                $this->addTableHeader($pdf);
    
                // Datos de la tabla
                $pdf->SetFont('Arial', '', 6);
                foreach ($datos as $dato) {
                    $this->addTableRow($pdf, [
                        utf8_decode($dato->subregion),
                        $dato->peticiones,
                        $dato->atendidas,
                        $dato->porcentaje_atencion . '%',
                        $dato->atendida_no_autorizada,
                        $dato->no_realizada_por_libertad,
                        $dato->no_realizada_por_prision_preventiva,
                        $dato->no_realizada_por_circunstancias_especiales,
                        $dato->no_realizada_por_traslado_agencia,
                        $dato->no_realizada_por_traslado_centro_preventivo,
                        $dato->no_realizada_por_condicion_medica,
                        $dato->no_atendida,
                        $dato->riesgo_alto,
                        $dato->riesgo_medio,
                        $dato->riesgo_bajo,
                        $dato->sin_nivel_de_riesgo,
                        $dato->informe
                    ]);
                }
    
                $pdf->Ln(10);
                $pdf->Cell(0, 10, "Elaboró", 0, 1, 'R');
                $pdf->Cell(0, 10, utf8_decode("Área de Monitoreo e Informática"), 0, 1, 'R');
    
                ob_end_clean();
                $pdf->Output('I', "Reporte_$tipoUsuario.pdf");
                exit;
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "Error generando el reporte: " . $e->getMessage();
        }
    }
    
    private function addTableRow($pdf, $data) {
        $widths = [20, 13, 14, 15, 15, 18, 15,28,25,26,20,15,9,9,9,13,11];
        $cellHeight = 6; // Altura base para cada línea
    
        // Determinar la altura máxima de la fila
        $maxHeight = $this->getMaxRowHeight($pdf, $data, $widths, $cellHeight);
    
        // Dibujar cada celda de la fila
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        foreach ($data as $i => $text) {
            $startX = $x;
            $startY = $y;
    
            // Dibujar un rectángulo para la celda (solo el borde)
            $pdf->Rect($startX, $startY, $widths[$i], $maxHeight);
    
            // Calcular la posición para centrar el texto verticalmente
            $nbLines = ceil($pdf->GetStringWidth($text) / ($widths[$i] - 2));
            $textHeight = $nbLines * $cellHeight;
            $verticalPadding = ($maxHeight - $textHeight) / 2;
    
            // Dibujar el texto dentro de la celda centrado
            $pdf->SetXY($startX, $startY + $verticalPadding);
            $pdf->MultiCell($widths[$i], $cellHeight, utf8_decode($text), 0, 'C');
    
            // Ajustar posición del cursor a la siguiente celda
            $x += $widths[$i];
            $pdf->SetXY($x, $y);
        }
    
        // Moverse a la siguiente fila
        $pdf->Ln($maxHeight);
    }
    
    private function getMaxRowHeight($pdf, $data, $widths, $cellHeight) {
        $maxHeight = 0;
    
        foreach ($data as $i => $text) {
            // Calcular el número de líneas necesarias para esta celda
            $nbLines = ceil($pdf->GetStringWidth($text) / ($widths[$i] - 2)); // Restar 2 para márgenes
            $height = $nbLines * $cellHeight; // Altura total para esta celda
    
            // Mantener la mayor altura encontrada
            if ($height > $maxHeight) {
                $maxHeight = $height;
            }
        }
    
        return $maxHeight;
    }
    
    private function addTableHeader($pdf) {
        $headers = [
            'SUBDIRECCIÓN', 
            'PETICIONES', 
            'ATENDIDAS Y REALIZADAS', 
            'PORCENTAJE DE ATENCIÓN',
            'ATENDIDA, NO AUTORIZADA',
            'ATENDIDA, NO REALIZADA POR LIBERTAD',
            'ATENDIDA, NO REALIZADA POR PRISIÓN PREVENTIVA', 
            'ATENDIDA, NO REALIZADA POR CIRCUNSTANCIAS ESPECIALES',
            'ATENDIDA, NO REALIZADA POR TRASLADO DE AGENCIA',
            'ATENDIDA, NO REALIZADA POR TRASLADO A CENTRO PREVENTIVO',
            'ATENDIDA, NO REALIZADA POR CONDICIÓN MEDICA',
            'NO ATENDIDA',    
            'RIESGO ALTO', 
            'RIESGO MEDIO', 
            'RIESGO BAJO',
            'SIN NIVEL DE RIESGO',
            'INFORME'
        ];
    
        $widths = [20, 13, 14, 15, 15, 18, 15,28,25,26,20,15,9,9,9,13,11];
        $cellHeight = 6;
    
        // Calcular la altura máxima para los encabezados
        $maxHeight = $this->getMaxRowHeight($pdf, $headers, $widths, $cellHeight);
        $pdf->SetFillColor(139, 140, 137); // Fondo gris
        $pdf->SetTextColor(255, 255, 255); // Ejemplo: Texto rojo


    
        // Dibujar los encabezados
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        foreach ($headers as $i => $header) {
            $startX = $x;
            $startY = $y;
    
            // Dibujar un rectángulo para el encabezado (solo el borde)
            $pdf->Rect($startX, $startY, $widths[$i], $maxHeight,'DF');
    
            // Calcular la posición para centrar el texto verticalmente
            $nbLines = ceil($pdf->GetStringWidth($header) / ($widths[$i] - 2));
            $textHeight = $nbLines * $cellHeight;
            $verticalPadding = ($maxHeight - $textHeight) / 2;
    
            // Dibujar el texto dentro del rectángulo centrado
            $pdf->SetXY($startX, $startY + $verticalPadding);
            $pdf->MultiCell($widths[$i], $cellHeight, utf8_decode($header), 0, 'C');
    
            // Ajustar posición del cursor a la siguiente celda
            $x += $widths[$i];
            $pdf->SetXY($x, $y);
        }
    
        // Moverse a la siguiente fila
        $pdf->Ln($maxHeight);
        $pdf->SetTextColor(0, 0, 0); // Restaurar a texto negro

    }
}

// Manejo de acciones
$action = $_GET['action'] ?? 'mostrarFormulario';
$controller = new EstadisticasMensualesController();

if ($action === 'generarReportePDF') {
    $controller->generarReportePDF();
} elseif ($action === 'generarReportePDFR') {
    $controller->generarReportePDFR();
} else {
    $controller->mostrarFormulario();
}
?>
