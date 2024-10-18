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
        
        if(!empty($this->objetos)) {
            $_SESSION['avatar'] = $this->objetos[0]->avatar; // Actualizar la sesión con el avatar actual
        }
        
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
            return 'update';
        } else {
            return 'no update';
        }
    }   

    function cambiar_photo($idusuario, $nombre) {
        $sql = "UPDATE usuario SET avatar = :nombre WHERE idusuario = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $idusuario, ':nombre' => $nombre));
        
        if($query->rowCount() > 0) {
            $_SESSION['avatar'] = $nombre;
            return true;
        }
        return false;
    }

    function obtener_avatar_anterior($idusuario) {
        $sql = "SELECT avatar FROM usuario WHERE idusuario = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id' => $idusuario));
        $resultado = $query->fetch(PDO::FETCH_OBJ);
        return $resultado ? $resultado->avatar : 'default.png';
    }
}
?>