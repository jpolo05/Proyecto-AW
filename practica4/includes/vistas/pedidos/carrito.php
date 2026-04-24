<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Pedido;
use es\ucm\fdi\aw\usuarios\Producto;
use es\ucm\fdi\aw\usuarios\Recompensa;
use es\ucm\fdi\aw\usuarios\Usuario;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');
$ofertasActivas = \es\ucm\fdi\aw\usuarios\Oferta::obtenerOfertasActivas();

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [
        'tipo' => 'Local',
        'items' => [],
        'ofertas' => [],
        'recompensas' => [],
    ];
}

if (!isset($_SESSION['carrito']['ofertas']) || !is_array($_SESSION['carrito']['ofertas'])) {
    $_SESSION['carrito']['ofertas'] = [];
}
if (!isset($_SESSION['carrito']['recompensas']) || !is_array($_SESSION['carrito']['recompensas'])) {
    $_SESSION['carrito']['recompensas'] = [];
}

$usuarioSesion = Usuario::buscaUsuario((string)($_SESSION['user'] ?? ''));
$bistroCoinsCliente = $usuarioSesion ? (int)$usuarioSesion->getBistroCoins() : 0;
$_SESSION['bistroCoins'] = $bistroCoinsCliente;

$recompensasDisponibles = Recompensa::listarConProducto(true);
$mapaRecompensas = [];
foreach ($recompensasDisponibles as $rec) {
    $mapaRecompensas[(int)($rec['id'] ?? 0)] = $rec;
}

$csrfToken = Auth::getCsrfToken();
$error = '';
$mensaje = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF invalido.';
    } else {
        $accion = $_POST['accion'] ?? '';
        $tipo = $_POST['tipo'] ?? ($_SESSION['carrito']['tipo'] ?? 'Local');
        if (!in_array($tipo, ['Local', 'Llevar'], true)) {
            $tipo = 'Local';
        }

        $cantidades = $_POST['cantidad'] ?? [];
        if (!is_array($cantidades)) {
            $cantidades = [];
        }
        $cantidadesRecompensa = $_POST['recompensa_cantidad'] ?? [];
        if (!is_array($cantidadesRecompensa)) {
            $cantidadesRecompensa = [];
        }

        $ofertaSeleccionada = (int)($_POST['oferta'] ?? 0);
        $ofertasNormalizadas = $ofertaSeleccionada > 0 ? [$ofertaSeleccionada] : [];

        $itemsNormalizados = [];
        foreach ($cantidades as $idProducto => $cantidad) {
            $id = (int)$idProducto;
            $cant = (int)$cantidad;
            if ($id > 0 && $cant > 0) {
                $itemsNormalizados[$id] = $cant;
            }
        }

        $recompensasNormalizadas = [];
        foreach ($cantidadesRecompensa as $idRecompensa => $cantidad) {
            $id = (int)$idRecompensa;
            $cant = (int)$cantidad;
            if ($id > 0 && $cant > 0 && isset($mapaRecompensas[$id])) {
                $recompensasNormalizadas[$id] = $cant;
            }
        }

        $coinsNecesarios = 0;
        foreach ($recompensasNormalizadas as $idRecompensa => $cantidadRec) {
            $coinsNecesarios += ((int)($mapaRecompensas[$idRecompensa]['bistroCoins'] ?? 0)) * $cantidadRec;
        }

        $_SESSION['carrito']['tipo'] = $tipo;

        if ($accion === 'vaciar') {
            $_SESSION['carrito']['items'] = [];
            $_SESSION['carrito']['ofertas'] = [];
            $_SESSION['carrito']['recompensas'] = [];
            header('Location: '.RUTA_APP.'includes/vistas/pedidos/carrito.php?msg=Carrito+vaciado');
            exit;
        }

        $_SESSION['carrito']['items'] = $itemsNormalizados;
        $_SESSION['carrito']['ofertas'] = $ofertasNormalizadas;
        $_SESSION['carrito']['recompensas'] = $recompensasNormalizadas;

        if ($accion === 'actualizar') {
            header('Location: '.RUTA_APP.'includes/vistas/pedidos/crearPedido.php?msg=Carrito+actualizado');
            exit;
        }

        if ($accion === 'finalizar') {
            if (empty($itemsNormalizados) && empty($recompensasNormalizadas)) {
                $error = 'El carrito esta vacio.';
            } elseif ($coinsNecesarios > $bistroCoinsCliente) {
                $error = 'No tienes BistroCoins suficientes para las recompensas seleccionadas.';
            } else {
                $lineas = [];
                foreach ($itemsNormalizados as $idProducto => $cantidad) {
                    $lineas[] = [
                        'idProducto' => (int)$idProducto,
                        'cantidad' => (int)$cantidad,
                    ];
                }

                $lineasRecompensa = [];
                foreach ($recompensasNormalizadas as $idRecompensa => $cantidad) {
                    $lineasRecompensa[] = [
                        'idRecompensa' => (int)$idRecompensa,
                        'cantidad' => (int)$cantidad,
                    ];
                }

                $cliente = $_SESSION['user'] ?? '';
                $numeroPedido = Pedido::crear($cliente, $tipo, $lineas, $ofertasNormalizadas, $lineasRecompensa);
                if ($numeroPedido !== null) {
                    $_SESSION['carrito'] = [
                        'tipo' => 'Local',
                        'items' => [],
                        'ofertas' => [],
                        'recompensas' => [],
                    ];
                    header('Location: '.RUTA_APP.'includes/vistas/pedidos/visualizarPedido.php?numeroPedido='.$numeroPedido);
                    exit;
                }

                $error = 'No se pudo finalizar el pedido.';
            }
        }
    }
}

