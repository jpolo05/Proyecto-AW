<?php
use es\ucm\fdi\aw\Pedido;

require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Cliente');
$pedidos = Pedido::listar_cliente($_SESSION['user']);

$tituloPagina = 'Listado Pedidos';

$tablaPedidos = '
    <table border="1" cellpadding="8">
        <tr>
            <th>Número Pedido</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th>Total</th>
            <th>Accion</th>
        </tr>';

foreach ($pedidos as $p) {
    $numeroPedido = $p['numeroPedido'];
    $estado = $p['estado'];
    $tipo = $p['tipo'];
    $total = $p['total'];

    $tablaPedidos .= "
    <tr>
        <td>$numeroPedido</td>
        <td>$estado</td>
        <td>$tipo</td>
        <td>$total</td>
        <td>
            <a href='verPedido.php?numeroPedido=$numeroPedido'>
                <button>Ver Pedido</button>
            </a>
            <br>
            <a href='borrarPedido.php?numeroPedido=$numeroPedido'>
                <button>Cancelar/Borrar Pedido</button>
            </a>
        </td>
    </tr>";
}
$tablaPedidos .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Pedidos</h1>
    $tablaPedidos
EOS;

require __DIR__.'/../plantillas/plantilla.php';