<?php
require_once __DIR__.'/../../auth.php';
verificarAcceso('Cocinero');//De momento solo a partir de cocinero

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/pedido_mysql.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: '.RUTA_APP.'error.php?error=acceso%20No%20Permitido');
    exit;
}

$numeroPedido = trim($_POST['numeroPedido'] ?? '');

$exito = pedidos_actualizarEstado($numeroPedido, 'Cocinando', $_SESSION['user']);

if ($exito) {    
    header('Location: '.RUTA_APP.'cocinero.php');
} else {
    header('Location: '.RUTA_APP.'error.php?error=procesaPedido-Error%20sql');
}
exit;