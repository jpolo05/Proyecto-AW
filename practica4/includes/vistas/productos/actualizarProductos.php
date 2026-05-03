<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Categoria; //Usa la clase Categoria
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0); //Intenta obtener el id del producto (de GET o de POST)
$producto = $id > 0 ? Producto::buscaPorId($id) : null; //Si el id es mayor que 0, busca el producto en la base de datos, si no lo deja null

if (!$producto) { //Si no encuentra producto
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+no+encontrado'); //Redirige a la lista de productos (con un mensaje)
    exit;
}

$error = ''; //Prepara mensaje error
$csrfToken = Auth::getCsrfToken(); //Obtiene un token CSRF (seguridad)

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la pagina se esta cargando por un envio de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Comprueba que el token sea correcto
        $error = 'Token CSRF inválido.';
    }

    //Recoge datos enviados
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idCategoria = (int)($_POST['id_categoria'] ?? 0); //Recoge categoria seleccionada
    $precioBase = (float)($_POST['precio_base'] ?? 0); //Recoge precio base
    $iva = (int)($_POST['iva'] ?? 10); //Recoge IVA seleccionado
    $disponible = isset($_POST['disponible']); //Comprueba si el checkbox disponible esta marcado
    $ofertado = isset($_POST['ofertado']); //Comprueba si el checkbox ofertado esta marcado
    $imagen = trim($_POST['imagen'] ?? ''); //Recoge la ruta de la imagen escrita en el formulario

    if ($error === '' && ($nombre === '' || $descripcion === '' || $precioBase <= 0 || !in_array($iva, [4, 10, 21], true))) { //Comprueba errores
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Producto::actualizar( //Si no hay errores llama a actualizar
            $id,
            $nombre,
            $descripcion,
            $idCategoria > 0 ? $idCategoria : null, //Si no hay categoria guarda null
            $precioBase,
            $iva,
            $disponible,
            $ofertado,
            $imagen !== '' ? $imagen : null //Si no hay imagen guarda null
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+actualizado'); //Redirige si todo sale bien
            exit;
        }

        $error = 'No se pudo actualizar el producto.';
    }
}

$categorias = Categoria::listar(); //Lista categorias
$tituloPagina = 'Actualizar producto';

//Convierte datos antes de meterlos en HTML (seguridad)
$idCatActual = (int)($producto['id_categoria'] ?? 0);
$nombre = htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$precioBase = number_format((float)($producto['precio_base'] ?? 0), 2, '.', ''); //Formatea precio base con 2 decimales
$ivaActual = (int)($producto['iva'] ?? 10); //Recoge el IVA actual del producto
$imagen = htmlspecialchars($producto['imagen'] ?? '', ENT_QUOTES, 'UTF-8'); //Prepara la ruta de la imagen
$disponibleChecked = ((int)($producto['disponible'] ?? 0) === 1) ? 'checked' : ''; //Marca el checkbox si esta disponible
$ofertadoChecked = ((int)($producto['ofertado'] ?? 0) === 1) ? 'checked' : ''; //Marca el checkbox si esta ofertado

$opcionesCategorias = '<option value="0">Sin categoría</option>'; //Opcion por defecto sin categoria
foreach ($categorias as $cat) { //Recorre categorias
    $idCat = (int)$cat['id'];
    $nombreCat = htmlspecialchars($cat['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $sel = $idCat === $idCatActual ? 'selected' : ''; //Marca selected si es la categoria actual
    $opcionesCategorias .= "<option value='{$idCat}' {$sel}>{$nombreCat}</option>"; //Añade opcion al select
}

$sel4 = $ivaActual === 4 ? 'selected' : ''; //Marca IVA 4 si es el actual
$sel10 = $ivaActual === 10 ? 'selected' : ''; //Marca IVA 10 si es el actual
$sel21 = $ivaActual === 21 ? 'selected' : ''; //Marca IVA 21 si es el actual

$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Prepara mensaje con error
//Prepara URLs
$action = htmlspecialchars(RUTA_APP.'includes/vistas/productos/actualizarProductos.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/productos/listarProductos.php', ENT_QUOTES, 'UTF-8');

//HTML contenido principal (que vera el usuario)
//Los selected y checked se calculan arriba para mantener los valores actuales
$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Actualizar producto #{$id}</h1>
</div>

<form method="POST" action="$action" class="form-estandar">
    <div class="info-categoria">
    $errorHtml
    
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$id}">

        <div class="campo-form">
            <label for="nombre"><strong>Nombre:</strong></label>
            <input type="text" id="nombre" name="nombre" value="$nombre" required>
        </div>

        <div class="campo-form">
            <label for="descripcion"><strong>Descripción:</strong></label>
            <textarea id="descripcion" name="descripcion" rows="4" required>$descripcion</textarea>
        </div>

        <div class="campo-form">
            <label for="id_categoria"><strong>Categoría:</strong></label>
            <select name="id_categoria" id="id_categoria">
                $opcionesCategorias
            </select>
        </div>

        <div class="campo-form">
            <label for="precio_base"><strong>Precio base:</strong></label>
            <input type="number" step="0.01" min="0.01" name="precio_base" id="precio_base" value="$precioBase" required>
        </div>

        <div class="campo-form">
            <label for="iva"><strong>IVA (%):</strong></label>
            <select name="iva" id="iva">
                <option value="4" $sel4>4</option>
                <option value="10" $sel10>10</option>
                <option value="21" $sel21>21</option>
            </select>
        </div>

        <div class="campo-form">
            <label for="precio_final"><strong>Precio final:</strong></label>
            <input type="text" id="precio_final" data-sufijo=" EUR" readonly class="input-solo-lectura">
        </div>

        <div class="campo-form">
            <label for="imagen"><strong>Imagen:</strong></label>
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

    </div> 
        <div class="buttons-estandar">
            <button type="submit" class="button-estandar">Guardar cambios</button>
            <a href="$urlCancelar" class="button-estandar">Cancelar</a>
        </div>
</form>

EOS;

$funcionesJS = "<script src='".RUTA_JS."productosForm.js'></script>"; //Carga el archivo JS productosForm.js

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun


