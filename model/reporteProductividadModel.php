<?php
require_once 'conexion.php';

class ReporteProductividadModel {
    private $pdo;

    public function __construct() {
        $conexion = new Conexion();
        $this->pdo = $conexion->pdo;
    }

    public function obtenerEvaluadores() {
        try {
            $sql = "SELECT DISTINCT evaluador AS id, evaluador AS nombre 
                    FROM eval_adolescentes 
                    WHERE evaluador IS NOT NULL 
                    AND evaluador != ''
                    ORDER BY evaluador";
            
            $query = $this->pdo->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error en obtenerEvaluadores: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerResumenPorTipoAtencion($id, $fechaInicio, $fechaFin) {
        $sql = "SELECT tipo_atencion AS tipo, COUNT(*) as total, 
                       (COUNT(*) / (SELECT COUNT(*) FROM eval_adolescentes WHERE evaluador = :id AND fecha_recepcion BETWEEN :fechaInicio AND :fechaFin)) * 100 as porcentaje
                FROM eval_adolescentes
                WHERE evaluador = :id AND fecha_recepcion BETWEEN :fechaInicio AND :fechaFin
                GROUP BY tipo_atencion";
        
        $query = $this->pdo->prepare($sql);
        $query->execute([':id' => $id, ':fechaInicio' => $fechaInicio, ':fechaFin' => $fechaFin]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function obtenerResumenPorTipoRiesgo($id, $fechaInicio, $fechaFin) {
        $sql = "SELECT tipo_riesgo AS tipo, COUNT(*) as total, 
                       (COUNT(*) / (SELECT COUNT(*) FROM eval_adolescentes WHERE evaluador = :id AND fecha_recepcion BETWEEN :fechaInicio AND :fechaFin)) * 100 as porcentaje
                FROM eval_adolescentes
                WHERE evaluador = :id AND fecha_recepcion BETWEEN :fechaInicio AND :fechaFin
                GROUP BY tipo_riesgo";
        
        $query = $this->pdo->prepare($sql);
        $query->execute([':id' => $id, ':fechaInicio' => $fechaInicio, ':fechaFin' => $fechaFin]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
}
?>
