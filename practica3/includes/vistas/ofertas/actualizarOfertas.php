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
        $error = 'Token CSRF invalido.';
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
        $ok = Oferta::actualizar(
            $id,
            $nombre,
            $descripcion,
            $comienzo !== '' ? $comienzo : null,
            $fin !== '' ? $fin : null,
            $descuento,
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

// Preparar valores actuales
$nombre = htmlspecialchars($oferta['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($oferta['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$comienzo = htmlspecialchars($oferta['comienzo'] ?? '', ENT_QUOTES, 'UTF-8');
$fin = htmlspecialchars($oferta['fin'] ?? '', ENT_QUOTES, 'UTF-8');
$descuentoActual = number_format((float)($oferta['descuento'] ?? 0), 2, '.', '');
$lineasActuales = $oferta['lineas'] ?? [];

// Preparar opciones de productos
$opcionesProductos = '';
foreach ($productos as $p) {
    $nombreP = htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8');
    $idP = $p['id'];
    $opcionesProductos .= "<option value='$idP'>$nombreP</option>";
}

// Preparar HTML para las líneas actuales
$lineasHtml = '';
foreach ($lineasActuales as $linea) {
    $idProd = (int)$linea['idProd'];
    $cantidad = (int)$linea['cantidad'];
    
    $selectHtml = '<select name="productos[]" required onchange="recalcularPrecios()">';
    $selectHtml .= '<option value="">Selecciona un producto...</option>';
    foreach ($productos as $p) {
        $nombreP = htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8');
        $idP = (int)$p['id'];
        $sel = $idP === $idProd ? 'selected' : '';
        $selectHtml .= "<option value='$idP' $sel>$nombreP</option>";
    }
    $selectHtml .= '</select>';
    
    $lineasHtml .= '<div class="linea-oferta">';
    $lineasHtml .= $selectHtml;
    $lineasHtml .= "<input type='number' name='cantidades[]' min='1' value='$cantidad' onchange='recalcularPrecios()'>";
    $lineasHtml .= '<button type="button" onclick="this.parentElement.remove(); recalcularPrecios();">Eliminar</button>';
    $lineasHtml .= '</div>';
}

$productosJsonHtml = htmlspecialchars(json_encode($productos), ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
    <h1>Actualizar oferta #{$id}</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$id}">
        
        <fieldset>
            <legend>Datos de la Oferta</legend>
            <p><label>Nombre: <input type="text" name="nombre" value="$nombre" required></label></p>
            <p><label>Descripción: <textarea name="descripcion" required>$descripcion</textarea></label></p>
            <p><label>Comienzo: <input type="date" name="comienzo" value="$comienzo"></label></p>
            <p><label>Fin: <input type="date" name="fin" value="$fin"></label></p>
            <p>Descuento aplicado: <span id="porcentajeMostrado">$descuentoActual</span>%</p>
            <input type="hidden" name="descuento" id="inputDescuento" value="$descuentoActual">
        </fieldset>

        <fieldset>
            <legend>Productos incluidos</legend>
            <div id="contenedor-lineas">
                $lineasHtml
            </div>
            <button type="button" onclick="agregarLinea($productosJsonHtml)">+ Añadir Producto</button>
        </fieldset>
        <div>
            precio previo total: <span id="precioTotal">0</span> €
            precio con descuento: <input type="number" id="precioDescuento" step="0.01" oninput="recalcularDescuento()"> €
        </div>
        
        <div>
            <button type="submit">Guardar cambios</button>
            <a href="$urlCancelar"><button type="button">Cancelar</button></a>
        </div>
    </form>
EOS;
