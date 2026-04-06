<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

$id = (int)($_GET['id'] ?? 0);
$categoria = $id > 0 ? Categoria::buscaPorId($id) : null;

if (!$categoria) {
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg=Categoria+no+encontrada');
    exit;
}

$tituloPagina = 'Visualizar categoria';

$idMostrado = (int)$categoria['id'];
$nombre = htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = nl2br(htmlspecialchars($categoria['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'));
$imagenRaw = trim((string)($categoria['imagen'] ?? ''));
$imagenRaw = htmlspecialchars($imagenRaw, ENT_QUOTES, 'UTF-8');
$urlVolver = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/listarCategorias.php', ENT_QUOTES, 'UTF-8');

$imgHtml = '<p>Sin imagen</p>';
if ($imagenRaw !== '') {
    $src = preg_match('/^https?:\/\//', $imagenRaw)
        ? h($imagenRaw)
        : RUTA_APP.ltrim($imagenRaw, '/');
    $imgHtml = "<img src='{$src}' alt='Imagen de {$nombre}' width='220'>";
}

$contenidoPrincipal =$contenidoPrincipal = <<<EOS
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
require __DIR__.'/../plantillas/plantilla.php';
