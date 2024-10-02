<?php
include_once '../model/usuario.php';
session_start();
$user = $_POST['user'];
$pass = $_POST['pass'];
$usuario = new usuario();
$usuario->logearse($user,$pass);

if(!empty($usuario->objetos)){
    foreach($usuario->objetos as $objeto){
        $_SESSION['usuario']=$objeto->idusuario;
        $_SESSION['tipo_usuario_id']=$objeto->tipo_usuario_id;
        $_SESSION['nombre']=$objeto->nombre;
    }
    switch ( $_SESSION['tipo_usuario_id']) {
        case '1':
            header('Location: ../view/menuAdmin.php');
            break;
        case '2':
            header('Location: ../view/menuUsuario.php');
            break;
    }
}else{
    header('Location: ../index.php');
}

?>