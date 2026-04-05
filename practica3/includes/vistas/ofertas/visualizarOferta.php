<?php
use es\ucm\fdi\aw\usuarios\Oferta;

require_once __DIR__.'/../../config.php';

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+invalida');
    exit;
}

$oferta = Oferta::buscaPorId($id);
if (!$oferta) {
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+no+encontrada');
    exit;
}

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');
$tituloPagina = 'Visualizar oferta';

$nombre = h((string) ($oferta['nombre'] ?? ''));
$descripcion = h((string) ($oferta['descripcion'] ?? ''));
$comienzo = h((string) ($oferta['comienzo'] ?? ''));
$fin = h((string) ($oferta['fin'] ?? ''));
$descuento = number_format((float) ($oferta['descuento'] ?? 0), 2, ',', '.');
$lineas = $oferta['lineas'] ?? [];
$urlVolver = h(RUTA_APP.'includes/vistas/ofertas/listarOfertas.php');
$accionesGerente = '';

if ($esGerente) {
    $urlEditar = h(RUTA_APP.'includes/vistas/ofertas/actualizarOfertas.php?id='.$id);
    $urlBorrar = h(RUTA_APP.'includes/vistas/ofertas/borrarOfertas.php?id='.$id);
    $accionesGerente = <<<EOS
    <p>
        <a href="$urlEditar" class="button-estandar">Editar oferta</a>
        <a href="$urlBorrar" class="button-estandar">Borrar oferta</a>
    </p>
EOS;
}

$productosHtml = '<li>Sin productos asociados</li>';
if (!empty($lineas)) {
    $items = [];
    foreach ($lineas as $linea) {
        $producto = h((string) ($linea['producto'] ?? ''));
        $cantidadLinea = (int) ($linea['cantidad'] ?? 1);
        $items[] = "<li>{$producto} ({$cantidadLinea})</li>";
    }
    $productosHtml = implode('', $items);
}

$contenidoPrincipal = <<<EOS
    <h1>Oferta #{$id}</h1>
    <p><strong>Nombre:</strong> {$nombre}</p>
    <p><strong>Descripcion:</strong> {$descripcion}</p>
    <p><strong>Comienzo:</strong> {$comienzo}</p>
    <p><strong>Fin:</strong> {$fin}</p>
    <p><strong>Descuento:</strong> {$descuento}%</p>
    <h2>Productos incluidos</h2>
    <ul>
        {$productosHtml}
    </ul>
    {$accionesGerente}
    <p><a href="$urlVolver" class="button-estandar">Volver</a></p>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
