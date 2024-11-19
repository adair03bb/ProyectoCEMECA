<?php
require_once 'conexion.php';

class EstadisticasMensualesModel {
    private $pdo;

    public function __construct() {
        $conexion = new Conexion();
        $this->pdo = $conexion->pdo;
    }

    public function obtenerDatosPorSubregion($tipo, $fechaInicio, $fechaFin) {
        try {
            $subregionPatterns = ($tipo === 'Adultos') 
                ? ['SN%', 'ST%', 'SV1%', 'SV2%', 'SS%']
                : ['SA-N%', 'SV2A%', 'SA-VII%', 'SA-T%', 'SA-VI%', 'SA-S%'];
    
            $sql = "SELECT evaluacion AS subregion, COUNT(*) as peticiones,
                           SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, SI AUTORIZADA' THEN 1 ELSE 0 END) as atendidas_y_realizadas,
                           SUM(CASE WHEN tipo_atencion = 'SI ATENDIDA, NO REALIZADA POR LIBERTAD' THEN 1 ELSE 0 END) as no_realizada_por_libertad,
                           SUM(CASE WHEN tipo_riesgo = 'Riesgo Alto' THEN 1 ELSE 0 END) as riesgo_alto,
                           SUM(CASE WHEN tipo_riesgo = 'Riesgo Medio' THEN 1 ELSE 0 END) as riesgo_medio,
                           SUM(CASE WHEN tipo_riesgo = 'Riesgo Bajo' THEN 1 ELSE 0 END) as riesgo_bajo,
                           SUM(CASE WHEN tipo_riesgo = 'Sin Nivel de Riesgo' THEN 1 ELSE 0 END) as sin_nivel_de_riesgo
                    FROM eval_adolescentes
                    WHERE (" . implode(" OR ", array_fill(0, count($subregionPatterns), "evaluacion LIKE ?")) . ")
                    AND fecha_recepcion BETWEEN ? AND ?
                    GROUP BY evaluacion";
    
            $queryParams = array_merge($subregionPatterns, [$fechaInicio, $fechaFin]);
            $query = $this->pdo->prepare($sql);
            $query->execute($queryParams);
    
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error en obtenerDatosPorSubregion: " . $e->getMessage());
            return [];
        }
    }
}
?>
