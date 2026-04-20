<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Recompensas {
    public static function listar(): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'SELECT r.id, r.id_producto, r.bistroCoins
                FROM recompensas r
                ORDER BY r.id ASC';
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