<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Oferta;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$csrfToken = Auth::getCsrfToken();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$oferta = $id > 0 ? Oferta::buscaPorId($id) : null;

if (!$oferta) {
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+no+encontrada');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $msg = rawurlencode('Token CSRF inválido');
    } else {
        $ok = Oferta::borrar($id);
        $msg = $ok ? 'Oferta+borrada' : 'No+se+pudo+borrar+la+oferta';
    }

    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg='.$msg);
    exit;
}

$tituloPagina = 'Borrar oferta';

$idMostrado = (int) ($oferta['id'] ?? 0);
$nombre = h((string) ($oferta['nombre'] ?? ''));
$descripcion = h((string) ($oferta['descripcion'] ?? ''));
$comienzo = h((string) ($oferta['comienzo'] ?? ''));
$fin = h((string) ($oferta['fin'] ?? ''));
$descuento = number_format((float) ($oferta['descuento'] ?? 0), 2, ',', '.');
$lineas = $oferta['lineas'] ?? [];
$action = h(RUTA_APP.'includes/vistas/ofertas/borrarOfertas.php');
$urlCancelar = h(RUTA_APP.'includes/vistas/ofertas/visualizarOferta.php?id='.$idMostrado);

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
    <h1>Borrar oferta</h1>
    <p>Esta acción eliminará la oferta de la base de datos y sus líneas asociadas.</p>
    <ul>
        <li><strong>ID:</strong> {$idMostrado}</li>
        <li><strong>Nombre:</strong> {$nombre}</li>
        <li><strong>Descripción:</strong> {$descripcion}</li>
        <li><strong>Comienzo:</strong> {$comienzo}</li>
        <li><strong>Fin:</strong> {$fin}</li>
        <li><strong>Descuento:</strong> {$descuento}%</li>
    </ul>
    <h2>Productos incluidos</h2>
    <ul>
        {$productosHtml}
    </ul>
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$idMostrado}">
        <button type="submit" class="button-estandar">Confirmar</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
