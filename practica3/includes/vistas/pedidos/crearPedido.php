<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Pedido;
use es\ucm\fdi\aw\usuarios\Producto;

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
    <table border="1" cellpadding="6">
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
$scriptTotal = <<<EOS
    <script>
    (function () {
        function recalcularTotalPedido() {
            var total = 0;
            var inputs = document.querySelectorAll('.cantidad-producto');
            inputs.forEach(function (input) {
                var cantidad = parseInt(input.value, 10);
                var precio = parseFloat(input.dataset.precio || '0');
                if (!Number.isFinite(cantidad) || cantidad < 0) {
                    cantidad = 0;
                }
                if (!Number.isFinite(precio) || precio < 0) {
                    precio = 0;
                }
                total += cantidad * precio;
            });
            var nodoTotal = document.getElementById('totalPedido');
            if (nodoTotal) {
                nodoTotal.textContent = total.toFixed(2);
            }
        }

        document.querySelectorAll('.cantidad-producto').forEach(function (input) {
            input.addEventListener('input', recalcularTotalPedido);
        });
        recalcularTotalPedido();
    })();
    </script>
EOS;

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
        $bloqueTotal
        <p>
            <button type="submit" class='button-estandar'>Crear pedido</button>
            <a href="$urlVolver" class='button-estandar'>Volver</a>
        </p>
    </form>
    $scriptTotal
EOS;

require __DIR__.'/../plantillas/plantilla.php';
