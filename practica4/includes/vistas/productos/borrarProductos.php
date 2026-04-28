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
        $msg = rawurlencode('Token CSRF inválido');
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
<div class="seccion-titulo">
    <h1>Retirar producto</h1>
</div>

<div class="info-categoria">
    <div class="mensaje-alerta">
        <p>Esta acción no eliminará el producto de la base de datos, pero se marcará como 'no ofertado'.</p>
    </div>
    
    <p><strong>ID:</strong> {$idMostrado}</p>
    <p><strong>Nombre:</strong> {$nombre}</p>
    <p><strong>Descripción:</strong> {$descripcion}</p>

</div> <form method="POST" action="$action">
    <input type="hidden" name="csrfToken" value="$csrfToken">
    <input type="hidden" name="id" value="{$idMostrado}">
    
    <div class="buttons-estandar">
        <button type="submit" class="button-delete">Confirmar Retirada</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';


