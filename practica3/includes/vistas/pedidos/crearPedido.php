<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Oferta;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [
        'tipo' => 'Local',
        'items' => [],
    ];
}

$error = '';
$mensaje = '';
$tipo = $_POST['tipo'] ?? ($_SESSION['carrito']['tipo'] ?? 'Local');
$productos = Producto::listar(true);
$ofertasActivas = Oferta::obtenerOfertasActivas();
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF invalido.';
    }

    $cantidades = $_POST['cantidad'] ?? [];
    if (!is_array($cantidades)) {
        $cantidades = [];
    }

    $itemsActuales = is_array($_SESSION['carrito']['items'] ?? null) ? $_SESSION['carrito']['items'] : [];
    $itemsAnadidos = 0;

    foreach ($cantidades as $idProducto => $cantidad) {
        $id = (int)$idProducto;
        $cant = (int)$cantidad;
        if ($id > 0 && $cant > 0) {
            $itemsActuales[$id] = ((int)($itemsActuales[$id] ?? 0)) + $cant;
            $itemsAnadidos += $cant;
        }
    }

    if ($error === '' && $itemsAnadidos === 0) {
        $error = 'Debes seleccionar al menos un producto con cantidad mayor que cero.';
    } elseif ($error === '' && !in_array($tipo, ['Local', 'Llevar'], true)) {
        $error = 'Tipo de pedido no valido.';
    } else {
        $_SESSION['carrito']['tipo'] = $tipo;
        $_SESSION['carrito']['items'] = $itemsActuales;
        header('Location: '.RUTA_APP.'includes/vistas/pedidos/carrito.php?msg=Productos+anadidos+al+carrito');
        exit;
    }
}

$itemsCarrito = is_array($_SESSION['carrito']['items'] ?? null) ? $_SESSION['carrito']['items'] : [];
$unidadesCarrito = array_sum(array_map('intval', $itemsCarrito));

$tituloPagina = 'Crear pedido';
$errorHtml = $error !== '' ? '<p style="text-align: center;"><strong>'.h($error).'</strong></p>' : '';
$mensaje = $_GET['msg'] ?? '';
$mensajeHtml = $mensaje !== '' ? '<p style="text-align: center;"><strong>'.h($mensaje).'</strong></p>' : '';
$urlVolver = RUTA_APP.'includes/vistas/pedidos/carrito.php';
$action = h(RUTA_APP.'includes/vistas/pedidos/crearPedido.php');
$selLocal = ($tipo === 'Local') ? 'selected' : '';
$selLlevar = ($tipo === 'Llevar') ? 'selected' : '';
$urlCarrito = RUTA_APP.'includes/vistas/pedidos/carrito.php';

$bloqueOfertas = '<p style="text-align: center;">No hay ofertas activas disponibles actualmente.</p>';
if (!empty($ofertasActivas)) {
    $htmlOfertas = '';
    foreach ($ofertasActivas as $oferta) {
        $idOferta = (int)($oferta['id'] ?? 0);
        $nombreOferta = h((string)($oferta['nombre'] ?? ''));
        $descripcionOferta = h((string)($oferta['descripcion'] ?? ''));
        $finOferta = h((string)($oferta['fin'] ?? ''));
        $descuentoOferta = number_format((float)($oferta['descuento'] ?? 0), 2, '.', '');
        $urlVerOferta = RUTA_APP.'includes/vistas/ofertas/visualizarOferta.php?id='.$idOferta.'&origen=pedido';

        $htmlOfertas .= "
        <tr>
            <td>{$nombreOferta}</td>
            <td>{$descripcionOferta}</td>
            <td>{$finOferta}</td>
            <td>{$descuentoOferta}%</td>
            <td><a href='{$urlVerOferta}' class='button-estandar'>Ver</a></td>
        </tr>";
    }

    $bloqueOfertas = "
    <table style='width: 42%; margin: 0 auto 20px auto;'>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Fin</th>
            <th>Descuento</th>
            <th>Acción</th>
        </tr>
        {$htmlOfertas}
    </table>";
}

$filasProductos = '';
$totalInicial = 0.0;
foreach ($productos as $p) {
    $id = (int)($p['id'] ?? 0);
    $nombre = h((string)($p['nombre'] ?? ''));
    $precioBase = (float)($p['precio_base'] ?? 0);
    $iva = (int)($p['iva'] ?? 0);
    $precioFinal = $precioBase + ($precioBase * $iva / 100);
    $cantidadDefecto = (int)($_POST['cantidad'][$id] ?? 0);
    $totalInicial += ($precioFinal * $cantidadDefecto);
    $precioFinalTexto = number_format($precioFinal, 2, '.', '');

    $filasProductos .= '
    <tr>
        <td>'.$nombre.'</td>
        <td>'.$precioFinalTexto.'</td>
        <td><input type="number" min="0" step="1" name="cantidad['.$id.']" value="'.$cantidadDefecto.'" class="cantidad-producto" data-precio="'.$precioFinalTexto.'"></td>
    </tr>';
}

$bloqueProductos = '';
if ($filasProductos === '') {
    $bloqueProductos = '<p>No hay productos disponibles para pedir.</p>';
} else {
    $bloqueProductos = '
    <table style="width: 60%; margin: 0 auto;">
        <tr>
            <th>Producto</th>
            <th>Precio (IVA incl.)</th>
            <th>Cantidad</th>
        </tr>
        '.$filasProductos.'
    </table>';
}

$totalInicialTexto = number_format($totalInicial, 2, '.', '');
$bloqueTotal = '<p><strong>Total del pedido: <span id="totalPedido">'.$totalInicialTexto.'</span> EUR</strong></p>';

$contenidoPrincipal = <<<EOS
    <h1 style="text-align: center;">Crear pedido</h1>
    $errorHtml
    $mensajeHtml
    <p style="text-align: center;"><strong>Carrito actual:</strong> {$unidadesCarrito} producto(s). <a href="$urlCarrito" class="button-estandar">Ver carrito</a></p>
    <h2 style="text-align: center;">Ofertas disponibles</h2>
    $bloqueOfertas
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <h2 style="text-align: center;">Productos</h2>
        <p style="text-align: center; font-size: 1.4rem;">
            <label>Tipo:
                <select name="tipo" style="font-size: 1.2rem; padding: 6px 12px;">
                    <option value="Local" $selLocal>Local</option>
                    <option value="Llevar" $selLlevar>Llevar</option>
                </select>
            </label>
        </p>
        $bloqueProductos
        $bloqueTotal
        <p>
            <button type="submit" class='button-estandar'>Anadir al carrito</button>
            <a href="$urlVolver" class='button-estandar'>Ir al carrito</a>
        </p>
    </form>
EOS;

$funcionesJS = "<script src='".RUTA_JS."crearPedido.js'></script>";

require __DIR__.'/../plantillas/plantilla.php';
