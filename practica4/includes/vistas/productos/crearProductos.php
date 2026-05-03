<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Categoria; //Usa la clase Categoria
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente

$error = ''; //Prepara mensaje error
$csrfToken = Auth::getCsrfToken(); //Obtiene un token CSRF (seguridad)

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la pagina se esta cargando por un envio de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Comprueba que el token sea correcto
        $error = 'Token CSRF inválido.';
    }

    //Recoge datos enviados
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $precioBase = (float)($_POST['precio_base'] ?? 0);
    $iva = (int)($_POST['iva'] ?? 10);
    $disponible = isset($_POST['disponible']); //Comprueba si el checkbox disponible esta marcado
    $ofertado = isset($_POST['ofertado']); //Comprueba si el checkbox ofertado esta marcado

    $imagenFinal = ''; //Prepara ruta final de la imagen

    if (isset($_FILES['imagenArchivo']) && $_FILES['imagenArchivo']['error'] !== UPLOAD_ERR_NO_FILE) { //Si se ha subido una imagen
        if ($_FILES['imagenArchivo']['error'] !== UPLOAD_ERR_OK) { //Comprueba errores de subida
           $error = 'Error al subir la imagen.';
        } else {
            $archivo = $_FILES['imagenArchivo'];
            //Tipos de imagen permitidos
            $mimesPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
            ];

            if (!is_uploaded_file($archivo['tmp_name'])) { //Comprueba que sea un fichero subido
                $error = 'Fichero de subida no valido.';
            } elseif ($archivo['size'] > 2000000) { //Comprueba tamaño maximo
                $error = 'La imagen es demasiado grande (maximo 2MB).';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeReal = $finfo ? finfo_file($finfo, $archivo['tmp_name']) : false; //Detecta el MIME real del archivo
                if ($finfo) { //Cierra el recurso si se ha abierto bien
                    finfo_close($finfo);
                }

                if ($mimeReal === false || !isset($mimesPermitidos[$mimeReal])) { //Comprueba formato permitido
                    $error = 'Formato de imagen no permitido (solo JPG o PNG).';
                } elseif (@getimagesize($archivo['tmp_name']) === false) { //Comprueba que sea una imagen real
                    $error = 'El archivo subido no es una imagen valida.';
                } else {
                    $extensionSegura = $mimesPermitidos[$mimeReal]; //Obtiene extension segura segun MIME
                    $nuevoNombre = uniqid('img_', true) . '.' . $extensionSegura; //Genera nombre unico para evitar pisar imagenes

                    $rutaRelativaDestino = 'img/uploads/productos/' . $nuevoNombre; //Ruta que se guardara en la BD
                    $rutaDestinoFisica = dirname(RAIZ_APP) . '/' . $rutaRelativaDestino; //Ruta fisica donde se guarda el archivo

                    if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoFisica)) { //Guarda la imagen en uploads
                        $imagenFinal = $rutaRelativaDestino; //Guarda la ruta final para crear el producto
                    } else {
                        $error = 'Error al guardar la imagen. Revisa los permisos de la carpeta.';
                    }
                }
            }
        }
    }

    if ($error !== '' && ($nombre === '' || $descripcion === '' || $precioBase <= 0 || !in_array($iva, [4, 10, 21], true))) { //Comprueba errores
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Producto::crear( //Si no hay errores llama a crear
            $nombre,
            $descripcion,
            $idCategoria > 0 ? $idCategoria : null, //Si no hay categoria guarda null
            $precioBase,
            $iva,
            $disponible,
            $ofertado,
            $imagenFinal !== '' ? $imagenFinal : null //Si no hay imagen guarda null
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+creado'); //Redirige si todo sale bien
            exit;
        }

        $error = 'No se pudo crear el producto.';
    }
}

$categorias = Categoria::listar(); //Lista categorias
$tituloPagina = 'Crear producto';

$opcionesCategorias = '<option value="0">Sin categoría</option>'; //Opcion por defecto sin categoria
foreach ($categorias as $cat) { //Recorre categorias
    $idCat = (int)$cat['id'];
    $nombreCat = htmlspecialchars($cat['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $opcionesCategorias .= "<option value='{$idCat}'>{$nombreCat}</option>"; //Anade opcion al select
}

$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Prepara mensaje con error
//Prepara URLs
$action = htmlspecialchars(RUTA_APP.'includes/vistas/productos/crearProductos.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/productos/listarProductos.php', ENT_QUOTES, 'UTF-8');

//HTML contenido principal (que vera el usuario)
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

$funcionesJS = "<script src='".RUTA_JS."productosForm.js'></script>"; //Carga el archivo JS productosForm.js

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun


