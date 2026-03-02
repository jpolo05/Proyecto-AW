<?php

require_once __DIR__ . '/../../mysql/pedido_mysql.php';
$pedidos = pedidos_listar();

$tituloPagina = 'Listado Pedidos';

$tablaPedidos = '
    <table border="1" cellpadding="6">
        <tr>
            <th>Número Pedido</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th>Cocinero</th>
            <th>Total</th>
            <th>Accion</th>
        </tr>';

foreach ($pedidos as $p) {
    $numeroPedido = $p['numeroPedido'];
    $estado = $p['estado'];
    $tipo = $p['tipo'];
    $cocinero = $p['cocinero'];
    $total = $p['total'];

    $tablaPedidos .= "
    <tr>
        <td>$numeroPedido</td>
        <td>$estado</td>
        <td>$tipo</td>
        <td>$cocinero</td>
        <td>$total €</td>
        <td>
            <a href='verPedido.php?numeroPedido=$numeroPedido'>
                <button>Ver Pedido</button>
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

