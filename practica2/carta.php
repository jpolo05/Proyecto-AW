<?php

require_once __DIR__.'/includes/config.php';

$productos = \es\ucm\fdi\aw\Producto::listar();
$tituloPagina = 'Carta';

$tablaProductos = '
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio Base</th>
        </tr>';

foreach ($productos as $p) {
    $id = (int)$p['id'];
    $nombre = $p['nombre'];
    $descripcion = $p['descripcion'];
    $precioBase = $p['precio_base'];

    $tablaProductos .= "
    <tr>
        <td>$id</td>
        <td>$nombre</td>
        <td>$descripcion</td>
        <td>$precioBase €</td>
    </tr>";
}
$tablaProductos .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Carta</h1>
    $tablaProductos
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';