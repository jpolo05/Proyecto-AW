<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Oferta;

require_once __DIR__.'/../../config.php';

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$tituloPagina = 'Ofertas';
$msg = $_GET['msg'] ?? '';

$ofertas = Oferta::listar();
$mensajeHtml = $msg !== '' ? '<p><strong>'.h($msg).'</strong></p>' : '';

if (empty($ofertas)) {
    $contenidoPrincipal = "<h1>Ofertas</h1>{$mensajeHtml}<p>No hay ofertas registradas actualmente.</p>";
} else {
    $tabla = '<table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Comienzo</th>
                        <th>Fin</th>
                        <th>Descuento</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($ofertas as $o) {
        $nombre = h((string)($o['nombre'] ?? ''));
        $descripcion = h((string)($o['descripcion'] ?? ''));
        $productos = $o['lineas'] ?? [];
        $comienzo = h((string)($o['comienzo'] ?? ''));
        $fin = h((string)($o['fin'] ?? ''));
        $descuento = (float)($o['descuento'] ?? 0);
        
        $productosHtml = '<ul>';
        if (empty($productos)) {
            $productosHtml .= '<li>Sin productos</li>';
        } else {
            foreach ($productos as $p) {
                $pNombre = h((string)($p['producto'] ?? ''));
                $cantidad = (int)($p['cantidad'] ?? 0);
                $productosHtml .= "<li>{$pNombre} ({$cantidad})</li>";
            }
        }
        $productosHtml .= '</ul>';

        $urlVer = 'visualizarOferta.php?id=' . urlencode($o['id']);
        
        $tabla .= "<tr>
                    <td>{$nombre}</td>
                    <td>{$descripcion}</td>
                    <td>{$productosHtml}</td>
                    <td>{$comienzo}</td>
                    <td>{$fin}</td>
                    <td>{$descuento}%</td>
                    <td><a href='{$urlVer}' class='button-estandar'>Ver oferta</a></td>
                  </tr>";
    }

    $tabla .= '</tbody></table>';
    $contenidoPrincipal = "<h1>Ofertas</h1>{$mensajeHtml}" . $tabla;
}

require __DIR__.'/../plantillas/plantilla.php';
