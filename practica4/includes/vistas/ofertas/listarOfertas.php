<?php
use es\ucm\fdi\aw\usuarios\Oferta;

require_once __DIR__.'/../../config.php';
$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esOfertaCaducada(array $oferta): bool {
    $fin = trim((string)($oferta['fin'] ?? ''));
    if ($fin === '') {
        return false;
    }

    $timestampFin = strtotime($fin);
    if ($timestampFin === false) {
        return false;
    }

    return $timestampFin < time();
}

function renderTablaOfertas(array $ofertas, bool $esGerente): string {
    if (empty($ofertas)) {
        return '<p class="ofertas-texto-centrado">No hay ofertas en esta sección.</p>';
    }

    $tabla = '<table class="tabla-carta-centro">
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

        $idOferta = (int)($o['id'] ?? 0);
        $urlVer = $o['url_ver'] ?? ('visualizarOferta.php?id=' . urlencode((string)$idOferta));
        $acciones = "<a href='{$urlVer}' class='link-usuario'>Ver</a>";
        if ($esGerente) {
            $urlEditar = 'actualizarOfertas.php?id=' . urlencode((string)$idOferta);
            $urlBorrar = 'borrarOfertas.php?id=' . urlencode((string)$idOferta);
            $acciones .= " | <a href='{$urlEditar}' class='link-usuario'>Editar</a>";
            $acciones .= " | <a href='{$urlBorrar}' class='link-usuario'>Borrar</a>";
        }

        $tabla .= "<tr>
                    <td>{$nombre}</td>
                    <td>{$descripcion}</td>
                    <td>{$productosHtml}</td>
                    <td>{$comienzo}</td>
                    <td>{$fin}</td>
                    <td>{$descuento}%</td>
                    <td>{$acciones}</td>
                  </tr>";
    }

    $tabla .= '</tbody></table>';
    return $tabla;
}

$tituloPagina = 'Ofertas';
$msg = $_GET['msg'] ?? '';
$solo = $_GET['solo'] ?? '';
$origen = $_GET['origen'] ?? '';

if ($solo === 'activas' && $origen === '') {
    $origen = 'carta';
}

$ofertas = Oferta::listar();
$ofertasActivas = [];
$ofertasCaducadas = [];

foreach ($ofertas as $oferta) {
    if (esOfertaCaducada($oferta)) {
        $ofertasCaducadas[] = $oferta;
    } else {
        $ofertasActivas[] = $oferta;
    }
}

$mensajeHtml = $msg !== '' ? '<div class="mensaje-alerta"><p><strong>'.h($msg).'</strong></p></div>' : '';

if ($origen === 'carta') {
    foreach ($ofertasActivas as &$ofertaActiva) {
        $idOferta = (int)($ofertaActiva['id'] ?? 0);
        $ofertaActiva['id'] = $idOferta;
        $ofertaActiva['url_ver'] = 'visualizarOferta.php?id=' . urlencode((string)$idOferta) . '&origen=carta';
    }
    unset($ofertaActiva);

    foreach ($ofertasCaducadas as &$ofertaCaducada) {
        $idOferta = (int)($ofertaCaducada['id'] ?? 0);
        $ofertaCaducada['id'] = $idOferta;
        $ofertaCaducada['url_ver'] = 'visualizarOferta.php?id=' . urlencode((string)$idOferta) . '&origen=carta';
    }
    unset($ofertaCaducada);
}

$contenidoPrincipal = '
<div class="seccion-titulo">
    <h1>Ofertas</h1>
</div>' . $mensajeHtml;

$contenidoPrincipal .= '<div class="contenedor-ofertas">';

if ($solo === 'activas') {
    $contenidoPrincipal .= '
    <div class="seccion-ofertas">
        <h2 class="ofertas-texto-centrado">Ofertas activas</h2>' . renderTablaOfertas($ofertasActivas, $esGerente) . '
    </div>';
} else {
    $contenidoPrincipal .= '
    <div class="seccion-ofertas">
        <h2 class="ofertas-texto-centrado">Ofertas activas</h2>' . renderTablaOfertas($ofertasActivas, $esGerente) . '
    </div>
    <div class="seccion-ofertas">
        <h2 class="ofertas-texto-centrado">Ofertas caducadas</h2>' . renderTablaOfertas($ofertasCaducadas, $esGerente) . '
    </div>';
}

if ($esGerente) {
    $urlCrear = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/crearOfertas.php', ENT_QUOTES, 'UTF-8');
    $rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8');
    $contenidoPrincipal .= "
    <div class='buttons-estandar'>
        <a href='$rutaPanelGerente' class='button-estandar'>Volver al Panel</a>
        <a href='$urlCrear' class='button-estandar'>Crear oferta</a>
    </div>";
}

$contenidoPrincipal .= '</div>';

require __DIR__.'/../plantillas/plantilla.php';
