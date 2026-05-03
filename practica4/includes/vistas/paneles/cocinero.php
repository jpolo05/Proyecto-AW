<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\FormularioActualizaPedido; //Usa la clase FormularioActualizaPedido
use es\ucm\fdi\aw\usuarios\Pedido; //Usa la clase Pedido

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Cocinero'); //Solo permite entrar a usuarios con al menos el rol Cocinero
$rolSesion = $_SESSION['rol'] ?? ''; //Recoge el rol de la sesion
if (!in_array($rolSesion, ['Cocinero', 'Gerente'], true)) { //Comprueba que sea Cocinero o Gerente
    header('Location: '.RUTA_APP.'error.php?error=permiso%20insuficiente'); //Redirige si no tiene permiso
    exit;
}

//Funcion para limpiar el texto (seguridad)
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$nombreUsuario = h((string)($_SESSION['nombre'] ?? 'Cocinero')); //Nombre del cocinero
$apellidosUsuario = h((string)($_SESSION['apellidos'] ?? '')); //Apellidos del cocinero
$rutaVerPedido = RUTA_APP.'includes/vistas/pedidos/visualizarPedido.php'; //URL base para ver pedido
$rutaInicio = RUTA_APP.'index.php'; //URL para volver al inicio

$pedidos = Pedido::listar(); //Llama a listar (devuelve un array)
$columnaPreparacion = ''; //Pedidos pendientes de empezar a cocinar
$columnaCocinando = ''; //Pedidos que ya estan cocinando

foreach ($pedidos as $p) { //Recorre todos los pedidos
    //Recoge datos
    $estado = (string)($p['estado'] ?? '');
    $numeroPedido = (int)($p['numeroPedido'] ?? 0);
    $cliente = h((string)($p['cliente'] ?? ''));
    $tipo = h((string)($p['tipo'] ?? ''));
    $total = number_format((float)($p['total'] ?? 0), 2, '.', '');
    $urlDetalle = $rutaVerPedido.'?numeroPedido='.$numeroPedido; //URL para ver detalle

    if ($estado === Pedido::ESTADO_EN_PREPARACION) { //Pedidos en preparacion
        $form = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_COCINANDO, [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/cocinero.php',
        ]); //Formulario para empezar a cocinar
        $htmlForm = $form->gestiona(); //Llamada a gestiona()

        //Añade pedido a la columna en preparacion
        $columnaPreparacion .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Para {$tipo}<br>
                Total: {$total} EUR<br>
                <a href='{$urlDetalle}' class='button-estandar'>Ver detalle</a>
                {$htmlForm}
            </div>
        ";
    } elseif ($estado === Pedido::ESTADO_COCINANDO) { //Pedidos en modo cocinando
        $cocinero = h((string)($p['cocinero'] ?? '')); //Cocinero asignado
        $urlCocinar = $rutaVerPedido.'?numeroPedido='.$numeroPedido.'&accion=cocinar'; //URL para cocinar lineas del pedido
        //Añade pedido a la columna cocinando
        $columnaCocinando .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Cocinero: {$cocinero}<br>
                Para {$tipo}<br>
                <a href='{$urlCocinar}' class='button-estandar'>Cocinar</a>
            </div>
        ";
    }
}

if ($columnaPreparacion === '') { //Si no hay pedidos en preparacion
    $columnaPreparacion = '<p>No hay pedidos en preparación.</p>';
}
if ($columnaCocinando === '') { //Si no hay pedidos cocinando
    $columnaCocinando = '<p>No hay pedidos cocinando.</p>';
}

$tituloPagina = 'Administración - Bistro FDI';

//HTML contenido principal (que vera el usuario)
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
                <td>$columnaPreparacion</td>
                <td>$columnaCocinando</td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <a href="$rutaInicio" class="button-estandar">Volver al Inicio</a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun


