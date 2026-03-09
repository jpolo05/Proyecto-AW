<?php
use es\ucm\fdi\aw\Auth;
use es\ucm\fdi\aw\Categoria;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

$cats = Categoria::listar();

$tituloPagina = 'Listado CategorÃƒÂ­as';

$tablaCategorias = '
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>DescripciÃƒÂ³n</th>
        </tr>';

foreach ($cats as $c) {
    $id = (int)$c['id'];
    $nombre = $c['nombre'];
    $descripcion = $c['descripcion'];

    $tablaCategorias .= "
    <tr>
        <td>$id</td>
        <td>$nombre</td>
        <td>$descripcion</td>
    </tr>";
}
$tablaCategorias .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>CategorÃƒÂ­as</h1>
    $tablaCategorias
EOS;

require __DIR__.'/../plantillas/plantilla.php';






