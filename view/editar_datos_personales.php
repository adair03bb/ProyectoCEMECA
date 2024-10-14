<?php
session_start();
if ($_SESSION['tipo_usuario_id'] == 1) {
    include_once 'layouts/header.php';
?>
    <title>Adm | Editar Datos</title>
<?php
    include_once 'layouts/nav.php';
?>
<!-- Modal -->
<div class="modal fade" id="cambiarPassword" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Cambiar Contraseña</h1>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="text-center">
            <img src="../img/perfil.png" alt="" class="profile-user-img img-fluid img-circle">
        </div>
        <div class="text-center">
            <b>
                <?php echo $_SESSION['nombre']; ?>
            </b>
        </div>
        <div class="alert alert-success text-center" id="update" style='display:none;'>
            <span><i class="fas fa-check m-1"></i>Contraseña actualizada correctamente</span>
        </div>
        <div class="alert alert-danger text-center" id="noupdate" style='display:none;'>
            <span><i class="fas fa-times m-1"></i>Error al actualizar la contraseña</span>
        </div>
        <form id="form-pass">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-unlock-alt"></i></span>
                </div>
                <input id="oldPassword" type="password" class="form-control" placeholder="Ingresa la contraseña actual">
            </div>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                </div>
                <input id="newPassword" type="password" class="form-control" placeholder="Ingresa la contraseña nueva">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn bg-gradient-primary" form="form-pass">Cambiar Contraseña</button>
      </div>
    </div>
  </div>
</div>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Datos Personales</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../view/menuAdmin.php">Ir a Inicio</a></li>
                        <li class="breadcrumb-item active">Datos Personales</li>
                    </ol>
                </div>
            </div>
        </div>
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
                                    <button data-toggle="modal" data-target="#cambiarPassword" type="button" class="btn btn-block btn-outline-warning btn-sm">Cambiar Contraseña</button>
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
                                <div class="alert alert-success text-center" id="editado" style='display:none;'>
                                    <span><i class="fas fa-check m-1"></i>Editado</span>
                                </div>
                                <div class="alert alert-danger text-center" id="noeditado" style='display:none;'>
                                    <span><i class="fas fa-times m-1"></i>Edición deshabilitada</span>
                                </div>
                                <form id="form-usuario" class="form-horizontal">
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
                                            <button type="submit" id="guardar" class="btn btn-block btn-outline-success">Guardar</button>
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

<?php
    include_once 'layouts/footer.php';
} else {
    header('Location: ../index.php');
}
?>
<script src="../js/usuario.js"></script>


