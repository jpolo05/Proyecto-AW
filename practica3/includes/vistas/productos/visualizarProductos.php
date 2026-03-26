<?php
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+invalido');
    exit;
}

$producto = Producto::buscaPorId($id);
if (!$producto) {
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+no+encontrado');
    exit;
}

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');
$idCategoriaContexto = (int)($_GET['id_categoria'] ?? ($producto['id_categoria'] ?? 0));

$nombre = h((string)($producto['nombre'] ?? ''));
$descripcion = h((string)($producto['descripcion'] ?? ''));
$categoria = h((string)($producto['categoria'] ?? 'Sin categoria'));
$precioBase = (float)($producto['precio_base'] ?? 0);
$iva = (int)($producto['iva'] ?? 0);
$precioFinal = $precioBase + ($precioBase * $iva / 100);
$precioBaseFmt = number_format($precioBase, 2, '.', '');
$precioFinalFmt = number_format($precioFinal, 2, '.', '');
$disponible = ((int)($producto['disponible'] ?? 0) === 1) ? 'Si' : 'No';
$ofertado = ((int)($producto['ofertado'] ?? 0) === 1) ? 'Si' : 'No';
$imagenRaw = (string)($producto['imagen'] ?? '');

$imgHtml = '<p>Sin imagen</p>';
if ($imagenRaw !== '') {
    $src = preg_match('/^https?:\/\//', $imagenRaw)
        ? h($imagenRaw)
        : RUTA_APP.ltrim($imagenRaw, '/');
    $imgHtml = "<img src='{$src}' alt='Imagen de {$nombre}' width='220'>";
}

$urlVolver = RUTA_APP.'includes/vistas/productos/listarProductos.php';
if (!$esGerente && $idCategoriaContexto > 0) {
    $urlVolver .= '?id_categoria='.$idCategoriaContexto;
}
$accionesGerente = '';
$bloquePrecioGerente = '';
$bloquePrecioPublico = '';
$accionCliente = '';
if ($esGerente) {
    $urlEditar = RUTA_APP.'includes/vistas/productos/actualizarProductos.php?id='.$id;
    $urlBorrar = RUTA_APP.'includes/vistas/productos/borrarProductos.php?id='.$id;
    $accionesGerente = <<<EOS
    <p>
        <a href="$urlEditar" class="button-estandar">Actualizar</a>
        <a href="$urlBorrar" class="button-estandar">Retirar</a>
    </p>
EOS;
    $bloquePrecioGerente = <<<EOS
    <p><strong>Precio base:</strong> $precioBaseFmt EUR</p>
    <p><strong>IVA:</strong> $iva%</p>
    <p><strong>Precio final:</strong> $precioFinalFmt EUR</p>
    <p><strong>Ofertado:</strong> $ofertado</p>
EOS;
} else {
    $bloquePrecioPublico = "<p><strong>Precio:</strong> $precioFinalFmt EUR</p>";
    $urlCrearPedido = RUTA_APP.'includes/vistas/pedidos/crearPedido.php';
    $accionCliente = "<p><a href=\"{$urlCrearPedido}\" class='button-estandar'>Crear pedido</a></p>";
}

$tituloPagina = 'Visualizar producto';
$contenidoPrincipal = <<<EOS
    <h1>Producto #$id</h1>
    <p><strong>Nombre:</strong> $nombre</p>
    <p><strong>Descripcion:</strong> $descripcion</p>
    <p><strong>Categoria:</strong> $categoria</p>
    $bloquePrecioGerente
    $bloquePrecioPublico
    <p><strong>Disponible:</strong> $disponible</p>
    <div>$imgHtml</div>
    $accionesGerente
    $accionCliente
    <p><a href="$urlVolver" class='button-estandar'>Volver</a></p>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
