<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Categoria; //Usa la clase Categoria

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0); //Intenta obtener el id de la categoria (de GET o de POST)
$categoria = $id > 0 ? Categoria::buscaPorId($id) : null; //Si el id es mayor que 0, busca la categoria en la base de datos, si no lo deja null

if (!$categoria) { //Si no encuentra categoria
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg='.rawurlencode('Categoría no encontrada')); //Redirige a la lista de categorias (con un mensaje)
    exit;
}

$error = ''; //Prepara mensaje error
$csrfToken = Auth::getCsrfToken(); //Obtiene un token CSFR (seguridad)

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la página se está cargando por un envío de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Comprueba que el token sea correcto
        $error = 'Token CSRF inválido.';
    }

    //Recoge datos enviados
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');

    if ($error === '' && ($nombre === '' || $descripcion === '')) { //Comprueba errores
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Categoria::actualizar($id, $nombre, $descripcion, $imagen !== '' ? $imagen : null); //Si no hay errores llama a actualizar

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg='.rawurlencode('Categoría actualizada')); //Redirige si todo sale bien
            exit;
        }

        $error = 'No se pudo actualizar la categoría.';
    }
}

$tituloPagina = 'Actualizar categoría';

//Convierte datos antes de meterlos en HTML (seguridad)
$nombre = htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($categoria['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$imagen = htmlspecialchars($categoria['imagen'] ?? '', ENT_QUOTES, 'UTF-8');

$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Prepara mensaje con error

//Prepara URLs
$action = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/actualizarCategorias.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/listarCategorias.php', ENT_QUOTES, 'UTF-8');

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Actualizar Categoría #{$id}</h1>
</div>

<form method="POST" action="$action" class="form-estandar">
    <div class="info-categoria"> $errorHtml
    
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$id}">

        <div class="campo-form">
            <label for="nombre">
                <strong>Nombre:</strong>
            </label>
            <input type="text" id="nombre" name="nombre" value="$nombre" required>
        </div>

        <div class="campo-form">
            <label for="descripcion">
                <strong>Descripción:</strong>
            </label>
            <textarea id="descripcion" name="descripcion" rows="4" required>$descripcion</textarea>
        </div>

        <div class="campo-form">
            <label for="imagen">
                <strong>Imagen:</strong>
            </label>
            <input type="text" id="imagen" name="imagen" value="$imagen">
        </div>

    </div> 
    <div class="buttons-estandar">
        <button type="submit" class="button-estandar">Guardar cambios</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
