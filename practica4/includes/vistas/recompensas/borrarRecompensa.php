<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Recompensa;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');
$csrfToken = Auth::getCsrfToken();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$recompensa = $id > 0 ? Recompensa::buscaPorId($id) : null;

if (!$recompensa) {
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+no+encontrada');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $msg = 'Token+CSRF+invalido';
    } else {
        $ok = Recompensa::borrar($id);
        $msg = $ok ? 'Recompensa+eliminada' : 'No+se+pudo+eliminar+la+recompensa';
    }
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg='.$msg);
    exit;
}  

$tituloPagina = 'Eliminar Recompensa';

$idMostrado = (int)$recompensa['id'];
$id_producto = htmlspecialchars($recompensa['id_producto'] ?? '', ENT_QUOTES, 'UTF-8');
$bistroCoins = htmlspecialchars($recompensa['bistroCoins'] ?? '', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php', ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Eliminar Recompensa</h1>
</div>

<div class="info-categoria">
    
    <p><strong>ID:</strong> {$idMostrado}</p>
    <p><strong>Producto:</strong> {$id_producto}</p>
    <p><strong>Bistro coins:</strong> {$bistroCoins}</p>

</div> <form method="POST" action="$action">
    <input type="hidden" name="csrfToken" value="$csrfToken">
    <input type="hidden" name="id" value="{$idMostrado}">
    
    <div class="buttons-estandar">
        <button type="submit" class="button-delete">Eliminar</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';


