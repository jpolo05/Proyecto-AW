<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\FormularioActualizaPedido;
use es\ucm\fdi\aw\usuarios\Pedido;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cocinero');

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$nombreUsuario = h((string)($_SESSION['nombre'] ?? 'Cocinero'));
$apellidosUsuario = h((string)($_SESSION['apellidos'] ?? ''));
$rutaVerPedido = RUTA_APP.'includes/vistas/pedidos/visualizarPedido.php';
$rutaInicio = RUTA_APP.'index.php';

$pedidos = Pedido::listar();
$columnaPreparacion = '';
$columnaCocinando = '';

foreach ($pedidos as $p) {
    $estado = (string)($p['estado'] ?? '');
    $numeroPedido = (int)($p['numeroPedido'] ?? 0);
    $cliente = h((string)($p['cliente'] ?? ''));
    $tipo = h((string)($p['tipo'] ?? ''));
    $total = number_format((float)($p['total'] ?? 0), 2, '.', '');

    if ($estado === Pedido::ESTADO_EN_PREPARACION) {
        $form = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_COCINANDO, [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/cocinero.php',
        ]);
        $htmlForm = $form->gestiona();

        $columnaPreparacion .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Para {$tipo}<br>
                Total: {$total} EUR<br>
                {$htmlForm}
            </div>
        ";
    } elseif ($estado === Pedido::ESTADO_COCINANDO) {
        $cocinero = h((string)($p['cocinero'] ?? ''));
        $urlCocinar = $rutaVerPedido.'?numeroPedido='.$numeroPedido.'&accion=cocinar';
        $columnaCocinando .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Cocinero: {$cocinero}<br>
                Para {$tipo}<br>
                <a href='{$urlCocinar}'><button class='button-estandar'>Cocinar</button></a>
            </div>
        ";
    }
}

if ($columnaPreparacion === '') {
    $columnaPreparacion = '<p>No hay pedidos en preparacion.</p>';
}
if ($columnaCocinando === '') {
    $columnaCocinando = '<p>No hay pedidos cocinando.</p>';
}

$tituloPagina = 'Administracion - Bistro FDI';

$contenidoPrincipal = <<<EOS
<div>
    <h2 class="titulo">Panel de Cocina - Bistro FDI</h2>
    <hr>
    <br>

    <table class="cocina-table">
        <thead>
            <tr>
                <th colspan="2">
                    Cocinero: $nombreUsuario $apellidosUsuario
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>En preparacion</th>
                <th>Cocinando</th>
            </tr>
            <tr>
                <td valign="top">$columnaPreparacion</td>
                <td valign="top">$columnaCocinando</td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <a href="$rutaInicio"><button class="button-estandar">Volver al Inicio</button></a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
