<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Recompensa; //Usa la clase Recompensa
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga configuracion
Auth::verificarAcceso('Gerente'); //Comprueba acceso de gerente
$csrfToken = Auth::getCsrfToken(); //Obtiene token CSRF

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0); //Recoge id por GET o POST
$recompensa = $id > 0 ? Recompensa::buscaPorId($id) : null; //Busca recompensa

if (!$recompensa) { //Si no existe
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+no+encontrada'); //Redirige al listado
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Si se confirma el borrado
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Valida token CSRF
        $msg = rawurlencode('Token CSRF inválido'); //Mensaje de error
    } else {
        $ok = Recompensa::borrar($id); //Borra recompensa
        $msg = $ok ? 'Recompensa+eliminada' : 'No+se+pudo+eliminar+la+recompensa'; //Mensaje de resultado
    }
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg='.$msg); //Vuelve al listado
    exit;
}  

$tituloPagina = 'Eliminar recompensa'; //Titulo de la pagina

$idMostrado = (int)$recompensa['id']; //Id mostrado
$id_producto = htmlspecialchars($recompensa['id_producto'] ?? '', ENT_QUOTES, 'UTF-8'); //Id del producto seguro
$bistroCoins = htmlspecialchars($recompensa['bistroCoins'] ?? '', ENT_QUOTES, 'UTF-8'); //BistroCoins seguros
$action = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/borrarRecompensa.php?id='.urlencode((string)$idMostrado), ENT_QUOTES, 'UTF-8'); //URL del formulario
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php', ENT_QUOTES, 'UTF-8'); //URL para cancelar

$producto = $id_producto > 0 ? Producto::buscaPorId($id_producto) : null; //Busca producto asociado
$nombreProducto = "Producto no encontrado"; //Texto por defecto
if ($producto) { //Si existe producto
    $nombreProducto = htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); //Nombre seguro
}

$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Eliminar recompensa</h1>
</div>

<div class="info-categoria">
    
    <p><strong>ID:</strong> {$idMostrado}</p>
    <p><strong>Producto:</strong> {$nombreProducto}</p>
    <p><strong>BistroCoins:</strong> {$bistroCoins}</p>

</div> <form method="POST" action="$action">
    <input type="hidden" name="csrfToken" value="$csrfToken">
    <input type="hidden" name="id" value="{$idMostrado}">
    
    <div class="buttons-estandar">
        <button type="submit" class="button-delete">Eliminar</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS; //HTML principal

require __DIR__.'/../plantillas/plantilla.php'; //Carga plantilla
