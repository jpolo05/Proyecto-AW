<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\FormularioActualizaPedido;
use es\ucm\fdi\aw\usuarios\Pedido;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Camarero');
$rolSesion = $_SESSION['rol'] ?? '';
if (!in_array($rolSesion, ['Camarero', 'Gerente'], true)) {
    header('Location: '.RUTA_APP.'error.php?error=permiso%20insuficiente');
    exit;
}

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$nombreUsuario = h((string)($_SESSION['nombre'] ?? 'Camarero'));
$apellidosUsuario = h((string)($_SESSION['apellidos'] ?? ''));
$rutaInicio = RUTA_APP.'index.php';
$rutaVerPedido = RUTA_APP.'includes/vistas/pedidos/visualizarPedido.php';

$pedidos = Pedido::listar();
$columnaRecibidos = '';
$columnaListoCocina = '';
$columnaPendienteEntrega = '';

foreach ($pedidos as $p) {
    $estado = (string)($p['estado'] ?? '');
    $numeroPedido = (int)($p['numeroPedido'] ?? 0);
    $cliente = h((string)($p['cliente'] ?? ''));
    $tipo = h((string)($p['tipo'] ?? ''));
    $total = number_format((float)($p['total'] ?? 0), 2, '.', '');
    $urlDetalle = $rutaVerPedido.'?numeroPedido='.$numeroPedido;

    if ($estado === Pedido::ESTADO_RECIBIDO) {
        $form = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_EN_PREPARACION, [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/camarero.php',
            'textoBoton' => 'Cobrar y enviar a cocina',
        ]);
        $htmlForm = $form->gestiona();

        $columnaRecibidos .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Total: {$total} EUR<br>
                <a href='{$urlDetalle}' class='button-estandar'>Ver detalle</a>
                {$htmlForm}
            </div>
        ";
    } elseif ($estado === Pedido::ESTADO_LISTO_COCINA) {
        $cocinero = h((string)($p['cocinero'] ?? ''));
        $form = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_TERMINADO, [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/camarero.php',
            'textoBoton' => 'Recoger de cocina',
        ]);
        $htmlForm = $form->gestiona();

        $columnaListoCocina .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Cocinero: {$cocinero}<br>
                <a href='{$urlDetalle}' class='button-estandar'>Ver detalle</a>
                {$htmlForm}
            </div>
        ";
    } elseif ($estado === Pedido::ESTADO_TERMINADO) {
        $form = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_ENTREGADO, [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/camarero.php',
        ]);
        $htmlForm = $form->gestiona();

        $columnaPendienteEntrega .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Para {$tipo}<br>
                Total: {$total} EUR<br>
                <a href='{$urlDetalle}' class='button-estandar'>Ver detalle</a>
                {$htmlForm}
            </div>
        ";
    }
}

if ($columnaRecibidos === '') {
    $columnaRecibidos = '<p>No hay pedidos recibidos.</p>';
}
if ($columnaListoCocina === '') {
    $columnaListoCocina = '<p>No hay pedidos listos en cocina.</p>';
}
if ($columnaPendienteEntrega === '') {
    $columnaPendienteEntrega = '<p>No hay pedidos pendientes de entregar.</p>';
}

$tituloPagina = 'Administracion - Bistro FDI';

$contenidoPrincipal = <<<EOS
<div>
    <h2 class="titulo">Panel de Camarero - Bistro FDI</h2>
    <hr>
    <br>

    <table class="cocina-table">
        <thead>
            <tr>
                <th colspan="3">Camarero: $nombreUsuario $apellidosUsuario</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Pedidos recibidos</th>
                <th>Listos en cocina</th>
                <th>Pendientes de entregar</th>
            </tr>
            <tr>
                <td>$columnaRecibidos</td>
                <td>$columnaListoCocina</td>
                <td>$columnaPendienteEntrega</td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <a href="$rutaInicio" class="button-estandar">Volver al Inicio</a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
