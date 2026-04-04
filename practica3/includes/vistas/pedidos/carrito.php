<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Pedido;
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

        $itemsNormalizados = [];
        foreach ($cantidades as $idProducto => $cantidad) {
            $id = (int)$idProducto;
            $cant = (int)$cantidad;
            if ($id > 0 && $cant > 0) {
                $itemsNormalizados[$id] = $cant;
            }
        }

        $_SESSION['carrito']['tipo'] = $tipo;

        if ($accion === 'vaciar') {
            $_SESSION['carrito']['items'] = [];
            header('Location: '.RUTA_APP.'includes/vistas/pedidos/carrito.php?msg=Carrito+vaciado');
            exit;
        }

        $_SESSION['carrito']['items'] = $itemsNormalizados;

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
                $numeroPedido = Pedido::crear($cliente, $tipo, $lineas);
                if ($numeroPedido !== null) {
                    $_SESSION['carrito'] = [
                        'tipo' => 'Local',
                        'items' => [],
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

$tituloPagina = 'Mi carrito';
$errorHtml = $error !== '' ? '<p><strong>'.h($error).'</strong></p>' : '';
$mensajeHtml = $mensaje !== '' ? '<p><strong>'.h($mensaje).'</strong></p>' : '';
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
            <th>Descripcion</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        '.$filas.'
    </table>';
}

$totalTexto = number_format($total, 2, '.', '');
$scriptTotal = <<<EOS
    <script>
    (function () {
        function recalcularTotales() {
            var total = 0;
            document.querySelectorAll('.cantidad-carrito').forEach(function (input) {
                var cantidad = parseInt(input.value, 10);
                var precio = parseFloat(input.dataset.precio || '0');
                if (!Number.isFinite(cantidad) || cantidad < 0) {
                    cantidad = 0;
                }
                if (!Number.isFinite(precio) || precio < 0) {
                    precio = 0;
                }
                var subtotal = cantidad * precio;
                total += subtotal;
                var celda = input.closest('tr').querySelector('.subtotal-linea');
                if (celda) {
                    celda.textContent = subtotal.toFixed(2) + ' EUR';
                }
            });
            var nodoTotal = document.getElementById('totalCarrito');
            if (nodoTotal) {
                nodoTotal.textContent = total.toFixed(2);
            }
        }

        document.querySelectorAll('.cantidad-carrito').forEach(function (input) {
            input.addEventListener('input', recalcularTotales);
        });
        recalcularTotales();
    })();
    </script>
EOS;

$contenidoPrincipal = <<<EOS
    <h1>Mi carrito</h1>
    $errorHtml
    $mensajeHtml
    <form method="POST">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <p>
            <label>Tipo de pedido:
                <select name="tipo">
                    <option value="Local" $selLocal>Local</option>
                    <option value="Llevar" $selLlevar>Llevar</option>
                </select>
            </label>
        </p>
        $bloqueTabla
        <p><strong>Total del carrito: <span id="totalCarrito">$totalTexto</span> EUR</strong></p>
        <div class="buttons-estandar">
            <button type="submit" name="accion" value="actualizar" class="button-estandar">Actualizar carrito</button>
            <button type="submit" name="accion" value="finalizar" class="button-estandar">Finalizar pedido</button>
            <button type="submit" name="accion" value="vaciar" class="button-delete">Vaciar carrito</button>
        </div>
    </form>
    $scriptTotal
EOS;

require __DIR__.'/../plantillas/plantilla.php';
