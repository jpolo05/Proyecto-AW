<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Oferta;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$oferta = $id > 0 ? Oferta::buscaPorId($id) : null;

if (!$oferta) {
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+no+encontrada');
    exit;
}

$productos = Producto::listarNombres();
$error = '';
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF inválido.';
    }

    $nombrePost = trim($_POST['nombre'] ?? '');
    $descripcionPost = trim($_POST['descripcion'] ?? '');
    $comienzoPost = trim($_POST['comienzo'] ?? '');
    $finPost = trim($_POST['fin'] ?? '');
    $productosElegidos = $_POST['productos'] ?? [];
    $cantidadesElegidas = $_POST['cantidades'] ?? [];
    $descuentoPost = (float)($_POST['descuento'] ?? 0.00);

    if ($error === '' && ($nombrePost === '' || $descripcionPost === '')) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Oferta::actualizar(
            $id,
            $nombrePost,
            $descripcionPost,
            $comienzoPost !== '' ? $comienzoPost : null,
            $finPost !== '' ? $finPost : null,
            $descuentoPost,
            $productosElegidos,
            $cantidadesElegidas
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+actualizada');
            exit;
        }

        $error = 'No se pudo actualizar la oferta.';
    }
}

$tituloPagina = 'Actualizar oferta';
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
$action = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/actualizarOfertas.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/listarOfertas.php', ENT_QUOTES, 'UTF-8');
$rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8');

$nombre = htmlspecialchars($oferta['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($oferta['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$comienzo = htmlspecialchars($oferta['comienzo'] ?? '', ENT_QUOTES, 'UTF-8');
$fin = htmlspecialchars($oferta['fin'] ?? '', ENT_QUOTES, 'UTF-8');
$descuentoActual = number_format((float)($oferta['descuento'] ?? 0), 2, '.', '');
$lineasActuales = $oferta['lineas'] ?? [];

$lineasHtml = '';
foreach ($lineasActuales as $linea) {
    $idProd = (int)($linea['idProd'] ?? 0);
    $cantidad = (int)($linea['cantidad'] ?? 1);

    $selectHtml = '<select name="productos[]" required>';
    $selectHtml .= '<option value="">Selecciona un producto...</option>';
    foreach ($productos as $p) {
        $nombreP = htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8');
        $idP = (int)$p['id'];
        $sel = $idP === $idProd ? 'selected' : '';
        $selectHtml .= "<option value='$idP' $sel>$nombreP</option>";
    }
    $selectHtml .= '</select>';

    $lineasHtml .= '<div>';
    $lineasHtml .= $selectHtml;
    $lineasHtml .= "<input type='number' name='cantidades[]' min='1' value='$cantidad'>";
    $lineasHtml .= '<button type="button" class="js-eliminar-linea">Eliminar</button>';
    $lineasHtml .= '</div>';
}

$productosJsonHtml = htmlspecialchars(json_encode($productos), ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
    <h1>Actualizar oferta #{$id}</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$id}">

        <h2>Datos de la oferta</h2>
        <p><label>Nombre: <input type="text" name="nombre" value="$nombre" required></label></p>
        <p><label>Descripción: <textarea name="descripcion" required>$descripcion</textarea></label></p>
        <p><label>Comienzo: <input type="date" name="comienzo" value="$comienzo"></label></p>
        <p><label>Fin: <input type="date" name="fin" value="$fin"></label></p>
        <p>Descuento aplicado: <span id="porcentajeMostrado">$descuentoActual</span>%</p>
        <input type="hidden" name="descuento" id="inputDescuento" value="$descuentoActual">

        <h2>Productos incluidos</h2>
        <div id="contenedor-lineas">$lineasHtml</div>
        <p><button type="button" class="js-agregar-linea" data-productos="$productosJsonHtml">+ Añadir producto</button></p>

        <h2>Resumen</h2>
        <p>
            Precio previo total: <span id="precioTotal">0</span> €
            Precio con descuento: <input type="number" id="precioDescuento" step="0.01" min="0">
        </p>

        <p>
            <button type="submit">Guardar cambios</button>
            <button type="button" class="js-cancelar-oferta" data-url="$urlCancelar">Cancelar</button>
        </p>
    </form>
    <p><a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a></p>
EOS;

$rutaJs = RUTA_JS . 'crearOfertas.js';
$funcionesJS = "<script src='$rutaJs'></script>";

require __DIR__.'/../plantillas/plantilla.php';
