<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Recompensa; //Usa la clase Recompensa
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga configuracion
Auth::verificarAcceso('Gerente'); //Comprueba acceso de gerente

$id = (int)($_GET['id'] ?? 0); //Recoge id de recompensa
if ($id <= 0) { //Si no es valido
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg='.rawurlencode('Recompensa inválida')); //Redirige al listado
    exit;
}

$recompensa = Recompensa::buscaPorId($id); //Busca recompensa
if (!$recompensa) { //Si no existe
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+no+encontrada'); //Redirige al listado
    exit;
}

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente'); //Comprueba rol gerente

$idProducto = (int)($recompensa['id_producto'] ?? 0); //Producto asociado
$producto = Producto::buscaPorId($idProducto); //Busca producto
$nombreProducto = htmlspecialchars($producto['nombre'] ?? 'Producto desconocido', ENT_QUOTES, 'UTF-8'); //Nombre seguro
$bistroCoins = (int)($recompensa['bistroCoins'] ?? 0); //Coste en BistroCoins

$urlVolver = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php', ENT_QUOTES, 'UTF-8'); //URL para volver

$accionesGerente = ''; //Acciones de gerente
if ($esGerente) { //Si es gerente
    $urlEditar = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/actualizarRecompensa.php?id='.urlencode((string)$id), ENT_QUOTES, 'UTF-8'); //URL editar
    $urlBorrar = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/borrarRecompensa.php?id='.urlencode((string)$id), ENT_QUOTES, 'UTF-8'); //URL borrar
    $accionesGerente = <<<EOS
        <a href="$urlEditar" class="button-estandar">Actualizar</a>
        <a href="$urlBorrar" class="button-estandar">Borrar</a>
    EOS; //Botones de gerente
}
$tituloPagina = 'Visualizar recompensa'; //Titulo de la pagina
$contenidoPrincipal = <<<EOS
<div class="contenedor-producto">
    <div class="info-producto">
        <h1>Recompensa #$id</h1>
        <p><strong>Producto:</strong> $nombreProducto</p>
        <p><strong>BistroCoins necesarios:</strong> $bistroCoins BC</p>
    </div>
</div>
<div class="buttons-estandar">
    <a href="$urlVolver" class="button-estandar">Volver</a>
    $accionesGerente
</div>
EOS; //HTML principal

require __DIR__.'/../plantillas/plantilla.php'; //Carga plantilla
