<?php
require 'vendor/autoload.php';
require 'conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// SweetAlert
function generateSweetAlert($title, $text, $icon, $redirectUrl) {
    return "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: '$title',
            text: '$text',
            icon: '$icon',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '$redirectUrl';
            }
        });
    });
    </script>
    ";
}


if (isset($_POST['submit'])) {
    
    if (isset($_FILES['archivoExcel']) && $_FILES['archivoExcel']['error'] == 0) {
        $archivoTmp = $_FILES['archivoExcel']['tmp_name'];
        $nombreArchivo = $_FILES['archivoExcel']['name'];
        $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
       
        if ($extension == 'xlsx' || $extension == 'xls') {
           
            $documento = IOFactory::load($archivoTmp);
            $hojaActual = $documento->getSheet(1);
         
            $numeroFilas = $hojaActual->getHighestDataRow();
            $sql = "INSERT INTO reevaluaciones (
                evaluacion, reevaluacion, carpeta_administrativa, juzgado, fecha_recepcion, hora_recepcion, 
                fuero, fiscalia, agencia, juez_control, turno, telefono, email, carpeta_investigacion, 
                falla_tecnica, nuc, nic, fecha_disposicion, hora_disposicion, puesta_disposicion, 
                paterno, materno, nombre, curp, edad, genero, municipio, colonia, calle, numero, 
                municipio_delito, colonia_delito, descripcion_delito, delito1, delito2, 
                delito3, delito4, subdireccion, distrito_judicial, evaluador, tipo_atencion, 
                fecha_entrevista, hora_entrevista, defensor, tipo_riesgo, nuevo_tipo_riesgo, riesgo168, 
                riesgo169, riesgo170, fecha_envio, hora_envio, estado, verificada, 
                tipo_verificacion, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ?, ?)";

            $stmt = $mysqli->prepare($sql);
            for ($indiceFila = 2; $indiceFila <= $numeroFilas; $indiceFila++) {
                $evaluacion = $hojaActual->getCell('A' . $indiceFila)->getValue();
                $reevaluacion = $hojaActual->getCell('B' . $indiceFila)->getValue();
                $carpeta_administrativa = $hojaActual->getCell('C' . $indiceFila)->getValue();
                $juzgado = $hojaActual->getCell('D' . $indiceFila)->getValue();

                $valorFechaRecepcion = $hojaActual->getCell('E' . $indiceFila)->getValue();
                if (is_numeric($valorFechaRecepcion)) {
                    $fecha_recepcion = Date::excelToDateTimeObject($valorFechaRecepcion)->format('Y-m-d');;
                } else {
                    $fecha_recepcion = null; 
                }

                $valorhora_recepcion = $hojaActual->getCell('F' . $indiceFila)->getValue();
                if (is_numeric($valorhora_recepcion)) {
                    $hora_recepcion = Date::excelToDateTimeObject($valorhora_recepcion)->format('H:i');
                } else {
                    $hora_recepcion = null; 
                }
            
                $fuero = $hojaActual->getCell('G' . $indiceFila)->getValue();
                $fiscalia = $hojaActual->getCell('H' . $indiceFila)->getValue();
                $agencia = $hojaActual->getCell('I' . $indiceFila)->getValue();
                $juezcontrol = $hojaActual->getCell('J' . $indiceFila)->getValue();
                $turno = $hojaActual->getCell('K' . $indiceFila)->getValue();
                $telefono = $hojaActual->getCell('L' . $indiceFila)->getValue();
                $email = $hojaActual->getCell('M' . $indiceFila)->getValue();
                $carpeta_investigacion = $hojaActual->getCell('N' . $indiceFila)->getValue();
                $falla_tecnica = $hojaActual->getCell('O' . $indiceFila)->getValue();
                $nuc = $hojaActual->getCell('P' . $indiceFila)->getValue();
                $nic = $hojaActual->getCell('Q' . $indiceFila)->getValue();

                $valorFechaDisposicion = $hojaActual->getCell('R' . $indiceFila)->getValue();
                if (is_numeric($valorFechaDisposicion)) {
                    $fecha_disposicion = Date::excelToDateTimeObject($valorFechaDisposicion)->format('Y-m-d');
                } else {
                    $fecha_disposicion = null; 
                }
               
                $valorhora_disposicion =  $hojaActual->getCell('S' . $indiceFila)->getFormattedValue();
                if (is_numeric($valorhora_disposicion)) {
                    $hora_disposicion = Date::excelToDateTimeObject($valorhora_disposicion)->format('H:i');
                } else {
                    $hora_disposicion = null; 
                }

                $puesta_disposicion = $hojaActual->getCell('T' . $indiceFila)->getValue();
                $paterno = $hojaActual->getCell('U' . $indiceFila)->getValue();
                $materno = $hojaActual->getCell('V' . $indiceFila)->getValue();
                $nombre = $hojaActual->getCell('W' . $indiceFila)->getValue();
                $curp = $hojaActual->getCell('X' . $indiceFila)->getValue();
                $edad = $hojaActual->getCell('Y' . $indiceFila)->getValue();
                $genero = $hojaActual->getCell('Z' . $indiceFila)->getValue();
                $municipio = $hojaActual->getCell('AA' . $indiceFila)->getValue();
                $colonia = $hojaActual->getCell('AB' . $indiceFila)->getValue();
                $calle = $hojaActual->getCell('AC' . $indiceFila)->getValue();
                $numero = $hojaActual->getCell('AD' . $indiceFila)->getValue();
                $municipio_delito = $hojaActual->getCell('AE' . $indiceFila)->getValue();
                $colonia_delito = $hojaActual->getCell('AF' . $indiceFila)->getValue();
                $descripcion_delito = $hojaActual->getCell('AG' . $indiceFila)->getValue();
                $delito1 = $hojaActual->getCell('AH' . $indiceFila)->getValue();
                $delito2 = $hojaActual->getCell('AI' . $indiceFila)->getValue();
                $delito3 = $hojaActual->getCell('AJ' . $indiceFila)->getValue();
                $delito4 = $hojaActual->getCell('AK' . $indiceFila)->getValue();
                $subdireccion = $hojaActual->getCell('AL' . $indiceFila)->getValue();
                $distrito_judicial = $hojaActual->getCell('AM' . $indiceFila)->getValue();
                $evaluador = $hojaActual->getCell('AN' . $indiceFila)->getValue();
                $tipo_atencion = $hojaActual->getCell('AO' . $indiceFila)->getValue();

                $valorFechaEntrebista = $hojaActual->getCell('AQ' . $indiceFila)->getValue();
                if (is_numeric($valorFechaEntrebista)) {
                    $fecha_entrevista = Date::excelToDateTimeObject($valorFechaEntrebista)->format('Y-m-d');
                } else {
                    $fecha_entrevista = null; 
                }

                $valorhora_entrevista =  $hojaActual->getCell('AR' . $indiceFila)->getValue();
                if (is_numeric($valorhora_entrevista)) {
                    $hora_entrevista = Date::excelToDateTimeObject($valorhora_entrevista)->format('H:i');
                } else {
                    $hora_entrevista = null; 
                }
                $defensor = $hojaActual->getCell('AS' . $indiceFila)->getValue();
                $tipo_riesgo = $hojaActual->getCell('AT' . $indiceFila)->getValue();
                $nuevo_tipo_riesgo = $tipo_riesgo = $hojaActual->getCell('AU' . $indiceFila)->getValue();
                $riesgo_168 = $hojaActual->getCell('AV' . $indiceFila)->getValue();
                $riesgo_169 = $hojaActual->getCell('AW' . $indiceFila)->getValue();
                $riesgo_170 = $hojaActual->getCell('AX' . $indiceFila)->getValue();

                $valorFechaEnvio = $hojaActual->getCell('AY' . $indiceFila)->getValue();
                if (is_numeric($valorFechaEnvio)) {
                    $fecha_envio = Date::excelToDateTimeObject($valorFechaEnvio)->format('Y-m-d');
                } else {
                    $fecha_envio = null; 
                }
                
                $valorhora_envio =  $hojaActual->getCell('AZ' . $indiceFila)->getValue();
                if (is_numeric($valorhora_envio)) {
                    $hora_envio = Date::excelToDateTimeObject($valorhora_envio)->format('H:i');
                } else {
                    $hora_envio = null; 
                }
               
                $estado = $hojaActual->getCell('BA' . $indiceFila)->getValue();
                $verificado = $hojaActual->getCell('BB' . $indiceFila)->getValue();
                $tipo_verificacion = $hojaActual->getCell('BC' . $indiceFila)->getValue();
                $observaciones = $hojaActual->getCell('BD' . $indiceFila)->getValue();

                if (
                    empty($evaluacion) && empty($reevaluacion) && empty($carpeta_administrativa) &&
                    empty($juzgado) && empty($fuero) && empty($fiscalia) && empty($agencia) &&
                    empty($juezcontrol) && empty($turno) && empty($telefono) &&
                    empty($email) && empty($carpeta_investigacion) && empty($falla_tecnica) &&
                    empty($nuc) && empty($nic) && empty($puesta_disposicion) &&
                    empty($paterno) && empty($materno) && empty($nombre) &&
                    empty($curp) && empty($edad) && empty($genero) &&
                    empty($municipio) && empty($colonia) && empty($calle) &&
                    empty($numero) && empty($municipio_delito) &&
                    empty($colonia_delito) && empty($descripcion_delito) &&
                    empty($delito1) && empty($delito2) &&
                    empty($delito3) && empty($delito4) &&
                    empty($subdireccion) && empty($distrito_judicial) &&
                    empty($evaluador) && empty($tipo_atencion) &&
                    empty($tutor) && empty($defensor) &&
                    empty($tipo_riesgo) && empty($nuevo_tipo_riesgo) &&
                    empty($riesgo_168) &&
                    empty($riesgo_169) && empty($riesgo_170) &&
                    empty($estado) && empty($verificado) &&
                    empty($tipo_verificacion) && empty($observaciones)
                ) {
                    continue;
                }
                $stmt->bind_param(
                    'sssssssssssssssssssssssssssssssssssssssssssssssssssssss',
                    $evaluacion,
                    $reevaluacion,
                    $carpeta_administrativa,
                    $juzgado,
                    $fecha_recepcion,
                    $hora_recepcion,
                    $fuero,
                    $fiscalia,
                    $agencia,
                    $juezcontrol,
                    $turno,
                    $telefono,
                    $email,
                    $carpeta_investigacion,
                    $falla_tecnica,
                    $nuc,
                    $nic,
                    $fecha_disposicion,
                    $hora_disposicion,
                    $puesta_disposicion,
                    $paterno,
                    $materno,
                    $nombre,
                    $curp,
                    $edad,
                    $genero,
                    $municipio,
                    $colonia,
                    $calle,
                    $numero,
                    $municipio_delito,
                    $colonia_delito,
                    $descripcion_delito,
                    $delito1,
                    $delito2,
                    $delito3,
                    $delito4,
                    $subdireccion,
                    $distrito_judicial,
                    $evaluador,
                    $tipo_atencion,
                    $fecha_entrevista,
                    $hora_entrevista,
                    $defensor,
                    $tipo_riesgo,
                    $nuevo_tipo_riesgo,
                    $riesgo_168,
                    $riesgo_169,
                    $riesgo_170,
                    $fecha_envio,
                    $hora_envio,
                    $estado,
                    $verificado,
                    $tipo_verificacion,
                    $observaciones
                );
               
                $stmt->execute();
            }

            $stmt->close();
            $mysqli->close();

             echo generateSweetAlert('¡Éxito!', 'Archivo procesado exitosamente!', 'success', 'mostralExcel2hoja.php');
        } else {
            echo generateSweetAlert('Error', 'Por favor, sube un archivo de Excel válido (.xlsx, .xls).', 'error', 'mostralExcel2hoja.php');
        }
    } else {
        echo generateSweetAlert('Error', 'Hubo un error al subir el archivo.', 'error', 'mostralExcel2hoja.php');
    }
}
