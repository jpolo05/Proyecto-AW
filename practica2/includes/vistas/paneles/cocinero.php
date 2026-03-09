<?php
use es\ucm\fdi\aw\Pedido;
use es\ucm\fdi\aw\FormularioActualizaPedido;

require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Cocinero');

require_once __DIR__.'/../../config.php';

$nombreUsuario = $_SESSION['nombre'] ?? 'Cocinero';
$apellidosUsuario = $_SESSION['apellidos'] ?? '';
$rutaVerPedido = RUTA_APP.'includes/vistas/pedidos/verPedido.php';
$rutaInicio = RUTA_APP.'index.php';

$pedidos = Pedido::listar();
$columnaPreparacion = '';
$columnaCocinando = '';

if ($pedidos) {
    foreach ($pedidos as $p) {
        if ($p['estado'] === 'En preparación') {
            $form = new FormularioActualizaPedido($p['numeroPedido'], 'Cocinando');
            $htmlForm = $form->gestiona();
        
            $columnaPreparacion .= "
            <div class='pedido'>
                Pedido: #{$p['numeroPedido']}<br>
                Cliente: {$p['cliente']}<br>
                Para {$p['tipo']}<br>
                Total: {$p['total']}€<br>
                $htmlForm
            </div>
        ";
        } else if ($p['estado'] === 'Cocinando') {
            $columnaCocinando .= "
            <div class='pedido'>
                Pedido: #{$p['numeroPedido']}<br>
                Cliente: {$p['cliente']}<br>
                Cocinero: {$p['cocinero']}<br>
                Para {$p['tipo']}<br>
                <a href='{$rutaVerPedido}?numeroPedido={$p['numeroPedido']}&accion=cocinar'>
                    <button class='button-estandar'>Cocinar</button>
                </a>
            </div>
        ";
        }
    }
}

$tituloPagina = 'Administración - Bistro FDI';

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
                <th>En preparación</th>
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