$tipo = $_SESSION['carrito']['tipo'] ?? 'Local';
$itemsCarrito = is_array($_SESSION['carrito']['items'] ?? null) ? $_SESSION['carrito']['items'] : [];
$ofertasGuardadas = is_array($_SESSION['carrito']['ofertas'] ?? null) ? $_SESSION['carrito']['ofertas'] : [];
$recompensasGuardadas = is_array($_SESSION['carrito']['recompensas'] ?? null) ? $_SESSION['carrito']['recompensas'] : [];

$tituloPagina = 'Mi carrito';
$errorHtml = $error !== '' ? '<p class="carrito-texto-centrado"><strong>'.h($error).'</strong></p>' : '';
$mensajeHtml = $mensaje !== '' ? '<p class="carrito-texto-centrado"><strong>'.h($mensaje).'</strong></p>' : '';
$selLocal = ($tipo === 'Local') ? 'selected' : '';
$selLlevar = ($tipo === 'Llevar') ? 'selected' : '';

$filas = '';
$total = 0.0;
foreach ($itemsCarrito as $idProducto => $cantidad) {
    $producto = Producto::buscaPorId((int)$idProducto);
    if (!$producto) {
        continue;
    }

    $nombre = h((string)($producto['nombre'] ?? ''));
    $descripcion = h((string)($producto['descripcion'] ?? ''));
    $precioBase = (float)($producto['precio_base'] ?? 0);
    $iva = (int)($producto['iva'] ?? 0);
    $precioFinal = $precioBase + ($precioBase * $iva / 100);
    $subtotal = $precioFinal * (int)$cantidad;
    $total += $subtotal;
    $precioTexto = number_format($precioFinal, 2, '.', '');
    $subtotalTexto = number_format($subtotal, 2, '.', '');

    $filas .= '
    <tr>
        <td>'.$nombre.'</td>
        <td>'.$descripcion.'</td>
        <td>'.$precioTexto.' EUR</td>
        <td><input type="number" min="0" step="1" name="cantidad['.(int)$idProducto.']" value="'.(int)$cantidad.'" class="cantidad-carrito" data-precio="'.$precioTexto.'"></td>
        <td class="subtotal-linea">'.$subtotalTexto.' EUR</td>
    </tr>';
}

