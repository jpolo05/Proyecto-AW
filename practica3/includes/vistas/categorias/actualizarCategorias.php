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
    <h1>Actualizar categoría #{$id}</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$id}">
        <p><label>Nombre: <input type="text" name="nombre" value="$nombre" required></label></p>
        <p><label>Descripción: <textarea name="descripcion" required>$descripcion</textarea></label></p>
        <p><label>Imagen (ruta relativa o URL): <input type="text" name="imagen" value="$imagen"></label></p>
        <p>
            <button type="submit" class="button-estandar">Guardar cambios</button>
            <a href="$urlCancelar"><button type="button" class="button-estandar">Cancelar</button></a>
        </p>
    </form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
