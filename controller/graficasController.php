<?php
require_once 'model/graficasModel.php';

class graficasController {
    private $modelo;

    public function __construct() {
        $this->modelo = new graficasModel();
    }

    public function mostrarGrafica() {
        // Obtiene los datos del modelo
        $datos = $this->modelo->obtenerDatosAdolescentes();
    
        // Organiza los datos para la vista
        $edades = [];
        $subdirecciones = [];
        $descripcion_delitos = [];
        $totales = [];
    
        foreach ($datos as $fila) {
            $edades[] = $fila['edad'];
            $subdirecciones[] = $fila['subdireccion'];
            $descripcion_delitos[] = $fila['descripcion_delito'];
            $totales[] = $fila['total'];
        }
    
        // Incluye la vista pasando los datos
        require_once 'view/menuAdmin.php';
    }
}
?>
