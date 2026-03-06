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

        $sql = "SELECT numeroPedido, idProducto, cantidad, subtotal FROM linea_pedido WHERE numeroPedido = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, "i", $numeroPedido);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['idProducto'] = Producto::nombre($row['idProducto']);
            $out[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $out;
    }

    public static function actualizarEstado($numeroPedido, $nuevoEstado, $cocinero): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "UPDATE pedidos SET estado = ?, cocinero = ? WHERE numeroPedido = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "ssi", $nuevoEstado, $cocinero, $numeroPedido);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}

