<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Producto {
    public static function listar(bool $soloOfertados = false): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = 'SELECT p.id, p.nombre, p.descripcion, p.id_categoria, p.precio_base, p.iva, p.disponible, p.ofertado, p.imagen, c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id';

        if ($soloOfertados) {
            $sql .= ' WHERE p.ofertado = 1 AND p.disponible = 1';
        }

        $sql .= ' ORDER BY p.id_categoria, p.nombre';
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

    public static function listarNombres(bool $soloOfertados = false): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = 'SELECT p.id, p.nombre, p.id_categoria, p.precio_base, p.iva
                FROM productos p';
        if ($soloOfertados) {
            $sql .= ' WHERE p.ofertado = 1 AND p.disponible = 1';
        }
        $sql .= ' ORDER BY p.id_categoria ASC, p.nombre ASC';

        $res = mysqli_query($conn, $sql);
        
        if (!$res) {
            return [];
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $out[] = [
                'id' => (int) $row['id'],
                'nombre' => $row['nombre'],
                'precio_base' => (float) $row['precio_base'],
                'iva' => (int) $row['iva']
            ];
        }

        mysqli_free_result($res);
        return $out;
    }

    public static function buscaPorId(int $id): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'SELECT p.id, p.nombre, p.descripcion, p.id_categoria, p.precio_base, p.iva, p.disponible, p.ofertado, p.imagen, c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                WHERE p.id = ? LIMIT 1';
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

    public static function crear(
        string $nombre,
        string $descripcion,
        ?int $idCategoria,
        float $precioBase,
        int $iva,
        bool $disponible,
        bool $ofertado,
        ?string $imagen = null
    ): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $usaCategoria = $idCategoria !== null && $idCategoria > 0;
        $sql = $usaCategoria
            ? 'INSERT INTO productos (nombre, descripcion, id_categoria, precio_base, iva, disponible, ofertado, imagen)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            : 'INSERT INTO productos (nombre, descripcion, id_categoria, precio_base, iva, disponible, ofertado, imagen)
               VALUES (?, ?, NULL, ?, ?, ?, ?, ?)';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        $disponibleInt = $disponible ? 1 : 0;
        $ofertadoInt = $ofertado ? 1 : 0;
        $imagen = ($imagen !== null && $imagen !== '') ? $imagen : null;

        if ($usaCategoria) {
            mysqli_stmt_bind_param(
                $stmt,
                'ssidiiis',
                $nombre,
                $descripcion,
                $idCategoria,
                $precioBase,
                $iva,
                $disponibleInt,
                $ofertadoInt,
                $imagen
            );
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                'ssdiiis',
                $nombre,
                $descripcion,
                $precioBase,
                $iva,
                $disponibleInt,
                $ofertadoInt,
                $imagen
            );
        }

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function actualizar(
        int $id,
        string $nombre,
        string $descripcion,
        ?int $idCategoria,
        float $precioBase,
        int $iva,
        bool $disponible,
        bool $ofertado,
        ?string $imagen = null
    ): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $usaCategoria = $idCategoria !== null && $idCategoria > 0;
        $sql = $usaCategoria
            ? 'UPDATE productos
               SET nombre = ?, descripcion = ?, id_categoria = ?, precio_base = ?, iva = ?, disponible = ?, ofertado = ?, imagen = ?
               WHERE id = ?'
            : 'UPDATE productos
               SET nombre = ?, descripcion = ?, id_categoria = NULL, precio_base = ?, iva = ?, disponible = ?, ofertado = ?, imagen = ?
               WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        $disponibleInt = $disponible ? 1 : 0;
        $ofertadoInt = $ofertado ? 1 : 0;
        $imagen = ($imagen !== null && $imagen !== '') ? $imagen : null;

        if ($usaCategoria) {
            mysqli_stmt_bind_param(
                $stmt,
                'ssidiiisi',
                $nombre,
                $descripcion,
                $idCategoria,
                $precioBase,
                $iva,
                $disponibleInt,
                $ofertadoInt,
                $imagen,
                $id
            );
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                'ssdiiisi',
                $nombre,
                $descripcion,
                $precioBase,
                $iva,
                $disponibleInt,
                $ofertadoInt,
                $imagen,
                $id
            );
        }

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function desofertar(int $id): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = 'UPDATE productos SET ofertado = 0 WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
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

    public static function listarPorCategoria(int $idCategoria, bool $soloOfertados = false): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = 'SELECT p.id, p.nombre, p.descripcion, p.id_categoria, p.precio_base, p.iva, p.disponible, p.ofertado, p.imagen, c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                WHERE p.id_categoria = ?';

        if ($soloOfertados) {
            $sql .= ' AND p.ofertado = 1 AND p.disponible = 1';
        }

        $sql .= ' ORDER BY p.id';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, 'i', $idCategoria);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (!$res) {
            mysqli_stmt_close($stmt);
            return [];
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $out[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $out;
    }
}

