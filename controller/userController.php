<?php

use PhpOffice\PhpSpreadsheet\Cell\DataType;

include_once '../model/usuario.php';

$usuario = new usuario();
session_start();
$idusuario=$_SESSION['usuario'];

if ($_POST['funcion'] == 'buscar_usuario') {
    $json = array();
    $usuario->obtener_datos($_POST['dato']);
    foreach ($usuario->objetos as $objeto) {
        $json[] = array(
            'idusuario' => $objeto->idusuario,
            'nombre' => $objeto->nombre,
            'usuario' => $objeto->usuario,
            'tipo' => $objeto->tipo,
            'estado' => $objeto->estado,
            'avatar'=>'../img/'.$objeto->avatar
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}

if ($_POST['funcion'] == 'capturar_datos') {
    $json = array();
    $idusuario = $_POST['idusuario'];
    $usuario->obtener_datos($idusuario);
    foreach ($usuario->objetos as $objeto) {
        $json[] = array(
            'nombre' => $objeto->nombre,
            'usuario' => $objeto->usuario,
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}

if ($_POST['funcion'] == 'editar_usuario') {
    $idusuario = $_POST['idusuario'];
    $nombre = $_POST['nombre'];
    $usuario_nombre = $_POST['usuario'];
    if ($usuario instanceof usuario) {
        $usuario->editar($idusuario, $nombre, $usuario_nombre);
        echo 'editado';
    } else {
        echo 'Error: objeto usuario no inicializado correctamente';
    }
}
if ($_POST['funcion'] == 'cambiar_contra') {
    $idusuario = $_POST['idusuario'];
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $usuario->cambiar_contra($idusuario, $oldPassword, $newPassword);
}



if ($_POST['funcion'] == 'cambiar_photo') {
    if(($_FILES['photo']['type']=='image/png')||($_FILES['photo']['type']=='image/jpeg')){
        $nombre=uniqid().'-'.$_FILES['photo']['name'];
        $ruta='../img/'.$nombre;
        move_uploaded_file($_FILES['photo']['tmp_name'],$ruta);
        $usuario->cambiar_photo($idusuario,$nombre);
        foreach($usuario->objetos as $objeto){
            unlink('../img/'.$objeto->avatar);
        }
        $json=array();
        $json[] = array(
            'ruta'=>$ruta,
            'alert'=>'edit'
        );
        $jsonstring = json_encode($json[0]);
        echo $jsonstring;
    }else{
        $json=array();
        $json[] = array(
            'alert'=>'noedit'
        );
        $jsonstring = json_encode($json[0]);
        echo $jsonstring;
    }
}


if ($_POST['funcion'] == 'buscar_usuarios_adm') {
    $json = array();
    $fecha_actual = new DateTime();
    $usuario->buscar();
    foreach ($usuario->objetos as $objeto) {
        $json[] = array(
            'idusuario' => $objeto->idusuario,  // Añade esta línea
            'nombre' => $objeto->nombre,
            'usuario' => $objeto->usuario,
            'contrasena' =>$objeto->contrasena,
            'fecha_alta' =>$objeto->fecha_alta,
            'tipo' => $objeto->tipo,
            'tipo_usuario'=>$objeto->tipo_usuario_id,
            'estado' => $objeto->estado,
            'avatar'=>'../img/'.$objeto->avatar
            
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}

if (isset($_POST['funcion']) && $_POST['funcion'] == 'crear_usuario') {
    $nombre = $_POST['nombres'];
    $nombreUsu = $_POST['nombreUsu'];
    $pass = $_POST['password'];
    $fechaalta = date('Y-m-d');
    $tipo = 2;
    $estado = 1;
    $avatar = 'perfil.png';
    $usuario->crear($nombre, $nombreUsu, $pass, $fechaalta, $tipo, $estado, $avatar);
}


if ($_POST['funcion'] == 'ascender'){
    $pass=$_POST['pass'];
    $id_ascendido=$_POST['id_usuario'];
   $usuario->ascender($pass,$id_ascendido, $idusuario);
}

if ($_POST['funcion'] == 'descender'){
    $pass=$_POST['pass'];
    $id_descendido=$_POST['id_usuario'];
   $usuario->descender($pass,$id_descendido, $idusuario);
}

if ($_POST['funcion'] == 'borrar_usuario'){
    $pass=$_POST['pass'];
    $id_borrado=$_POST['id_usuario'];
   $usuario->borrar($pass,$id_borrado, $idusuario);
}


if ($_POST['funcion'] == 'cambiar_estado') {
    $idusuario = $_POST['idusuario'];
    $estado = $_POST['estado'];
    $usuario->cambiar_estado($idusuario, $estado);
    echo 'update';
}
if ($_POST['funcion'] == 'obtener_edades_por_municipio') {
    $json = array();
    $municipio = $_POST['municipio'];
    $datos = $usuario->obtener_edades_por_municipio($municipio);
    foreach ($datos as $dato) {
        $json[] = array(
            'tipo_evaluacion' => $dato->municipio,
            'edad' => $dato->edad,
            'total' => $dato->total
        );
    }
    echo json_encode($json);
    exit;
}

if ($_POST['funcion'] == 'obtener_municipios') {
    $json = array();
    $municipios = $usuario->obtener_municipios();
    foreach ($municipios as $municipio) {
        $json[] = array('municipio' => $municipio->municipio);
    }
    echo json_encode($json);
    exit;
}


if ($_POST['funcion'] == 'obtener_edad_mas_comun') {
    $sql = "SELECT t1.municipio, t1.edad, t1.total
            FROM (
                SELECT municipio, edad, COUNT(*) AS total
                FROM eval_adolescentes
                GROUP BY municipio, edad
            ) t1
            INNER JOIN (
                SELECT municipio, MAX(total) AS max_total
                FROM (
                    SELECT municipio, edad, COUNT(*) AS total
                    FROM eval_adolescentes
                    GROUP BY municipio, edad
                ) t2
                GROUP BY municipio
            ) t3
            ON t1.municipio = t3.municipio AND t1.total = t3.max_total
            ORDER BY t1.municipio";

    $query = $usuario->acceso->prepare($sql);
    $query->execute();
    $resultados = $query->fetchAll(PDO::FETCH_OBJ);

    $json = array();
    foreach ($resultados as $resultado) {
        $json[] = array(
            'municipio' => $resultado->municipio,
            'edad' => $resultado->edad,
            'total' => $resultado->total
        );
    }
    echo json_encode($json);
    exit;
}


?>
