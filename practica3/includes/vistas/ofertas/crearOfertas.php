<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Oferta;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');
$productos = Producto::listarNombres();

$error = '';
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF inválido.';
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $comienzo = trim($_POST['comienzo'] ?? '');
    $fin = trim($_POST['fin'] ?? '');
    $productosElegidos = $_POST['productos'] ?? [];
    $cantidadesElegidas = $_POST['cantidades'] ?? [];
    $descuento = (float)($_POST['descuento'] ?? 0.00);

    if ($error === '' && ($nombre === '' || $descripcion === '')) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Oferta::crear(
            $nombre,
            $descripcion,
            $comienzo !== '' ? $comienzo : null,
            $fin !== '' ? $fin : null,
            $descuento,
            $productosElegidos,
            $cantidadesElegidas
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+creada');
            exit;
        }

        $error = 'No se pudo crear la oferta.';
    }
}

$tituloPagina = 'Crear oferta';
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
$action = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/crearOfertas.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/listarOfertas.php', ENT_QUOTES, 'UTF-8');
$rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8');
$productosJsonHtml = htmlspecialchars(json_encode($productos), ENT_QUOTES, 'UTF-8');
$productosJsonJs = json_encode($productos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$contenidoPrincipal = <<<EOS
    <h1>Crear nueva oferta</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">

        <h2>Datos de la oferta</h2>
        <p><label>Nombre: <input type="text" name="nombre" required></label></p>
        <p><label>Descripción: <textarea name="descripcion" required></textarea></label></p>
        <p><label>Comienzo: <input type="date" name="comienzo"></label></p>
        <p><label>Fin: <input type="date" name="fin"></label></p>
        <p>Descuento aplicado: <span id="porcentajeMostrado">0</span>%</p>
        <input type="hidden" name="descuento" id="inputDescuento" value="0.00">

        <h2>Productos incluidos</h2>
        <div id="contenedor-lineas"></div>
        <p><button type="button" onclick="agregarLinea($productosJsonHtml)">+ Añadir producto</button></p>

        <h2>Resumen</h2>
        <p>
            Precio previo total: <span id="precioTotal">0</span> €
            Precio con descuento: <input type="number" id="precioDescuento" step="0.01" min="0" oninput="recalcularDescuento()"> €
        </p>

        <p>
            <button type="submit">Guardar oferta</button>
            <button type="button" onclick="window.location.href='$urlCancelar'">Cancelar</button>
        </p>
    </form>
    <p><a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a></p>
EOS;

$rutaJs = RUTA_JS . 'crearOfertas.js';
$funcionesJS = "<script src='$rutaJs'></script><script>document.addEventListener('DOMContentLoaded', function () { if (document.getElementById('contenedor-lineas') && document.getElementsByName('productos[]').length === 0) { agregarLinea($productosJsonJs); } });</script>";

require __DIR__.'/../plantillas/plantilla.php';
