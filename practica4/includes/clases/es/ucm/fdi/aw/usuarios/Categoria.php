<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Categoria {
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT id, nombre, descripcion, imagen FROM categorias ORDER BY id";
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

    public static function buscaPorId(int $id): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'SELECT id, nombre, descripcion, imagen FROM categorias WHERE id = ? LIMIT 1';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $fila = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        mysqli_free_result($res);

        return $fila ?: null;
    }

    public static function borrar(int $id): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'DELETE FROM categorias WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function actualizar(int $id, string $nombre, string $descripcion, ?string $imagen = null): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'UPDATE categorias SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        $imagen = ($imagen !== null && $imagen !== '') ? $imagen : null;
        mysqli_stmt_bind_param($stmt, 'sssi', $nombre, $descripcion, $imagen, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}

