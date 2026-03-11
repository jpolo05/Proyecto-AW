<?php
use es\ucm\fdi\aw\Auth;
use es\ucm\fdi\aw\Pedido;
use es\ucm\fdi\aw\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$error = '';
$tipo = $_POST['tipo'] ?? 'Local';
$productos = Producto::listar(true);
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF invalido.';
    }

    $cantidades = $_POST['cantidad'] ?? [];
    $lineas = [];

    if (!is_array($cantidades)) {
        $cantidades = [];
    }

    foreach ($cantidades as $idProducto => $cantidad) {
        $id = (int)$idProducto;
        $cant = (int)$cantidad;
        if ($id > 0 && $cant > 0) {
            $lineas[] = [
                'idProducto' => $id,
                'cantidad' => $cant,
            ];
        }
    }

    if ($error === '' && empty($lineas)) {
        $error = 'Debes seleccionar al menos un producto con cantidad mayor que cero.';
    } elseif ($error === '' && !in_array($tipo, ['Local', 'Llevar'], true)) {
        $error = 'Tipo de pedido no valido.';
    } elseif ($error === '') {
        $cliente = $_SESSION['user'] ?? '';
        $numeroPedido = Pedido::crear($cliente, $tipo, $lineas);

        if ($numeroPedido !== null) {
            header('Location: '.RUTA_APP.'includes/vistas/pedidos/visualizarPedido.php?numeroPedido='.$numeroPedido);
            exit;
        }

        $error = 'No se pudo crear el pedido.';
    }
}

$tituloPagina = 'Crear pedido';
$errorHtml = $error !== '' ? '<p><strong>'.h($error).'</strong></p>' : '';
$urlVolver = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php';
$action = h(RUTA_APP.'includes/vistas/pedidos/crearPedido.php');
$selLocal = ($tipo === 'Local') ? 'selected' : '';
$selLlevar = ($tipo === 'Llevar') ? 'selected' : '';

$filasProductos = '';
foreach ($productos as $p) {
    $id = (int)($p['id'] ?? 0);
    $nombre = h((string)($p['nombre'] ?? ''));
    $precioBase = (float)($p['precio_base'] ?? 0);
    $iva = (int)($p['iva'] ?? 0);
    $precioFinal = $precioBase + ($precioBase * $iva / 100);
    $cantidadDefecto = (int)($_POST['cantidad'][$id] ?? 0);

    $filasProductos .= '
    <tr>
        <td>'.$nombre.'</td>
        <td>'.number_format($precioFinal, 2, '.', '').'</td>
        <td><input type="number" min="0" step="1" name="cantidad['.$id.']" value="'.$cantidadDefecto.'"></td>
    </tr>';
}

$bloqueProductos = '';
if ($filasProductos === '') {
    $bloqueProductos = '<p>No hay productos disponibles para pedir.</p>';
} else {
    $bloqueProductos = '
    <table border="1" cellpadding="6">
        <tr>
            <th>Producto</th>
            <th>Precio (IVA incl.)</th>
            <th>Cantidad</th>
        </tr>
        '.$filasProductos.'
    </table>';
}

$contenidoPrincipal = <<<EOS
    <h1>Crear pedido</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <p>
            <label>Tipo:
                <select name="tipo">
                    <option value="Local" $selLocal>Local</option>
                    <option value="Llevar" $selLlevar>Llevar</option>
                </select>
            </label>
        </p>
        $bloqueProductos
        <p>
            <button type="submit">Crear pedido</button>
            <a href="$urlVolver"><button type="button">Volver</button></a>
        </p>
    </form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
