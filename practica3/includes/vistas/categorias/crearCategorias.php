<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;

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
    $imagen = trim($_POST['imagen'] ?? '');

    if ($error === '' && ($nombre === '' || $descripcion === '')) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Categoria::crear(
            $nombre,
            $descripcion,
            $imagen !== '' ? $imagen : null
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg=Categoria+creada');
            exit;
        }

        $error = 'No se pudo crear la categoria.';
    }
}

$tituloPagina = 'Crear categoria';
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
$action = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/crearCategorias.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/listarCategorias.php', ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
    <h1>Crear categoria</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <p><label>Nombre: <input type="text" name="nombre" required></label></p>
        <p><label>Descripcion: <textarea name="descripcion" required></textarea></label></p>
        <p><label>Imagen (ruta relativa o URL): <input type="text" name="imagen"></label></p>
        <p>
            <button type="submit">Guardar</button>
            <a href="$urlCancelar"><button type="button">Cancelar</button></a>
        </p>
    </form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