if ($filas === '') {
    $bloqueTabla = '<p>No hay productos normales en el carrito.</p>';
} else {
    $bloqueTabla = '
    <table>
        <tr>
            <th>Producto</th>
            <th>Descripcion</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        '.$filas.'
    </table>';
}

$bloqueRecompensasDisponibles = '<p>No hay recompensas disponibles actualmente.</p>';
$coinsNecesariosSeleccion = 0;
if (!empty($recompensasDisponibles)) {
    $filasRecompensas = '';
    foreach ($recompensasDisponibles as $recompensa) {
        $idRecompensa = (int)($recompensa['id'] ?? 0);
        $nombreProducto = h((string)($recompensa['nombre_producto'] ?? ''));
        $descripcionProducto = h((string)($recompensa['descripcion_producto'] ?? ''));
        $coins = (int)($recompensa['bistroCoins'] ?? 0);
        $cantidadSel = (int)($recompensasGuardadas[$idRecompensa] ?? 0);
        $aplicable = $coins > 0 && $bistroCoinsCliente >= $coins;
        $estadoAplicable = $aplicable ? 'Aplicable' : 'No aplicable';
        $coinsNecesariosSeleccion += $coins * max(0, $cantidadSel);

        $filasRecompensas .= '
        <tr>
            <td>'.$nombreProducto.'</td>
            <td>'.$descripcionProducto.'</td>
            <td>'.$coins.' BC</td>
            <td>'.$estadoAplicable.'</td>
            <td><input type="number" min="0" step="1" name="recompensa_cantidad['.$idRecompensa.']" value="'.$cantidadSel.'" class="cantidad-recompensa" data-coins="'.$coins.'"></td>
        </tr>';
    }

    $bloqueRecompensasDisponibles = '
    <table>
        <tr>
            <th>Producto recompensa</th>
            <th>Descripcion</th>
            <th>Coste</th>
            <th>Estado</th>
            <th>Cantidad</th>
        </tr>
        '.$filasRecompensas.'
    </table>';
}

$bloqueOfertasDisponibles = '<p>No hay ofertas activas disponibles actualmente.</p>';
if (!empty($ofertasActivas)) {
    $partesOfertas = [];
    foreach ($ofertasActivas as $oferta) {
        $esAplicable = true;
        foreach (($oferta['lineas'] ?? []) as $lineaOferta) {
            $idProd = (int)($lineaOferta['idProd'] ?? 0);
            $cantidadRequerida = (int)($lineaOferta['cantidad'] ?? 0);
            $cantidadCarrito = (int)($itemsCarrito[$idProd] ?? 0);
            if ($idProd <= 0 || $cantidadRequerida <= 0 || $cantidadCarrito < $cantidadRequerida) {
                $esAplicable = false;
                break;
            }
        }

        if (!$esAplicable) {
            continue;
        }

        $idOferta = (int)($oferta['id'] ?? 0);
        $nombreOferta = h((string)($oferta['nombre'] ?? ''));
        $descripcionOferta = h((string)($oferta['descripcion'] ?? ''));
        $descuentoOferta = number_format((float)($oferta['descuento'] ?? 0), 2, '.', '');
        $checked = in_array($idOferta, $ofertasGuardadas, true) ? 'checked' : '';

        $partesOfertas[] = "
        <p>
            <label>
                <input type='radio' name='oferta' value='{$idOferta}' class='oferta-disponible' {$checked}>
                <strong>{$nombreOferta}</strong> - {$descripcionOferta} ({$descuentoOferta}%)
            </label>
        </p>";
    }
    if (!empty($partesOfertas)) {
        $bloqueOfertasDisponibles = implode('', $partesOfertas);
    } else {
        $bloqueOfertasDisponibles = '<p>No hay ofertas aplicables a los productos seleccionados.</p>';
    }
}
$bloqueOfertasDisponibles = '<div class="carrito-ofertas-centro">'.$bloqueOfertasDisponibles.'</div>';

