<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

$cats = Categoria::listar();

$tituloPagina = 'Listado Categorias';
//border="1" cellpadding="6"
$tablaCategorias = '
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripcion</th>
            <th>Acciones</th>
        </tr>';

foreach ($cats as $c) {
    $id = (int)$c['id'];
    $nombre = htmlspecialchars($c['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars($c['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
    $urlVer = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/visualizarCategorias.php?id='.$id, ENT_QUOTES, 'UTF-8');

    $tablaCategorias .= "
    <tr>
        <td>$id</td>
        <td>$nombre</td>
        <td>$descripcion</td>
        <td><a href=\"$urlVer\">Ver</a></td>
    </tr>";
}
$tablaCategorias .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Categorias</h1>
    $tablaCategorias
EOS;

require __DIR__.'/../plantillas/plantilla.php';






