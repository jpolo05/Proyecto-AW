<?php
require_once 'includes/auth.php';
verificarAcceso('Cocinero');

require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/mysql/pedido_mysql.php';

$nombreUsuario = $_SESSION['nombre'] ?? 'Cocinero';
$apellidosUsuario = $_SESSION['apellidos'] ?? '';

$pedidos = pedidos_listar();
$columnaPreparacion = '';
$columnaCocinando = '';

if ($pedidos) {
    foreach ($pedidos as $p) {
        
        if ($p['estado'] === 'En preparación') {
            $columnaPreparacion .= "
            <div class='pedido'> 
                Pedido: #{$p['numeroPedido']}<br>
                Cliente: {$p['cliente']}<br>
                Para {$p['tipo']}<br>
                Total: {$p['total']}€<br>
                <form action='includes/vistas/pedidos/procesarPedido.php' method='POST'>
                    <input type='hidden' name='numeroPedido' value='{$p['numeroPedido']}'>
                    <button type='submit' class='button-estandar'>Tomar Pedido</button>
                </form>
            </div>
        ";
        } else if ($p['estado'] === 'Cocinando') {
            $columnaCocinando .= "
            <div class='pedido'>
                Pedido: #{$p['numeroPedido']}<br>
                Cliente: {$p['cliente']}<br>
                Cocinero: {$p['cocinero']}<br>
                Para {$p['tipo']}<br>
                <a href='includes/vistas/pedidos/verPedido.php?numeroPedido={$p['numeroPedido']}'>
                    <button class='button-estandar'>Ver Pedido</button>
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
    <a href="index.php"><button class="button-estandar">Volver al Inicio</button></a>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';