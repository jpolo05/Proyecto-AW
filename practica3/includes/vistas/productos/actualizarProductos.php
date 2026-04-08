<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$producto = $id > 0 ? Producto::buscaPorId($id) : null;

if (!$producto) {
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+no+encontrado');
    exit;
}

$error = '';
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF invalido.';
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $precioBase = (float)($_POST['precio_base'] ?? 0);
    $iva = (int)($_POST['iva'] ?? 10);
    $disponible = isset($_POST['disponible']);
    $ofertado = isset($_POST['ofertado']);
    $imagen = trim($_POST['imagen'] ?? '');

    if ($error === '' && ($nombre === '' || $descripcion === '' || $precioBase <= 0 || !in_array($iva, [4, 10, 21], true))) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Producto::actualizar(
            $id,
            $nombre,
            $descripcion,
            $idCategoria > 0 ? $idCategoria : null,
            $precioBase,
            $iva,
            $disponible,
            $ofertado,
            $imagen !== '' ? $imagen : null
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+actualizado');
            exit;
        }

        $error = 'No se pudo actualizar el producto.';
    }
}

$categorias = Categoria::listar();
$tituloPagina = 'Actualizar producto';

$idCatActual = (int)($producto['id_categoria'] ?? 0);
$nombre = htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$precioBase = number_format((float)($producto['precio_base'] ?? 0), 2, '.', '');
$ivaActual = (int)($producto['iva'] ?? 10);
$imagen = htmlspecialchars($producto['imagen'] ?? '', ENT_QUOTES, 'UTF-8');
$disponibleChecked = ((int)($producto['disponible'] ?? 0) === 1) ? 'checked' : '';
$ofertadoChecked = ((int)($producto['ofertado'] ?? 0) === 1) ? 'checked' : '';

$opcionesCategorias = '<option value="0">Sin categoria</option>';
foreach ($categorias as $cat) {
    $idCat = (int)$cat['id'];
    $nombreCat = htmlspecialchars($cat['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $sel = $idCat === $idCatActual ? 'selected' : '';
    $opcionesCategorias .= "<option value='{$idCat}' {$sel}>{$nombreCat}</option>";
}

$sel4 = $ivaActual === 4 ? 'selected' : '';
$sel10 = $ivaActual === 10 ? 'selected' : '';
$sel21 = $ivaActual === 21 ? 'selected' : '';

$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
$action = htmlspecialchars(RUTA_APP.'includes/vistas/productos/actualizarProductos.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/productos/listarProductos.php', ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Actualizar producto #{$id}</h1>
</div>

<div class="info-categoria">
    $errorHtml
    <form method="POST" action="$action" class="form-estandar">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$id}">

        <div class="campo-form">
            <label for="nombre"><p><strong>Nombre:</strong></p></label>
            <input type="text" id="nombre" name="nombre" value="$nombre" required>
        </div>

        <div class="campo-form">
            <label for="descripcion"><p><strong>Descripción:</strong></p></label>
            <textarea id="descripcion" name="descripcion" rows="4" required>$descripcion</textarea>
        </div>

        <div class="campo-form">
            <label for="id_categoria"><p><strong>Categoría:</strong></p></label>
            <select name="id_categoria" id="id_categoria">
                $opcionesCategorias
            </select>
        </div>

        <div class="campo-form">
            <label for="precio_base"><p><strong>Precio base:</strong></p></label>
            <input type="number" step="0.01" min="0.01" name="precio_base" id="precio_base" value="$precioBase" required>
        </div>

        <div class="campo-form">
            <label for="iva"><p><strong>IVA (%):</strong></p></label>
            <select name="iva" id="iva">
                <option value="4" $sel4>4</option>
                <option value="10" $sel10>10</option>
                <option value="21" $sel21>21</option>
            </select>
        </div>

        <div class="campo-form">
            <label for="precio_final"><p><strong>Precio final:</strong></p></label>
            <input type="text" id="precio_final" data-sufijo=" EUR" readonly class="input-solo-lectura">
        </div>

        <div class="campo-form">
            <label for="imagen"><p><strong>Imagen:</strong></p></label>
            <input type="text" id="imagen" name="imagen" value="$imagen">
        </div>

        <div class="campo-form-checkbox">
            <label class="checkbox-item">
                <input type="checkbox" name="disponible" $disponibleChecked> <strong>Disponible</strong>
            </label>
            <label class="checkbox-item">
                <input type="checkbox" name="ofertado" $ofertadoChecked> <strong>Ofertado</strong>
            </label>
        </div>

        </div> <div class="buttons-estandar">
            <button type="submit" class="button-estandar">Guardar cambios</button>
            <a href="$urlCancelar" class="button-estandar">Cancelar</a>
        </div>
    </form>

EOS;

$funcionesJS = "<script src='".RUTA_JS."productosForm.js'></script>";

require __DIR__.'/../plantillas/plantilla.php';


