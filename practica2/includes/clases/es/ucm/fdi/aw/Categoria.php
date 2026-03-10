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

    public static function crear(string $nombre, string $descripcion, ?string $imagen = null): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        $imagen = ($imagen !== null && $imagen !== '') ? $imagen : null;
        mysqli_stmt_bind_param($stmt, 'sss', $nombre, $descripcion, $imagen);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}

