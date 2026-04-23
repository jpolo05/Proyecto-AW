<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Recompensa {
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

    public static function buscaPorId(int $id): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'SELECT id, id_producto, bistroCoins FROM recompensas WHERE id = ? LIMIT 1';
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

    public static function crear(int $idProducto, int $bistroCoins): bool
    {
        if ($idProducto <= 0 || $bistroCoins <= 0) {
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'INSERT INTO recompensas (id_producto, bistroCoins) VALUES (?, ?)';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ii', $idProducto, $bistroCoins);
        $ok = mysqli_stmt_execute($stmt);
    public static function actualizar(int $id, int $idProducto, int $bistroCoins): bool
    {
        if ($id <= 0 || $idProducto <= 0 || $bistroCoins <= 0) {
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'UPDATE recompensas SET id_producto = ?, bistroCoins = ? WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'iii', $idProducto, $bistroCoins, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function borrar(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'DELETE FROM recompensas WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
