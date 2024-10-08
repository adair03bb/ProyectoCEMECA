<?php
include_once '../model/usuario.php';
session_start(); // Asegúrate de iniciar la sesión

$usuario = new usuario();
if ($_POST['funcion'] == 'buscar_usuario') {
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

if ($_POST['funcion'] == 'capturar_datos') {
    $json = array();
    $idusuario = $_POST['idusuario'];
    $usuario->obtener_datos($idusuario);
    foreach ($usuario->objetos as $objeto) {
        $json[] = array(
            'nombre' => $objeto->nombre,
            'usuario' => $objeto->usuario,
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}
?>
