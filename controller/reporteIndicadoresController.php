<?php
require_once '../model/reporteIndicadoresModel.php';
require_once '../lib/fpdf.php';

class MYPDF extends FPDF {
    private $tipoReporte;
    
    public function __construct($tipoReporte = 'adolescentes', $orientation = 'L') {
        // 'L' establece la orientación horizontal (Landscape)
        parent::__construct($orientation);
        $this->tipoReporte = strtolower($tipoReporte);
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
        $titulo = match($this->tipoReporte) {
            'adultos' => 'Reporte Indicador de Adultos',
            'Adolescentes' => 'Reporte Indicador de Adolescentes',
            'medidas_adolescentes' => 'Reporte de Medidas de Adolescentes',
            'condiciones_adolescentes' => 'Reporte de Condiciones de Adolescentes',
            'colab_medidas_adolescentes' => 'Reporte de Colaboraciones de Medidas de Adolescentes',
            'colab_condiciones_adolescentes' => 'Reporte de Colaboraciones de Condiciones de Adolescentes',
            default => 'Reporte General'
        };
            
        // Imprime el título centrado
        $this->Cell(180, 10, utf8_decode($titulo), 0, 1, 'C');

        // Logotipo derecho
        $this->Image('../img/edomex.png', $this->GetPageWidth() - 45, 8, 35, 20, 'PNG');

        // Salto de línea
        $this->Ln(5);
    }
    public function getNombreArchivo() {
        return match($this->tipoReporte) {
            'adultos' => 'Indicadores_Adultos.pdf',
            'adolescentes' => 'Indicadores_Adolescentes.pdf',
            'medidas_adolescentes' => 'Medidas_Adolescentes.pdf',
            'condiciones_adolescentes' => 'Condiciones_Adolescentes.pdf',
            'colab_medidas_adolescentes' => 'Colaboraciones_Medidas_Adolescentes.pdf',
            'colab_condiciones_adolescentes' => 'Colaboraciones_Condiciones_Adolescentes.pdf',
            default => 'Reporte_General.pdf'
        };
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

class ReporteIndicadoresController {
    private $model;
    private $subregiones = [
        'NORTE' => ['SN-A', 'SN-A-', 'SA-N-', 'SA-N','SN-'],
        'SUR' => ['SS-A', 'SSA-'],
        'TLALNEPANTLA' => ['ST-A', 'ST-A-', 'SA-T-', 'SA-T'],
        'VALLE DE MÉXICO I' => ['SV1-A', 'SV1-A-', 'SA-VI-','SV1-'],
        'VALLE DE MÉXICO II' => ['SV2-A', 'SV2A-', 'SA-VII-', 'SV2A', 'SV2A/'],
    ];

    public function __construct() {
        $this->model = new ReporteIndicadoresModel();
    }

    public function mostrarFormulario() {
        require_once '../view/reporteIndicadores.php';
    } 

    public function generarReportePDF() {
        try {
            ob_clean();
    
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $fechaInicio = $_POST['fechaInicio'] ?? '';
                $fechaFin = $_POST['fechaFin'] ?? '';
    
                $fechaActual = date('Y-m-d');

                if (strtotime($fechaInicio) > strtotime($fechaActual) || 
                    strtotime($fechaFin) > strtotime($fechaActual)) {
                    // Maneja el error de fechas futuras
                    echo "No se permiten fechas futuras.";
                    exit;
                }
    
                $datos = $this->model->obtenerDatosPorSubregion($fechaInicio, $fechaFin);
                $datosVerificacion = $this->model->obtenerVerificacionesPorSubregion($fechaInicio, $fechaFin);
    
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
                $pdf = new MYPDF('adolescentes');
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(50, 50, 50);
                setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');
    
                // Cambiar tamaño de la fuente para las fechas
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 10, utf8_decode('Desde el: ') . strftime('%d-%b-%Y', strtotime($fechaInicio)), 0, 0, 'L');
                $pdf->Cell(0, 10, utf8_decode('Hasta el: ') . strftime('%d-%b-%Y', strtotime($fechaFin)), 0, 1, 'R');
                $pdf->Ln(5);
    
                // Restaurar tamaño de fuente para la tabla
                $pdf->SetFont('Arial', 'B', 5);
                $this->addTableHeader($pdf);
    
                // Imprime las filas de datos
                $pdf->SetFont('Arial', '', 6);
                $subregionesOrdenadas = array_keys($resultadoPorSubregion);
                for ($i = 0; $i < count($subregionesOrdenadas); $i++) {
                    $subregion = $subregionesOrdenadas[$i];
                    $this->imprimirFilaTabla($pdf, $subregion, $resultadoPorSubregion[$subregion]);
                }
    
                // Imprime la fila de totales
                $pdf->SetFont('Arial', 'B', 6);
                $this->imprimirFilaTabla($pdf, 'TOTALES', $totalesGenerales);
    
                // Inicializar estructura para verificaciones
                $verificacionesPorSubregion = [];
                foreach ($subregiones as $nombre => $prefijos) {
                    $verificacionesPorSubregion[$nombre] = [
                        'atendidas' => 0,
                        'verificadas' => 0,
                        'agencia' => 0,
                        'domiciliaria' => 0,
                        'personal' => 0,
                        'telefonica' => 0
                    ];
                }
    
                // Procesar los datos de verificación
                foreach ($datosVerificacion as $dato) {
                    $subregionEncontrada = null;
                    foreach ($subregiones as $nombre => $variantes) {
                        if (in_array(strtolower(trim($dato->subdireccion)), array_map('strtolower', array_map('trim', $variantes)))) {
                            $subregionEncontrada = $nombre;
                            break;
                        }
                    }
    
                    if ($subregionEncontrada !== null) {
                        // Convertir el tipo de verificación a minúsculas y quitar espacios
                        $tipo = preg_replace('/[^a-záéíóúñ]/u', '', mb_strtolower(trim($dato->tipo_verificacion)));
                        
                        if ($tipo == 'agencia') {
                            $verificacionesPorSubregion[$subregionEncontrada]['agencia'] = $dato->total;
                        }
                        elseif ($tipo == 'domiciliaria') {
                            $verificacionesPorSubregion[$subregionEncontrada]['domiciliaria'] = $dato->total;
                        }
                        elseif ($tipo == 'personal') {
                            $verificacionesPorSubregion[$subregionEncontrada]['personal'] = $dato->total;
                        }
                        elseif ($tipo == 'telefonica' || $tipo == 'telefónica' || strpos($tipo, 'telef') !== false) {
                            $verificacionesPorSubregion[$subregionEncontrada]['telefonica'] = $dato->total;
                        }
                        
                        // Actualizar el total de verificadas
                        $verificacionesPorSubregion[$subregionEncontrada]['verificadas'] += $dato->total;
                    
                }
                }
    
                // Nueva tabla de verificaciones
                $pdf->Ln(10);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetFillColor(200, 200, 200);
                $pdf->SetTextColor(0, 0, 0);
    
                // Definir anchos de columnas
                $w1 = 40; // SUBDIRECCIÓN
                $w2 = 25; // ATENDIDAS
                $w3 = 25; // VERIFICADAS
                $w4 = 20; // AGENCIA
                $w5 = 25; // DOMICILIARIA
                $w6 = 20; // PERSONAL
                $w7 = 25; // TELEFÓNICA
    
                // Calcular ancho total de las columnas de verificación
                $w_verificacion = $w4 + $w5 + $w6 + $w7;
    
                // Altura de las celdas
                $h = 6;
    
                // Primera fila
                $pdf->Cell($w1, $h * 2, 'SUBDIRECCION', 1, 0, 'C', true);
                $pdf->Cell($w2, $h * 2, 'ATENDIDAS', 1, 0, 'C', true);
                $pdf->Cell($w3, $h * 2, 'VERIFICADAS', 1, 0, 'C', true);
                $pdf->Cell($w_verificacion, $h, 'TIPO DE VERIFICACION', 1, 1, 'C', true);
    
                // Segunda fila de encabezados
                $x = $pdf->GetX() + $w1 + $w2 + $w3;
                $y = $pdf->GetY();
                $pdf->SetXY($x, $y);
                $pdf->Cell($w4, $h, 'AGENCIA', 1, 0, 'C', true);
                $pdf->Cell($w5, $h, 'DOMICILIARIA', 1, 0, 'C', true);
                $pdf->Cell($w6, $h, 'PERSONAL', 1, 0, 'C', true);
                $pdf->Cell($w7, $h, utf8_decode('TELEFÓNICA'), 1, 1, 'C', true);
    
                foreach ($verificacionesPorSubregion as $nombre => $datos) {
                    $pdf->Cell($w1, $h, utf8_decode($nombre), 1, 0, 'C');
                    $pdf->Cell($w2, $h, isset($resultadoPorSubregion[$nombre]) ? $resultadoPorSubregion[$nombre]['peticiones'] : 0, 1, 0, 'C');
                    $pdf->Cell($w3, $h, $datos['verificadas'], 1, 0, 'C');
                    $pdf->Cell($w4, $h, $datos['agencia'], 1, 0, 'C');
                    $pdf->Cell($w5, $h, $datos['domiciliaria'], 1, 0, 'C');
                    $pdf->Cell($w6, $h, $datos['personal'], 1, 0, 'C');
                    $pdf->Cell($w7, $h, $datos['telefonica'], 1, 1, 'C');
                }
    
                // Calcular totales
                $totalesVerificacion = array_reduce($verificacionesPorSubregion, function($carry, $item) {
                    $carry['verificadas'] += $item['verificadas'];
                    $carry['agencia'] += $item['agencia'];
                    $carry['domiciliaria'] += $item['domiciliaria'];
                    $carry['personal'] += $item['personal'];
                    $carry['telefonica'] += $item['telefonica'];
                    return $carry;
                }, [
                    'verificadas' => 0,
                    'agencia' => 0,
                    'domiciliaria' => 0,
                    'personal' => 0,
                    'telefonica' => 0
                ]);
    
                // Fila de totales
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($w1, $h, 'TOTALES', 1, 0, 'C', true);
                $pdf->Cell($w2, $h, $totalesGenerales['peticiones'], 1, 0, 'C', true);
                $pdf->Cell($w3, $h, $totalesVerificacion['verificadas'], 1, 0, 'C', true);
                $pdf->Cell($w4, $h, $totalesVerificacion['agencia'], 1, 0, 'C', true);
                $pdf->Cell($w5, $h, $totalesVerificacion['domiciliaria'], 1, 0, 'C', true);
                $pdf->Cell($w6, $h, $totalesVerificacion['personal'], 1, 0, 'C', true);
                $pdf->Cell($w7, $h, $totalesVerificacion['telefonica'], 1, 1, 'C', true);
    
                $pdf->Output('D', $pdf->getNombreArchivo());
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
            ob_clean();
    
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $fechaInicio = $_POST['fechaInicio'] ?? '';
                $fechaFin = $_POST['fechaFin'] ?? '';
    
                if (empty($fechaInicio) || empty($fechaFin)) {
                    ob_end_clean();
                    echo "Por favor, complete todos los campos.";
                    return;
                }
    
                $datos = $this->model->obtenerDatosPorSubregionA($fechaInicio, $fechaFin);
                $datosVerificacion = $this->model->obtenerVerificacionesPorSubregionA($fechaInicio, $fechaFin);
    
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
                $pdf = new MYPDF('adultos');
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(50, 50, 50);
                setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');
    
                // Cambiar tamaño de la fuente para las fechas
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 10, utf8_decode('Desde el: ') . strftime('%d-%b-%Y', strtotime($fechaInicio)), 0, 0, 'L');
                $pdf->Cell(0, 10, utf8_decode('Hasta el: ') . strftime('%d-%b-%Y', strtotime($fechaFin)), 0, 1, 'R');
                $pdf->Ln(5);
    
                // Restaurar tamaño de fuente para la tabla
                $pdf->SetFont('Arial', 'B', 5);
                $this->addTableHeader($pdf);
    
                // Imprime las filas de datos
                $pdf->SetFont('Arial', '', 6);
                $subregionesOrdenadas = array_keys($resultadoPorSubregion);
                for ($i = 0; $i < count($subregionesOrdenadas); $i++) {
                    $subregion = $subregionesOrdenadas[$i];
                    $this->imprimirFilaTabla($pdf, $subregion, $resultadoPorSubregion[$subregion]);
                }
    
                // Imprime la fila de totales
                $pdf->SetFont('Arial', 'B', 6);
                $this->imprimirFilaTabla($pdf, 'TOTALES', $totalesGenerales);
    
                // Inicializar estructura para verificaciones
                $verificacionesPorSubregion = [];
                foreach ($subregiones as $nombre => $prefijos) {
                    $verificacionesPorSubregion[$nombre] = [
                        'atendidas' => 0,
                        'verificadas' => 0,
                        'agencia' => 0,
                        'domiciliaria' => 0,
                        'personal' => 0,
                        'telefonica' => 0
                    ];
                }
    
                // Procesar los datos de verificación
                foreach ($datosVerificacion as $dato) {
                    $subregionEncontrada = null;
                    foreach ($subregiones as $nombre => $variantes) {
                        if (in_array(strtolower(trim($dato->subdireccion)), array_map('strtolower', array_map('trim', $variantes)))) {
                            $subregionEncontrada = $nombre;
                            break;
                        }
                    }
    
                    if ($subregionEncontrada !== null) {
                        // Convertir el tipo de verificación a minúsculas y quitar espacios
                        $tipo = preg_replace('/[^a-záéíóúñ]/u', '', mb_strtolower(trim($dato->tipo_verificacion)));
                        
                        if ($tipo == 'agencia') {
                            $verificacionesPorSubregion[$subregionEncontrada]['agencia'] = $dato->total;
                        }
                        elseif ($tipo == 'domiciliaria') {
                            $verificacionesPorSubregion[$subregionEncontrada]['domiciliaria'] = $dato->total;
                        }
                        elseif ($tipo == 'personal') {
                            $verificacionesPorSubregion[$subregionEncontrada]['personal'] = $dato->total;
                        }
                        elseif ($tipo == 'telefonica' || $tipo == 'telefónica' || strpos($tipo, 'telef') !== false) {
                            $verificacionesPorSubregion[$subregionEncontrada]['telefonica'] = $dato->total;
                        }
                        
                        // Actualizar el total de verificadas
                        $verificacionesPorSubregion[$subregionEncontrada]['verificadas'] += $dato->total;
                    
                }
                }
    
                // Nueva tabla de verificaciones
                $pdf->Ln(10);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetFillColor(200, 200, 200);
                $pdf->SetTextColor(0, 0, 0);
    
                // Definir anchos de columnas
                $w1 = 40; // SUBDIRECCIÓN
                $w2 = 25; // ATENDIDAS
                $w3 = 25; // VERIFICADAS
                $w4 = 20; // AGENCIA
                $w5 = 25; // DOMICILIARIA
                $w6 = 20; // PERSONAL
                $w7 = 25; // TELEFÓNICA
    
                // Calcular ancho total de las columnas de verificación
                $w_verificacion = $w4 + $w5 + $w6 + $w7;
    
                // Altura de las celdas
                $h = 6;
    
                // Primera fila
                $pdf->Cell($w1, $h * 2, 'SUBDIRECCION', 1, 0, 'C', true);
                $pdf->Cell($w2, $h * 2, 'ATENDIDAS', 1, 0, 'C', true);
                $pdf->Cell($w3, $h * 2, 'VERIFICADAS', 1, 0, 'C', true);
                $pdf->Cell($w_verificacion, $h, 'TIPO DE VERIFICACION', 1, 1, 'C', true);
    
                // Segunda fila de encabezados
                $x = $pdf->GetX() + $w1 + $w2 + $w3;
                $y = $pdf->GetY();
                $pdf->SetXY($x, $y);
                $pdf->Cell($w4, $h, 'AGENCIA', 1, 0, 'C', true);
                $pdf->Cell($w5, $h, 'DOMICILIARIA', 1, 0, 'C', true);
                $pdf->Cell($w6, $h, 'PERSONAL', 1, 0, 'C', true);
                $pdf->Cell($w7, $h, utf8_decode('TELEFÓNICA'), 1, 1, 'C', true);
    
                foreach ($verificacionesPorSubregion as $nombre => $datos) {
                    $pdf->Cell($w1, $h, utf8_decode($nombre), 1, 0, 'C');
                    $pdf->Cell($w2, $h, isset($resultadoPorSubregion[$nombre]) ? $resultadoPorSubregion[$nombre]['peticiones'] : 0, 1, 0, 'C');
                    $pdf->Cell($w3, $h, $datos['verificadas'], 1, 0, 'C');
                    $pdf->Cell($w4, $h, $datos['agencia'], 1, 0, 'C');
                    $pdf->Cell($w5, $h, $datos['domiciliaria'], 1, 0, 'C');
                    $pdf->Cell($w6, $h, $datos['personal'], 1, 0, 'C');
                    $pdf->Cell($w7, $h, $datos['telefonica'], 1, 1, 'C');
                }
    
                // Calcular totales
                $totalesVerificacion = array_reduce($verificacionesPorSubregion, function($carry, $item) {
                    $carry['verificadas'] += $item['verificadas'];
                    $carry['agencia'] += $item['agencia'];
                    $carry['domiciliaria'] += $item['domiciliaria'];
                    $carry['personal'] += $item['personal'];
                    $carry['telefonica'] += $item['telefonica'];
                    return $carry;
                }, [
                    'verificadas' => 0,
                    'agencia' => 0,
                    'domiciliaria' => 0,
                    'personal' => 0,
                    'telefonica' => 0
                ]);
    
                // Fila de totales
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell($w1, $h, 'TOTALES', 1, 0, 'C', true);
                $pdf->Cell($w2, $h, $totalesGenerales['peticiones'], 1, 0, 'C', true);
                $pdf->Cell($w3, $h, $totalesVerificacion['verificadas'], 1, 0, 'C', true);
                $pdf->Cell($w4, $h, $totalesVerificacion['agencia'], 1, 0, 'C', true);
                $pdf->Cell($w5, $h, $totalesVerificacion['domiciliaria'], 1, 0, 'C', true);
                $pdf->Cell($w6, $h, $totalesVerificacion['personal'], 1, 0, 'C', true);
                $pdf->Cell($w7, $h, $totalesVerificacion['telefonica'], 1, 1, 'C', true);
    
                $pdf->Output('D',$pdf->getNombreArchivo());
                exit;
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "Error generando el reporte: " . $e->getMessage();
        }
    }


