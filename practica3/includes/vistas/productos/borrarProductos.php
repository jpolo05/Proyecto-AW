<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');
$csrfToken = Auth::getCsrfToken();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$producto = $id > 0 ? Producto::buscaPorId($id) : null;

if (!$producto) {
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+no+encontrado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $msg = 'Token+CSRF+invalido';
    } else {
        $ok = Producto::desofertar($id);
        $msg = $ok ? 'Producto+retirado+de+la+oferta' : 'No+se+pudo+retirar+el+producto';
    }
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg='.$msg);
    exit;
}

$tituloPagina = 'Retirar producto de la oferta';

$idMostrado = (int)$producto['id'];
$nombre = htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$action = htmlspecialchars(RUTA_APP.'includes/vistas/productos/borrarProductos.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/productos/listarProductos.php', ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
    <h1>Retirar producto</h1>
    <p>Esta accion no elimina el producto de la base de datos.</p>
    <p>Se marcara como no ofertado.</p>
    <ul>
        <li><strong>ID:</strong> {$idMostrado}</li>
        <li><strong>Nombre:</strong> {$nombre}</li>
        <li><strong>Descripcion:</strong> {$descripcion}</li>
    </ul>
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$idMostrado}">
        <button type="submit">Confirmar</button>
        <a href="$urlCancelar"><button type="button">Cancelar</button></a>
    </form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';


