<?php

require_once __DIR__ . '/../../mysql/categoria_mysql.php';
$cats = categorias_listar();

$tituloPagina = 'Listado Categorías';

$tablaCategorias = '
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
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
    <h1>Categorías</h1>
    $tablaCategorias
EOS;

require __DIR__.'/../plantillas/plantilla.php';

