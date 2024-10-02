<?php
include_once '../model/usuario.php';
session_start();
$user = $_POST['user'];
$pass = $_POST['pass'];
$usuario = new usuario();
$usuario->logearse($user,$pass);
foreach($usuario->objetos as $objeto){
    print_r($objeto);
}
?>