<?php
require_once 'conexion.php';

class PruebaModelo {
    private $pdo;

    public function __construct() {
        $conexion = new Conexion();
        $this->pdo = $conexion->pdo;
    }

    public function obtenerEvaluadores() {
        try {
            $sql = "SELECT DISTINCT evaluador AS id, evaluador AS nombre FROM eval_adolescentes WHERE evaluador IS NOT NULL AND evaluador != ''";
            $query = $this->pdo->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "Error al obtener evaluadores: " . $e->getMessage();
            return [];
        }
    }
}

$modelo = new PruebaModelo();
$evaluadores = $modelo->obtenerEvaluadores();

echo "<h1>Lista de Evaluadores:</h1>";
if (!empty($evaluadores)) {
    echo "<ul>";
    foreach ($evaluadores as $evaluador) {
        echo "<li>{$evaluador->nombre}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No hay evaluadores disponibles.</p>";
}
