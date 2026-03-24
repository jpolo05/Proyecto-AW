<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Oferta {
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = 'SELECT o.id, o.nombre, o.descripcion, o.cantidad, o.comienzo, o.fin, o.descuento,
                       p.nombre AS producto_nombre
                FROM ofertas o
                LEFT JOIN lineas_oferta lo ON lo.id = o.id
                LEFT JOIN productos p ON p.id = lo.producto
                ORDER BY o.id, p.nombre';
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            return [];
        }

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $id = (int) $row['id'];
            if (!isset($out[$id])) {
                $out[$id] = [
                    'id' => $id,
                    'nombre' => $row['nombre'],
                    'descripcion' => $row['descripcion'],
                    'cantidad' => (int) ($row['cantidad'] ?? 1),
                    'comienzo' => $row['comienzo'],
                    'fin' => $row['fin'],
                    'descuento' => $row['descuento'],
                    'lineas' => [],
                ];
            }

            if (!empty($row['producto_nombre'])) {
                $out[$id]['lineas'][] = [
                    'producto' => $row['producto_nombre'],
                    'cantidad' => (int) ($row['cantidad'] ?? 1),
                ];
            }
        }

        mysqli_free_result($res);
        return array_values($out);
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
        $sql = 'SELECT id, nombre, descripcion, cantidad, comienzo, fin, descuento
                FROM ofertas
                WHERE id = ? LIMIT 1';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $fila = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        if (!$fila) {
            return null;
        }

        $cantidad = (int) ($fila['cantidad'] ?? 1);
        $sqlLineas = 'SELECT p.nombre
                      FROM lineas_oferta lo
                      INNER JOIN productos p ON p.id = lo.producto
                      WHERE lo.id = ?
                      ORDER BY p.nombre';
        $stmtLineas = mysqli_prepare($conn, $sqlLineas);
        if ($stmtLineas) {
            mysqli_stmt_bind_param($stmtLineas, 'i', $id);
            mysqli_stmt_execute($stmtLineas);
            $resLineas = mysqli_stmt_get_result($stmtLineas);
            $fila['lineas'] = [];

            if ($resLineas) {
                while ($linea = mysqli_fetch_assoc($resLineas)) {
                    $fila['lineas'][] = [
                        'producto' => $linea['nombre'],
                        'cantidad' => $cantidad,
                    ];
                }
            }

            mysqli_stmt_close($stmtLineas);
        } else {
            $fila['lineas'] = [];
        }

        return $fila ?: null;
    }

    public static function borrar(int $id): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sqlLineas = 'DELETE FROM lineas_oferta WHERE id = ?';
        $stmtLineas = mysqli_prepare($conn, $sqlLineas);
        if (!$stmtLineas) {
            return false;
        }

        mysqli_stmt_bind_param($stmtLineas, 'i', $id);
        $okLineas = mysqli_stmt_execute($stmtLineas);
        mysqli_stmt_close($stmtLineas);

        if (!$okLineas) {
            return false;
        }

        $sql = 'DELETE FROM ofertas WHERE id = ?';
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

