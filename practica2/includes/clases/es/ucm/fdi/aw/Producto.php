<?php
namespace es\ucm\fdi\aw;

class Producto {
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT id, nombre, descripcion, precio_base FROM productos ORDER BY nombre";
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            return [];
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $out[] = $row;
        }

        mysqli_free_result($res);
        return $out;
    }

    public static function nombre($id): string {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT nombre FROM productos WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return "";
        }

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);
        $fila = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        return $fila['nombre'] ?? "";
    }
}

