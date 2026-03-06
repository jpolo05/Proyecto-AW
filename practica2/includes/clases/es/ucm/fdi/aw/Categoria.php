<?php
namespace es\ucm\fdi\aw;

class Categoria {
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT id, nombre, descripcion, imagen FROM categorias ORDER BY nombre";
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
}

