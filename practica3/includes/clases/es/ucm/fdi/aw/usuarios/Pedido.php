<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion;

class Pedido
{
    public const ESTADO_NUEVO = 'Nuevo';
    public const ESTADO_RECIBIDO = 'Recibido';
    public const ESTADO_EN_PREPARACION = "En preparaci\xC3\xB3n";
    public const ESTADO_COCINANDO = 'Cocinando';
    public const ESTADO_LISTO_COCINA = 'Listo cocina';
    public const ESTADO_TERMINADO = 'Terminado';
    public const ESTADO_ENTREGADO = 'Entregado';
    public const ESTADO_CANCELADO = 'Cancelado';

    private const ESTADOS_VALIDOS = [
        self::ESTADO_NUEVO,
        self::ESTADO_RECIBIDO,
        self::ESTADO_EN_PREPARACION,
        self::ESTADO_COCINANDO,
        self::ESTADO_LISTO_COCINA,
        self::ESTADO_TERMINADO,
        self::ESTADO_ENTREGADO,
        self::ESTADO_CANCELADO,
    ];
    private static function siguienteNumeroPedido(): int
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT COALESCE(MAX(numeroPedido), 0) + 1 AS siguiente FROM pedidos";
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            return 1;
        }

        $fila = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        return (int)($fila['siguiente'] ?? 1);
    }

    private static function siguienteIdInterno(): int
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 AS siguiente FROM pedidos";
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            return 1;
        }

        $fila = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        return (int)($fila['siguiente'] ?? 1);
    }

    private static function idInternoDesdeNumero(int $numeroPedido): ?int
    {
        if ($numeroPedido <= 0) {
            return null;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT id FROM pedidos WHERE numeroPedido = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $numeroPedido);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $fila = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        return $fila ? (int)$fila['id'] : null;
    }

    private static function calcularDescuentoOfertas(array $lineas, array $ofertasSeleccionadas): float
    {
        if (empty($lineas) || empty($ofertasSeleccionadas)) {
            return 0.0;
        }

        $cantidadesActuales = [];
        $preciosUnitarios = [];
        foreach ($lineas as $linea) {
            $idProducto = (int)($linea['idProducto'] ?? 0);
            $cantidad = (int)($linea['cantidad'] ?? 0);
            if ($idProducto <= 0 || $cantidad <= 0) {
                continue;
            }

            $cantidadesActuales[$idProducto] = $cantidad;

            $producto = Producto::buscaPorId($idProducto);
            if ($producto) {
                $precioBase = (float)($producto['precio_base'] ?? 0);
                $iva = (int)($producto['iva'] ?? 0);
                $preciosUnitarios[$idProducto] = $precioBase + ($precioBase * $iva / 100);
            }
        }

        $ofertasActivas = Oferta::obtenerOfertasActivas();
        $ofertasSeleccionadas = array_values(array_unique(array_map('intval', $ofertasSeleccionadas)));
        if (count($ofertasSeleccionadas) > 1) {
            $ofertasSeleccionadas = [(int)$ofertasSeleccionadas[0]];
        }
        $descuentoTotal = 0.0;

        foreach ($ofertasActivas as $oferta) {
            $idOferta = (int)($oferta['id'] ?? 0);
            if (!in_array($idOferta, $ofertasSeleccionadas, true)) {
                continue;
            }

            $maxAplicacionesPack = PHP_INT_MAX;
            $precioBasePack = 0.0;
            $cumpleOferta = true;

            foreach (($oferta['lineas'] ?? []) as $lineaOferta) {
                $idProd = (int)($lineaOferta['idProd'] ?? 0);
                $cantReq = (int)($lineaOferta['cantidad'] ?? 0);

                if ($idProd <= 0 || $cantReq <= 0 || !isset($cantidadesActuales[$idProd]) || $cantidadesActuales[$idProd] < $cantReq) {
                    $cumpleOferta = false;
                    break;
                }

                $veces = (int) floor($cantidadesActuales[$idProd] / $cantReq);
                if ($veces < $maxAplicacionesPack) {
                    $maxAplicacionesPack = $veces;
                }

                $precioBasePack += ((float)($preciosUnitarios[$idProd] ?? 0)) * $cantReq;
            }

            if ($cumpleOferta && $maxAplicacionesPack > 0 && $maxAplicacionesPack !== PHP_INT_MAX) {
                $ahorroPorPack = $precioBasePack * ((float)($oferta['descuento'] ?? 0) / 100);
                $descuentoTotal += round($ahorroPorPack * $maxAplicacionesPack, 2);
            }
        }

        return round($descuentoTotal, 2);
    }

    public static function crear(string $cliente, string $tipo, array $lineas, array $ofertasSeleccionadas = []): ?int
    {
        if ($cliente === '' || !in_array($tipo, ['Local', 'Llevar'], true) || empty($lineas)) {
            return null;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        mysqli_begin_transaction($conn);

        try {
            $idPedido = self::siguienteIdInterno();
            $numeroPedido = self::siguienteNumeroPedido();

            // Se inserta también el id interno para soportar instalaciones donde la tabla
            // pedidos todavía no tenga AUTO_INCREMENT configurado correctamente.
            $sqlPedido = "INSERT INTO pedidos (id, numeroPedido, estado, tipo, fecha, cliente, total) VALUES (?, ?, ?, ?, NOW(), ?, 0)";
            $stmtPedido = mysqli_prepare($conn, $sqlPedido);
            if (!$stmtPedido) {
                mysqli_rollback($conn);
                return null;
            }

            $estadoInicial = self::ESTADO_RECIBIDO;
            mysqli_stmt_bind_param($stmtPedido, "iisss", $idPedido, $numeroPedido, $estadoInicial, $tipo, $cliente);
            $okPedido = mysqli_stmt_execute($stmtPedido);
            mysqli_stmt_close($stmtPedido);

            if (!$okPedido || $idPedido <= 0) {
                mysqli_rollback($conn);
                return null;
            }

            $total = 0.0;
            $lineasInsertadas = 0;

            $sqlLinea = "INSERT INTO linea_pedido (numeroPedido, idProducto, cantidad, subtotal, estado) VALUES (?, ?, ?, ?, ?)";
            $stmtLinea = mysqli_prepare($conn, $sqlLinea);
            if (!$stmtLinea) {
                mysqli_rollback($conn);
                return null;
            }

            foreach ($lineas as $linea) {
                $idProducto = (int)($linea['idProducto'] ?? 0);
                $cantidad = (int)($linea['cantidad'] ?? 0);
                if ($idProducto <= 0 || $cantidad <= 0) {
                    continue;
                }

                $producto = Producto::buscaPorId($idProducto);
                if (!$producto) {
                    mysqli_stmt_close($stmtLinea);
                    mysqli_rollback($conn);
                    return null;
                }

                $precioBase = (float)($producto['precio_base'] ?? 0);
                $iva = (int)($producto['iva'] ?? 0);
                $precioFinalUnitario = $precioBase + ($precioBase * $iva / 100);
                $subtotal = round($precioFinalUnitario * $cantidad, 2);
                $estadoLinea = 0;

                mysqli_stmt_bind_param($stmtLinea, "iiidi", $idPedido, $idProducto, $cantidad, $subtotal, $estadoLinea);
                $okLinea = mysqli_stmt_execute($stmtLinea);
                if (!$okLinea) {
                    mysqli_stmt_close($stmtLinea);
                    mysqli_rollback($conn);
                    return null;
                }

                $total += $subtotal;
                $lineasInsertadas++;
            }

            mysqli_stmt_close($stmtLinea);

            if ($lineasInsertadas === 0) {
                mysqli_rollback($conn);
                return null;
            }

            $descuentoTotal = self::calcularDescuentoOfertas($lineas, $ofertasSeleccionadas);
            $total = max(0, round($total - $descuentoTotal, 2));

            $sqlTotal = "UPDATE pedidos SET total = ? WHERE id = ?";
            $stmtTotal = mysqli_prepare($conn, $sqlTotal);
            if (!$stmtTotal) {
                mysqli_rollback($conn);
                return null;
            }

            mysqli_stmt_bind_param($stmtTotal, "di", $total, $idPedido);
            $okTotal = mysqli_stmt_execute($stmtTotal);
            mysqli_stmt_close($stmtTotal);

            if (!$okTotal) {
                mysqli_rollback($conn);
                return null;
            }

            mysqli_commit($conn);
            return $numeroPedido;
        } catch (\Throwable $e) {
            mysqli_rollback($conn);
            error_log('Error al crear pedido: '.$e->getMessage());
            return null;
        }
    }

    public static function listar(): array
    {
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

    public static function buscaPorNumero(int $numeroPedido): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT id, numeroPedido, estado, tipo, fecha, cliente, cocinero, imagenCocinero, total
                FROM pedidos
                WHERE numeroPedido = ?
                LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $numeroPedido);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $fila = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        return $fila ?: null;
    }

    public static function buscaPorNumeroYCliente(int $numeroPedido, string $cliente): ?array
    {
        if ($numeroPedido <= 0 || $cliente === '') {
            return null;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT id, numeroPedido, estado, tipo, fecha, cliente, cocinero, imagenCocinero, total
                FROM pedidos
                WHERE numeroPedido = ? AND cliente = ?
                LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "is", $numeroPedido, $cliente);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $fila = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        return $fila ?: null;
    }

    public static function listarDetalle($numeroPedido): array
    {
        $numeroPedido = (int)$numeroPedido;
        $idPedido = self::idInternoDesdeNumero($numeroPedido);
        if ($idPedido === null) {
            return [];
        }

        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT numeroPedido, idProducto, cantidad, subtotal, estado FROM linea_pedido WHERE numeroPedido = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, "i", $idPedido);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['numeroPedido'] = $numeroPedido;
            $row['producto'] = Producto::nombre($row['idProducto']);
            $out[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $out;
    }

    public static function listar_cliente($cliente): array
    {
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

    public static function clientePuedeCancelarEstado(string $estado): bool
    {
        return in_array($estado, [
            self::ESTADO_NUEVO,
            self::ESTADO_RECIBIDO,
            self::ESTADO_EN_PREPARACION,
        ], true);
    }

    public static function actualizarEstado(
        int $numeroPedido,
        string $nuevoEstado,
        ?string $cocinero = null,
        ?string $imagenCocinero = null
    ): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();

        if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS, true) || $numeroPedido <= 0) {
            return false;
        }

        if ($nuevoEstado === self::ESTADO_COCINANDO) {
            if ($cocinero === null || $cocinero === '') {
                return false;
            }
            if ($imagenCocinero === null || $imagenCocinero === '') {
                $imagenCocinero = 'img/uploads/usuarios/default.jpg';
            }
            $sql = "UPDATE pedidos SET estado = ?, cocinero = ?, imagenCocinero = ? WHERE numeroPedido = ?";
        } else {
            $sql = "UPDATE pedidos SET estado = ? WHERE numeroPedido = ?";
        }
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return false;
        }

        if ($nuevoEstado === self::ESTADO_COCINANDO) {
            mysqli_stmt_bind_param($stmt, "sssi", $nuevoEstado, $cocinero, $imagenCocinero, $numeroPedido);
        } else {
            mysqli_stmt_bind_param($stmt, "si", $nuevoEstado, $numeroPedido);
        }
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public static function actualizarEstadoSi(
        int $numeroPedido,
        string $estadoActual,
        string $nuevoEstado,
        ?string $cocinero = null,
        ?string $imagenCocinero = null
    ): bool {
        if ($numeroPedido <= 0 || !in_array($estadoActual, self::ESTADOS_VALIDOS, true)) {
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS, true)) {
            return false;
        }

        if ($nuevoEstado === self::ESTADO_COCINANDO) {
            if ($cocinero === null || $cocinero === '') {
                return false;
            }
            if ($imagenCocinero === null || $imagenCocinero === '') {
                $imagenCocinero = 'img/uploads/usuarios/default.jpg';
            }

            $sql = "UPDATE pedidos
                    SET estado = ?, cocinero = ?, imagenCocinero = ?
                    WHERE numeroPedido = ? AND estado = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                return false;
            }

            mysqli_stmt_bind_param($stmt, "sssis", $nuevoEstado, $cocinero, $imagenCocinero, $numeroPedido, $estadoActual);
        } else {
            $sql = "UPDATE pedidos
                    SET estado = ?
                    WHERE numeroPedido = ? AND estado = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                return false;
            }

            mysqli_stmt_bind_param($stmt, "sis", $nuevoEstado, $numeroPedido, $estadoActual);
        }

        $ok = mysqli_stmt_execute($stmt);
        if ($ok) {
            $ok = mysqli_stmt_affected_rows($stmt) > 0;
        }
        mysqli_stmt_close($stmt);

        return $ok;
    }

    public static function borrar(int $numeroPedido, ?string $cliente = null): bool
    {
        if ($numeroPedido <= 0) {
            return false;
        }

        $idPedido = self::idInternoDesdeNumero($numeroPedido);
        if ($idPedido === null) {
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        mysqli_begin_transaction($conn);

        try {
            $sqlLineas = "DELETE FROM linea_pedido WHERE numeroPedido = ?";
            $stmtLineas = mysqli_prepare($conn, $sqlLineas);
            if (!$stmtLineas) {
                mysqli_rollback($conn);
                return false;
            }
            mysqli_stmt_bind_param($stmtLineas, "i", $idPedido);
            mysqli_stmt_execute($stmtLineas);
            mysqli_stmt_close($stmtLineas);

            if ($cliente !== null && $cliente !== '') {
                $sqlPedido = "DELETE FROM pedidos WHERE id = ? AND cliente = ?";
                $stmtPedido = mysqli_prepare($conn, $sqlPedido);
                if (!$stmtPedido) {
                    mysqli_rollback($conn);
                    return false;
                }
                mysqli_stmt_bind_param($stmtPedido, "is", $idPedido, $cliente);
            } else {
                $sqlPedido = "DELETE FROM pedidos WHERE id = ?";
                $stmtPedido = mysqli_prepare($conn, $sqlPedido);
                if (!$stmtPedido) {
                    mysqli_rollback($conn);
                    return false;
                }
                mysqli_stmt_bind_param($stmtPedido, "i", $idPedido);
            }

            mysqli_stmt_execute($stmtPedido);
            $borrados = mysqli_stmt_affected_rows($stmtPedido);
            mysqli_stmt_close($stmtPedido);

            if ($borrados < 1) {
                mysqli_rollback($conn);
                return false;
            }

            mysqli_commit($conn);
            return true;
        } catch (\Throwable $e) {
            mysqli_rollback($conn);
            return false;
        }
    }

    public static function actualizarEstadoLinea($numeroPedido, $idProducto): bool
    {
        $numeroPedido = (int)$numeroPedido;
        $idProducto = (int)$idProducto;
        if ($numeroPedido <= 0 || $idProducto <= 0) {
            return false;
        }
        $idPedido = self::idInternoDesdeNumero($numeroPedido);
        if ($idPedido === null) {
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "UPDATE linea_pedido SET estado = 1 WHERE numeroPedido = ? AND idProducto = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "ii", $idPedido, $idProducto);
        $ok = mysqli_stmt_execute($stmt);
        if ($ok) {
            $ok = mysqli_stmt_affected_rows($stmt) > 0;
        }
        mysqli_stmt_close($stmt);
        return $ok;
    }
}