    public function generarReportePDFM() {
        try {
            ob_clean();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido");
            }

            $fechaInicio = $_POST['fechaInicio'] ?? '';
            $fechaFin = $_POST['fechaFin'] ?? '';

            if (empty($fechaInicio) || empty($fechaFin)) {
                throw new Exception("Por favor, complete todos los campos.");
            }

            // Obtener datos de la base de datos
            $datos = $this->model->obtenerDatosMedidas($fechaInicio, $fechaFin);
            
            // Procesar datos por subregión
            $estadisticas = $this->procesarDatosMedidas($datos);

            // Generar PDF
            $pdf = new MYPDF('medidas_adolescentes');
            $pdf->AddPage('L');
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 12);
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');


            // Encabezado con fechas
            $pdf->Cell(0, 10, utf8_decode('Desde el: ') . strftime('%d-%b-%Y', strtotime($fechaInicio)), 0, 0, 'L');
            $pdf->Cell(0, 10, utf8_decode('Hasta el: ') . strftime('%d-%b-%Y', strtotime($fechaFin)), 0, 1, 'R');
            $pdf->Ln(10);

            // Configuración de anchos de columna
            $w = [
                'supervision' => 35,
                'registros' => 20,
                'seguimiento' => 28,
                'sin_seguimiento' => 35,
                'concluidas' => 20,
                'cesacion' => 35,
            ];

