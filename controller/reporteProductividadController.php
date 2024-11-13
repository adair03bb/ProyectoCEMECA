<?php
require_once '../model/reporteProductividadModel.php';
require_once '../lib/fpdf.php';

class MYPDF extends FPDF {
    public function Header() {
        // Configuración del fondo del header
        $this->SetFillColor(139, 140, 137);
        $this->Rect(0, 0, $this->GetPageWidth(), 35, 'F');
        $this->SetTextColor(255, 255, 255); // Texto blanco

        // Logotipo izquierdo (con fondo transparente)
        $this->Image('../img/gobiernoEdoMex.png', 8, 8, 35, 20, 'PNG'); // Asegúrate de que sea PNG con fondo transparente

        // Título principal en el centro
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('CENTRO ESTATAL DE MEDIDAS CAUTELARES'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(0, 14, utf8_decode('Reporte de Actividades del Evaluador'), 0, 1, 'C');

        // Logotipo derecho (con fondo transparente)
        $this->Image('../img/edomex.png', 168, 8, 35, 20, 'PNG'); // Asegúrate de que sea PNG con fondo transparente

        // Salto de línea para separar del contenido
        $this->Ln(12);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

class ReporteProductividadController {
    private $model;
    
    private const COL_TIPO_WIDTH = 120;
    private const COL_PORCENTAJE_WIDTH = 35;
    private const COL_TOTALES_WIDTH = 35;
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
            $resumenAgencia = $this->model->obtenerResumenPorAgencia($id, $fechaInicio, $fechaFin);
            $resumenTipoVerificacion = $this->model->obtenerResumenPorTipoVerificacion($id, $fechaInicio, $fechaFin);

            if (empty($resumenAtencion) && empty($resumenRiesgo) && empty($resumenAgencia) && empty($resumenTipoVerificacion)) {
                echo "No hay datos para el reporte.";
                return;
            }

            $pdf = new MYPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetMargins(10, 10, 10);

            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');

            // Información general centrada
            $pdf->Ln(5); // Espacio adicional
            $pdf->Cell(0, 10, utf8_decode('Evaluador: ') . utf8_decode($id), 0, 1, 'C');
            $pdf->Cell(95, 10, utf8_decode('Desde el: ') . strftime('%d-%b-%Y', strtotime($fechaInicio)), 0, 0, 'L'); // Fecha inicio a la izquierda
            $pdf->Cell(95, 10, utf8_decode('Hasta el: ') . strftime('%d-%b-%Y', strtotime($fechaFin)), 0, 1, 'R'); // Fecha fin a la derecha

            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, utf8_decode('Fuente de datos: Tabla - Evaluación Adolescentes'), 0, 1, 'L');
            $pdf->Ln(10);

            $this->crearTabla($pdf, 'Tipo de Atención', 
                             ['Tipo de Atención', 'Porcentaje', 'Totales'], 
                             $resumenAtencion);
            $pdf->Ln(10);
            $this->crearTabla($pdf, 'Tipo de Riesgo', 
                             ['Tipo de Riesgo', 'Porcentaje', 'Totales'], 
                             $resumenRiesgo);
            $pdf->Ln(10);
            $this->crearTabla($pdf, 'Agencia', 
                             ['Agencia', 'Porcentaje', 'Totales'], 
                             $resumenAgencia);
            $pdf->Ln(10);
            $this->crearTabla($pdf, 'Tipo de Verificación', 
                             ['Tipo de Verificación', 'Porcentaje', 'Totales'], 
                             $resumenTipoVerificacion);

            $pdf->Output();
        }
    }

    private function crearTabla($pdf, $titulo, $encabezados, $data) {
        $leftMargin = ($pdf->GetPageWidth() - self::TABLE_WIDTH) / 2;
        $pdf->SetX($leftMargin);
    
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(139, 140, 137);
        
        // Verificar si la tabla cabe en la página actual
        if ($pdf->GetY() + 10 + (count($data) + 1) * 8 > $pdf->GetPageHeight() - 15) {
            // Si no cabe, agregar una nueva página
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(139, 140, 137);
        }
    
        $pdf->Cell(self::TABLE_WIDTH, 10, utf8_decode($titulo), 1, 1, 'C', true);
        
        $pdf->SetX($leftMargin);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(self::COL_TIPO_WIDTH, 8, utf8_decode($encabezados[0]), 1, 0, 'C', true);
        $pdf->Cell(self::COL_PORCENTAJE_WIDTH, 8, utf8_decode($encabezados[1]), 1, 0, 'C', true);
        $pdf->Cell(self::COL_TOTALES_WIDTH, 8, utf8_decode($encabezados[2]), 1, 1, 'C', true);
    
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(255, 255, 255);
        $fill = false;
        $totalSum = 0;
        $percentageSum = 0;
    
        foreach ($data as $row) {
            // Verificar si la fila cabe en la página actual
            if ($pdf->GetY() + 8 > $pdf->GetPageHeight() - 15) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFillColor(139, 140, 137);
                $pdf->Cell(self::TABLE_WIDTH, 10, utf8_decode($titulo), 1, 1, 'C', true);
                
                $pdf->SetX($leftMargin);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFillColor(230, 230, 230);
                $pdf->Cell(self::COL_TIPO_WIDTH, 8, utf8_decode($encabezados[0]), 1, 0, 'C', true);
                $pdf->Cell(self::COL_PORCENTAJE_WIDTH, 8, utf8_decode($encabezados[1]), 1, 0, 'C', true);
                $pdf->Cell(self::COL_TOTALES_WIDTH, 8, utf8_decode($encabezados[2]), 1, 1, 'C', true);
    
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetFillColor(255, 255, 255);
            }
    
            $pdf->SetX($leftMargin);
            $texto = utf8_decode($row->tipo ?? 'N/A');
    
            $altura = 8;
            $longitud = strlen($texto);
            $lineasNecesarias = ceil($longitud / 60);
            $alturaCalculada = max($altura, $lineasNecesarias * 6);
    
            $x = $pdf->GetX();
            $y = $pdf->GetY();
    
            $pdf->MultiCell(self::COL_TIPO_WIDTH, 6, $texto, 1, 'L', $fill);
            $nuevaY = $pdf->GetY();
            $alturaReal = $nuevaY - $y;
            
            $pdf->SetXY($x + self::COL_TIPO_WIDTH, $y);
            
            $pdf->Cell(self::COL_PORCENTAJE_WIDTH, $alturaReal, 
                      number_format($row->porcentaje ?? 0, 2) . '%', 1, 0, 'C', $fill);
            $pdf->Cell(self::COL_TOTALES_WIDTH, $alturaReal, 
                      $row->total ?? '0', 1, 1, 'C', $fill);
    
            $totalSum += $row->total ?? 0;
            $percentageSum += $row->porcentaje ?? 0;
            $fill = !$fill;
        }
    
        $pdf->SetX($leftMargin);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(self::COL_TIPO_WIDTH, 8, 'Total', 1, 0, 'C', true);
        $pdf->Cell(self::COL_PORCENTAJE_WIDTH, 8, 
                  number_format($percentageSum, 2) . '%', 1, 0, 'C', true);
        $pdf->Cell(self::COL_TOTALES_WIDTH, 8, $totalSum, 1, 1, 'C', true);
    }

    public function mostrarFormulario() {
        session_start();
        $_SESSION['evaluadores'] = $this->model->obtenerEvaluadores();
        require_once '../view/reporteProductividad.php';
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