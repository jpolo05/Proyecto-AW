<?php
use es\ucm\fdi\aw\Auth;
use es\ucm\fdi\aw\Categoria;

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
$imagen = htmlspecialchars($imagenRaw, ENT_QUOTES, 'UTF-8');
$urlVolver = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/listarCategorias.php', ENT_QUOTES, 'UTF-8');

$bloqueImagen = '';
if ($imagenRaw !== '') {
    $bloqueImagen = '<p><img src="'.$imagen.'" alt="Imagen categoria" style="max-width: 320px; height: auto;"></p>';
}

$contenidoPrincipal = <<<EOS
    <h1>Categoria #{$idMostrado}</h1>
    <ul>
        <li><strong>ID:</strong> {$idMostrado}</li>
        <li><strong>Nombre:</strong> {$nombre}</li>
        <li><strong>Descripcion:</strong><br>{$descripcion}</li>
    </ul>
    $bloqueImagen
    <p><a href="$urlVolver"><button type="button">Volver</button></a></p>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
