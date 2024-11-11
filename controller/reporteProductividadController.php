<?php
require_once '../model/reporteProductividadModel.php';
require_once '../lib/fpdf.php';

class MYPDF extends FPDF {
    public function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(0, 10, utf8_decode('Reporte de Productividad'), 0, 1, 'C');
        $this->Ln(5);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    // Agregamos el método NbLines para calcular el número de líneas
    public function NbLines($w, $txt) {
        // Computa el número de líneas que tomará un MultiCell de ancho w
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c] ?? 1000;
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}

class ReporteProductividadController {
    private $model;
    
    private const COL_TIPO_WIDTH = 90;
    private const COL_PORCENTAJE_WIDTH = 50;
    private const COL_TOTALES_WIDTH = 50;
    private const TABLE_WIDTH = 190;

    public function __construct() {
        $this->model = new ReporteProductividadModel();
    }

    public function generarReportePDF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $fechaInicio = $_POST['fechaInicio'] ?? '';
            $fechaFin = $_POST['fechaFin'] ?? '';

            if (empty($id) || empty($fechaInicio) || empty($fechaFin)) {
                echo "Por favor, complete todos los campos.";
                return;
            }

            $resumenAtencion = $this->model->obtenerResumenPorTipoAtencion($id, $fechaInicio, $fechaFin);
            $resumenRiesgo = $this->model->obtenerResumenPorTipoRiesgo($id, $fechaInicio, $fechaFin);

            if (empty($resumenAtencion) && empty($resumenRiesgo)) {
                echo "No hay datos para el reporte.";
                return;
            }

            $pdf = new MYPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetMargins(10, 10, 10);

            // Información general
            $pdf->Cell(0, 10, utf8_decode('Evaluador: ') . $id, 0, 1);
            $pdf->Cell(0, 10, utf8_decode('Desde el: ') . date("d-M-Y", strtotime($fechaInicio)) . 
                       utf8_decode(' Hasta el: ') . date("d-M-Y", strtotime($fechaFin)), 0, 1);
            $pdf->Ln(10);

            // Crear tablas con datos de resumen
            $this->crearTabla($pdf, 'Tipo de Atención', 
                             ['Tipo de Atención', 'Porcentaje', 'Totales'], 
                             $resumenAtencion);
            $pdf->Ln(10);
            $this->crearTabla($pdf, 'Tipo de Riesgo', 
                             ['Tipo de Riesgo', 'Porcentaje', 'Totales'], 
                             $resumenRiesgo);

            $pdf->Output();
        }
    }

    private function crearTabla($pdf, $titulo, $encabezados, $data) {
        // Calcular posición X inicial para centrar la tabla
        $leftMargin = ($pdf->GetPageWidth() - self::TABLE_WIDTH) / 2;
        $pdf->SetX($leftMargin);

        // Título de la tabla
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(128, 64, 0);
        $pdf->Cell(self::TABLE_WIDTH, 10, utf8_decode($titulo), 1, 1, 'C', true);
        
        // Encabezados
        $pdf->SetX($leftMargin);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(self::COL_TIPO_WIDTH, 8, utf8_decode($encabezados[0]), 1, 0, 'C', true);
        $pdf->Cell(self::COL_PORCENTAJE_WIDTH, 8, utf8_decode($encabezados[1]), 1, 0, 'C', true);
        $pdf->Cell(self::COL_TOTALES_WIDTH, 8, utf8_decode($encabezados[2]), 1, 1, 'C', true);

        // Datos
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(245, 245, 245);
        $fill = false;
        $totalSum = 0;
        $percentageSum = 0;

        foreach ($data as $row) {
            $pdf->SetX($leftMargin);
            
            // Calcular altura necesaria
            $texto = utf8_decode($row->tipo ?? 'N/A');
            $lineHeight = 6;
            $nbLines = max(1, $pdf->NbLines(self::COL_TIPO_WIDTH, $texto));
            $height = $lineHeight * $nbLines;

            // Almacenar posición inicial
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // Dibujar celda con el tipo
            $pdf->MultiCell(self::COL_TIPO_WIDTH, $lineHeight, $texto, 1, 'L', $fill);
            
            // Restaurar posición y dibujar las otras celdas
            $pdf->SetXY($x + self::COL_TIPO_WIDTH, $y);
            $pdf->Cell(self::COL_PORCENTAJE_WIDTH, $height, 
                      number_format($row->porcentaje ?? 0, 2) . '%', 1, 0, 'C', $fill);
            $pdf->Cell(self::COL_TOTALES_WIDTH, $height, 
                      $row->total ?? '0', 1, 1, 'C', $fill);

            $totalSum += $row->total ?? 0;
            $percentageSum += $row->porcentaje ?? 0;
            $fill = !$fill;
        }

        // Totales
        $pdf->SetX($leftMargin);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(self::COL_TIPO_WIDTH, 8, 'Total', 1, 0, 'C', true);
        $pdf->Cell(self::COL_PORCENTAJE_WIDTH, 8, 
                  number_format($percentageSum, 2) . '%', 1, 0, 'C', true);
        $pdf->Cell(self::COL_TOTALES_WIDTH, 8, $totalSum, 1, 1, 'C', true);
    }

    public function mostrarFormulario() {
        echo "Formulario de reportes";
    }
}

$action = $_GET['action'] ?? 'mostrarFormulario';
$controller = new ReporteProductividadController();

if ($action === 'generarReportePDF') {
    $controller->generarReportePDF();
} else {
    $controller->mostrarFormulario();
}
?>