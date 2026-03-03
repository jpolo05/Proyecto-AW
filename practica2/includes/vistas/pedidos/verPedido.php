<?php

require_once __DIR__ . '/../../mysql/pedido_mysql.php';

$numeroPedido = $_GET['numeroPedido'] ?? 0;

$pedido = pedido_listar($numeroPedido);

$tituloPagina = 'Contenido Pedido';

$lineaPedido = '
    <table border="1" cellpadding="8">
        <tr>
            <th>Número Pedido</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
';

foreach ($pedido as $fila) {
    $lineaPedido .= "
    <tr>
        <td>$numeroPedido</td>
        <td>{$fila['idProducto']}</td>
        <td>{$fila['cantidad']}</td>
        <td>{$fila['subtotal']} €</td>
    </tr>";
}

$lineaPedido .= '</table>';

$contenidoPrincipal = <<<EOS
    <a href='listarPedidos.php'>
        <button>⬅️Volver a Pedidos</button>
    </a>
    <h1>Pedido #$numeroPedido</h1>
    $lineaPedido
EOS;

require __DIR__.'/../plantillas/plantilla.php';

