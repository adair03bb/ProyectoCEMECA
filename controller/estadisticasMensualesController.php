<?php
require_once '../model/estadisticasMensualesModel.php';
require_once '../lib/fpdf.php';

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
    
                $datos = $this->model->obtenerDatosPorSubregion($tipoUsuario, $fechaInicio, $fechaFin);
    
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
    
        // Dibujar los encabezados
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        foreach ($headers as $i => $header) {
            $startX = $x;
            $startY = $y;
    
            // Dibujar un rectángulo para el encabezado (solo el borde)
            $pdf->Rect($startX, $startY, $widths[$i], $maxHeight);
    
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
    }
    
    
    
}

// Manejo de acciones
$action = $_GET['action'] ?? 'mostrarFormulario';
$controller = new EstadisticasMensualesController();

if ($action === 'generarReportePDF') {
    $controller->generarReportePDF();
} else {
    $controller->mostrarFormulario();
}
