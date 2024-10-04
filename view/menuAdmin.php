<?php
session_start();
if($_SESSION['tipo_usuario_id']==1){
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>
</head>
<body>
    <h1>Hola administrador</h1>
    <a href="../controller/logout.php">Cerrar sesión</a>
</body>
</html>
<?php
}
else{
    header('Location: ../index.php');
}
?>