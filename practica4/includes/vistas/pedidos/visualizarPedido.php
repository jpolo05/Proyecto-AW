<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\FormularioActualizaLineaPedido; //Usa la clase FormularioActualizaLineaPedido
use es\ucm\fdi\aw\usuarios\FormularioActualizaPedido; //Usa la clase FormularioActualizaPedido
use es\ucm\fdi\aw\usuarios\Pedido; //Usa la clase Pedido

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente

//Funcion para limpiar el texto (seguridad)
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

//Recoge datos necesarios
$numeroPedido = (int)($_GET['numeroPedido'] ?? 0);
$accionSolicitada = $_GET['accion'] ?? null;
$rol = $_SESSION['rol'] ?? '';
$usuario = $_SESSION['user'] ?? '';

if ($numeroPedido <= 0) { //Si el numero de pedido no es valido redirige a error
    header('Location: '.RUTA_APP.'error.php?error='.rawurlencode('número de pedido inválido'));
    exit;
}

if ($rol === 'Cliente') { //Si es cliente comprueba que el pedido existe y sea suyo
    $cabeceraPedido = Pedido::buscaPorNumeroYCliente($numeroPedido, $usuario);
    if (!$cabeceraPedido) { //Si no encuentra pedido redirige a error
        header('Location: '.RUTA_APP.'error.php?error=permiso+insuficiente');
        exit;
    }
} else {
    $cabeceraPedido = Pedido::buscaPorNumero($numeroPedido); //Si no es cliente, solo comprueba que el pedido existe
    if (!$cabeceraPedido) { //Si no encuentra pedido redirige a error
        header('Location: '.RUTA_APP.'error.php?error=pedido+no+encontrado');
        exit;
    }
}

$rolPuedeCocinar = in_array($rol, ['Cocinero', 'Gerente'], true); //Comprueba si el rol puede cocinar
$esModoCocina = ($accionSolicitada === 'cocinar'); //Comprueba si se ha pedido el modo cocina
if ($esModoCocina && !$rolPuedeCocinar) { //Si no tiene permisos para cocinar redirige a error
    header('Location: '.RUTA_APP.'error.php?error=permiso+insuficiente');
    exit;
}

$pedido = Pedido::listarDetalle($numeroPedido); //Lista las lineas del pedido
$tituloPagina = 'Contenido pedido';

//Recoge totales de la cabecera
$totalPedidoValor = (float)($cabeceraPedido['total'] ?? 0);
$coinsGastadosPedidoValor = (int)($cabeceraPedido['bistroCoinsGastados'] ?? 0);
$totalOriginalValor = 0.0;
foreach ($pedido as $fila) { //Recorre las lineas para calcular el total original
    $totalOriginalValor += (float)($fila['subtotal'] ?? 0);
}
$descuentoAplicadoValor = max(0, round($totalOriginalValor - $totalPedidoValor, 2)); //Calcula descuento aplicado

//Formatea datos para mostrar
$totalPedido = number_format($totalPedidoValor, 2, '.', '');
$totalOriginal = number_format($totalOriginalValor, 2, '.', '');
$descuentoAplicado = number_format($descuentoAplicadoValor, 2, '.', '');
$coinsGastadosPedido = (string)$coinsGastadosPedidoValor;

$bloqueTotalPedido = "<p><strong>Total del pedido: {$totalPedido} EUR</strong></p>"; //Prepara bloque de total
if ($descuentoAplicadoValor > 0) { //Si hay descuento muestra total original y final
    $bloqueTotalPedido = "<p><strong>Total original: {$totalOriginal} EUR</strong></p>"
        . "<p><strong>Descuento aplicado: -{$descuentoAplicado} EUR</strong></p>"
        . "<p><strong>Total final del pedido: {$totalPedido} EUR</strong></p>";
}
if ($coinsGastadosPedidoValor > 0) { //Si se han usado BistroCoins los muestra
    $bloqueTotalPedido .= "<p><strong>BistroCoins usados: {$coinsGastadosPedido} BC</strong></p>";
}

//Empieza a crear la tabla HTML
$lineaPedido = '
    <table>
        <tr>
            <th>Número pedido</th>
            <th>Producto</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Subtotal</th>';

