<?php
use es\ucm\fdi\aw\Auth;
use es\ucm\fdi\aw\FormularioBorrarPedido;
use es\ucm\fdi\aw\Pedido;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

$numeroPedido = (int)($_GET['numeroPedido'] ?? 0);
$usuario = $_SESSION['user'] ?? '';
$rol = $_SESSION['rol'] ?? '';

if ($numeroPedido <= 0) {
    header('Location: '.RUTA_APP.'error.php?error=numeroPedido+invalido');
    exit;
}

if (!in_array($rol, ['Cliente', 'Gerente'], true)) {
    header('Location: '.RUTA_APP.'error.php?error=permiso+insuficiente');
    exit;
}

if ($rol === 'Cliente') {
    $pedido = Pedido::buscaPorNumeroYCliente($numeroPedido, $usuario);
    if (!$pedido) {
        header('Location: '.RUTA_APP.'error.php?error=permiso+insuficiente');
        exit;
    }
} else {
    $pedido = Pedido::buscaPorNumero($numeroPedido);
    if (!$pedido) {
        header('Location: '.RUTA_APP.'error.php?error=pedido+no+encontrado');
        exit;
    }
}

$tituloPagina = 'Eliminar pedido';
$formulario = new FormularioBorrarPedido($numeroPedido);
$htmlFormulario = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h1>Eliminacion de pedido</h1>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php';
