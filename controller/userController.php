<?php
include_once '../model/usuario.php';

$usuario = new usuario();
if($_POST['funcion']=='buscar_usuario'){
    $json = array();
    $usuario->obtener_datos($_POST['dato']);
    foreach ($usuario->objetos as $objeto) {
        $json[] = array(
            'nombre' => $objeto->nombre,
            'usuario' => $objeto->usuario,
            'estado' => $objeto->estado,
            'tipo' => $objeto->tipo,
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}
?>