$totalTexto = number_format($total, 2, '.', '');
$ofertasJSONRaw = json_encode($ofertasActivas, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($ofertasJSONRaw === false) {
    $ofertasJSONRaw = '[]';
}
$ofertasJSON = htmlspecialchars($ofertasJSONRaw, ENT_QUOTES, 'UTF-8');

$rutaJsCarrito = dirname(__DIR__, 3).'/js/carrito.js';
$versionJsCarrito = @filemtime($rutaJsCarrito);
$urlJsCarrito = RUTA_JS.'carrito.js';
if ($versionJsCarrito !== false) {
    $urlJsCarrito .= '?v='.$versionJsCarrito;
}
$funcionesJS = "<script src='".h($urlJsCarrito)."'></script>";

$contenidoPrincipal = <<<EOS
<style>
    .carrito-centrado,
    .carrito-centrado p,
    .carrito-centrado h1,
    .carrito-centrado h2,
    .carrito-centrado h3,
    .carrito-centrado label {
        text-align: center;
    }

    .carrito-centrado table th,
    .carrito-centrado table td {
        text-align: center;
    }

    .carrito-centrado select#tipo {
        width: 320px;
        max-width: 100%;
        margin: 0 auto;
        display: block;
    }

    .carrito-centrado .coins-linea {
        white-space: nowrap;
    }

    .carrito-centrado .coins-linea strong {
        display: inline;
    }
</style>

<div class="seccion-titulo">
    <h1>Mi carrito</h1>
</div>

<div class="info-categoria carrito-centrado"> $errorHtml
    $mensajeHtml

    <input type="hidden" id="config-ofertas-json" value="$ofertasJSON">
    
    <form method="POST" class="form-estandar">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        
        <div class="campo-form">
            <label for="tipo"><p><strong>Tipo de pedido:</strong></p></label>
            <select name="tipo" id="tipo">
                <option value="Local" $selLocal>Para tomar aqui</option>
                <option value="Llevar" $selLlevar>Para llevar</option>
            </select>
        </div>

        <div class="campo-form">
            <p class="coins-linea"><strong>BistroCoins disponibles:</strong> {$bistroCoinsCliente} BC</p>
            <p class="coins-linea"><strong>BistroCoins seleccionados en recompensas:</strong> <span id="coinsSeleccionados">{$coinsNecesariosSeleccion}</span> BC</p>
        </div>

        <div class="tabla-carrito-contenedor">
            <h3>Productos normales</h3>
            $bloqueTabla
        </div>

        <div class="tabla-carrito-contenedor">
            <h3>Recompensas disponibles</h3>
            $bloqueRecompensasDisponibles
        </div>

        <div class="seccion-ofertas-carrito">
            <h3><i class="fas fa-tag"></i> Ofertas aplicables</h3>
            <div class="bloque-gris">
                $bloqueOfertasDisponibles
            </div>
        </div>

        <div id="contenedorOfertas" class="seccion-ofertas-carrito">
            <h3><i class="fas fa-check-circle"></i> Ofertas seleccionadas</h3>
            <ul id="listaOfertasAplicadas" class="lista-limpia"></ul>
        </div>

        <div class="resumen-totales">
            <p>Subtotal: <span id="totalCarrito">$totalTexto</span> EUR</p>
            <p class="total-destacado">Total con descuento: <span id="totalCarritoDescuento">0.00</span> EUR</p>
        </div>

        <div class="buttons-estandar">
            <button type="submit" name="accion" value="actualizar" class="button-estandar">Actualizar carrito</button>
            <button type="submit" name="accion" value="finalizar" class="button-estandar">Finalizar pedido</button>
            <button type="submit" name="accion" value="vaciar" class="button-estandar btn-peligro">Vaciar carrito</button>
        </div>
    </form>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
