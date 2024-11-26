<?php
require_once 'conexion.php';

class graficasModel {
    private $pdo;

    public function __construct() {
        $conexion = new conexion();
        $this->pdo = $conexion->pdo;
    }

    public function obtenerDatosAdolescentes() {
        try {
            $query = $this->pdo->prepare("
                SELECT edad, subdireccion, descripcion_delito, COUNT(*) as total
                FROM eval_adolescentes
                GROUP BY edad, subdireccion, descripcion_delito
                ORDER BY total DESC
            ");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC); // Devuelve un arreglo asociativo
        } catch (PDOException $e) {
            die("Error al obtener los datos: " . $e->getMessage());
        }
    }
}
?>
