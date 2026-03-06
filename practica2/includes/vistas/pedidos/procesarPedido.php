<?php
use es\ucm\fdi\aw\Pedido;

require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Cocinero');//De momento solo a partir de cocinero

require_once __DIR__.'/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: '.RUTA_APP.'error.php?error=acceso%20No%20Permitido');
    exit;
}

$numeroPedido = trim($_POST['numeroPedido'] ?? '');

$exito = Pedido::actualizarEstado($numeroPedido, 'Cocinando', $_SESSION['user']);

if ($exito) {    
    header('Location: '.RUTA_APP.'includes/vistas/paneles/cocinero.php');
} else {
    header('Location: '.RUTA_APP.'error.php?error=procesaPedido-Error%20sql');
}
exit;




