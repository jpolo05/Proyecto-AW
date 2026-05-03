<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\FormularioBorrarPedido; //Usa la clase FormularioBorrarPedido
use es\ucm\fdi\aw\usuarios\Pedido; //Usa la clase Pedido

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente

//Recoge datos necesarios
$numeroPedido = (int)($_GET['numeroPedido'] ?? 0);
$usuario = $_SESSION['user'] ?? '';
$rol = $_SESSION['rol'] ?? '';

//Si el numero de pedido no es valido redirige a error
if ($numeroPedido <= 0) {
    header('Location: '.RUTA_APP.'error.php?error='.rawurlencode('número de pedido inválido'));
    exit;
}

//Comprueba si el rol puede borrar pedidos
if (!in_array($rol, ['Cliente', 'Gerente'], true)) {
    header('Location: '.RUTA_APP.'error.php?error=permiso+insuficiente');
    exit;
}

if ($rol === 'Cliente') { //Si es cliente comprueba que el pedido existe y sea suyo
    $pedido = Pedido::buscaPorNumeroYCliente($numeroPedido, $usuario);
    if (!$pedido) {
        header('Location: '.RUTA_APP.'error.php?error=permiso+insuficiente');
        exit;
    }
} else {
    $pedido = Pedido::buscaPorNumero($numeroPedido); //Si es Gerente, solo comprueba que el pedido existe
    if (!$pedido) {
        header('Location: '.RUTA_APP.'error.php?error=pedido+no+encontrado');
        exit;
    }
}

$tituloPagina = 'Eliminar pedido';
$formulario = new FormularioBorrarPedido($numeroPedido); //Crea el formulario de borrado para ese pedido concreto
$htmlFormulario = $formulario->gestiona(); //Llamada a gestiona()

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<h1>Eliminacion de pedido</h1>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
