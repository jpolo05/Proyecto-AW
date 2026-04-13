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

    $imagen = '';

    if (isset($_FILES['imagenArchivo']) && $_FILES['imagenArchivo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['imagenArchivo']['error'] !== UPLOAD_ERR_OK) {
           $error = 'Error al subir la imagen.';
        } else {
            $archivo = $_FILES['imagenArchivo'];
            $extensionesValidas = ['jpg', 'jpeg', 'png'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $extensionesValidas)) {
                $error = 'Formato de imagen no permitido (solo JPG o PNG).';
            } elseif ($archivo['size'] > 2000000) { // 2MB
                $error = 'La imagen es demasiado grande (máximo 2MB).';
            } else {

                $nuevoNombre = uniqid('img_', true) . '.' . $extension;
                
                $rutaRelativaDestino = 'img/uploads/categorias/' . $nuevoNombre;
                $rutaDestinoFisica = dirname(RAIZ_APP) . '/' . $rutaRelativaDestino;

                if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoFisica)) {
                    $imagen = $rutaRelativaDestino;
                } else {
                    $error = 'Error al guardar la imagen. Revisa los permisos de la carpeta.';
                }
            }
        }
    }

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

<form method="POST" action="$action" class="form-estandar" enctype="multipart/form-data">
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
            <label for="imagenArchivo">Sube tu foto:</label>
            <input id="imagenArchivo" type="file" name="imagenArchivo">
        </div>

    </div>

    <div class="buttons-estandar">
        <button type="submit" class="button-estandar">Guardar</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>

EOS;

require __DIR__.'/../plantillas/plantilla.php';
