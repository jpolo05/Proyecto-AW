<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\FormularioActualizaLineaPedido;
use es\ucm\fdi\aw\usuarios\FormularioActualizaPedido;
use es\ucm\fdi\aw\usuarios\Pedido;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$numeroPedido = (int)($_GET['numeroPedido'] ?? 0);
$accionSolicitada = $_GET['accion'] ?? null;
$rol = $_SESSION['rol'] ?? '';
$usuario = $_SESSION['user'] ?? '';

if ($numeroPedido <= 0) {
    header('Location: '.RUTA_APP.'error.php?error=numeroPedido+invalido');
    exit;
}

if ($rol === 'Cliente') {
    $cabeceraPedido = Pedido::buscaPorNumeroYCliente($numeroPedido, $usuario);
    if (!$cabeceraPedido) {
        header('Location: '.RUTA_APP.'error.php?error=permiso+insuficiente');
        exit;
    }
} else {
    $cabeceraPedido = Pedido::buscaPorNumero($numeroPedido);
    if (!$cabeceraPedido) {
        header('Location: '.RUTA_APP.'error.php?error=pedido+no+encontrado');
        exit;
    }
}

$rolPuedeCocinar = in_array($rol, ['Cocinero', 'Gerente'], true);
$esModoCocina = ($accionSolicitada === 'cocinar');
if ($esModoCocina && !$rolPuedeCocinar) {
    header('Location: '.RUTA_APP.'error.php?error=permiso+insuficiente');
    exit;
}

$pedido = Pedido::listarDetalle($numeroPedido);
$tituloPagina = 'Contenido pedido';
$totalPedidoValor = (float)($cabeceraPedido['total'] ?? 0);
$totalOriginalValor = 0.0;
foreach ($pedido as $fila) {
    $totalOriginalValor += (float)($fila['subtotal'] ?? 0);
}
$descuentoAplicadoValor = max(0, round($totalOriginalValor - $totalPedidoValor, 2));

$totalPedido = number_format($totalPedidoValor, 2, '.', '');
$totalOriginal = number_format($totalOriginalValor, 2, '.', '');
$descuentoAplicado = number_format($descuentoAplicadoValor, 2, '.', '');

$bloqueTotalPedido = "<p><strong>Total del pedido: {$totalPedido} EUR</strong></p>";
if ($descuentoAplicadoValor > 0) {
    $bloqueTotalPedido = "<p><strong>Total original: {$totalOriginal} EUR</strong></p>"
        . "<p><strong>Descuento aplicado: -{$descuentoAplicado} EUR</strong></p>"
        . "<p><strong>Total final del pedido: {$totalPedido} EUR</strong></p>";
}

// border="1" cellpadding="8"
$lineaPedido = '
    <table>
        <tr>
            <th>Numero pedido</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Subtotal</th>';

if ($esModoCocina) {
    $lineaPedido .= '<th>Accion</th>';
}
$lineaPedido .= '</tr>';

$hayLineasPendientes = false;
foreach ($pedido as $fila) {
    $numFila = (int)($fila['numeroPedido'] ?? 0);
    $producto = h((string)($fila['producto'] ?? ''));
    $cantidad = (int)($fila['cantidad'] ?? 0);
    $subtotal = number_format((float)($fila['subtotal'] ?? 0), 2, '.', '');
    $idProd = (int)($fila['idProducto'] ?? 0);
    $estadoLinea = (int)($fila['estado'] ?? 0);

    $lineaPedido .= "
        <tr>
            <td>{$numFila}</td>
            <td>{$producto}</td>
            <td>{$cantidad}</td>
            <td>{$subtotal} EUR</td>
    ";

    if ($esModoCocina) {
        if ($estadoLinea === 0) {
            $hayLineasPendientes = true;
            $form = new FormularioActualizaLineaPedido($numeroPedido, $idProd);
            $boton = $form->gestiona();
        } else {
            $boton = 'Listo';
        }
        $lineaPedido .= "<td>{$boton}</td>";
    }

    $lineaPedido .= '</tr>';
}

$lineaPedido .= '</table>';

$botonFinalizar = '';
if ($esModoCocina && !$hayLineasPendientes && !empty($pedido)) {
    $formFinal = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_LISTO_COCINA, [
        'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/cocinero.php',
    ]);
    $botonFinalizar = $formFinal->gestiona();
}

$botonVolver = '';
if ($esModoCocina) {
    $urlVolver = RUTA_APP.'includes/vistas/paneles/cocinero.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver al panel de cocina</a>";
} elseif ($rol === 'Cocinero') {
    $urlVolver = RUTA_APP.'includes/vistas/paneles/cocinero.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver al panel de cocina</a>";
} elseif ($rol === 'Camarero') {
    $urlVolver = RUTA_APP.'includes/vistas/paneles/camarero.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver al panel de camarero</a>";
} elseif ($rol === 'Gerente') {
    $urlVolver = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver a todos los pedidos</a>";
} else {
    $urlVolver = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver a mis pedidos</a>";
}

$contenidoPrincipal = <<<EOS
    $botonVolver
    <h1>Pedido #$numeroPedido</h1>
    $bloqueTotalPedido
    $lineaPedido
    $botonFinalizar
EOS;

require __DIR__.'/../plantillas/plantilla.php';
