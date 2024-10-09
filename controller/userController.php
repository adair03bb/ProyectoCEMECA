<?php
include_once '../model/usuario.php';
session_start();

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

if ($_POST['funcion'] == 'editar_usuario') {
    $idusuario = $_POST['idusuario'];
    $nombre = $_POST['nombre'];
    $usuario_nombre = $_POST['usuario'];
    if ($usuario instanceof usuario) {
        $usuario->editar($idusuario, $nombre, $usuario_nombre);
        echo 'editado';
    } else {
        echo 'Error: objeto usuario no inicializado correctamente';
    }
}
?>
