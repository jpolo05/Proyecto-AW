<?php
use es\ucm\fdi\aw\Categoria;
use es\ucm\fdi\aw\Producto;

require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Gerente');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$producto = $id > 0 ? Producto::buscaPorId($id) : null;

if (!$producto) {
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+no+encontrado');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $precioBase = (float)($_POST['precio_base'] ?? 0);
    $iva = (int)($_POST['iva'] ?? 10);
    $disponible = isset($_POST['disponible']);
    $ofertado = isset($_POST['ofertado']);
    $imagen = trim($_POST['imagen'] ?? '');

    if ($nombre === '' || $descripcion === '' || $precioBase <= 0 || !in_array($iva, [4, 10, 21], true)) {
        $error = 'Revisa los datos del formulario.';
    } else {
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
    <h1>Actualizar producto #{$id}</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="id" value="{$id}">
        <p><label>Nombre: <input type="text" name="nombre" value="$nombre" required></label></p>
        <p><label>Descripcion: <textarea name="descripcion" required>$descripcion</textarea></label></p>
        <p><label>Categoria:
            <select name="id_categoria">
                $opcionesCategorias
            </select>
        </label></p>
        <p><label>Precio base: <input type="number" step="0.01" min="0.01" name="precio_base" id="precio_base" value="$precioBase" required></label></p>
        <p><label>IVA:
            <select name="iva" id="iva">
                <option value="4" $sel4>4</option>
                <option value="10" $sel10>10</option>
                <option value="21" $sel21>21</option>
            </select>
        </label></p>
        <p><label>Precio final: <input type="text" id="precio_final" readonly></label></p>
        <p><label>Imagen (ruta relativa o URL): <input type="text" name="imagen" value="$imagen"></label></p>
        <p><label><input type="checkbox" name="disponible" $disponibleChecked> Disponible</label></p>
        <p><label><input type="checkbox" name="ofertado" $ofertadoChecked> Ofertado</label></p>
        <p>
            <button type="submit">Guardar cambios</button>
            <a href="$urlCancelar"><button type="button">Cancelar</button></a>
        </p>
    </form>
    <script>
        (function () {
            const base = document.getElementById('precio_base');
            const iva = document.getElementById('iva');
            const total = document.getElementById('precio_final');
            function recalcula() {
                const b = parseFloat(base.value || '0');
                const i = parseFloat(iva.value || '0');
                const r = b + (b * i / 100);
                total.value = r.toFixed(2);
            }
            base.addEventListener('input', recalcula);
            iva.addEventListener('change', recalcula);
            recalcula();
        })();
    </script>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
