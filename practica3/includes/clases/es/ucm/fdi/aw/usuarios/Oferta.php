<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Oferta {
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT id, nombre, descripcion, comienzo, fin, descuento FROM ofertas ORDER BY id";
        $sql2 = "SELECT id AS oferta_id, producto, cantidad FROM lineas_oferta";

        $res = mysqli_query($conn, $sql);
        $res2 = mysqli_query($conn, $sql2);
        
        if (!$res) {
            return [];
        }

        $lineasPorOferta = [];
        if ($res2) {
            while ($linea = mysqli_fetch_assoc($res2)) {
                $ofertaId = (int) $linea['oferta_id'];
                unset($linea['oferta_id']);
                $lineasPorOferta[$ofertaId][] = $linea;
            }
            mysqli_free_result($res2);
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['lineas'] = $lineasPorOferta[(int) $row['id']] ?? [];
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

