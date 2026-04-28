<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$error = '';
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF inválido.';
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $precioBase = (float)($_POST['precio_base'] ?? 0);
    $iva = (int)($_POST['iva'] ?? 10);
    $disponible = isset($_POST['disponible']);
    $ofertado = isset($_POST['ofertado']);

    $imagenFinal = '';

    if (isset($_FILES['imagenArchivo']) && $_FILES['imagenArchivo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['imagenArchivo']['error'] !== UPLOAD_ERR_OK) {
           $error = 'Error al subir la imagen.';
        } else {
            $archivo = $_FILES['imagenArchivo'];
            $mimesPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
            ];

            if (!is_uploaded_file($archivo['tmp_name'])) {
                $error = 'Fichero de subida no valido.';
            } elseif ($archivo['size'] > 2000000) {
                $error = 'La imagen es demasiado grande (maximo 2MB).';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeReal = $finfo ? finfo_file($finfo, $archivo['tmp_name']) : false;
                if ($finfo) {
                    finfo_close($finfo);
                }

                if ($mimeReal === false || !isset($mimesPermitidos[$mimeReal])) {
                    $error = 'Formato de imagen no permitido (solo JPG o PNG).';
                } elseif (@getimagesize($archivo['tmp_name']) === false) {
                    $error = 'El archivo subido no es una imagen valida.';
                } else {
                    $extensionSegura = $mimesPermitidos[$mimeReal];
                    $nuevoNombre = uniqid('img_', true) . '.' . $extensionSegura;

                    $rutaRelativaDestino = 'img/uploads/productos/' . $nuevoNombre;
                    $rutaDestinoFisica = dirname(RAIZ_APP) . '/' . $rutaRelativaDestino;

                    if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoFisica)) {
                        $imagenFinal = $rutaRelativaDestino;
                    } else {
                        $error = 'Error al guardar la imagen. Revisa los permisos de la carpeta.';
                    }
                }
            }
        }
    }

    if ($error !== '' && ($nombre === '' || $descripcion === '' || $precioBase <= 0 || !in_array($iva, [4, 10, 21], true))) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Producto::crear(
            $nombre,
            $descripcion,
            $idCategoria > 0 ? $idCategoria : null,
            $precioBase,
            $iva,
            $disponible,
            $ofertado,
            $imagenFinal !== '' ? $imagenFinal : null
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+creado');
            exit;
        }

        $error = 'No se pudo crear el producto.';
    }
}

$categorias = Categoria::listar();
$tituloPagina = 'Crear producto';

$opcionesCategorias = '<option value="0">Sin categoria</option>';
foreach ($categorias as $cat) {
    $idCat = (int)$cat['id'];
    $nombreCat = htmlspecialchars($cat['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $opcionesCategorias .= "<option value='{$idCat}'>{$nombreCat}</option>";
}

$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
$action = htmlspecialchars(RUTA_APP.'includes/vistas/productos/crearProductos.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/productos/listarProductos.php', ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
    <h2>Crear producto</h2>
    $errorHtml
    <form method="POST" action="$action" enctype="multipart/form-data">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <p><label>Nombre: <input type="text" name="nombre" required></label></p>
        <p><label>Descripción: <textarea name="descripcion" required></textarea></label></p>
        <p><label>Categoría:
            <select name="id_categoria">
                $opcionesCategorias
            </select>
        </label></p>
        <p><label>Precio base: <input type="number" step="0.01" min="0.01" name="precio_base" id="precio_base" required></label></p>
        <p><label>IVA:
            <select name="iva" id="iva">
                <option value="4">4</option>
                <option value="10" selected>10</option>
                <option value="21">21</option>
            </select>
        </label></p>
        <p><label>Precio final: <input type="text" id="precio_final" data-sufijo="" readonly></label></p>
        <p><label>Imagen: <input type="file" name="imagenArchivo" accept=".jpg,.jpeg,.png"></label></p>
        <p><label><input type="checkbox" name="disponible" checked> Disponible</label></p>
        <p><label><input type="checkbox" name="ofertado" checked> Ofertado</label></p>
        <p>
            <button type="submit" class="button-estandar">Guardar</button>
            <a href="$urlCancelar" class="button-estandar">Cancelar</a>
        </p>
    </form>
EOS;

$funcionesJS = "<script src='".RUTA_JS."productosForm.js'></script>";

require __DIR__.'/../plantillas/plantilla.php';


