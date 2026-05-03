<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Categoria; //Usa la clase Categoria

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente

$id = (int)($_GET['id'] ?? 0); //Recoge el id desde la URL
$categoria = $id > 0 ? Categoria::buscaPorId($id) : null; //Si el id es mayor que 0, busca la categoria en la base de datos, si no lo deja null

if (!$categoria) { //Si no encuentra categoria
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg='.rawurlencode('Categoría no encontrada')); //Redirige a la lista de categorias (con un mensaje)
    exit;
}

$tituloPagina = 'Visualizar categoría';

//Convierte datos antes de meterlos en HTML (seguridad)
$idMostrado = (int)$categoria['id'];
$nombre = htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = nl2br(htmlspecialchars($categoria['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'));
$imagenRaw = trim((string)($categoria['imagen'] ?? ''));
$imagenRaw = htmlspecialchars($imagenRaw, ENT_QUOTES, 'UTF-8');

$urlVolver = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/listarCategorias.php', ENT_QUOTES, 'UTF-8'); //URL para volver

$imgHtml = '<p>Sin imagen</p>'; //Por defecto si no hay imagen mostrara "Sin imagen"
if ($imagenRaw !== '') { //Si la imagen no esta vacia
    $src = preg_match('/^https?:\/\//', $imagenRaw)
        ? h($imagenRaw)
        : RUTA_APP.ltrim($imagenRaw, '/'); //Construye la ruta de la imagen
    $imgHtml = "<img src='{$src}' alt='Imagen de {$nombre}' width='220'>"; //Crea el HTML de la imagen
}

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Categoría #{$idMostrado}</h1>
</div>

<div class="info-categoria">
    <p><strong>ID:</strong> $idMostrado</p>
    <p><strong>Nombre:</strong> $nombre </p>
    <p><strong>Descripción:</strong>$descripcion</p>
    $imgHtml
</div>

<div class="buttons-estandar">
    <a href="$urlVolver" class="button-estandar">Volver</a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
