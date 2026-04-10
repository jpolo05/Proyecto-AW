<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Pedido;
use es\ucm\fdi\aw\usuarios\Producto;

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
    ];
}

if (!isset($_SESSION['carrito']['ofertas']) || !is_array($_SESSION['carrito']['ofertas'])) {
    $_SESSION['carrito']['ofertas'] = [];
}

$csrfToken = Auth::getCsrfToken();
$error = '';
$mensaje = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF inválido.';
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

        $ofertaSeleccionada = (int)($_POST['oferta'] ?? 0);

        $itemsNormalizados = [];
        foreach ($cantidades as $idProducto => $cantidad) {
            $id = (int)$idProducto;
            $cant = (int)$cantidad;
            if ($id > 0 && $cant > 0) {
                $itemsNormalizados[$id] = $cant;
            }
        }

        $ofertasNormalizadas = $ofertaSeleccionada > 0 ? [$ofertaSeleccionada] : [];

        $_SESSION['carrito']['tipo'] = $tipo;

        if ($accion === 'vaciar') {
            $_SESSION['carrito']['items'] = [];
            $_SESSION['carrito']['ofertas'] = [];
            header('Location: '.RUTA_APP.'includes/vistas/pedidos/carrito.php?msg=Carrito+vaciado');
            exit;
        }

        $_SESSION['carrito']['items'] = $itemsNormalizados;
        $_SESSION['carrito']['ofertas'] = $ofertasNormalizadas;

        if ($accion === 'actualizar') {
            header('Location: '.RUTA_APP.'includes/vistas/pedidos/crearPedido.php?msg=Carrito+actualizado');
            exit;
        }

        if ($accion === 'finalizar') {
            if (empty($itemsNormalizados)) {
                $error = 'El carrito está vacío.';
            } else {
                $lineas = [];
                foreach ($itemsNormalizados as $idProducto => $cantidad) {
                    $lineas[] = [
                        'idProducto' => (int)$idProducto,
                        'cantidad' => (int)$cantidad,
                    ];
                }

                $cliente = $_SESSION['user'] ?? '';
                $numeroPedido = Pedido::crear($cliente, $tipo, $lineas, $ofertasNormalizadas);
                if ($numeroPedido !== null) {
                    $_SESSION['carrito'] = [
                        'tipo' => 'Local',
                        'items' => [],
                        'ofertas' => [],
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
    $bloqueTabla = '<p>Tu carrito está vacío.</p>';
} else {
    $bloqueTabla = '
    <table>
        <tr>
            <th>Producto</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        '.$filas.'
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
$ofertasJSON = htmlspecialchars(
    $ofertasJSONRaw,
    ENT_QUOTES,
    'UTF-8'
);
$funcionesJS = "<script src='".RUTA_JS."carrito.js'></script>";

$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Mi carrito</h1>
</div>

<div class="info-categoria"> $errorHtml
    $mensajeHtml

    <input type="hidden" id="config-ofertas-json" value="$ofertasJSON">
    
    <form method="POST" class="form-estandar">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        
        <div class="campo-form">
            <label for="tipo"><p><strong>Tipo de pedido:</strong></p></label>
            <select name="tipo" id="tipo">
                <option value="Local" $selLocal>Para tomar aquí (Local)</option>
                <option value="Llevar" $selLlevar>Para llevar</option>
            </select>
        </div>

        <div class="tabla-carrito-contenedor">
            $bloqueTabla
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

    </div> <div class="buttons-estandar">
        <button type="submit" name="accion" value="actualizar" class="button-estandar">Actualizar cantidades</button>
        <button type="submit" name="accion" value="finalizar" class="button-estandar">Finalizar pedido</button>
        <button type="submit" name="accion" value="vaciar" class="button-estandar btn-peligro">Vaciar carrito</button>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
