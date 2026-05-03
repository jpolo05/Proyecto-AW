<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\FormularioActualizaPedido; //Usa la clase FormularioActualizaPedido
use es\ucm\fdi\aw\usuarios\Pedido; //Usa la clase Pedido

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Camarero'); //Solo permite entrar a usuarios con al menos el rol Camarero
$rolSesion = $_SESSION['rol'] ?? ''; //Recoge el rol de la sesion
if (!in_array($rolSesion, ['Camarero', 'Gerente'], true)) { //Comprueba que sea Camarero o Gerente
    header('Location: '.RUTA_APP.'error.php?error=permiso%20insuficiente'); //Redirige si no tiene permiso
    exit;
}

//Funcion para limpiar el texto (seguridad)
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$nombreUsuario = h((string)($_SESSION['nombre'] ?? 'Camarero')); //Nombre del camarero
$apellidosUsuario = h((string)($_SESSION['apellidos'] ?? '')); //Apellidos del camarero
$rutaInicio = RUTA_APP.'index.php'; //URL para volver al inicio
$rutaVerPedido = RUTA_APP.'includes/vistas/pedidos/visualizarPedido.php'; //URL base para ver pedido

$pedidos = Pedido::listar(); //Llama a listar (devuelve un array)
$columnaRecibidos = ''; //Pedidos recibidos pendientes de cobrar
$columnaListoCocina = ''; //Pedidos listos para recoger en cocina
$columnaPendienteEntrega = ''; //Pedidos pendientes de entregar

foreach ($pedidos as $p) { //Recorre todos los pedidos
    //Recoge datos
    $estado = (string)($p['estado'] ?? '');
    $numeroPedido = (int)($p['numeroPedido'] ?? 0);
    $cliente = h((string)($p['cliente'] ?? ''));
    $tipo = h((string)($p['tipo'] ?? ''));
    $total = number_format((float)($p['total'] ?? 0), 2, '.', '');
    $urlDetalle = $rutaVerPedido.'?numeroPedido='.$numeroPedido; //URL para ver detalle

    if ($estado === Pedido::ESTADO_RECIBIDO) { //Pedidos recibidos: se cobran y pasan a preparacion
        $form = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_EN_PREPARACION, [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/camarero.php',
            'textoBoton' => 'Cobrar y enviar a cocina',
        ]); //Formulario para cambiar estado
        $htmlForm = $form->gestiona(); //Llamada a gestiona()

        //Añade pedido a la columna recibidos
        $columnaRecibidos .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Total: {$total} EUR<br>
                <a href='{$urlDetalle}' class='button-estandar'>Ver detalle</a>
                {$htmlForm}
            </div>
        ";
    } elseif ($estado === Pedido::ESTADO_LISTO_COCINA) { //Pedidos listos en cocina
        $cocinero = h((string)($p['cocinero'] ?? '')); //Cocinero asignado
        $form = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_TERMINADO, [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/camarero.php',
            'textoBoton' => 'Recoger de cocina',
        ]); //Formulario para marcar como terminado
        $htmlForm = $form->gestiona(); //Llamada a gestiona()

        //Añade pedido a la columna listos en cocina
        $columnaListoCocina .= "
            <div class='pedido'>
                Pedido: #{$numeroPedido}<br>
                Cliente: {$cliente}<br>
                Cocinero: {$cocinero}<br>
                <a href='{$urlDetalle}' class='button-estandar'>Ver detalle</a>
                {$htmlForm}
            </div>
        ";
    } elseif ($estado === Pedido::ESTADO_TERMINADO) { //Pedidos terminados pendientes de entregar
        $form = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_ENTREGADO, [
            'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/camarero.php',
        ]); //Formulario para marcar como entregado
        $htmlForm = $form->gestiona(); //Llamada a gestiona()

        //Añade pedido a la columna pendientes de entregar
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

if ($columnaRecibidos === '') { //Si no hay pedidos recibidos
    $columnaRecibidos = '<p>No hay pedidos recibidos.</p>';
}
if ($columnaListoCocina === '') { //Si no hay pedidos listos
    $columnaListoCocina = '<p>No hay pedidos listos en cocina.</p>';
}
if ($columnaPendienteEntrega === '') { //Si no hay pedidos pendientes de entregar
    $columnaPendienteEntrega = '<p>No hay pedidos pendientes de entregar.</p>';
}

$tituloPagina = 'Administración - Bistro FDI';

//HTML contenido principal (que vera el usuario)
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

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
