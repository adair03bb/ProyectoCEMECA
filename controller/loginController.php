<?php
include_once '../model/usuario.php';
session_start();
$user = $_POST['user'];
$pass = $_POST['pass'];
$usuario = new usuario();

if (!empty($_SESSION['tipo_usuario_id'])) {
    switch ($_SESSION['tipo_usuario_id']) {
        case '1':
            header('Location: ../view/menuAdmin.php');
            break;
        case '2':
            header('Location: ../view/menuAdmin.php');
            break;
        case '3':
            header('Location: ../view/menuAdmin.php');
            break;
    }
} else {
    // Buscar usuario por credenciales
    $usuario->logearse($user, $pass);

    if (!empty($usuario->objetos)) {
        foreach ($usuario->objetos as $objeto) {
            // Verificar si el usuario está activo
            if ($objeto->estado == 0) {
                // Redirigir al login con mensaje de error
                header('Location: ../index.php?error=Usuario inactivo');
                exit;
            }

            // Si está activo, inicializar sesión
            $_SESSION['usuario'] = $objeto->idusuario;
            $_SESSION['tipo_usuario_id'] = $objeto->tipo_usuario_id;
            $_SESSION['nombre'] = $objeto->nombre;
            $_SESSION['avatar'] = $objeto->avatar;
        }

        // Redirigir según el tipo de usuario
        switch ($_SESSION['tipo_usuario_id']) {
            case '1':
                header('Location: ../view/menuAdmin.php');
                break;
            case '2':
                header('Location: ../view/menuAdmin.php');
                break;
            case '3':
                header('Location: ../view/menuAdmin.php');
                break;
        }
    } else {
        // Credenciales incorrectas
        header('Location: ../index.php?error=Credenciales incorrectas');
    }
}
?>
