<?php
use es\ucm\fdi\aw\Pedido;
use es\ucm\fdi\aw\FormularioActualizaLineaPedido;
use es\ucm\fdi\aw\FormularioActualizaPedido;

require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Cliente');

$numeroPedido = $_GET['numeroPedido'] ?? 0;
$accionSolicitada = $_GET['accion'] ?? null;
$esCocinero = ($accionSolicitada === 'cocinar');

$pedido = Pedido::listarDetalle($numeroPedido);

$tituloPagina = 'Contenido Pedido';

$lineaPedido = '
    <table border="1" cellpadding="8">
        <tr>
            <th>Número Pedido</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
';

if ($esCocinero) {
    $lineaPedido .= '<th>Acción</th>';
}
$lineaPedido .= '</tr>';

foreach ($pedido as $fila) {
    $lineaPedido .= "
        <tr>
            <td>{$fila['numeroPedido']}</td>
            <td>{$fila['producto']}</td>
            <td>{$fila['cantidad']}</td>
            <td>{$fila['subtotal']}€</td>
    ";

    $idProd = $fila['idProducto'];

    if ($esCocinero) {
        if ($fila['estado'] == 0) {
            $form = new FormularioActualizaLineaPedido($numeroPedido, $idProd);
            $boton = $form->gestiona();
        } else {
            $boton = "Listo";
        }
        $lineaPedido .= "<td>$boton</td>";
    }

    $lineaPedido .= "</tr>";
}

$lineaPedido .= '</table>';

$numFilas = count($pedido);
foreach ($pedido as $fila) {
    if ($esCocinero && $fila['estado'] == 0) {
        break;
    }
    $numFilas--;
}

$aux = ($_SESSION['rol'] === 'Gerente') ? '' : 'hidden';

$botonFinalizar = "";
if ($esCocinero && $numFilas == 0) {
    $formFinal = new FormularioActualizaPedido($numeroPedido, 'Listo Cocina');
    $botonFinalizar = $formFinal->gestiona();
}

$contenidoPrincipal = <<<EOS
    <a href='pedidosUsuario.php'>
        <button>Volver a mis pedidos</button>
    </a>
    <div $aux>
        <a href='listarPedidos.php'>
            <button>Volver a todos los pedidos</button>
        </a>
    </div>
    <h1>Pedido #$numeroPedido</h1>
    $lineaPedido
    $botonFinalizar
EOS;

require __DIR__.'/../plantillas/plantilla.php';





