<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Oferta {
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = 'SELECT o.id, o.nombre, o.descripcion, o.comienzo, o.fin, o.descuento,
                       p.nombre AS producto_nombre, lo.cantidad
                FROM ofertas o
                LEFT JOIN lineas_oferta lo ON lo.id_oferta = o.id
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

    public static function buscaPorId(int $id): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        // 1. Obtener los datos principales de la oferta
        $sql = 'SELECT id, nombre, descripcion, comienzo, fin, descuento
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

        // 2. Obtener las líneas asociadas (incluyendo la cantidad)
        $fila['lineas'] = [];
        $sqlLineas = 'SELECT p.id AS idProd, p.nombre, lo.cantidad 
                    FROM lineas_oferta lo
                    INNER JOIN productos p ON p.id = lo.producto
                    WHERE lo.id_oferta = ?
                    ORDER BY p.nombre';
        
        // NOTA: Asegúrate de que la columna en lineas_oferta sea 'id_oferta' o la FK correspondiente
        $stmtLineas = mysqli_prepare($conn, $sqlLineas);
        if ($stmtLineas) {
            mysqli_stmt_bind_param($stmtLineas, 'i', $id);
            mysqli_stmt_execute($stmtLineas);
            $resLineas = mysqli_stmt_get_result($stmtLineas);

            if ($resLineas) {
                while ($linea = mysqli_fetch_assoc($resLineas)) {
                    $fila['lineas'][] = [
                        'idProd' => (int) $linea['idProd'],
                        'producto' => $linea['nombre'],
                        'cantidad' => (int) $linea['cantidad'], // Ahora la variable existe
                    ];
                }
            }
            mysqli_stmt_close($stmtLineas);
        }

        return $fila;
    }

    public static function borrar(int $id): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = 'DELETE FROM lineas_oferta WHERE id_oferta = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);


        $sql = 'DELETE FROM ofertas WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $result !== false;
    }
    public static function crear(string $nombre, string $descripcion, ?string $comienzo, ?string $fin, float $descuento, array $productos, array $cantidades): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        // 1. Insertar la oferta
        $sql = 'INSERT INTO ofertas (nombre, descripcion, comienzo, fin, descuento) VALUES (?, ?, ?, ?, ?)';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ssssd', $nombre, $descripcion, $comienzo, $fin, $descuento);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $idOferta = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // 2. Insertar las líneas de oferta
        $sqlLinea = 'INSERT INTO lineas_oferta (id_oferta, producto, cantidad) VALUES (?, ?, ?)';
        foreach ($productos as $index => $productoId) {
            if (empty($productoId)) {
                continue; // Saltar productos no seleccionados
            }

            $cantidad = isset($cantidades[$index]) && is_numeric($cantidades[$index]) && (int)$cantidades[$index] > 0 ? (int)$cantidades[$index] : 1;

            $stmtLinea = mysqli_prepare($conn, $sqlLinea);
            if (!$stmtLinea) {
                continue; // O manejar el error de otra forma
            }

            mysqli_stmt_bind_param($stmtLinea, 'iii', $idOferta, $productoId, $cantidad);
            mysqli_stmt_execute($stmtLinea);
            mysqli_stmt_close($stmtLinea);
        }

        return true;
    }

    public static function obtenerOfertasActivas(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $hoy = date('Y-m-d H:i:s');
        
        $sql = 'SELECT o.id, o.descuento 
                FROM ofertas o 
                WHERE (o.comienzo <= ? OR o.comienzo IS NULL) 
                AND (o.fin >= ? OR o.fin IS NULL)';
                
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $hoy, $hoy);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        $ofertas = [];
        while ($fila = mysqli_fetch_assoc($res)) {
            $ofertas[] = self::buscaPorId((int)$fila['id']);
        }
        return $ofertas;
    }

    public static function actualizar(int $id, string $nombre, string $descripcion, ?string $comienzo, ?string $fin, float $descuento, array $productos, array $cantidades): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = 'UPDATE ofertas SET nombre = ?, descripcion = ?, comienzo = ?, fin = ?, descuento = ? WHERE id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }


        return true;
    }
}

