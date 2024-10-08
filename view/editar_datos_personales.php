<?php
session_start();
if ($_SESSION['tipo_usuario_id'] == 1) {
    include_once 'layouts/header.php';
?>
    <title>Adm | Editar Datos</title>
    <!-- Tell the browser to be responsive to screen width -->
<?php
    include_once 'layouts/nav.php';
?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Datos Personales</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../view/menuAdmin.php">Home</a></li>
                            <li class="breadcrumb-item active">Datos Personales</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section>
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card card-success card-outline">
                                <div class="card-body box-profile">
                                    <div class="text-center">
                                        <img src="../img/perfil.png" alt="" class="profile-user-img img-fluid img-circle">
                                    </div>
                                    <input id="idusuario" type="hidden" value="<?php echo $_SESSION['usuario']; ?>">
                                    <h3 id="nombre" class="profile-user-name text-center text-success">Nombre</h3>
                                    <ul class="list-group list-group-unbordered mb-3">
                                        <li class="list-group-item">
                                            <b style="color:#0b7300">Usuario</b><span id="usuario" class="float-right"></span>
                                        </li>
                                        <li class="list-group-item">
                                            <b style="color:#0b7300">Estado</b><span id="estado" class="float-right"></span>
                                        </li>
                                        <li class="list-group-item">
                                            <b style="color:#0b7300">Tipo Usuario</b>
                                            <span id="tipo_usuario_id" class="float-right badge badge-primary"></span>
                                        </li>
                                    </ul>
                                    <button class="edit btn btn-block bg-gradient-danger">Editar</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Editar Datos Personales</h3>
                                </div>
                                <div class="card-body">
                                    <form class="form-horizontal">
                                        <div class="form-group row">
                                            <label for="nombreInput" class="col-sm-2 col-form-label">Nombre</label>
                                            <div class="col-sm-10">
                                                <input type="text" id="nombreInput" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="usuarioInput" class="col-sm-2 col-form-label">Usuario</label>
                                            <div class="col-sm-10">
                                                <input type="text" id="usuarioInput" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10 float-right">
                                                <button type="button" id="guardar" class="btn btn-block btn-outline-success">Guardar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- /.content-wrapper -->
<?php
    include_once 'layouts/footer.php';
} else {
    header('Location: ../index.php');
}
?>
<script src="../js/usuario.js"></script>