if ($esModoCocina) { //Si esta en modo cocina anade columna de accion
    $lineaPedido .= '<th>Acción</th>';
}
$lineaPedido .= '</tr>'; //Cierra la fila de cabecera

$hayLineasPendientes = false; //Indica si quedan lineas sin preparar
foreach ($pedido as $fila) { //Recorre cada linea del pedido

    //Recoge datos
    $numFila = (int)($fila['numeroPedido'] ?? 0);
    $producto = h((string)($fila['producto'] ?? ''));
    $esRecompensa = (int)($fila['esRecompensa'] ?? 0);
    $tipoLinea = $esRecompensa === 1 ? 'Recompensa' : 'Normal';
    $cantidad = (int)($fila['cantidad'] ?? 0);
    $subtotal = number_format((float)($fila['subtotal'] ?? 0), 2, '.', '');
    $coinsLinea = (int)($fila['bistroCoinsGastados'] ?? 0);
    $idProd = (int)($fila['idProducto'] ?? 0);
    $estadoLinea = (int)($fila['estado'] ?? 0);
    $textoSubtotal = $esRecompensa === 1 ? "{$subtotal} EUR ({$coinsLinea} BC)" : "{$subtotal} EUR"; //Texto del subtotal segun tipo

    //Anade 1 fila a la tabla
    $lineaPedido .= "
        <tr>
            <td>{$numFila}</td>
            <td>{$producto}</td>
            <td>{$tipoLinea}</td>
            <td>{$cantidad}</td>
            <td>{$textoSubtotal}</td>
    ";

    if ($esModoCocina) { //Si esta en modo cocina muestra accion de cada linea
        if ($estadoLinea === 0) { //Si la linea esta pendiente
            $hayLineasPendientes = true;
            $form = new FormularioActualizaLineaPedido($numeroPedido, $idProd); //Crea el formulario para marcar linea como lista
            $boton = $form->gestiona(); //Llamada a gestiona()
        } else { //Si la linea ya esta lista
            $boton = 'Listo';
        }
        $lineaPedido .= "<td>{$boton}</td>"; //Anade columna de accion
    }

    $lineaPedido .= '</tr>'; //Cierra la fila
}

$lineaPedido .= '</table>'; //Cierra la tabla HTML

$botonFinalizar = ''; //Prepara boton de finalizar
if ($esModoCocina && !$hayLineasPendientes && !empty($pedido)) { //Si todas las lineas estan listas permite finalizar
    $formFinal = new FormularioActualizaPedido($numeroPedido, Pedido::ESTADO_LISTO_COCINA, [
        'urlRedireccion' => RUTA_APP.'includes/vistas/paneles/cocinero.php',
    ]); //Crea el formulario para actualizar el estado del pedido
    $botonFinalizar = $formFinal->gestiona(); //Llamada a gestiona()
}

$botonVolver = ''; //Prepara boton de volver
if ($esModoCocina) { //Si esta en modo cocina vuelve al panel de cocina
    $urlVolver = RUTA_APP.'includes/vistas/paneles/cocinero.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver al panel de cocina</a>";
} elseif ($rol === 'Cocinero') { //Si es cocinero vuelve al panel de cocina
    $urlVolver = RUTA_APP.'includes/vistas/paneles/cocinero.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver al panel de cocina</a>";
} elseif ($rol === 'Camarero') { //Si es camarero vuelve al panel de camarero
    $urlVolver = RUTA_APP.'includes/vistas/paneles/camarero.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver al panel de camarero</a>";
} elseif ($rol === 'Gerente') { //Si es gerente vuelve a todos los pedidos
    $urlVolver = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver a todos los pedidos</a>";
} else { //Si es cliente vuelve a sus pedidos
    $urlVolver = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php';
    $botonVolver = "<a href='{$urlVolver}' class='button-estandar'>Volver a mis pedidos</a>";
}

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
    $botonVolver
    <h1>Pedido #$numeroPedido</h1>
    $bloqueTotalPedido
    $lineaPedido
    $botonFinalizar
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
