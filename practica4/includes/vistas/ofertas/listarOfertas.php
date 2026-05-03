<?php
use es\ucm\fdi\aw\usuarios\Oferta; //Usa la clase Oferta

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente'); //Comprueba si es Gerente

//Funcion para limpiar el texto (seguridad)
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

//Comprueba si una oferta esta caducada
function esOfertaCaducada(array $oferta): bool {
    $fin = trim((string)($oferta['fin'] ?? ''));
    if ($fin === '') { //Si no tiene fecha de fin no esta caducada
        return false;
    }

    $timestampFin = strtotime($fin); //Convierte fecha de fin a timestamp
    if ($timestampFin === false) { //Si la fecha no es valida no la trata como caducada
        return false;
    }

    return $timestampFin < time(); //Compara fecha de fin con la fecha actual
}

//Crea la tabla HTML de ofertas
function renderTablaOfertas(array $ofertas, bool $esGerente): string {
    if (empty($ofertas)) { //Si no hay ofertas
        return '<p class="ofertas-texto-centrado">No hay ofertas en esta sección.</p>';
    }

    //Empieza a crear la tabla HTML
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

    foreach ($ofertas as $o) { //Recorre cada oferta
        //Recoge datos
        $nombre = h((string)($o['nombre'] ?? ''));
        $descripcion = h((string)($o['descripcion'] ?? ''));
        $productos = $o['lineas'] ?? []; //Productos incluidos en la oferta
        $comienzo = h((string)($o['comienzo'] ?? ''));
        $fin = h((string)($o['fin'] ?? ''));
        $descuento = (float)($o['descuento'] ?? 0); //Descuento de la oferta

        $productosHtml = '<ul class="lista-tabla">'; //Empieza lista de productos
        if (empty($productos)) { //Si no hay productos
            $productosHtml .= '<li>Sin productos</li>';
        } else {
            foreach ($productos as $p) { //Recorre productos de la oferta
                $pNombre = h((string)($p['producto'] ?? ''));
                $cantidad = (int)($p['cantidad'] ?? 0); //Cantidad necesaria de ese producto
                $productosHtml .= "<li>{$pNombre} ({$cantidad})</li>"; //Añade producto a la lista
            }
        }
        $productosHtml .= '</ul>';

        $idOferta = (int)($o['id'] ?? 0);
        $urlVer = $o['url_ver'] ?? ('visualizarOferta.php?id=' . urlencode((string)$idOferta)); //Crea enlace para ver oferta
        $acciones = "<a href='{$urlVer}' class='link-usuario'>Ver</a>";
        if ($esGerente) { //Si es gerente añade editar y borrar
            $urlEditar = 'actualizarOfertas.php?id=' . urlencode((string)$idOferta); //URL para editar oferta
            $urlBorrar = 'borrarOfertas.php?id=' . urlencode((string)$idOferta); //URL para borrar oferta
            $acciones .= " | <a href='{$urlEditar}' class='link-usuario'>Editar</a>";
            $acciones .= " | <a href='{$urlBorrar}' class='link-usuario'>Borrar</a>";
        }

        //Añade 1 fila a la tabla
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

    $tabla .= '</tbody></table>'; //Cierra la tabla HTML
    return $tabla;
}

$tituloPagina = 'Ofertas';
$msg = $_GET['msg'] ?? ''; //Recoge mensaje de la URL
$solo = $_GET['solo'] ?? ''; //Recoge filtro de ofertas
$origen = $_GET['origen'] ?? ''; //Recoge desde donde viene el usuario

if ($solo === 'activas' && $origen === '') { //Si solo pide activas y no hay origen, usa carta
    $origen = 'carta';
}

$ofertas = Oferta::listar(); //Llama a listar (devuelve un array)
$ofertasActivas = [];
$ofertasCaducadas = [];

foreach ($ofertas as $oferta) { //Recorre todas las ofertas
    if (esOfertaCaducada($oferta)) { //Si esta caducada
        $ofertasCaducadas[] = $oferta; //Guarda oferta en caducadas
    } else {
        $ofertasActivas[] = $oferta; //Guarda oferta en activas
    }
}

$mensajeHtml = $msg !== '' ? '<div class="mensaje-alerta"><p><strong>'.h($msg).'</strong></p></div>' : ''; //Prepara mensaje si llega por URL

if ($origen === 'carta') { //Si viene de la carta ajusta las URLs de volver
    foreach ($ofertasActivas as &$ofertaActiva) { //Recorre ofertas activas
        $idOferta = (int)($ofertaActiva['id'] ?? 0);
        $ofertaActiva['id'] = $idOferta; //Normaliza el id
        $ofertaActiva['url_ver'] = 'visualizarOferta.php?id=' . urlencode((string)$idOferta) . '&origen=carta'; //Añade origen para volver a carta
    }
    unset($ofertaActiva);

    foreach ($ofertasCaducadas as &$ofertaCaducada) { //Recorre ofertas caducadas
        $idOferta = (int)($ofertaCaducada['id'] ?? 0);
        $ofertaCaducada['id'] = $idOferta; //Normaliza el id
        $ofertaCaducada['url_ver'] = 'visualizarOferta.php?id=' . urlencode((string)$idOferta) . '&origen=carta'; //Añade origen para volver a carta
    }
    unset($ofertaCaducada);
}

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = '
<div class="seccion-titulo">
    <h1>Ofertas</h1>
</div>' . $mensajeHtml;

$contenidoPrincipal .= '<div class="contenedor-ofertas">';

if ($solo === 'activas') { //Si solo quiere ver ofertas activas
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

if ($esGerente) { //Si es gerente muestra acciones de gestion
    $urlCrear = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/crearOfertas.php', ENT_QUOTES, 'UTF-8'); //URL para crear oferta
    $rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8'); //URL para volver al panel
    $contenidoPrincipal .= "
    <div class='buttons-estandar'>
        <a href='$rutaPanelGerente' class='button-estandar'>Volver al Panel</a>
        <a href='$urlCrear' class='button-estandar'>Crear oferta</a>
    </div>";
}

$contenidoPrincipal .= '</div>';

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
