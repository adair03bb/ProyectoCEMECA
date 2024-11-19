<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGECA</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="/css/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 -->
</head>
<?php
session_start();
if (!empty($_SESSION['tipo_usuario_id'])) {
    header('Location: controller/loginController.php');
} else {
    session_destroy();
?>
<body>
    <header>
        <div class="logos">
            <img id="logoEscudo" src="img/gobiernoEdoMex.png" alt="Logo Escudo">
        </div>
        <div class="contenido-central">
            <h1 id="titulo">SIGECA</h1>
            <div class="division"></div>
            <h2 class="subtitulo">Sistema de Gestión de Control de Adolescentes</h2>
        </div>
        <div class="logos">
            <img id="logoEdomex" src="img/edomex.png" alt="Logo Edomex">
        </div>
    </header>
    <div class="cajas-login">
        <form class="formulario" action="controller/loginController.php" method="post">
            <h1>Login</h1>

            <div class="labelInput">
                <input type="text" placeholder="Usuario" name="user" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="labelInput">
                <input type="password" placeholder="Contraseña" name="pass" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <button id="btnLogin" type="submit" class="btn_ingresar">Ingresar</button>
        </form>
    </div>

    <!-- SweetAlert para mensajes de error -->
    <?php if (isset($_GET['error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo htmlspecialchars($_GET['error']); ?>',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php'; // Redirigir al index sin parámetros
                }
            });
        </script>
    <?php endif; ?>
</body>
<script src="js/menu.js"></script>
</html>
<?php
}
?>
