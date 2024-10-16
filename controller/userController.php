<?php
include_once '../model/usuario.php';

$usuario = new usuario();
session_start();
$idusuario=$_SESSION['usuario'];
if ($_POST['funcion'] == 'buscar_usuario') {
    $json = array();
    $usuario->obtener_datos($_POST['dato']);
    foreach ($usuario->objetos as $objeto) {
        $json[] = array(
            'nombre' => $objeto->nombre,
            'usuario' => $objeto->usuario,
            'estado' => $objeto->estado,
            'tipo' => $objeto->tipo,
            'avatar'=>'../img/'.$objeto->avatar
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
if ($_POST['funcion'] == 'cambiar_contra') {
    $idusuario = $_POST['idusuario'];
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $usuario->cambiar_contra($idusuario, $oldPassword, $newPassword);
}
if ($_POST['funcion'] == 'cambiar_photo') {
    if(($_FILES['photo']['type']=='image/png')||($_FILES['photo']['type']=='image/jpeg')){
        $nombre=uniqid().'-'.$_FILES['photo']['name'];
        $ruta='../img/'.$nombre;
        move_uploaded_file($_FILES['photo']['tmp_name'],$ruta);
        $usuario->cambiar_photo($idusuario,$nombre);
        foreach($usuario->objetos as $objeto){
            unlink('../img/'.$objeto->avatar);
        }
        $json=array();
        $json[] = array(
            'ruta'=>$ruta,
            'alert'=>'edit'
        );
        $jsonstring = json_encode($json);
        echo $jsonstring;
    }else{
        $json=array();
        $json[] = array(
            'alert'=>'noedit'
        );
        $jsonstring = json_encode($json);
        echo $jsonstring;
    }
}
?>
