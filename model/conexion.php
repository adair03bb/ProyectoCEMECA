<?php
class conexion {
    private $servidor = "10.15.106.118";
    private $db = "sigemeca";
    private $puerto = "33060";
    private $charset = "utf8";
    private $usuario = "root";
    private $password = "AVSum585*";
    public $pdo = null;
    private $atributos = [
        PDO::ATTR_CASE => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ];

    public function __construct() {
        $this->pdo = new PDO(
            "mysql:dbname={$this->db};host={$this->servidor};port={$this->puerto};charset={$this->charset}",
            $this->usuario,
            $this->password,
            $this->atributos
        );
    }
}
?>