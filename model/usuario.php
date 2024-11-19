<?php
include_once 'conexion.php';
class usuario {
    var $objetos;
    private $acceso;
    public function __construct() {
        $db = new conexion();
        $this->acceso = $db->pdo;
    }

    function logearse($user,$pass) {
        $sql="SELECT * FROM usuario INNER JOIN tipos_usuario on tipo_usuario_id=id where usuario=:user and contrasena=:pass";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':user'=>$user,':pass'=>$pass));
        $this->objetos=$query->fetchall();
        return $this->objetos;
    }

    function obtener_datos($id) {
        $sql="SELECT * FROM usuario join tipos_usuario on tipo_usuario_id=id and idusuario=:id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id'=>$id));
        $this->objetos=$query->fetchall();
        return $this->objetos;
    }

    function editar($idusuario, $nombre, $usuario) {
        $sql = "UPDATE usuario SET nombre = :nombre, usuario = :usuario WHERE idusuario = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $idusuario, ':nombre' => $nombre, ':usuario' => $usuario));
        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function cambiar_contra($idusuario, $oldPassword, $newPassword) {
        $sql = "SELECT * FROM usuario WHERE idusuario = :id AND contrasena = :oldPassword";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $idusuario, ':oldPassword' => $oldPassword));
        $this->objetos = $query->fetchAll();
        if (!empty($this->objetos)) {
            $sql = "UPDATE usuario SET contrasena = :newPassword WHERE idusuario = :id";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id' => $idusuario, ':newPassword' => $newPassword));
            echo 'update';
        } else {
            echo 'noupdate';
        }
    }   

    function cambiar_photo($idusuario, $nombre) {
        $sql = "SELECT avatar FROM usuario WHERE idusuario = :id ";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $idusuario));
        $this->objetos = $query->fetchAll();

        $sql = "UPDATE usuario SET avatar = :nombre WHERE idusuario = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $idusuario, ':nombre' => $nombre));
        return $this->objetos;
    }

    function buscar(){
        if(!empty($_POST['consulta'])){
            $consulta=$_POST['consulta'];
            $sql="SELECT usuario.*, tipos_usuario.tipo FROM usuario JOIN tipos_usuario ON usuario.tipo_usuario_id = tipos_usuario.id WHERE usuario.nombre LIKE :consulta;";
            $query = $this->acceso->prepare($sql);
            $query ->execute(array(':consulta'=>"%$consulta%"));
            $this->objetos=$query->fetchAll();
            return $this->objetos;
        }else{
            $sql="SELECT usuario.*, tipos_usuario.tipo FROM usuario JOIN tipos_usuario ON usuario.tipo_usuario_id = tipos_usuario.id WHERE usuario.nombre NOT LIKE '' ORDER BY usuario.idusuario LIMIT 25;";
            $query = $this->acceso->prepare($sql);
            $query ->execute();
            $this->objetos=$query->fetchAll();
            return $this->objetos;
        }
    }

    function crear($nombre, $nombreUsu, $pass, $fechaalta, $tipo, $estado, $avatar){
        $sql = "SELECT idusuario FROM usuario where usuario=:usuario";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':usuario'=>$nombreUsu));
        $this->objetos = $query->fetchAll();
        
        if(!empty($this->objetos)){
            echo 'noadd';
        } else {
            $sql = "INSERT INTO usuario(nombre, usuario, contrasena, fecha_alta, tipo_usuario_id, estado, avatar) 
                    VALUES(:nombre, :usuario, :contrasena, :fecha_alta, :tipo_usuario_id, :estado, :avatar)";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(
                ':nombre' => $nombre,
                ':usuario' => $nombreUsu,
                ':contrasena' => $pass,
                ':fecha_alta' => $fechaalta,
                ':tipo_usuario_id' => $tipo,
                ':estado' => $estado,
                ':avatar' => $avatar
            ));
            echo 'add';
        }
    }

    function ascender($pass,$id_ascendido, $idusuario){
        $sql = "SELECT idusuario FROM usuario where idusuario=:idusuario AND contrasena=:pass";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':idusuario'=>$idusuario, 'pass'=>$pass));
        $this->objetos = $query->fetchAll();
        if (!empty($this->objetos)) {
            $tipo= 1;
            $sql = "UPDATE usuario SET tipo_usuario_id=:tipo where idusuario=:id";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id'=>$id_ascendido, 'tipo'=>$tipo));
           echo 'ascendido';
        }else{
            echo 'noascendido';
        }
    }

    function descender($pass,$id_descendido, $idusuario){
        $sql = "SELECT idusuario FROM usuario where idusuario=:idusuario AND contrasena=:pass";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':idusuario'=>$idusuario, 'pass'=>$pass));
        $this->objetos = $query->fetchAll();
        if (!empty($this->objetos)) {
            $tipo= 2;
            $sql = "UPDATE usuario SET tipo_usuario_id=:tipo where idusuario=:id";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id'=>$id_descendido, 'tipo'=>$tipo));
           echo 'descendido';
        }else{
            echo 'nodescendido';
        }
    }
    function borrar($pass,$id_borrado, $idusuario){
        $sql = "SELECT idusuario FROM usuario where idusuario=:idusuario AND contrasena=:pass";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':idusuario'=>$idusuario, 'pass'=>$pass));
        $this->objetos = $query->fetchAll();
        if (!empty($this->objetos)) {
            $sql = "DELETE FROM usuario WHERE idusuario=:id";
            $query = $this->acceso->prepare($sql);
            $query->execute(array(':id'=>$id_borrado));
           echo 'borrado';
        }else{
            echo 'noborrado';
        }
    }

    function cambiar_estado($idusuario, $estado) {
        $sql = "UPDATE usuario SET estado = :estado WHERE idusuario = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':estado' => $estado, ':id' => $idusuario));
    }
    
    
}
?>