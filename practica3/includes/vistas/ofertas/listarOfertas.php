<?php
use es\ucm\fdi\aw\usuarios\Oferta;

require_once __DIR__.'/../../config.php';
$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$tituloPagina = 'Ofertas';
$msg = $_GET['msg'] ?? '';

$ofertas = Oferta::listar();
$mensajeHtml = $msg !== '' ? '<div class="mensaje-alerta"><p><strong>'.h($msg).'</strong></p></div>' : '';

$contenidoPrincipal = '
<div class="seccion-titulo">
    <h1>Ofertas</h1>
</div>' . $mensajeHtml;

// Conenido central
if (empty($ofertas)) {
    $contenidoPrincipal .= '<p class="texto-centrado">No hay ofertas registradas actualmente.</p>';
} else {
    $tabla = '<div class="table-container">
                <table>
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
        
        $productosHtml = '<ul class="lista-tabla">';
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
                    <td><a href='{$urlVer}' class='link-usuario'>Ver</a></td>
                  </tr>";
    }

    $tabla .= '</tbody></table></div>';
    $contenidoPrincipal .= $tabla;
}

if($esGerente) {
    $urlCrear = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/crearOfertas.php', ENT_QUOTES, 'UTF-8');
    $rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8');
    $contenidoPrincipal .= "
    <div class='buttons-estandar'>
        <a href='$urlCrear' class='button-estandar'>Crear oferta</a>
        <a href='$rutaPanelGerente' class='button-estandar'>Volver al Panel</a>
    </div>";
}

require __DIR__.'/../plantillas/plantilla.php';
