<?php
require_once 'conexion.php';

class EstadisticasMensualesModel {
    private $pdo;

    public function __construct() {
        $conexion = new Conexion();
        $this->pdo = $conexion->pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilitar errores de PDO
    }

    public function obtenerDatosPorSubregionConTotales($tipo, $fechaInicio, $fechaFin) {
        try {
            // Validar parámetros
            if (empty($tipo) || empty($fechaInicio) || empty($fechaFin)) {
                throw new Exception("Parámetros incompletos: tipo=$tipo, fechaInicio=$fechaInicio, fechaFin=$fechaFin");
            }

            // Definir los patrones de subregión según el tipo
            $subregionPatterns = ($tipo === 'Adultos') 
                ? ['SN%', 'ST%', 'SV1%', 'SV2%', 'SS%']
                : ['SA-N%', 'SV2A%', 'SA-VII%', 'SA-T%', 'SA-VI%', 'SA-S%'];

            // Query con todas las columnas necesarias
            $sql = "
                SELECT 
                    CASE 
                        WHEN evaluacion IS NULL THEN 'TOTALES'
                        ELSE evaluacion
                    END AS subregion,
                    COUNT(*) as peticiones,
                    SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, SI AUTORIZADA' THEN 1 ELSE 0 END) as atendidas,
                    (CASE WHEN COUNT(*) > 0 THEN 
                        SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, SI AUTORIZADA' THEN 1 ELSE 0 END) * 100 / COUNT(*)
                    ELSE 0 END) as porcentaje_atencion,
                    SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, PERO NO AUTORIZADA' THEN 1 ELSE 0 END) as atendida_no_autorizada,
                    SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, NO REALIZADA POR LIBERTAD' THEN 1 ELSE 0 END) as no_realizada_por_libertad,
                    SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, NO REALIZADA POR PRISIÓN PREVENTIVA' THEN 1 ELSE 0 END) as no_realizada_por_prision_preventiva,
                    SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, NO REALIZADA POR CIRCUNSTANCIAS ESPECIALES' THEN 1 ELSE 0 END) as no_realizada_por_circunstancias_especiales,
                    SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, NO REALIZADA POR TRASLADO DE AGENCIA' THEN 1 ELSE 0 END) as no_realizada_por_traslado_agencia,
                    SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, NO REALIZADA POR TRASLADO A CENTRO PREVENTIVO' THEN 1 ELSE 0 END) as no_realizada_por_traslado_centro_preventivo,
                    SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, NO REALIZADA POR CONDICIÓN MÉDICA/HOSPITALIZACIÓN' THEN 1 ELSE 0 END) as no_realizada_por_condicion_medica,
                    SUM(CASE WHEN tipo_atencion LIKE 'NO%' THEN 1 ELSE 0 END) as no_atendida,
                    SUM(CASE WHEN tipo_riesgo = 'RIESGO ALTO' THEN 1 ELSE 0 END) as riesgo_alto,
                    SUM(CASE WHEN tipo_riesgo = 'RIESGO MEDIO' THEN 1 ELSE 0 END) as riesgo_medio,
                    SUM(CASE WHEN tipo_riesgo = 'RIESGO BAJO' THEN 1 ELSE 0 END) as riesgo_bajo,
                    SUM(CASE WHEN tipo_riesgo = 'SIN NIVEL DE RIESGO' THEN 1 ELSE 0 END) as sin_nivel_de_riesgo,
                    SUM(CASE WHEN tipo_atencion = 'INFORME' THEN 1 ELSE 0 END) as informe
                FROM eval_adolescentes
                WHERE (" . implode(" OR ", array_fill(0, count($subregionPatterns), "evaluacion LIKE ?")) . ")
                  AND fecha_recepcion BETWEEN ? AND ?
                GROUP BY evaluacion WITH ROLLUP";

            // Combinar patrones de subregión con parámetros de fecha
            $queryParams = array_merge($subregionPatterns, [$fechaInicio, $fechaFin]);

            // Preparar y ejecutar la consulta
            $query = $this->pdo->prepare($sql);
            $query->execute($queryParams);

            $resultados = $query->fetchAll(PDO::FETCH_OBJ);

            // Validar si hay resultados
            if (empty($resultados)) {
                error_log("No se encontraron datos para los parámetros: $tipo, $fechaInicio, $fechaFin");
                return [];
            }

            return $resultados;

        } catch (PDOException $e) {
            error_log("Error en obtenerDatosPorSubregionConTotales: " . $e->getMessage());
            return [];
        } catch (Exception $e) {
            error_log("Error general: " . $e->getMessage());
            return [];
        }
    }
}
