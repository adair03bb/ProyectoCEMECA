<?php
session_start();
if ($_SESSION['tipo_usuario_id'] == 1 || $_SESSION['tipo_usuario_id'] == 3  || $_SESSION['tipo_usuario_id'] == 2) {
    include_once 'layouts/header.php';
?>
    <title>Adm | Editar Datos</title>
<?php
    include_once 'layouts/nav.php';
?>
<!-- MODAL CONTRASEÑA ACENDER O DESENDER -->
<div class="modal fade" id="confirmar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Confirmar Accion</h1>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="text-center">
        
        <img id="avatar1" src="<?php  $avatar = $_SESSION['avatar'];  $avatarPath = '../img/' . ($avatar ? $avatar : 'perfil.png');
    
            // Opcional: Verificar si el archivo existe
            if (!file_exists($avatarPath)) {
                $avatarPath = '../img/perfil.png';
            }
            echo $avatarPath; 
        ?>" alt="" class="profile-user-img img-fluid img-circle">
            <b>
                <br><?php echo $_SESSION['nombre']; ?>
                
            </b>
        </div>
        <span>Necesitamos su password para continuar</span>
        <div class="alert alert-success text-center" id="confirmado" style='display:none;'>
            <span><i class="fas fa-check m-1"></i>Se realizo la accion en el usuario</span>
        </div>
        <div class="alert alert-danger text-center" id="rechazado" style='display:none;'>
            <span><i class="fas fa-times m-1"></i>Error password no es correcto</span>
        </div>
        <div class="alert alert-danger text-center" id="vacia" style='display:none;'>
            <span><i class="fas fa-times m-1"></i>Error: No se permiten contraseñas vacías</span>
        </div>
        <form id="form-confirmar">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-unlock-alt"></i></span>
                </div>
                <input id="oldPassword" type="password" class="form-control" placeholder="Ingresa la contraseña actual">
                <input type="hidden"  id="id_user">
                <input type="hidden"  id="funcion">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn bg-gradient-primary" form="form-confirmar" >Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="crearusuario" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title"> Crear Usuario</h3>
            <button data-dismiss="modal" arial-label="close"class="close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-success text-center" id="add" style='display:none;'>
                <span><i class="fas fa-check m-1"></i>Se agrego correctamente</span>
            </div>
            <div class="alert alert-danger text-center" id="noadd" style='display:none;'>
                <span><i class="fas fa-times m-1"></i>Error al agregar, no duplicar usuarios</span>
            </div>
            <form id="form-crear">
                <div class="form-group">
                    <label for="nombrePer">Nombre del personal</label>
                    <input id="nombrePer" type="text" class="form-control" placeholder="Ingrese nombre" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre de usuario</label>
                    <input id="nombre" type="text" class="form-control" placeholder="Ingrese nombre" required>
                </div>
                <div class="form-group">
                    <label for="pass">Contraseña</label>
                    <input id="pass" type="password" class="form-control" placeholder="Ingrese contraseña" required>
                </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn bg-gradient-primary float-right m-1" form="form-crear" >Guardar</button>
            <button type="button" data-dismiss="modal" class="btn btn-outline-secondary float-right m-1" >Cerrar</button>
            </form>
        </div>
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
                    <h1 >Administrar Usuarios <button id="button_crear" type="button" data-toggle="modal" data-target="#crearusuario" class="btn bg-gradient-primary ml-2">Crear usuario</button></h1>
                    <input type="hidden" id="tipo_usuario" value="<?php echo $_SESSION['tipo_usuario_id'] ?>">
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../view/menuAdmin.php">Ir a Inicio</a></li>
                        <li class="breadcrumb-item active">Administrar Usuarios</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section>
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Buscar Usuario</h3>
                    <div class="input-group">
                        <input id="buscar" type="text" class="form-control float-left" placeholder="Ingrese nombre de usuario">
                        <div class="input-group-append">
                            <button class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </div>

                </div>
                <div  class="card-body">
                    <div id="usuarios" class="row d-flex align-items-stretch">
                        
                    </div>
                </div>
                <div class="card-footer">

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
<script src="../js/gestion_usuario.js"></script>