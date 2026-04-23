<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Producto;
use es\ucm\fdi\aw\usuarios\Recompensa;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$id = (int)($_GET['id'] ?? 0);
$recompensa = $id > 0 ? Recompensa::buscaPorId($id) : null;

if (!$recompensa) {
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+no+encontrada');
    exit;
}

$error = '';
$csrfToken = Auth::getCsrfToken();

