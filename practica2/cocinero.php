<?php

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
            <div style='border: 1px solid #b0b0b0; margin: 5px; padding: 5px; border-radius: 5px; background: #eeeeee;'> 
                Pedido: #{$p['numeroPedido']}<br>
                Cliente: {$p['cliente']}<br>
                Para {$p['tipo']}<br>
                Total: {$p['total']}€<br>
                <a href='cocinero.php?numeroPedido={$p['numeroPedido']}&accion=cocinar'>
                    <button>Tomar Pedido</button>
                </a>
            </div>
        ";
        } else if ($p['estado'] === 'Cocinando') {
            $columnaCocinando .= "
            <div style='border: 1px solid #b0b0b0; margin: 5px; padding: 5px; border-radius: 5px; background: #eeeeee;'> 
                Pedido: #{$p['numeroPedido']}<br>
                Cliente: {$p['cliente']}<br>
                Cocinero: {$p['cocinero']}<br>
                Para {$p['tipo']}<br>
                <a href='cocinero.php?numeroPedido={$p['numeroPedido']}&accion=listarElementos'>
                    <button>Ver Pedido</button>
                </a>
            </div>
        ";
        }
    }
}

$tituloPagina = 'Administración - Bistro FDI';

$contenidoPrincipal = <<<EOS
<div align="center">
    <h2>Panel de Cocina - Bistro FDI</h2>
    <hr style="width: 75%;">
    <br>

    <table cellpadding="15" border="1" style="width: 80%; border-collapse: collapse;">
        <thead>
            <tr>
                <th align="center" colspan="2">
                    Cocinero: $nombreUsuario $apellidosUsuario
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th align="center" width="50%">En preparación</th>
                <th align="center" width="50%">Cocinando</th>
            </tr>
            <tr>
                <td valign="top">$columnaPreparacion</td>
                <td valign="top">$columnaCocinando</td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <a href="index.php"><button>Volver al Inicio</button></a>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';