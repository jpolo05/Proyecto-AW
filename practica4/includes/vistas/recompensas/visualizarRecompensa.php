<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Recompensa;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+invalida');
    exit;
}

$recompensa = Recompensa::buscaPorId($id);
if (!$recompensa) {
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+no+encontrada');
    exit;
}

