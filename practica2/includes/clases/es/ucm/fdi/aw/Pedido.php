<?php
namespace es\ucm\fdi\aw;

class Pedido {
    public static function listar(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT id, numeroPedido, estado, tipo, fecha, cliente, cocinero, imagenCocinero, total FROM pedidos ORDER BY numeroPedido ASC";
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

    public static function listarDetalle($numeroPedido): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT numeroPedido, idProducto, cantidad, subtotal, estado FROM linea_pedido WHERE numeroPedido = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, "i", $numeroPedido);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['producto'] = Producto::nombre($row['idProducto']);
            $out[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $out;
    }

    public static function listar_cliente($cliente): array {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT numeroPedido, estado, tipo, fecha, total FROM pedidos WHERE cliente = ? ORDER BY numeroPedido ASC";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, "s", $cliente);
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

    public static function actualizarEstado($numeroPedido, $nuevoEstado): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();

        if($nuevoEstado === 'Cocinando') {
            $cocinero = $_SESSION['user'] ?? 'Desconocido';
            $imagenCocinero = $_SESSION['imagen'] ?? 'default.jpg';
            $sql = "UPDATE pedidos SET estado = ?, cocinero = ?, imagenCocinero = ? WHERE numeroPedido = ?";
        } else {
            $sql = "UPDATE pedidos SET estado = ? WHERE numeroPedido = ?";
        }
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return false;
        }

        if($nuevoEstado === 'Cocinando') {
            mysqli_stmt_bind_param($stmt, "sssi", $nuevoEstado, $cocinero, $imagenCocinero, $numeroPedido);
        } else {
            mysqli_stmt_bind_param($stmt, "si", $nuevoEstado, $numeroPedido);
        }
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function borrar($numeroPedido): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "DELETE FROM pedidos WHERE numeroPedido = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "i", $numeroPedido);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
    
    public static function actualizarEstadoLinea($numeroPedido, $idProducto): bool {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $sql = "UPDATE linea_pedido SET estado = 1 WHERE numeroPedido = ? AND idProducto = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $numeroPedido, $idProducto);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
    }
}

