<?php
use es\ucm\fdi\aw\Auth;
use es\ucm\fdi\aw\Pedido;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

require_once __DIR__.'/../../config.php';
$pedidos = Pedido::listar();

$tituloPagina = 'Listado Pedidos';

$tablaPedidos = '
    <table border="1" cellpadding="8">
        <tr>
            <th>NÃƒÆ’Ã‚Âºmero Pedido</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th>Cocinero</th>
            <th>Foto</th>
            <th>Total</th>
            <th>Accion</th>
        </tr>';

foreach ($pedidos as $p) {
    $numeroPedido = $p['numeroPedido'];
    $estado = $p['estado'];
    $tipo = $p['tipo'];
    $cocinero = $p['cocinero'];
    $foto = RUTA_IMGS;
    $foto .= $p['imagenCocinero'];
    $total = $p['total'];

    $tablaPedidos .= "
    <tr>
        <td>$numeroPedido</td>
        <td>$estado</td>
        <td>$tipo</td>
        <td>$cocinero</td>
        <td><img src='$foto' width='50' height='50'></td>
        <td>$total ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬</td>
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







