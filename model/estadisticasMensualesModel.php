<?php
require_once 'conexion.php';

class EstadisticasMensualesModel {
    private $pdo;

    public function __construct() {
        $conexion = new Conexion();
        $this->pdo = $conexion->pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilitar errores de PDO
    }
    public function obtenerDatosPorSubregion($fechaInicio, $fechaFin) {
        try {
            $sql = "
                SELECT subdireccion, tipo_atencion, tipo_riesgo
                FROM eval_adolescentes
                WHERE fecha_recepcion BETWEEN ? AND ?";
            
            $query = $this->pdo->prepare($sql);
            $query->execute([$fechaInicio, $fechaFin]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosPorSubregion: " . $e->getMessage());
            return [];
        }
    }
        
}