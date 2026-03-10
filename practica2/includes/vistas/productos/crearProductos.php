<?php
use es\ucm\fdi\aw\Auth;
use es\ucm\fdi\aw\Categoria;
use es\ucm\fdi\aw\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

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

    $archivoSubido = $_FILES['imagenArchivo'] ?? null;
    if ($error === '' && $archivoSubido && is_array($archivoSubido)) {
        if (($archivoSubido['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tamMax = 2 * 1024 * 1024; // 2MB
            $tam = (int)($archivoSubido['size'] ?? 0);
            if ($tam <= 0 || $tam > $tamMax) {
                $error = 'La imagen debe pesar entre 1 byte y 2MB.';
            } else {
                $tmp = $archivoSubido['tmp_name'] ?? '';
                $nombreOriginal = (string)($archivoSubido['name'] ?? '');
                $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (!in_array($ext, $permitidas, true)) {
                    $error = 'Formato de imagen no permitido.';
                } elseif (!is_uploaded_file($tmp)) {
                    $error = 'Error al procesar la imagen subida.';
                } else {
                    $uploadsDir = dirname(RAIZ_APP).'/img/uploads/productos';
                    if (!is_dir($uploadsDir)) {
                        mkdir($uploadsDir, 0775, true);
                    }

                    try {
                        $nombreFichero = bin2hex(random_bytes(16)).'.'.$ext;
                    } catch (\Throwable $e) {
                        $nombreFichero = uniqid('prod_', true).'.'.$ext;
                    }
                    $destino = $uploadsDir.'/'.$nombreFichero;

                    if (!move_uploaded_file($tmp, $destino)) {
                        $error = 'No se pudo guardar la imagen subida.';
                    } else {
                        $imagen = 'img/uploads/productos/'.$nombreFichero;
                    }
                }
            }
        } elseif (($archivoSubido['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $error = 'Fallo al subir la imagen.';
        }
    }

    if ($error === '' && ($nombre === '' || $descripcion === '' || $precioBase <= 0 || !in_array($iva, [4, 10, 21], true))) {
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
            $imagen !== '' ? $imagen : null
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
    <h1>Crear producto</h1>
    $errorHtml
    <form method="POST" action="$action" enctype="multipart/form-data">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <p><label>Nombre: <input type="text" name="nombre" required></label></p>
        <p><label>Descripcion: <textarea name="descripcion" required></textarea></label></p>
        <p><label>Categoria:
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
        <p><label>Precio final: <input type="text" id="precio_final" readonly></label></p>
        <p><label>Imagen desde ordenador: <input type="file" name="imagenArchivo" accept=".jpg,.jpeg,.png,.webp,.gif"></label></p>
        <p><label>Imagen (ruta relativa o URL): <input type="text" name="imagen"></label></p>
        <p><label><input type="checkbox" name="disponible" checked> Disponible</label></p>
        <p><label><input type="checkbox" name="ofertado" checked> Ofertado</label></p>
        <p>
            <button type="submit">Guardar</button>
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