            $wMedidas = array_fill(1, 12, 8.5); // 15 unidades para cada medida

            $pdf->SetFillColor(200, 200, 200);


            // Primera fila: título de medidas
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(array_sum(array_values($w)), 8, '', 0, 0); // Espacios en blanco para las primeras columnas
            $pdf->Cell(array_sum($wMedidas), 8, 'MEDIDAS CAUTELARES IMPUESTAS', 1, 1, 'C', true);

            // Segunda fila: encabezados
            $pdf->Cell($w['supervision'], 8, utf8_decode('Supervisión'), 1, 0, 'C', true);
            $pdf->Cell($w['registros'], 8, 'Registros', 1, 0, 'C', true);
            $pdf->Cell($w['seguimiento'], 8, 'En Seguimiento', 1, 0, 'C', true);
            $pdf->Cell($w['sin_seguimiento'], 8, 'Vigente sin Seguimiento', 1, 0, 'C', true);
            $pdf->Cell($w['concluidas'], 8, 'Concluidas', 1, 0, 'C', true);
            $pdf->Cell($w['cesacion'], 8, utf8_decode('Cesación de Medidas'), 1, 0, 'C', true);

            // Números romanos para medidas
            $romanos = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            foreach ($romanos as $romano) {
                $pdf->Cell($wMedidas[1], 8, $romano, 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Datos por subregión
            foreach ($this->subregiones as $nombre => $prefijos) {
                $this->imprimirFilaSubregionMedidas($pdf, $nombre, $estadisticas[$nombre] ?? [], $w, $wMedidas);
            }

            $pdf->SetFillColor(200, 200, 200);
            // Fila de totales
            $this->imprimirFilaTotalesMedidas($pdf, $estadisticas, $w, $wMedidas);

            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 5, utf8_decode('DESCRIPCIÓN DE MEDIDAS CAUTELARES:'), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 9);
            
            $medidas = [
                'I.- Presentación periódica ante el Juez o ante autoridad.',
                'II.- La prohibición de salir del país, de la localidad en la cual reside o del ámbito territorial que fije el Órgano Jurisdiccional sin autorización del Juez.',
                'III.- La obligación de someterse al cuidado o vigilancia de una persona o institución determinada que informe regularmente al Órgano Jurisdiccional.',
                'IV.- La prohibición de asistir a determinadas reuniones o de visitar o acercarse a ciertos lugares.',
                'V.- La prohibición de convivir, acercarse o comunicarse con determinadas personas, con las víctimas u ofendidos o testigos, siempre que no se afecte el derecho de defensa.',
                'VI.- La separación inmediata del domicilio.',
                'VII.- Colocación de localizadores electrónicos.',
                'VIII.- Garantía económica para asegurar la comparecencia',
                'IX.- Embargo de bienes',
                'X.- Inmovilización de cuentas',
                'XI.- Resguardo en su domicilio con las modalidades que el Órgano Jurisdiccional Disponga',
                'XII.- Internamiento previo'
            ];

            foreach ($medidas as $medida) {
                $pdf->Cell(0, 5, utf8_decode($medida), 0, 1, 'L');
            }


            $pdf->Output('D',$pdf->getNombreArchivo());
            exit;

        } catch (Exception $e) {
            ob_end_clean();
            echo "Error generando el reporte: " . $e->getMessage();
        }
    }

    private function procesarDatosMedidas($datos) {
        $estadisticas = [];
        
        // Inicializar estadísticas para cada subregión
        foreach ($this->subregiones as $nombre => $prefijos) {
            $estadisticas[$nombre] = [
                'registros' => 0,
                'en_seguimiento' => 0,
                'sin_seguimiento' => 0,
                'concluidas' => 0,
                'cesacion' => 0,
                'medidas' => array_fill(1, 12, 0)
            ];
        }

        // Procesar cada registro
        foreach ($datos as $registro) {
            $subregion = $this->obtenerSubregionMedidas($registro['supervision']);
            if ($subregion) {
                $estadisticas[$subregion]['registros']++;

                // Procesar estado de seguimiento
                switch ($registro['seguimiento']) {
                    case 'EN SEGUIMIENTO':
                        $estadisticas[$subregion]['en_seguimiento']++;
                        break;
                    case 'VIGENTE SIN SEGUIMIENTO':
                        $estadisticas[$subregion]['sin_seguimiento']++;
                        break;
                    case 'CONCLUIDA':
                        $estadisticas[$subregion]['concluidas']++;
                        break;
                    case 'CESACIÓN':
                        $estadisticas[$subregion]['cesacion']++;
                        break;
                }

                // Contar medidas
                for ($i = 1; $i <= 12; $i++) {
                    if (!empty($registro["medida$i"])) {
                        $estadisticas[$subregion]['medidas'][$i]++;
                    }
                }
            }
        }

        return $estadisticas;
    }

    private function obtenerSubregionMedidas($supervision) {
        foreach ($this->subregiones as $nombre => $prefijos) {
            foreach ($prefijos as $prefijo) {
                if (strpos($supervision, $prefijo) === 0) {
                    return $nombre;
                }
            }
        }
        return null;
    }

    private function imprimirFilaSubregionMedidas($pdf, $nombre, $datos, $w, $wMedidas) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w['supervision'], 8, utf8_decode($nombre), 1, 0, 'L');
        $pdf->Cell($w['registros'], 8, $datos['registros'], 1, 0, 'C');
        $pdf->Cell($w['seguimiento'], 8, $datos['en_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['sin_seguimiento'], 8, $datos['sin_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['concluidas'], 8, $datos['concluidas'], 1, 0, 'C');
        $pdf->Cell($w['cesacion'], 8, $datos['cesacion'], 1, 0, 'C');

        foreach ($datos['medidas'] as $medida) {
            $pdf->Cell($wMedidas[1], 8, $medida, 1, 0, 'C');
        }
        $pdf->Ln();
    }

    private function imprimirFilaTotalesMedidas($pdf, $estadisticas, $w, $wMedidas) {
        $totales = [
            'registros' => 0,
            'en_seguimiento' => 0,
            'sin_seguimiento' => 0,
            'concluidas' => 0,
            'cesacion' => 0,
            'medidas' => array_fill(1, 12, 0)
        ];

        foreach ($estadisticas as $datos) {
            $totales['registros'] += $datos['registros'];
            $totales['en_seguimiento'] += $datos['en_seguimiento'];
            $totales['sin_seguimiento'] += $datos['sin_seguimiento'];
            $totales['concluidas'] += $datos['concluidas'];
            $totales['cesacion'] += $datos['cesacion'];
            
            for ($i = 1; $i <= 12; $i++) {
                $totales['medidas'][$i] += $datos['medidas'][$i];
            }
        }

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w['supervision'], 8, 'TOTALES', 1, 0, 'L');
        $pdf->Cell($w['registros'], 8, $totales['registros'], 1, 0, 'C');
        $pdf->Cell($w['seguimiento'], 8, $totales['en_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['sin_seguimiento'], 8, $totales['sin_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['concluidas'], 8, $totales['concluidas'], 1, 0, 'C');
        $pdf->Cell($w['cesacion'], 8, $totales['cesacion'], 1, 0, 'C');

        foreach ($totales['medidas'] as $total) {
            $pdf->Cell($wMedidas[1], 8, $total, 1, 0, 'C');
        }
        $pdf->Ln();
    }


    public function generarReportePDFC() {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            // Iniciar un nuevo buffer
            ob_start();
    
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido");
            }
    
            $fechaInicio = $_POST['fechaInicio'] ?? '';
            $fechaFin = $_POST['fechaFin'] ?? '';
    
            if (empty($fechaInicio) || empty($fechaFin)) {
                throw new Exception("Por favor, complete todos los campos.");
            }
    
            // Obtener datos de la base de datos
            $datos = $this->model->obtenerDatosCondiciones($fechaInicio, $fechaFin);
            
            // Procesar datos por subregión
            $estadisticas = $this->procesarDatosCondiciones($datos);
    
            // Limpiar el buffer antes de generar el PDF
            if (ob_get_length()) {
                ob_clean();
            }
    
            // Generar PDF
            $pdf = new MYPDF('condiciones_adolescentes');
            $pdf->AddPage('L');
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 12);
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');
    
            // Encabezado con fechas
            $pdf->Cell(0, 10, utf8_decode('Desde el: ') . strftime('%d-%b-%Y', strtotime($fechaInicio)), 0, 0, 'L');
            $pdf->Cell(0, 10, utf8_decode('Hasta el: ') . strftime('%d-%b-%Y', strtotime($fechaFin)), 0, 1, 'R');
            $pdf->Ln(10);
    
            // Configuración de anchos de columna
            $w = [
                'supervision' => 35,
                'registros' => 20,
                'seguimiento' => 28,
                'sin_seguimiento' => 35,
                'concluidas' => 20,
                'cesacion' => 40,
            ];
    
            $wCondiciones = array_fill(1, 7, 14);
    
            $pdf->SetFillColor(200, 200, 200);
    
            // Primera fila: título de medidas
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(array_sum(array_values($w)), 8, '', 0, 0);
            $pdf->Cell(array_sum($wCondiciones), 8, 'CONDICIONES IMPUESTAS', 1, 1, 'C', true);
    
            // Segunda fila: encabezados
            $pdf->Cell($w['supervision'], 8, utf8_decode('Supervisión'), 1, 0, 'C', true);
            $pdf->Cell($w['registros'], 8, 'Registros', 1, 0, 'C', true);
            $pdf->Cell($w['seguimiento'], 8, 'En Seguimiento', 1, 0, 'C', true);
            $pdf->Cell($w['sin_seguimiento'], 8, 'Vigente sin Seguimiento', 1, 0, 'C', true);
            $pdf->Cell($w['concluidas'], 8, 'Concluidas', 1, 0, 'C', true);
            $pdf->Cell($w['cesacion'], 8, utf8_decode('Cesación de Condiciones'), 1, 0, 'C', true);
    
            // Números romanos para medidas
            $romanos = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'];
            foreach ($romanos as $romano) {
                $pdf->Cell($wCondiciones[1], 8, $romano, 1, 0, 'C', true);
            }
            $pdf->Ln();
    
            // Datos por subregión
            foreach ($this->subregiones as $nombre => $prefijos) {
                $this->imprimirFilaSubregionCondiciones($pdf, $nombre, $estadisticas[$nombre] ?? [], $w, $wCondiciones);
            }
    
            $pdf->SetFillColor(200, 200, 200);
            // Fila de totales
            $this->imprimirFilaTotalesCondiciones($pdf, $estadisticas, $w, $wCondiciones);
    
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 5, utf8_decode('DESCRIPCIÓN DE MEDIDAS CAUTELARES:'), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 9);
            
            $condiciones = [
                'I.- Comenzar o continuar la escolaridad que le corresponda.',
                'II.- Presentar servicio social a favor de la comunidad, las víctimas del Estado o instituciones de beneficencia pública o privada, en caso de que la persona adolescente sea mayor de quince años.',
                'III.- Tener un trabajo o empleo, o adquirir, en el plazo que el juez determine, un oficio, arte, industria o profesión si no tiene medios propios de subsistencia, siempre y cuando su edad lo permita.',
                'IV.- En caso de hechos tipificados como delitos sexuales, la obligación de integrarse a programas de educación sexual que incorporen la perspectiva de género.',
                'V.- Abstenerse de consumir drogas o estupefacientes o de abusar de las bebidas alcohólicas.',
                'VI.- Participar en programas especiales para la prevención y el tratamiento de adicciones.',
                'VII.- Cualquier otra condición que, a juicio del juez, logre una efectiva tutela de los derechos de la víctima y contribuya a cumplir con los fines socioeducativos de la persona adolescente.'
            ];
    
            foreach ($condiciones as $condicion) {
                $pdf->Cell(0, 5, utf8_decode($condicion), 0, 1, 'L');
            }

    
            // Limpiar el buffer final antes de la salida
            if (ob_get_length()) {
                ob_clean();
            }
    
            $pdf->Output('D',$pdf->getNombreArchivo());
            exit;
    
        } catch (Exception $e) {
            // Limpiar todos los buffers en caso de error
            while (ob_get_level()) {
                ob_end_clean();
            }
        }
    }
    
    private function procesarDatosCondiciones($datos) {
        $estadisticas = [];
        
        // Inicializar estadísticas para cada subregión
        foreach ($this->subregiones as $nombre => $prefijos) {
            $estadisticas[$nombre] = [
                'registros' => 0,
                'en_seguimiento' => 0,
                'sin_seguimiento' => 0,
                'concluidas' => 0,
                'cesacion' => 0,
                'condiciones' => array_fill(1, 7, 0)
            ];
        }
    
        // Procesar cada registro
        foreach ($datos as $registro) {
            $subregion = $this->obtenerSubregionCondiciones($registro['supervision']);
            if ($subregion) {
                $estadisticas[$subregion]['registros']++;
    
                // Procesar estado de seguimiento
                switch ($registro['seguimiento']) {
                    case 'EN SEGUIMIENTO':
                        $estadisticas[$subregion]['en_seguimiento']++;
                        break;
                    case 'VIGENTE SIN SEGUIMIENTO':
                        $estadisticas[$subregion]['sin_seguimiento']++;
                        break;
                    case 'CONCLUIDA':
                        $estadisticas[$subregion]['concluidas']++;
                        break;
                    case 'CESACIÓN':
                        $estadisticas[$subregion]['cesacion']++;
                        break;
                }
    
                // Contar medidas
                for ($i = 1; $i <= 7; $i++) {
                    if (!empty($registro["condicion$i"])) {
                        $estadisticas[$subregion]['condiciones'][$i]++;
                    }
                }
            }
        }
    
        return $estadisticas;
    }
    
    private function obtenerSubregionCondiciones($supervision) {
        foreach ($this->subregiones as $nombre => $prefijos) {
            foreach ($prefijos as $prefijo) {
                if (strpos($supervision, $prefijo) === 0) {
                    return $nombre;
                }
            }
        }
        return null;
    }
    
    private function imprimirFilaSubregionCondiciones($pdf, $nombre, $datos, $w, $wCondiciones) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w['supervision'], 8, utf8_decode($nombre), 1, 0, 'L');
        $pdf->Cell($w['registros'], 8, $datos['registros'], 1, 0, 'C');
        $pdf->Cell($w['seguimiento'], 8, $datos['en_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['sin_seguimiento'], 8, $datos['sin_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['concluidas'], 8, $datos['concluidas'], 1, 0, 'C');
        $pdf->Cell($w['cesacion'], 8, $datos['cesacion'], 1, 0, 'C');
    
        for ($i = 1; $i <= 7; $i++) {
            $pdf->Cell($wCondiciones[1], 8, $datos['condiciones'][$i] ?? 0, 1, 0, 'C');
        }
        $pdf->Ln();
    }
    
    private function imprimirFilaTotalesCondiciones($pdf, $estadisticas, $w, $wCondiciones) {
        $totales = [
            'registros' => 0,
            'en_seguimiento' => 0,
            'sin_seguimiento' => 0,
            'concluidas' => 0,
            'cesacion' => 0,
            'condiciones' => array_fill(1, 7, 0)
        ];
    
        foreach ($estadisticas as $datos) {
            $totales['registros'] += $datos['registros'];
            $totales['en_seguimiento'] += $datos['en_seguimiento'];
            $totales['sin_seguimiento'] += $datos['sin_seguimiento'];
            $totales['concluidas'] += $datos['concluidas'];
            $totales['cesacion'] += $datos['cesacion'];
            
            for ($i = 1; $i <= 7; $i++) {
                $totales['condiciones'][$i] += $datos['condiciones'][$i] ?? 0;
            }
        }
    
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w['supervision'], 8, 'TOTALES', 1, 0, 'L');
        $pdf->Cell($w['registros'], 8, $totales['registros'], 1, 0, 'C');
        $pdf->Cell($w['seguimiento'], 8, $totales['en_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['sin_seguimiento'], 8, $totales['sin_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['concluidas'], 8, $totales['concluidas'], 1, 0, 'C');
        $pdf->Cell($w['cesacion'], 8, $totales['cesacion'], 1, 0, 'C');
    
        for ($i = 1; $i <= 7; $i++) {
            $pdf->Cell($wCondiciones[1], 8, $totales['condiciones'][$i], 1, 0, 'C');
        }
        $pdf->Ln();
    }


    public function generarReportePDFCM() {
        try {
            ob_clean();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido");
            }

            $fechaInicio = $_POST['fechaInicio'] ?? '';
            $fechaFin = $_POST['fechaFin'] ?? '';

            if (empty($fechaInicio) || empty($fechaFin)) {
                throw new Exception("Por favor, complete todos los campos.");
            }

            // Obtener datos de la base de datos
            $datos = $this->model->obtenerDatosColaboracionesMedidas($fechaInicio, $fechaFin);
            
            // Procesar datos por subregión
            $estadisticas = $this->procesarDatosColaboracionesMedidas($datos);

            // Generar PDF
            $pdf = new MYPDF('colab_medidas_adolescentes');
            $pdf->AddPage('L');
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 12);
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');


            // Encabezado con fechas
            $pdf->Cell(0, 10, utf8_decode('Desde el: ') . strftime('%d-%b-%Y', strtotime($fechaInicio)), 0, 0, 'L');
            $pdf->Cell(0, 10, utf8_decode('Hasta el: ') . strftime('%d-%b-%Y', strtotime($fechaFin)), 0, 1, 'R');
            $pdf->Ln(10);

            // Configuración de anchos de columna
            $w = [
                'supervision' => 35,
                'registros' => 20,
                'seguimiento' => 28,
                'sin_seguimiento' => 35,
                'concluidas' => 20,
                'cesacion' => 35,
            ];

            $wMedidas = array_fill(1, 12, 8.5); // 15 unidades para cada medida

            $pdf->SetFillColor(200, 200, 200);


            // Primera fila: título de medidas
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(array_sum(array_values($w)), 8, '', 0, 0); // Espacios en blanco para las primeras columnas
            $pdf->Cell(array_sum($wMedidas), 8, 'MEDIDAS CAUTELARES IMPUESTAS', 1, 1, 'C', true);

            // Segunda fila: encabezados
            $pdf->Cell($w['supervision'], 8, utf8_decode('Supervisión'), 1, 0, 'C', true);
            $pdf->Cell($w['registros'], 8, 'Registros', 1, 0, 'C', true);
            $pdf->Cell($w['seguimiento'], 8, 'En Seguimiento', 1, 0, 'C', true);
            $pdf->Cell($w['sin_seguimiento'], 8, 'Vigente sin Seguimiento', 1, 0, 'C', true);
            $pdf->Cell($w['concluidas'], 8, 'Concluidas', 1, 0, 'C', true);
            $pdf->Cell($w['cesacion'], 8, utf8_decode('Cesación de Medidas'), 1, 0, 'C', true);

            // Números romanos para medidas
            $romanos = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            foreach ($romanos as $romano) {
                $pdf->Cell($wMedidas[1], 8, $romano, 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Datos por subregión
            foreach ($this->subregiones as $nombre => $prefijos) {
                $this->imprimirFilaSubregionColaboracionesMedidas($pdf, $nombre, $estadisticas[$nombre] ?? [], $w, $wMedidas);
            }

            $pdf->SetFillColor(200, 200, 200);
            // Fila de totales
            $this->imprimirFilaTotalesColaboracionesMedidas($pdf, $estadisticas, $w, $wMedidas);

            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 5, utf8_decode('DESCRIPCIÓN DE MEDIDAS CAUTELARES:'), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 9);
            
            $medidas = [
                'I.- Presentación periódica ante el Juez o ante autoridad.',
                'II.- La prohibición de salir del país, de la localidad en la cual reside o del ámbito territorial que fije el Órgano Jurisdiccional sin autorización del Juez.',
                'III.- La obligación de someterse al cuidado o vigilancia de una persona o institución determinada que informe regularmente al Órgano Jurisdiccional.',
                'IV.- La prohibición de asistir a determinadas reuniones o de visitar o acercarse a ciertos lugares.',
                'V.- La prohibición de convivir, acercarse o comunicarse con determinadas personas, con las víctimas u ofendidos o testigos, siempre que no se afecte el derecho de defensa.',
                'VI.- La separación inmediata del domicilio.',
                'VII.- Colocación de localizadores electrónicos.',
                'VIII.- Garantía económica para asegurar la comparecencia',
                'IX.- Embargo de bienes',
                'X.- Inmovilización de cuentas',
                'XI.- Resguardo en su domicilio con las modalidades que el Órgano Jurisdiccional Disponga',
                'XII.- Internamiento previo'
            ];

            foreach ($medidas as $medida) {
                $pdf->Cell(0, 5, utf8_decode($medida), 0, 1, 'L');
            }
            $datosProcedencia = $this->model->obtenerDatosProcedenciaCM($fechaInicio, $fechaFin);

            $pdf->AddPage('L');
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_decode('Datos de Procedencia'), 0, 1, 'C');
            $pdf->Ln(5);
            
            // Configuración del color de fondo para encabezados
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(80, 8, 'Procedencia', 1, 0, 'C', true);
            $pdf->Cell(50, 8, 'En Seguimiento', 1, 0, 'C', true);
            $pdf->Cell(50, 8, 'Cantidad Total', 1, 1, 'C', true);
            
            // Configuración del espacio adicional para nueva página
            $extraSpace = 10; // Espacio adicional en milímetros
            
            // Table rows
            $pdf->SetFont('Arial', '', 10);
            foreach ($datosProcedencia as $dato) {
                // Verificar si el contenido excede el límite de página
                if ($pdf->GetY() > 180) { // Ajustar este valor según tu diseño de página
                    $pdf->AddPage('L'); // Añadir una nueva página
                    $pdf->Ln($extraSpace); // Añadir espacio adicional
                    // Repetir los encabezados en la nueva página
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->SetFillColor(200, 200, 200);
                    $pdf->Cell(80, 8, 'Procedencia', 1, 0, 'C', true);
                    $pdf->Cell(50, 8, 'En Seguimiento', 1, 0, 'C', true);
                    $pdf->Cell(50, 8, 'Cantidad Total', 1, 1, 'C', true);
                    $pdf->SetFont('Arial', '', 10); // Restaurar la fuente para el contenido
                }
            
                $enSeguimiento = $dato['en_seguimiento'] === null ? 'NULL' : $dato['en_seguimiento'];
                $cantidad = $dato['cantidad'] === null ? 'NULL' : $dato['cantidad'];
                
                $pdf->Cell(80, 8, utf8_decode($dato['procedencia'] ?: 'NULL'), 1, 0, 'L');
                $pdf->Cell(50, 8, $enSeguimiento, 1, 0, 'C');
                $pdf->Cell(50, 8, $cantidad, 1, 1, 'C');
            }

    
            // Limpiar el buffer final antes de la salida
            if (ob_get_length()) {
                ob_clean();
            }
    
            $pdf->Output('D',$pdf->getNombreArchivo());
            exit;
    
        } catch (Exception $e) {
            // Limpiar todos los buffers en caso de error
            while (ob_get_level()) {
                ob_end_clean();
            }
        }
    }

    private function procesarDatosColaboracionesMedidas($datos) {
        $estadisticas = [];
        
        // Inicializar estadísticas para cada subregión
        foreach ($this->subregiones as $nombre => $prefijos) {
            $estadisticas[$nombre] = [
                'registros' => 0,
                'en_seguimiento' => 0,
                'sin_seguimiento' => 0,
                'concluidas' => 0,
                'cesacion' => 0,
                'medidas' => array_fill(1, 12, 0)
            ];
        }

        // Procesar cada registro
        foreach ($datos as $registro) {
            $subregion = $this->obtenerSubregionColaboracionesMedidas($registro['supervision']);
            if ($subregion) {
                $estadisticas[$subregion]['registros']++;

                // Procesar estado de seguimiento
                switch ($registro['seguimiento']) {
                    case 'EN SEGUIMIENTO':
                        $estadisticas[$subregion]['en_seguimiento']++;
                        break;
                    case 'VIGENTE SIN SEGUIMIENTO':
                        $estadisticas[$subregion]['sin_seguimiento']++;
                        break;
                    case 'CONCLUIDA':
                        $estadisticas[$subregion]['concluidas']++;
                        break;
                    case 'CESACIÓN':
                        $estadisticas[$subregion]['cesacion']++;
                        break;
                }

                // Contar medidas
                for ($i = 1; $i <= 12; $i++) {
                    if (!empty($registro["medida$i"])) {
                        $estadisticas[$subregion]['medidas'][$i]++;
                    }
                }
            }
        }

        return $estadisticas;
    }

    private function obtenerSubregionColaboracionesMedidas($supervision) {
        foreach ($this->subregiones as $nombre => $prefijos) {
            foreach ($prefijos as $prefijo) {
                if (strpos($supervision, $prefijo) === 0) {
                    return $nombre;
                }
            }
        }
        return null;
    }

    private function imprimirFilaSubregionColaboracionesMedidas($pdf, $nombre, $datos, $w, $wMedidas) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w['supervision'], 8, utf8_decode($nombre), 1, 0, 'L');
        $pdf->Cell($w['registros'], 8, $datos['registros'], 1, 0, 'C');
        $pdf->Cell($w['seguimiento'], 8, $datos['en_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['sin_seguimiento'], 8, $datos['sin_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['concluidas'], 8, $datos['concluidas'], 1, 0, 'C');
        $pdf->Cell($w['cesacion'], 8, $datos['cesacion'], 1, 0, 'C');

        foreach ($datos['medidas'] as $medida) {
            $pdf->Cell($wMedidas[1], 8, $medida, 1, 0, 'C');
        }
        $pdf->Ln();
    }

    private function imprimirFilaTotalesColaboracionesMedidas($pdf, $estadisticas, $w, $wMedidas) {
        $totales = [
            'registros' => 0,
            'en_seguimiento' => 0,
            'sin_seguimiento' => 0,
            'concluidas' => 0,
            'cesacion' => 0,
            'medidas' => array_fill(1, 12, 0)
        ];

        foreach ($estadisticas as $datos) {
            $totales['registros'] += $datos['registros'];
            $totales['en_seguimiento'] += $datos['en_seguimiento'];
            $totales['sin_seguimiento'] += $datos['sin_seguimiento'];
            $totales['concluidas'] += $datos['concluidas'];
            $totales['cesacion'] += $datos['cesacion'];
            
            for ($i = 1; $i <= 12; $i++) {
                $totales['medidas'][$i] += $datos['medidas'][$i];
            }
        }

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w['supervision'], 8, 'TOTALES', 1, 0, 'L');
        $pdf->Cell($w['registros'], 8, $totales['registros'], 1, 0, 'C');
        $pdf->Cell($w['seguimiento'], 8, $totales['en_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['sin_seguimiento'], 8, $totales['sin_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['concluidas'], 8, $totales['concluidas'], 1, 0, 'C');
        $pdf->Cell($w['cesacion'], 8, $totales['cesacion'], 1, 0, 'C');

        foreach ($totales['medidas'] as $total) {
            $pdf->Cell($wMedidas[1], 8, $total, 1, 0, 'C');
        }
        $pdf->Ln();
    }

    public function generarReportePDFCC() {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            // Iniciar un nuevo buffer
            ob_start();
    
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido");
            }
    
            $fechaInicio = $_POST['fechaInicio'] ?? '';
            $fechaFin = $_POST['fechaFin'] ?? '';
    
            if (empty($fechaInicio) || empty($fechaFin)) {
                throw new Exception("Por favor, complete todos los campos.");
            }
    
            // Obtener datos de la base de datos
            $datos = $this->model->obtenerDatosColaboracionesCondiciones($fechaInicio, $fechaFin);
            

            
            // Procesar datos por subregión
            $estadisticas = $this->procesarDatosColaboracionesCondiciones($datos);
    
            // Limpiar el buffer antes de generar el PDF
            if (ob_get_length()) {
                ob_clean();
            }
    
            // Generar PDF
            $pdf = new MYPDF('colab_condiciones_adolescentes');
            $pdf->AddPage('L');
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 12);
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');
    
            // Encabezado con fechas
            $pdf->Cell(0, 10, utf8_decode('Desde el: ') . strftime('%d-%b-%Y', strtotime($fechaInicio)), 0, 0, 'L');
            $pdf->Cell(0, 10, utf8_decode('Hasta el: ') . strftime('%d-%b-%Y', strtotime($fechaFin)), 0, 1, 'R');
            $pdf->Ln(10);
    
            // Configuración de anchos de columna
            $w = [
                'supervision' => 35,
                'registros' => 20,
                'seguimiento' => 28,
                'sin_seguimiento' => 35,
                'concluidas' => 20,
                'cesacion' => 40,
            ];
    
            $wCondiciones = array_fill(1, 7, 14);
    
            $pdf->SetFillColor(200, 200, 200);
    
            // Primera fila: título de medidas
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(array_sum(array_values($w)), 8, '', 0, 0);
            $pdf->Cell(array_sum($wCondiciones), 8, 'CONDICIONES IMPUESTAS', 1, 1, 'C', true);
    
            // Segunda fila: encabezados
            $pdf->Cell($w['supervision'], 8, utf8_decode('Supervisión'), 1, 0, 'C', true);
            $pdf->Cell($w['registros'], 8, 'Registros', 1, 0, 'C', true);
            $pdf->Cell($w['seguimiento'], 8, 'En Seguimiento', 1, 0, 'C', true);
            $pdf->Cell($w['sin_seguimiento'], 8, 'Vigente sin Seguimiento', 1, 0, 'C', true);
            $pdf->Cell($w['concluidas'], 8, 'Concluidas', 1, 0, 'C', true);
            $pdf->Cell($w['cesacion'], 8, utf8_decode('Cesación de Condiciones'), 1, 0, 'C', true);
    
            // Números romanos para medidas
            $romanos = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'];
            foreach ($romanos as $romano) {
                $pdf->Cell($wCondiciones[1], 8, $romano, 1, 0, 'C', true);
            }
            $pdf->Ln();
    
            // Datos por subregión
            foreach ($this->subregiones as $nombre => $prefijos) {
                $this->imprimirFilaSubregionColaboracionesCondiciones($pdf, $nombre, $estadisticas[$nombre] ?? [], $w, $wCondiciones);
            }
    
            $pdf->SetFillColor(200, 200, 200);
            // Fila de totales
            $this->imprimirFilaTotalesColaboracionesCondiciones($pdf, $estadisticas, $w, $wCondiciones);
    
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 5, utf8_decode('DESCRIPCIÓN DE CONDICIONES CAUTELARES:'), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 9);
            
            
            $condiciones = [
                'I.- Comenzar o continuar la escolaridad que le corresponda.',
                'II.- Presentar servicio social a favor de la comunidad, las víctimas del Estado o instituciones de beneficencia pública o privada, en caso de que la persona adolescente sea mayor de quince años.',
                'III.- Tener un trabajo o empleo, o adquirir, en el plazo que el juez determine, un oficio, arte, industria o profesión si no tiene medios propios de subsistencia, siempre y cuando su edad lo permita.',
                'IV.- En caso de hechos tipificados como delitos sexuales, la obligación de integrarse a programas de educación sexual que incorporen la perspectiva de género.',
                'V.- Abstenerse de consumir drogas o estupefacientes o de abusar de las bebidas alcohólicas.',
                'VI.- Participar en programas especiales para la prevención y el tratamiento de adicciones.',
                'VII.- Cualquier otra condición que, a juicio del juez, logre una efectiva tutela de los derechos de la víctima y contribuya a cumplir con los fines socioeducativos de la persona adolescente.'
            ];
    
            foreach ($condiciones as $condicion) {
                $pdf->Cell(0, 5, utf8_decode($condicion), 0, 1, 'L');
            }

            // Obtener datos de procedencia
            $datosProcedencia = $this->model->obtenerDatosProcedenciaCC($fechaInicio, $fechaFin);

            $pdf->AddPage('L');
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_decode('Datos de Procedencia'), 0, 1, 'C');
            $pdf->Ln(5);
            
            // Configuración del color de fondo para encabezados
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(80, 8, 'Procedencia', 1, 0, 'C', true);
            $pdf->Cell(50, 8, 'En Seguimiento', 1, 0, 'C', true);
            $pdf->Cell(50, 8, 'Cantidad Total', 1, 1, 'C', true);
            
            // Configuración del espacio adicional para nueva página
            $extraSpace = 10; // Espacio adicional en milímetros
            
            // Table rows
            $pdf->SetFont('Arial', '', 10);
            foreach ($datosProcedencia as $dato) {
                // Verificar si el contenido excede el límite de página
                if ($pdf->GetY() > 180) { // Ajustar este valor según tu diseño de página
                    $pdf->AddPage('L'); // Añadir una nueva página
                    $pdf->Ln($extraSpace); // Añadir espacio adicional
                    // Repetir los encabezados en la nueva página
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->SetFillColor(200, 200, 200);
                    $pdf->Cell(80, 8, 'Procedencia', 1, 0, 'C', true);
                    $pdf->Cell(50, 8, 'En Seguimiento', 1, 0, 'C', true);
                    $pdf->Cell(50, 8, 'Cantidad Total', 1, 1, 'C', true);
                    $pdf->SetFont('Arial', '', 10); // Restaurar la fuente para el contenido
                }
            
                $enSeguimiento = $dato['en_seguimiento'] === null ? 'NULL' : $dato['en_seguimiento'];
                $cantidad = $dato['cantidad'] === null ? 'NULL' : $dato['cantidad'];
                
                $pdf->Cell(80, 8, utf8_decode($dato['procedencia'] ?: 'NULL'), 1, 0, 'L');
                $pdf->Cell(50, 8, $enSeguimiento, 1, 0, 'C');
                $pdf->Cell(50, 8, $cantidad, 1, 1, 'C');
            }

    
            // Limpiar el buffer final antes de la salida
            if (ob_get_length()) {
                ob_clean();
            }
    
            $pdf->Output('D',$pdf->getNombreArchivo());
            exit;
    
        } catch (Exception $e) {
            // Limpiar todos los buffers en caso de error
            while (ob_get_level()) {
                ob_end_clean();
            }
        }
    }
    
    private function procesarDatosColaboracionesCondiciones($datos) {
        $estadisticas = [];
        
        // Inicializar estadísticas para cada subregión
        foreach ($this->subregiones as $nombre => $prefijos) {
            $estadisticas[$nombre] = [
                'registros' => 0,
                'en_seguimiento' => 0,
                'sin_seguimiento' => 0,
                'concluidas' => 0,
                'cesacion' => 0,
                'condiciones' => array_fill(1, 7, 0)
            ];
        }
    
        // Procesar cada registro
        foreach ($datos as $registro) {
            $subregion = $this->obtenerSubregionColaboracionesCondiciones($registro['supervision']);
            if ($subregion) {
                $estadisticas[$subregion]['registros']++;
    
                // Procesar estado de seguimiento
                switch ($registro['seguimiento']) {
                    case 'EN SEGUIMIENTO':
                        $estadisticas[$subregion]['en_seguimiento']++;
                        break;
                    case 'VIGENTE SIN SEGUIMIENTO':
                        $estadisticas[$subregion]['sin_seguimiento']++;
                        break;
                    case 'CONCLUIDA':
                        $estadisticas[$subregion]['concluidas']++;
                        break;
                    case 'CESACIÓN':
                        $estadisticas[$subregion]['cesacion']++;
                        break;
                }
    
                // Contar medidas
                for ($i = 1; $i <= 7; $i++) {
                    if (!empty($registro["condicion$i"])) {
                        $estadisticas[$subregion]['condiciones'][$i]++;
                    }
                }
            }
        }
    
        return $estadisticas;
    }
    
    private function obtenerSubregionColaboracionesCondiciones($supervision) {
        foreach ($this->subregiones as $nombre => $prefijos) {
            foreach ($prefijos as $prefijo) {
                if (strpos($supervision, $prefijo) === 0) {
                    return $nombre;
                }
            }
        }
        return null;
    }
    
    private function imprimirFilaSubregionColaboracionesCondiciones($pdf, $nombre, $datos, $w, $wCondiciones) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w['supervision'], 8, utf8_decode($nombre), 1, 0, 'L');
        $pdf->Cell($w['registros'], 8, $datos['registros'], 1, 0, 'C');
        $pdf->Cell($w['seguimiento'], 8, $datos['en_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['sin_seguimiento'], 8, $datos['sin_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['concluidas'], 8, $datos['concluidas'], 1, 0, 'C');
        $pdf->Cell($w['cesacion'], 8, $datos['cesacion'], 1, 0, 'C');
    
        for ($i = 1; $i <= 7; $i++) {
            $pdf->Cell($wCondiciones[1], 8, $datos['condiciones'][$i] ?? 0, 1, 0, 'C');
        }
        $pdf->Ln();
    }
    
    private function imprimirFilaTotalesColaboracionesCondiciones($pdf, $estadisticas, $w, $wCondiciones) {
        $totales = [
            'registros' => 0,
            'en_seguimiento' => 0,
            'sin_seguimiento' => 0,
            'concluidas' => 0,
            'cesacion' => 0,
            'condiciones' => array_fill(1, 7, 0)
        ];
    
        foreach ($estadisticas as $datos) {
            $totales['registros'] += $datos['registros'];
            $totales['en_seguimiento'] += $datos['en_seguimiento'];
            $totales['sin_seguimiento'] += $datos['sin_seguimiento'];
            $totales['concluidas'] += $datos['concluidas'];
            $totales['cesacion'] += $datos['cesacion'];
            
            for ($i = 1; $i <= 7; $i++) {
                $totales['condiciones'][$i] += $datos['condiciones'][$i] ?? 0;
            }
        }
    
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w['supervision'], 8, 'TOTALES', 1, 0, 'L');
        $pdf->Cell($w['registros'], 8, $totales['registros'], 1, 0, 'C');
        $pdf->Cell($w['seguimiento'], 8, $totales['en_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['sin_seguimiento'], 8, $totales['sin_seguimiento'], 1, 0, 'C');
        $pdf->Cell($w['concluidas'], 8, $totales['concluidas'], 1, 0, 'C');
        $pdf->Cell($w['cesacion'], 8, $totales['cesacion'], 1, 0, 'C');
    
        for ($i = 1; $i <= 7; $i++) {
            $pdf->Cell($wCondiciones[1], 8, $totales['condiciones'][$i], 1, 0, 'C');
        }
        $pdf->Ln();
    }



        

    private function calcularTotalesGenerales($datos, $subregiones) {
        $totalesBase = [
            'peticiones' => 0,
            'atendidas' => 0,
            'porcentaje_atencion' => 0,
        ];
        
        $totalesGenerales = $totalesBase;
        
        foreach ($datos as $dato) {
            $totalesGenerales['peticiones']++;
        }
        
        $totalesGenerales['porcentaje_atencion'] = $totalesGenerales['peticiones'] > 0 
            ? round(($totalesGenerales['atendidas'] / $totalesGenerales['peticiones']) * 100, 2) . '%' 
            : '0%';
        
        return $totalesGenerales;
    }

    private function procesarVerificaciones($datosVerificacion, $subregiones, &$totalesGenerales) {
        $verificacionesPorSubregion = [];
        foreach ($subregiones as $nombre => $prefijos) {
            $verificacionesPorSubregion[$nombre] = [
                'atendidas' => 0,
                'verificadas' => 0,
                'agencia' => 0,
                'domiciliaria' => 0,
                'personal' => 0,
                'telefonica' => 0
            ];
        }
        
        foreach ($datosVerificacion as $dato) {
            $subregionEncontrada = null;
            foreach ($subregiones as $nombre => $variantes) {
                if (in_array(strtolower(trim($dato->subdireccion)), array_map('strtolower', array_map('trim', $variantes)))) {
                    $subregionEncontrada = $nombre;
                    break;
                }
            }
    
            if ($subregionEncontrada !== null) {
                // Convertir el tipo de verificación a minúsculas y quitar espacios
                $tipo = preg_replace('/[^a-záéíóúñ]/u', '', mb_strtolower(trim($dato->tipo_verificacion)));
                
                if ($tipo == 'agencia') {
                    $verificacionesPorSubregion[$subregionEncontrada]['agencia'] = $dato->total;
                }
                elseif ($tipo == 'domiciliaria') {
                    $verificacionesPorSubregion[$subregionEncontrada]['domiciliaria'] = $dato->total;
                }
                elseif ($tipo == 'personal') {
                    $verificacionesPorSubregion[$subregionEncontrada]['personal'] = $dato->total;
                }
                elseif ($tipo == 'telefonica' || $tipo == 'telefónica' || strpos($tipo, 'telef') !== false) {
                    $verificacionesPorSubregion[$subregionEncontrada]['telefonica'] = $dato->total;
                }
                
                // Actualizar el total de verificadas
                $verificacionesPorSubregion[$subregionEncontrada]['verificadas'] += $dato->total;
            }
        }
        
        return $verificacionesPorSubregion;
    }

    private function generarTablaVerificaciones($pdf, $verificacionesPorSubregion, $totalesGenerales) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetTextColor(0, 0, 0);
    
        // Definir anchos de columnas
        $w1 = 30; // SUBDIRECCIÓN
        $w2 = 20; // ATENDIDAS
        $w3 = 25; // VERIFICADAS
        $w4 = 20; // AGENCIA
        $w5 = 25; // DOMICILIARIA
        $w6 = 20; // PERSONAL
        $w7 = 25; // TELEFÓNICA
        $w8 = 20; // TOTAL
        $w9 = 20; // PORCENTAJE
        $w10 = 20; // PORCENTAJE
    
        // Calcular ancho total de las columnas de verificación
        $w_verificacion = $w7 + $w8 + $w9 + $w10;
    
        // Altura de las celdas
        $h = 6;
    
        // Primera fila
        $pdf->Cell($w1, $h * 2, 'SUBDIRECCION', 1, 0, 'C', true);
        $pdf->Cell($w2, $h * 2, 'ATENDIDAS', 1, 0, 'C', true);
        $pdf->Cell($w3, $h * 2, 'VERIFICADAS', 1, 0, 'C', true);
        $pdf->Cell($w_verificacion, $h, 'TIPO DE VERIFICACION', 1, 1, 'C', true);
    
        // Segunda fila de encabezados
        $x = $pdf->GetX() + $w1 + $w2 + $w3;
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->Cell($w4, $h, 'AGENCIA', 1, 0, 'C', true);
        $pdf->Cell($w5, $h, 'DOMICILIARIA', 1, 0, 'C', true);
        $pdf->Cell($w6, $h, 'PERSONAL', 1, 0, 'C', true);
        $pdf->Cell($w7, $h, utf8_decode('TELEFÓNICA'), 1, 1, 'C', true);
    
        // Calcular totales de verificación
        $totalesVerificacion = $this->calcularTotalesVerificacion($verificacionesPorSubregion);
    
        // Filas de datos por subregión
        foreach ($verificacionesPorSubregion as $nombre => $datos) {
            $pdf->Cell($w1, $h, utf8_decode($nombre), 1, 0, 'C');
            $pdf->Cell($w2, $h, $totalesGenerales['peticiones'], 1, 0, 'C');
            $pdf->Cell($w3, $h, $datos['verificadas'], 1, 0, 'C');
            $pdf->Cell($w4, $h, $datos['agencia'], 1, 0, 'C');
            $pdf->Cell($w5, $h, $datos['domiciliaria'], 1, 0, 'C');
            $pdf->Cell($w6, $h, $datos['personal'], 1, 0, 'C');
            $pdf->Cell($w7, $h, $datos['telefonica'], 1, 1, 'C');
        }
    
        // Fila de totales
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w1, $h, 'TOTALES', 1, 0, 'C', true);
        $pdf->Cell($w2, $h, $totalesGenerales['peticiones'], 1, 0, 'C', true);
        $pdf->Cell($w3, $h, $totalesVerificacion['verificadas'], 1, 0, 'C', true);
        $pdf->Cell($w4, $h, $totalesVerificacion['agencia'], 1, 0, 'C', true);
        $pdf->Cell($w5, $h, $totalesVerificacion['domiciliaria'], 1, 0, 'C', true);
        $pdf->Cell($w6, $h, $totalesVerificacion['personal'], 1, 0, 'C', true);
        $pdf->Cell($w7, $h, $totalesVerificacion['telefonica'], 1, 1, 'C', true);
        $pdf->Cell($w8, $h, $totalesVerificacion['telefonica'], 1, 1, 'C', true);
        $pdf->Cell($w9, $h, $totalesVerificacion['telefonica'], 1, 1, 'C', true);
        $pdf->Cell($w10, $h, $totalesVerificacion['telefonica'], 1, 1, 'C', true);


    }

    private function calcularTotalesVerificacion($verificacionesPorSubregion) {
        return array_reduce($verificacionesPorSubregion, function($carry, $item) {
            $carry['verificadas'] += $item['verificadas'];
            $carry['agencia'] += $item['agencia'];
            $carry['domiciliaria'] += $item['domiciliaria'];
            $carry['personal'] += $item['personal'];
            $carry['telefonica'] += $item['telefonica'];
            return $carry;
        }, [
            'verificadas' => 0,
            'agencia' => 0,
            'domiciliaria' => 0,
            'personal' => 0,
            'telefonica' => 0
        ]);
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
        $pdf->SetTextColor(255, 255, 255);


    
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
$action = $_GET['action'] ?? '';
$controller = new ReporteIndicadoresController();

switch($action) {
    case 'generarReportePDF':
        // Para adolescentes
        $controller->generarReportePDF();
        break;
    
    case 'generarReportePDFR':
        // Para adultos
        $controller->generarReportePDFR();
        break;
    case 'generarReportePDFM':
        // Para adultos
        $controller->generarReportePDFM();
        break;
    case 'generarReportePDFC':
        // Para adultos
        $controller->generarReportePDFC();
        break;
        case 'generarReportePDFCM':
        // Para adultos
        $controller->generarReportePDFCM();
        break;
        case 'generarReportePDFCC':
            // Para adultos
            $controller->generarReportePDFCC();
            break;
    default:
        $controller->mostrarFormulario();
        break;
}   
?>