<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$categoria = $id > 0 ? Categoria::buscaPorId($id) : null;

if (!$categoria) {
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg=Categoria+no+encontrada');
    exit;
}

$error = '';
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF inválido.';
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');

    if ($error === '' && ($nombre === '' || $descripcion === '')) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Categoria::actualizar(
            $id,
            $nombre,
            $descripcion,
            $imagen !== '' ? $imagen : null
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg=Categoria+actualizada');
            exit;
        }

        $error = 'No se pudo actualizar la categoría.';
    }
}

$tituloPagina = 'Actualizar categoría';

$nombre = htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($categoria['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$imagen = htmlspecialchars($categoria['imagen'] ?? '', ENT_QUOTES, 'UTF-8');
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
$action = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/actualizarCategorias.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/listarCategorias.php', ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Actualizar Categoría #{$id}</h1>
</div>

<div class="info-categoria"> $errorHtml
    <form method="POST" action="$action" class="form-estandar">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$id}">

        <div class="campo-form">
            <label for="nombre">
                <p><strong>Nombre:</strong></p>
            </label>
            <input type="text" id="nombre" name="nombre" value="$nombre" required>
        </div>

        <div class="campo-form">
            <label for="descripcion">
                <p><strong>Descripción:</strong></p>
            </label>
            <textarea id="descripcion" name="descripcion" rows="4" required>$descripcion</textarea>
        </div>

        <div class="campo-form">
            <label for="imagen">
                <p><strong>Imagen:</strong></p>
            </label>
            <input type="text" id="imagen" name="imagen" value="$imagen">
        </div>

</div> <div class="buttons-estandar">
        <button type="submit" class="button-estandar">Guardar Cambios</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
