<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Categoria;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');
$csrfToken = Auth::getCsrfToken();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$categoria = $id > 0 ? Categoria::buscaPorId($id) : null;

if (!$categoria) {
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg=Categoria+no+encontrada');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $msg = 'Token+CSRF+invalido';
    } else {
        $ok = Categoria::borrar($id);
        $msg = $ok ? 'Categoria+borrada' : 'No+se+pudo+borrar+la+categoria';
    }
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
<div class="seccion-titulo">
    <h1>Borrar categoría</h1>
</div>

<div class="info-categoria">
    <div class="mensaje-alerta">
        <p>¿Estás seguro de que deseas eliminar esta categoría?</p>
    </div>
    
    <p><strong>ID:</strong> {$idMostrado}</p>
    <p><strong>Nombre:</strong> {$nombre}</p>
    <p><strong>Descripción:</strong> {$descripcion}</p>
</div>

<form method="POST" action="$action">
    <input type="hidden" name="csrfToken" value="$csrfToken">
    <input type="hidden" name="id" value="{$idMostrado}">
    
    <div class="buttons-estandar">
        <button type="submit" class="button-delete"> Confirmar Borrado</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
