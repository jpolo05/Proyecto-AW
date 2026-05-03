<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Aplicacion; //Usa la clase Aplicacion

class Pedido
{
    //Estados posibles de un pedido
    public const ESTADO_NUEVO = 'Nuevo';
    public const ESTADO_RECIBIDO = 'Recibido';
    public const ESTADO_EN_PREPARACION = "En preparaci\xC3\xB3n";
    public const ESTADO_COCINANDO = 'Cocinando';
    public const ESTADO_LISTO_COCINA = 'Listo cocina';
    public const ESTADO_TERMINADO = 'Terminado';
    public const ESTADO_ENTREGADO = 'Entregado';
    public const ESTADO_CANCELADO = 'Cancelado';

    private const ESTADOS_VALIDOS = [ //Lista de estados permitidos
        self::ESTADO_NUEVO,
        self::ESTADO_RECIBIDO,
        self::ESTADO_EN_PREPARACION,
        self::ESTADO_COCINANDO,
        self::ESTADO_LISTO_COCINA,
        self::ESTADO_TERMINADO,
        self::ESTADO_ENTREGADO,
        self::ESTADO_CANCELADO,
    ];
    //Calcula el siguiente numero visible de pedido
    private static function siguienteNumeroPedido(): int
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "SELECT COALESCE(MAX(numeroPedido), 0) + 1 AS siguiente FROM pedidos"; //Busca el siguiente numero
        $res = mysqli_query($conn, $sql); //Ejecuta consulta
        if (!$res) { //Si falla empieza en 1
            return 1;
        }

