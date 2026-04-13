<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');
$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');

$cats = Categoria::listar();
$tituloPagina = 'Listado Categorias';

$tablaCategorias = '
    <table class="tabla-carta-centro">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>';

foreach ($cats as $c) {
    $id = (int)$c['id'];
    $nombre = htmlspecialchars($c['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars($c['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
    $urlVer = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/visualizarCategorias.php?id='.$id, ENT_QUOTES, 'UTF-8');
    $acciones = "<a href=\"$urlVer\" class='link-usuario'>Ver</a>";

    if ($esGerente) {
        $urlActualizar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/actualizarCategorias.php?id='.$id, ENT_QUOTES, 'UTF-8');
        $acciones .= " <span class='separador-tabla'> / </span> ";
        $acciones .= " <a href=\"$urlActualizar\" class='link-usuario'>Actualizar</a>";
        $acciones .= " <span class='separador-tabla'> / </span> ";
        $urlEliminar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/borrarCategorias.php?id='.$id, ENT_QUOTES, 'UTF-8');
        $acciones .= " <a href=\"$urlEliminar\" class='link-usuario js-confirmar-accion' data-confirm='¿Confirma que desea eliminar esta categoría?'>Eliminar</a>";
    }

    $tablaCategorias .= "
    <tr>
        <td>$id</td>
        <td>$nombre</td>
        <td>$descripcion</td>
        <td>$acciones</td>
    </tr>";
}
$tablaCategorias .= '</table>';

if ($esGerente) {
    $urlCrear = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/crearCategorias.php', ENT_QUOTES, 'UTF-8');
    $rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8');
    $aux = '';
} else {
    $urlCrear = '#';
    $rutaPanelGerente = '#';
    $aux = 'none';
}

$botonPanel = $esGerente ? '<a href="'.$rutaPanelGerente.'" class="button-estandar">Volver al Panel</a>' : '';

$contenidoPrincipal = <<<EOS
    <div class="seccion-titulo">
        <h1>Categorías</h1>
    </div>
    $tablaCategorias
    <div class="buttons-estandar">
        $botonPanel
        <a href="$urlCrear" class="button-estandar $aux">Crear categoría</a>
    </div>
EOS;

$funcionesJS = "<script src='".RUTA_JS."confirmaciones.js'></script>";

require __DIR__.'/../plantillas/plantilla.php';
