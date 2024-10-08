<?php
include_once 'conexion.php';
class usuario{
    var $objetos;
    public function __construct(){
        $db = new conexion();
        $this->acceso = $db->pdo;
    }
    function logearse($user,$pass){
        $sql="SELECT * FROM usuario INNER JOIN tipos_usuario on tipo_usuario_id=id where usuario=:user and contrasena=:pass";
        $query = $this->acceso->prepare($sql);
        $query->execute (array(':user'=>$user,':pass'=>$pass));
        $this->objetos=$query->fetchall();
        return $this->objetos;
    }
    function obtener_datos($id){
        $sql="SELECT * FROM usuario join tipos_usuario on tipo_usuario_id=id and idusuario=:id";
        $query = $this->acceso->prepare($sql);
        $query->execute (array(':id'=>$id));
        $this->objetos=$query->fetchall();
        return $this->objetos;
    }
}
?>