<?php
use es\ucm\fdi\aw\Auth;
use es\ucm\fdi\aw\Categoria;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$categoria = $id > 0 ? Categoria::buscaPorId($id) : null;

if (!$categoria) {
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg=Categoria+no+encontrada');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = Categoria::borrar($id);
    $msg = $ok ? 'Categoria+borrada' : 'No+se+pudo+borrar+la+categoria';
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg='.$msg);
    exit;
}

$tituloPagina = 'Borrar categoria';

$idMostrado = (int)$categoria['id'];
$nombre = htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($categoria['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$action = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/borrarCategorias.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/listarCategorias.php', ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
    <h1>Borrar categoria</h1>
    <p>Esta accion eliminara la categoria de la base de datos.</p>
    <ul>
        <li><strong>ID:</strong> {$idMostrado}</li>
        <li><strong>Nombre:</strong> {$nombre}</li>
        <li><strong>Descripcion:</strong> {$descripcion}</li>
    </ul>
    <form method="POST" action="$action">
        <input type="hidden" name="id" value="{$idMostrado}">
        <button type="submit">Confirmar</button>
        <a href="$urlCancelar"><button type="button">Cancelar</button></a>
    </form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
