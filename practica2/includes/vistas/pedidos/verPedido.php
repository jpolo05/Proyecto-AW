<?php
require_once __DIR__.'/../../auth.php';
verificarAcceso('Cliente');

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

if($_SESSION['rol'] === 'Cliente') {
    $url = RUTA_APP . 'index.php';
    $txt = "⬅️Volver a inicio";
} else {
    $url = "listarPedidos.php";
    $txt = "⬅️Volver a pedidos";
}

$contenidoPrincipal = <<<EOS
    <a href='$url'>
        <button>$txt</button>
    </a>
    <h1>Pedido #$numeroPedido</h1>
    $lineaPedido
EOS;

require __DIR__.'/../plantillas/plantilla.php';

