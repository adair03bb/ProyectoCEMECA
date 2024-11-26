<?php
require_once 'conexion.php';

class ReporteIndicadoresModel {
    private $pdo;

    public function __construct() {
        $conexion = new Conexion();
        $this->pdo = $conexion->pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilitar errores de PDO
    }
    public function obtenerDatosPorSubregion($fechaInicio, $fechaFin) {
        try {
            $sql = "
                SELECT subdireccion, tipo_atencion, tipo_riesgo
                FROM eval_adolescentes
                WHERE fecha_recepcion BETWEEN ? AND ?";
            
            $query = $this->pdo->prepare($sql);
            $query->execute([$fechaInicio, $fechaFin]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosPorSubregion: " . $e->getMessage());
            return [];
        }
    }
    public function obtenerVerificacionesPorSubregion($fechaInicio, $fechaFin) {
        try {
            $sql = "
                SELECT 
                    subdireccion,
                    tipo_verificacion,
                    COUNT(*) as total
                FROM eval_adolescentes
                WHERE fecha_recepcion BETWEEN ? AND ?
                AND tipo_verificacion IS NOT NULL
                GROUP BY subdireccion, tipo_verificacion";
            
            $query = $this->pdo->prepare($sql);
            $query->execute([$fechaInicio, $fechaFin]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            error_log("Error en obtenerVerificacionesPorSubregion: " . $e->getMessage());
            return [];
        }
    }
    public function obtenerDatosPorSubregionA($fechaInicio, $fechaFin) {
        try {
            $sql = "
                SELECT subdireccion, tipo_atencion, tipo_riesgo
                FROM eval_adultos
                WHERE fecha_recepcion BETWEEN ? AND ?";
            
            $query = $this->pdo->prepare($sql);
            $query->execute([$fechaInicio, $fechaFin]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosPorSubregion: " . $e->getMessage());
            return [];
        }
    }
    public function obtenerVerificacionesPorSubregionA($fechaInicio, $fechaFin) {
        try {
            $sql = "
                SELECT 
                    subdireccion,
                    tipo_verificacion,
                    COUNT(*) as total
                FROM eval_adultos
                WHERE fecha_recepcion BETWEEN ? AND ?
                AND tipo_verificacion IS NOT NULL
                GROUP BY subdireccion, tipo_verificacion";
            
            $query = $this->pdo->prepare($sql);
            $query->execute([$fechaInicio, $fechaFin]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            error_log("Error en obtenerVerificacionesPorSubregion: " . $e->getMessage());
            return [];
        }
    }
    public function obtenerDatosMedidas($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                        supervision,
                        seguimiento,
                        medida1, medida2, medida3, medida4, medida5, 
                        medida6, medida7, medida8, medida9, medida10,
                        medida11, medida12,
                        fecha_solicitud
                    FROM medidas_adolescentes
                    WHERE fecha_solicitud BETWEEN :fechaInicio AND :fechaFin";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':fechaInicio' => $fechaInicio,
                ':fechaFin' => $fechaFin
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosMedidas: " . $e->getMessage());
            throw $e;
        }
    }
    public function obtenerDatosCondiciones($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                        supervision,
                        seguimiento,
                        condicion1, condicion2, condicion3, condicion4, condicion5, 
                        condicion6, condicion7,
                        fecha_solicitud
                    FROM condiciones_adolescentes
                    WHERE fecha_solicitud BETWEEN :fechaInicio AND :fechaFin";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':fechaInicio' => $fechaInicio,
                ':fechaFin' => $fechaFin
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosCondiciones: " . $e->getMessage());
            throw $e;
        }
    }
    public function obtenerDatosColaboracionesMedidas($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                        supervision,
                        seguimiento,
                        medida1, medida2, medida3, medida4, medida5, 
                        medida6, medida7, medida8, medida9, medida10,
                        medida11, medida12,
                        fecha_solicitud
                    FROM colab_medidas_adolescentes
                    WHERE fecha_solicitud BETWEEN :fechaInicio AND :fechaFin";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':fechaInicio' => $fechaInicio,
                ':fechaFin' => $fechaFin
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosColaboracionesMedidas: " . $e->getMessage());
            throw $e;
        }
    }
    public function obtenerDatosColaboracionesCondiciones($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                        supervision,
                        seguimiento,
                        condicion1, condicion2, condicion3, condicion4, condicion5, 
                        condicion6, condicion7,
                        fecha_solicitud
                    FROM colab_condiciones_adolescentes
                    WHERE fecha_solicitud BETWEEN :fechaInicio AND :fechaFin";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':fechaInicio' => $fechaInicio,
                ':fechaFin' => $fechaFin
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosColaboracionesCondiciones: " . $e->getMessage());
            throw $e;
        }
    }
    public function obtenerDatosProcedenciaCC($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                        procedencia, 
                        COUNT(*) as cantidad,
                        SUM(CASE WHEN seguimiento = 'EN SEGUIMIENTO' THEN 1 ELSE 0 END) as en_seguimiento
                    FROM colab_condiciones_adolescentes
                    WHERE fecha_solicitud BETWEEN :fechaInicio AND :fechaFin
                    GROUP BY procedencia
                    ORDER BY cantidad DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':fechaInicio' => $fechaInicio,
                ':fechaFin' => $fechaFin
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosProcedenciaCC: " . $e->getMessage());
            throw $e;
        }
    }
    public function obtenerDatosProcedenciaCM($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                        procedencia, 
                        COUNT(*) as cantidad,
                        SUM(CASE WHEN seguimiento = 'EN SEGUIMIENTO' THEN 1 ELSE 0 END) as en_seguimiento
                    FROM colab_medidas_adolescentes
                    WHERE fecha_solicitud BETWEEN :fechaInicio AND :fechaFin
                    GROUP BY procedencia
                    ORDER BY cantidad DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':fechaInicio' => $fechaInicio,
                ':fechaFin' => $fechaFin
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosProcedenciaCM: " . $e->getMessage());
            throw $e;
        }
    }
}