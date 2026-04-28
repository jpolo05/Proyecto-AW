<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;

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
<div class="seccion-titulo">
    <h1>Crear categoría</h1>
</div>

<form method="POST" action="$action" class="form-estandar">
    <div class="info-categoria"> $errorHtml
    
        <input type="hidden" name="csrfToken" value="$csrfToken">
        
        <div class="campo-form">
            <label for="nombre"><strong>Nombre:</strong></label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Pizzas, Bebidas..." required>
        </div>

        <div class="campo-form">
            <label for="descripcion"><strong>Descripción:</strong></label>
            <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe brevemente la categoría..." required></textarea>
        </div>

        <div class="campo-form">
            <label for="imagen"><strong>Imagen (Ruta relativa o URL):</strong></label>
            <input type="text" id="imagen" name="imagen" placeholder="img/categorias/ejemplo.jpg">
        </div>

    </div>

    <div class="buttons-estandar">
        <button type="submit" class="button-estandar">Guardar</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>

EOS;

require __DIR__.'/../plantillas/plantilla.php';
