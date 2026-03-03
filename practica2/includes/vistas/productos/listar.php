<?php
require_once __DIR__.'/../../auth.php';
verificarAcceso('Gerente');

require_once __DIR__ . '/../../mysql/producto_mysql.php';
$prods = productos_listar();

$tituloPagina = 'Listado Productos';

$tablaProductos = '
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio Base</th>
        </tr>';

foreach ($prods as $p) {
    $id = (int)$p['id'];
    $nombre = $p['nombre'];
    $descripcion = $p['descripcion'];
    $precioBase = $p['precio_base'];

    $tablaProductos .= "
    <tr>
        <td>$id</td>
        <td>$nombre</td>
        <td>$descripcion</td>
        <td>$precioBase</td>
    </tr>";
}
$tablaProductos .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Productos</h1>
    $tablaProductos
EOS;

require __DIR__.'/../plantillas/plantilla.php';