        $fila = mysqli_fetch_assoc($res); //Recoge resultado
        mysqli_free_result($res); //Libera resultado
        return (int)($fila['siguiente'] ?? 1); //Devuelve siguiente numero
    }

    //Calcula el siguiente id interno del pedido
    private static function siguienteIdInterno(): int
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 AS siguiente FROM pedidos"; //Busca el siguiente id
        $res = mysqli_query($conn, $sql); //Ejecuta consulta
        if (!$res) { //Si falla empieza en 1
            return 1;
        }

        $fila = mysqli_fetch_assoc($res); //Recoge resultado
        mysqli_free_result($res); //Libera resultado
        return (int)($fila['siguiente'] ?? 1); //Devuelve siguiente id
    }

    //Obtiene el id interno a partir del numero visible
    private static function idInternoDesdeNumero(int $numeroPedido): ?int
    {
        if ($numeroPedido <= 0) { //Si el numero no es valido
            return null;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "SELECT id FROM pedidos WHERE numeroPedido = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta
        if (!$stmt) { //Si falla
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $numeroPedido); //Asocia numero
        mysqli_stmt_execute($stmt); //Ejecuta consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene resultado
        $fila = $res ? mysqli_fetch_assoc($res) : null; //Recoge fila
        mysqli_stmt_close($stmt); //Cierra statement
        mysqli_free_result($res); //Libera resultado

        return $fila ? (int)$fila['id'] : null; //Devuelve id interno
    }

    //Calcula el descuento total de las ofertas seleccionadas
    private static function calcularDescuentoOfertas(array $lineas, array $ofertasSeleccionadas): float
    {
        if (empty($lineas) || empty($ofertasSeleccionadas)) { //Si no hay lineas u ofertas no hay descuento
            return 0.0;
        }

        $cantidadesActuales = []; //Mapa idProducto => cantidad
        $preciosUnitarios = []; //Mapa idProducto => precio con IVA
        foreach ($lineas as $linea) { //Recorre lineas normales del pedido
            $idProducto = (int)($linea['idProducto'] ?? 0); //Producto de la linea
            $cantidad = (int)($linea['cantidad'] ?? 0); //Cantidad de la linea
            if ($idProducto <= 0 || $cantidad <= 0) { //Si no es valida, la salta
                continue;
            }

            $cantidadesActuales[$idProducto] = $cantidad; //Guarda cantidad actual

            $producto = Producto::buscaPorId($idProducto); //Busca producto para calcular precio
            if ($producto) { //Si existe
                $precioBase = (float)($producto['precio_base'] ?? 0);
                $iva = (int)($producto['iva'] ?? 0);
                $preciosUnitarios[$idProducto] = $precioBase + ($precioBase * $iva / 100); //Guarda precio con IVA
            }
        }

        $ofertasActivas = Oferta::obtenerOfertasActivas(); //Obtiene ofertas activas
        $ofertasSeleccionadas = array_values(array_unique(array_map('intval', $ofertasSeleccionadas))); //Normaliza ids
        if (count($ofertasSeleccionadas) > 1) { //Solo permite aplicar una oferta
            $ofertasSeleccionadas = [(int)$ofertasSeleccionadas[0]];
        }
        $descuentoTotal = 0.0; //Acumula descuento

        foreach ($ofertasActivas as $oferta) { //Recorre ofertas activas
            $idOferta = (int)($oferta['id'] ?? 0); //Id de la oferta
            if (!in_array($idOferta, $ofertasSeleccionadas, true)) { //Si no esta seleccionada, salta
                continue;
            }

            $maxAplicacionesPack = PHP_INT_MAX; //Veces maximas que se puede aplicar
            $precioBasePack = 0.0; //Precio del pack sin descuento
            $cumpleOferta = true; //Indica si cumple requisitos

            foreach (($oferta['lineas'] ?? []) as $lineaOferta) { //Recorre requisitos de la oferta
                $idProd = (int)($lineaOferta['idProd'] ?? 0); //Producto requerido
                $cantReq = (int)($lineaOferta['cantidad'] ?? 0); //Cantidad requerida

                if ($idProd <= 0 || $cantReq <= 0 || !isset($cantidadesActuales[$idProd]) || $cantidadesActuales[$idProd] < $cantReq) { //Si no cumple requisitos
                    $cumpleOferta = false;
                    break;
                }

                $veces = (int) floor($cantidadesActuales[$idProd] / $cantReq); //Veces posibles para esa linea
                if ($veces < $maxAplicacionesPack) { //Se queda con el minimo de todas
                    $maxAplicacionesPack = $veces;
                }

                $precioBasePack += ((float)($preciosUnitarios[$idProd] ?? 0)) * $cantReq; //Suma precio del pack
            }

            if ($cumpleOferta && $maxAplicacionesPack > 0 && $maxAplicacionesPack !== PHP_INT_MAX) { //Si la oferta aplica
                $ahorroPorPack = $precioBasePack * ((float)($oferta['descuento'] ?? 0) / 100); //Ahorro de un pack
                $descuentoTotal += round($ahorroPorPack * $maxAplicacionesPack, 2); //Suma ahorro total
            }
        }

        return round($descuentoTotal, 2); //Devuelve descuento redondeado
    }

    public static function crear(
        string $cliente,
        string $tipo,
        array $lineas,
        array $ofertasSeleccionadas = [],
        array $lineasRecompensa = []
    ): ?int
    {
        if ($cliente === '' || !in_array($tipo, ['Local', 'Llevar'], true)) { //Comprueba cliente y tipo
            return null;
        }
        if (empty($lineas) && empty($lineasRecompensa)) { //Debe haber productos o recompensas
            return null;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        mysqli_begin_transaction($conn); //Inicia transaccion

        try {
            $idPedido = self::siguienteIdInterno(); //Genera id interno
            $numeroPedido = self::siguienteNumeroPedido(); //Genera numero visible

            //Inserta tambien el id interno por si AUTO_INCREMENT no esta configurado
            $sqlPedido = "INSERT INTO pedidos (id, numeroPedido, estado, tipo, fecha, cliente, bistroCoinsGastados, total) VALUES (?, ?, ?, ?, NOW(), ?, 0, 0)";
            $stmtPedido = mysqli_prepare($conn, $sqlPedido); //Prepara insercion del pedido
            if (!$stmtPedido) { //Si falla
                mysqli_rollback($conn); //Cancela transaccion
                return null;
            }

            $estadoInicial = self::ESTADO_RECIBIDO; //Estado inicial del pedido
            mysqli_stmt_bind_param($stmtPedido, "iisss", $idPedido, $numeroPedido, $estadoInicial, $tipo, $cliente); //Asocia datos
            $okPedido = mysqli_stmt_execute($stmtPedido); //Ejecuta insercion
            mysqli_stmt_close($stmtPedido); //Cierra statement

            if (!$okPedido || $idPedido <= 0) { //Si no se inserta correctamente
                mysqli_rollback($conn); //Cancela transaccion
                return null;
            }

            $total = 0.0; //Total del pedido
            $bistroCoinsGastadosPedido = 0; //Coins gastados en recompensas
            $lineasInsertadas = 0; //Contador de lineas insertadas

            $sqlLinea = "INSERT INTO linea_pedido (numeroPedido, idProducto, esRecompensa, cantidad, subtotal, bistroCoinsGastados, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtLinea = mysqli_prepare($conn, $sqlLinea); //Prepara insercion de lineas
            if (!$stmtLinea) { //Si falla
                mysqli_rollback($conn); //Cancela transaccion
                return null;
            }

            foreach ($lineas as $linea) { //Recorre productos normales
                $idProducto = (int)($linea['idProducto'] ?? 0); //Producto elegido
                $cantidad = (int)($linea['cantidad'] ?? 0); //Cantidad elegida
                if ($idProducto <= 0 || $cantidad <= 0) { //Si la linea no es valida
                    continue;
                }

                $producto = Producto::buscaPorId($idProducto); //Busca producto
                if (!$producto) { //Si no existe
                    mysqli_stmt_close($stmtLinea); //Cierra statement
                    mysqli_rollback($conn); //Cancela transaccion
                    return null;
                }

                $precioBase = (float)($producto['precio_base'] ?? 0); //Precio sin IVA
                $iva = (int)($producto['iva'] ?? 0); //IVA del producto
                $precioFinalUnitario = $precioBase + ($precioBase * $iva / 100); //Precio con IVA
                $subtotal = round($precioFinalUnitario * $cantidad, 2); //Subtotal de la linea
                $esRecompensa = 0; //Marca linea normal
                $bistroCoinsGastadosLinea = 0; //No gasta coins
                $estadoLinea = 0; //Linea pendiente

                mysqli_stmt_bind_param(
                    $stmtLinea,
                    "iiiidii",
                    $idPedido,
                    $idProducto,
                    $esRecompensa,
                    $cantidad,
                    $subtotal,
                    $bistroCoinsGastadosLinea,
                    $estadoLinea
                );
                $okLinea = mysqli_stmt_execute($stmtLinea); //Inserta linea
                if (!$okLinea) { //Si falla
                    mysqli_stmt_close($stmtLinea); //Cierra statement
                    mysqli_rollback($conn); //Cancela transaccion
                    return null;
                }

                $total += $subtotal; //Suma al total
                $lineasInsertadas++; //Aumenta contador
            }

            foreach ($lineasRecompensa as $lineaRecompensa) { //Recorre recompensas
                $idRecompensa = (int)($lineaRecompensa['idRecompensa'] ?? 0); //Recompensa elegida
                $cantidadRecompensa = (int)($lineaRecompensa['cantidad'] ?? 0); //Cantidad de recompensas
                if ($idRecompensa <= 0 || $cantidadRecompensa <= 0) { //Si la linea no es valida
                    continue;
                }

                $recompensa = Recompensa::buscaPorId($idRecompensa); //Busca recompensa
                if (!$recompensa) { //Si no existe
                    mysqli_stmt_close($stmtLinea); //Cierra statement
                    mysqli_rollback($conn); //Cancela transaccion
                    return null;
                }

                $idProductoRecompensa = (int)($recompensa['id_producto'] ?? 0); //Producto asociado a la recompensa
                $productoRecompensa = Producto::buscaPorId($idProductoRecompensa); //Comprueba producto
                if (!$productoRecompensa) { //Si no existe
                    mysqli_stmt_close($stmtLinea); //Cierra statement
                    mysqli_rollback($conn); //Cancela transaccion
                    return null;
                }

                $esRecompensa = 1; //Marca linea de recompensa
                $subtotal = 0.0; //Las recompensas no suman euros
                $bistroCoinsUnitarias = (int)($recompensa['bistroCoins'] ?? 0); //Coste unitario en coins
                $bistroCoinsGastadosLinea = $bistroCoinsUnitarias * $cantidadRecompensa; //Coins de la linea
                $estadoLinea = 0; //Linea pendiente

                mysqli_stmt_bind_param(
                    $stmtLinea,
                    "iiiidii",
                    $idPedido,
                    $idProductoRecompensa,
                    $esRecompensa,
                    $cantidadRecompensa,
                    $subtotal,
                    $bistroCoinsGastadosLinea,
                    $estadoLinea
                );
                $okLinea = mysqli_stmt_execute($stmtLinea); //Inserta linea
                if (!$okLinea) { //Si falla
                    mysqli_stmt_close($stmtLinea); //Cierra statement
                    mysqli_rollback($conn); //Cancela transaccion
                    return null;
                }

                $bistroCoinsGastadosPedido += $bistroCoinsGastadosLinea; //Suma coins gastados
                $lineasInsertadas++; //Aumenta contador
            }

            mysqli_stmt_close($stmtLinea); //Cierra statement de lineas

            if ($lineasInsertadas === 0) { //Si no se inserto ninguna linea
                mysqli_rollback($conn); //Cancela transaccion
                return null;
            }

            $usuario = Usuario::buscaUsuario($cliente); //Busca usuario del pedido
            if (!$usuario || $usuario->getBistroCoins() < $bistroCoinsGastadosPedido) { //Comprueba coins disponibles
                mysqli_rollback($conn); //Cancela transaccion
                return null;
            }

            //Consume BistroCoins al finalizar el pedido en carrito
            if ($bistroCoinsGastadosPedido > 0) { //Si se han usado recompensas
                $sqlDetrae = "UPDATE usuarios
                              SET bistroCoins = bistroCoins - ?
                              WHERE user = ? AND bistroCoins >= ?";
                $stmtDetrae = mysqli_prepare($conn, $sqlDetrae); //Prepara resta de coins
                if (!$stmtDetrae) { //Si falla
                    mysqli_rollback($conn); //Cancela transaccion
                    return null;
                }

                mysqli_stmt_bind_param($stmtDetrae, "isi", $bistroCoinsGastadosPedido, $cliente, $bistroCoinsGastadosPedido); //Asocia datos
                $okDetrae = mysqli_stmt_execute($stmtDetrae); //Resta coins
                $detraidos = $okDetrae ? mysqli_stmt_affected_rows($stmtDetrae) : 0; //Filas modificadas
                mysqli_stmt_close($stmtDetrae); //Cierra statement

                if (!$okDetrae || $detraidos < 1) { //Si no se restan coins
                    mysqli_rollback($conn); //Cancela transaccion
                    return null;
                }
            }

            $descuentoTotal = self::calcularDescuentoOfertas($lineas, $ofertasSeleccionadas); //Calcula descuentos
            $total = max(0, round($total - $descuentoTotal, 2)); //Aplica descuento sin bajar de cero

            $sqlTotal = "UPDATE pedidos SET total = ?, bistroCoinsGastados = ? WHERE id = ?";
            $stmtTotal = mysqli_prepare($conn, $sqlTotal); //Prepara actualizacion del total
            if (!$stmtTotal) { //Si falla
                mysqli_rollback($conn); //Cancela transaccion
                return null;
            }

            mysqli_stmt_bind_param($stmtTotal, "dii", $total, $bistroCoinsGastadosPedido, $idPedido); //Asocia total
            $okTotal = mysqli_stmt_execute($stmtTotal); //Actualiza pedido
            mysqli_stmt_close($stmtTotal); //Cierra statement

            if (!$okTotal) { //Si falla
                mysqli_rollback($conn); //Cancela transaccion
                return null;
            }

            mysqli_commit($conn); //Confirma transaccion
            return $numeroPedido; //Devuelve numero visible
        } catch (\Throwable $e) { //Si ocurre un error
            mysqli_rollback($conn); //Cancela transaccion
            error_log('Error al crear pedido: '.$e->getMessage()); //Guarda error
            return null;
        }
    }

    public static function listar(): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "SELECT id, numeroPedido, estado, tipo, fecha, cliente, cocinero, imagenCocinero, bistroCoinsGastados, total FROM pedidos ORDER BY numeroPedido ASC";
        $res = mysqli_query($conn, $sql); //Ejecuta consulta

        if (!$res) { //Si falla
            return [];
        }

        $out = []; //Array de pedidos
        while ($row = mysqli_fetch_assoc($res)) { //Recorre pedidos
            $out[] = $row; //Añade pedido
        }

        mysqli_free_result($res); //Libera resultado
        return $out; //Devuelve pedidos
    }

    public static function buscaPorNumero(int $numeroPedido): ?array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "SELECT id, numeroPedido, estado, tipo, fecha, cliente, cocinero, imagenCocinero, bistroCoinsGastados, total
                FROM pedidos
                WHERE numeroPedido = ?
                LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta
        if (!$stmt) { //Si falla
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $numeroPedido); //Asocia numero
        mysqli_stmt_execute($stmt); //Ejecuta consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene resultado
        $fila = $res ? mysqli_fetch_assoc($res) : null; //Recoge pedido
        mysqli_stmt_close($stmt); //Cierra statement
        mysqli_free_result($res); //Libera resultado

        return $fila ?: null; //Devuelve pedido o null
    }

    public static function buscaPorNumeroYCliente(int $numeroPedido, string $cliente): ?array
    {
        if ($numeroPedido <= 0 || $cliente === '') { //Comprueba datos
            return null;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "SELECT id, numeroPedido, estado, tipo, fecha, cliente, cocinero, imagenCocinero, bistroCoinsGastados, total
                FROM pedidos
                WHERE numeroPedido = ? AND cliente = ?
                LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta
        if (!$stmt) { //Si falla
            return null;
        }

        mysqli_stmt_bind_param($stmt, "is", $numeroPedido, $cliente); //Asocia numero y cliente
        mysqli_stmt_execute($stmt); //Ejecuta consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene resultado
        $fila = $res ? mysqli_fetch_assoc($res) : null; //Recoge pedido
        mysqli_stmt_close($stmt); //Cierra statement
        mysqli_free_result($res); //Libera resultado

        return $fila ?: null; //Devuelve pedido o null
    }

    public static function listarDetalle($numeroPedido): array
    {
        $numeroPedido = (int)$numeroPedido; //Convierte numero
        $idPedido = self::idInternoDesdeNumero($numeroPedido); //Obtiene id interno
        if ($idPedido === null) { //Si no existe
            return [];
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD

        $sql = "SELECT numeroPedido, idProducto, esRecompensa, cantidad, subtotal, bistroCoinsGastados, estado FROM linea_pedido WHERE numeroPedido = ?";
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta

        if (!$stmt) { //Si falla
            return [];
        }

        mysqli_stmt_bind_param($stmt, "i", $idPedido); //Asocia id interno
        mysqli_stmt_execute($stmt); //Ejecuta consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene resultado

        $out = []; //Array de lineas
        while ($row = mysqli_fetch_assoc($res)) { //Recorre lineas
            $row['numeroPedido'] = $numeroPedido; //Muestra numero visible
            $row['esRecompensa'] = (int)($row['esRecompensa'] ?? 0); //Convierte marca de recompensa
            $row['bistroCoinsGastados'] = (int)($row['bistroCoinsGastados'] ?? 0); //Convierte coins
            $row['producto'] = Producto::nombre($row['idProducto']); //Añade nombre del producto
            $out[] = $row; //Añade linea
        }

        mysqli_stmt_close($stmt); //Cierra statement
        mysqli_free_result($res); //Libera resultado
        return $out; //Devuelve lineas
    }

    public static function listar_cliente($cliente): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "SELECT numeroPedido, estado, tipo, fecha, bistroCoinsGastados, total FROM pedidos WHERE cliente = ? ORDER BY numeroPedido ASC";
        $stmt = mysqli_prepare($conn, $sql); //Prepara consulta

        if (!$stmt) { //Si falla
            return [];
        }

        mysqli_stmt_bind_param($stmt, "s", $cliente); //Asocia cliente
        mysqli_stmt_execute($stmt); //Ejecuta consulta
        $res = mysqli_stmt_get_result($stmt); //Obtiene resultado

        if (!$res) { //Si falla
            mysqli_stmt_close($stmt); //Cierra statement
            return [];
        }

        $out = []; //Array de pedidos
        while ($row = mysqli_fetch_assoc($res)) { //Recorre pedidos
            $out[] = $row; //Añade pedido
        }

        mysqli_stmt_close($stmt); //Cierra statement
        mysqli_free_result($res); //Libera resultado
        return $out; //Devuelve pedidos del cliente
    }

    public static function clientePuedeCancelarEstado(string $estado): bool
    {
        return in_array($estado, [ //Estados que el cliente puede cancelar
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
        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD

        if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS, true) || $numeroPedido <= 0) { //Comprueba estado y pedido
            return false;
        }

        if ($nuevoEstado === self::ESTADO_COCINANDO) { //Si se asigna a cocina
            if ($cocinero === null || $cocinero === '') { //Debe tener cocinero
                return false;
            }
            if ($imagenCocinero === null || $imagenCocinero === '') { //Si no tiene imagen
                $imagenCocinero = 'img/uploads/usuarios/default.jpg'; //Usa imagen por defecto
            }
            $sql = "UPDATE pedidos SET estado = ?, cocinero = ?, imagenCocinero = ? WHERE numeroPedido = ?";
        } else {
            $sql = "UPDATE pedidos SET estado = ? WHERE numeroPedido = ?";
        }
        $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion

        if (!$stmt) { //Si falla
            return false;
        }

        if ($nuevoEstado === self::ESTADO_COCINANDO) { //Asocia datos de cocina
            mysqli_stmt_bind_param($stmt, "sssi", $nuevoEstado, $cocinero, $imagenCocinero, $numeroPedido);
        } else {
            mysqli_stmt_bind_param($stmt, "si", $nuevoEstado, $numeroPedido); //Asocia estado
        }
        $ok = mysqli_stmt_execute($stmt); //Ejecuta actualizacion
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }

    public static function actualizarEstadoSi(
        int $numeroPedido,
        string $estadoActual,
        string $nuevoEstado,
        ?string $cocinero = null,
        ?string $imagenCocinero = null
    ): bool {
        if ($numeroPedido <= 0 || !in_array($estadoActual, self::ESTADOS_VALIDOS, true)) { //Comprueba pedido y estado actual
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS, true)) { //Comprueba nuevo estado
            return false;
        }

        if ($nuevoEstado === self::ESTADO_COCINANDO) { //Si se asigna a cocina
            if ($cocinero === null || $cocinero === '') { //Debe tener cocinero
                return false;
            }
            if ($imagenCocinero === null || $imagenCocinero === '') { //Si no tiene imagen
                $imagenCocinero = 'img/uploads/usuarios/default.jpg'; //Usa imagen por defecto
            }

            $sql = "UPDATE pedidos
                    SET estado = ?, cocinero = ?, imagenCocinero = ?
                    WHERE numeroPedido = ? AND estado = ?";
            $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion
            if (!$stmt) { //Si falla
                return false;
            }

            mysqli_stmt_bind_param($stmt, "sssis", $nuevoEstado, $cocinero, $imagenCocinero, $numeroPedido, $estadoActual); //Asocia datos
        } else {
            $sql = "UPDATE pedidos
                    SET estado = ?
                    WHERE numeroPedido = ? AND estado = ?";
            $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion
            if (!$stmt) { //Si falla
                return false;
            }

            mysqli_stmt_bind_param($stmt, "sis", $nuevoEstado, $numeroPedido, $estadoActual); //Asocia estados
        }

        $ok = mysqli_stmt_execute($stmt); //Ejecuta actualizacion
        if ($ok) { //Si no falla la consulta
            $ok = mysqli_stmt_affected_rows($stmt) > 0; //Comprueba que cambio alguna fila
        }
        mysqli_stmt_close($stmt); //Cierra statement

        return $ok; //Devuelve resultado
    }

    public static function cobrarYEnviarACocina(int $numeroPedido): bool
    {
        if ($numeroPedido <= 0) { //Comprueba numero
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        mysqli_begin_transaction($conn); //Inicia transaccion

        try {
            $sqlPedido = "SELECT numeroPedido, estado, cliente, total, bistroCoinsGastados
                          FROM pedidos
                          WHERE numeroPedido = ?
                          LIMIT 1
                          FOR UPDATE";
            $stmtPedido = mysqli_prepare($conn, $sqlPedido); //Prepara consulta bloqueante
            if (!$stmtPedido) { //Si falla
                mysqli_rollback($conn); //Cancela transaccion
                return false;
            }

            mysqli_stmt_bind_param($stmtPedido, "i", $numeroPedido); //Asocia numero
            mysqli_stmt_execute($stmtPedido); //Ejecuta consulta
            $resPedido = mysqli_stmt_get_result($stmtPedido); //Obtiene resultado
            $pedido = $resPedido ? mysqli_fetch_assoc($resPedido) : null; //Recoge pedido
            mysqli_stmt_close($stmtPedido); //Cierra statement
            mysqli_free_result($resPedido); //Libera resultado

            if (!$pedido || (string)($pedido['estado'] ?? '') !== self::ESTADO_RECIBIDO) { //Debe estar recibido
                mysqli_rollback($conn); //Cancela transaccion
                return false;
            }

            $cliente = (string)($pedido['cliente'] ?? ''); //Cliente del pedido
            $totalPagado = (float)($pedido['total'] ?? 0); //Total pagado
            $bistroCoinsGastados = (int)($pedido['bistroCoinsGastados'] ?? 0); //Coins gastados
            $bistroCoinsGanados = (int) floor(max(0, $totalPagado)); //Coins ganados

            if ($cliente === '') { //Si no hay cliente valido
                mysqli_rollback($conn); //Cancela transaccion
                return false;
            }

            if ($bistroCoinsGanados > 0) { //Si gana coins
                $sqlSuma = "UPDATE usuarios SET bistroCoins = bistroCoins + ? WHERE user = ?";
                $stmtSuma = mysqli_prepare($conn, $sqlSuma); //Prepara suma de coins
                if (!$stmtSuma) { //Si falla
                    mysqli_rollback($conn); //Cancela transaccion
                    return false;
                }

                mysqli_stmt_bind_param($stmtSuma, "is", $bistroCoinsGanados, $cliente); //Asocia datos
                $okSuma = mysqli_stmt_execute($stmtSuma); //Suma coins
                mysqli_stmt_close($stmtSuma); //Cierra statement

                if (!$okSuma) { //Si falla
                    mysqli_rollback($conn); //Cancela transaccion
                    return false;
                }
            }

            $sqlEstado = "UPDATE pedidos
                          SET estado = ?
                          WHERE numeroPedido = ? AND estado = ?";
            $stmtEstado = mysqli_prepare($conn, $sqlEstado); //Prepara cambio de estado
            if (!$stmtEstado) { //Si falla
                mysqli_rollback($conn); //Cancela transaccion
                return false;
            }

            $nuevoEstado = self::ESTADO_EN_PREPARACION; //Estado al enviar a cocina
            $estadoActual = self::ESTADO_RECIBIDO; //Estado esperado
            mysqli_stmt_bind_param($stmtEstado, "sis", $nuevoEstado, $numeroPedido, $estadoActual); //Asocia estados
            $okEstado = mysqli_stmt_execute($stmtEstado); //Actualiza estado
            $actualizados = $okEstado ? mysqli_stmt_affected_rows($stmtEstado) : 0; //Filas modificadas
            mysqli_stmt_close($stmtEstado); //Cierra statement

            if (!$okEstado || $actualizados < 1) { //Si no cambio el estado
                mysqli_rollback($conn); //Cancela transaccion
                return false;
            }

            mysqli_commit($conn); //Confirma transaccion
            return true;
        } catch (\Throwable $e) { //Si ocurre un error
            mysqli_rollback($conn); //Cancela transaccion
            error_log('Error en cobro de pedido: '.$e->getMessage()); //Guarda error
            return false;
        }
    }

    public static function borrar(int $numeroPedido, ?string $cliente = null): bool
    {
        if ($numeroPedido <= 0) { //Comprueba numero
            return false;
        }

        $idPedido = self::idInternoDesdeNumero($numeroPedido); //Obtiene id interno
        if ($idPedido === null) { //Si no existe
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        mysqli_begin_transaction($conn); //Inicia transaccion

        try {
            $sqlLineas = "DELETE FROM linea_pedido WHERE numeroPedido = ?";
            $stmtLineas = mysqli_prepare($conn, $sqlLineas); //Prepara borrado de lineas
            if (!$stmtLineas) { //Si falla
                mysqli_rollback($conn); //Cancela transaccion
                return false;
            }
            mysqli_stmt_bind_param($stmtLineas, "i", $idPedido); //Asocia id interno
            mysqli_stmt_execute($stmtLineas); //Borra lineas
            mysqli_stmt_close($stmtLineas); //Cierra statement

            if ($cliente !== null && $cliente !== '') { //Si se limita al cliente
                $sqlPedido = "DELETE FROM pedidos WHERE id = ? AND cliente = ?";
                $stmtPedido = mysqli_prepare($conn, $sqlPedido); //Prepara borrado del pedido
                if (!$stmtPedido) { //Si falla
                    mysqli_rollback($conn); //Cancela transaccion
                    return false;
                }
                mysqli_stmt_bind_param($stmtPedido, "is", $idPedido, $cliente); //Asocia pedido y cliente
            } else {
                $sqlPedido = "DELETE FROM pedidos WHERE id = ?";
                $stmtPedido = mysqli_prepare($conn, $sqlPedido); //Prepara borrado del pedido
                if (!$stmtPedido) { //Si falla
                    mysqli_rollback($conn); //Cancela transaccion
                    return false;
                }
                mysqli_stmt_bind_param($stmtPedido, "i", $idPedido); //Asocia id interno
            }

            mysqli_stmt_execute($stmtPedido); //Borra pedido
            $borrados = mysqli_stmt_affected_rows($stmtPedido); //Filas borradas
            mysqli_stmt_close($stmtPedido); //Cierra statement

            if ($borrados < 1) { //Si no borra el pedido
                mysqli_rollback($conn); //Cancela transaccion
                return false;
            }

            mysqli_commit($conn); //Confirma transaccion
            return true;
        } catch (\Throwable $e) { //Si ocurre un error
            mysqli_rollback($conn); //Cancela transaccion
            return false;
        }
    }

    public static function actualizarEstadoLinea($numeroPedido, $idProducto): bool
    {
        $numeroPedido = (int)$numeroPedido; //Convierte numero
        $idProducto = (int)$idProducto; //Convierte producto
        if ($numeroPedido <= 0 || $idProducto <= 0) { //Comprueba datos
            return false;
        }
        $idPedido = self::idInternoDesdeNumero($numeroPedido); //Obtiene id interno
        if ($idPedido === null) { //Si no existe
            return false;
        }

        $conn = Aplicacion::getInstance()->getConexionBd(); //Obtiene conexion a la BD
        $sql = "UPDATE linea_pedido SET estado = 1 WHERE numeroPedido = ? AND idProducto = ?";
        $stmt = mysqli_prepare($conn, $sql); //Prepara actualizacion

        if (!$stmt) { //Si falla
            return false;
        }

        mysqli_stmt_bind_param($stmt, "ii", $idPedido, $idProducto); //Asocia pedido y producto
        $ok = mysqli_stmt_execute($stmt); //Marca linea como lista
        if ($ok) { //Si no falla la consulta
            $ok = mysqli_stmt_affected_rows($stmt) > 0; //Comprueba que cambio alguna fila
        }
        mysqli_stmt_close($stmt); //Cierra statement
        return $ok; //Devuelve resultado
    }
}
