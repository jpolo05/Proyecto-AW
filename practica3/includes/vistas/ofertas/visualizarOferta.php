<?php
use es\ucm\fdi\aw\usuarios\Oferta;

require_once __DIR__.'/../../config.php';

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+invalida');
    exit;
}

$oferta = Oferta::buscaPorId($id);
if (!$oferta) {
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+no+encontrada');
    exit;
}

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');
//$idCategoriaContexto = (int)($_GET['id_categoria'] ?? ($producto['id_categoria'] ?? 0));

$nombre = h((string)($oferta['nombre'] ?? ''));
$descripcion = h((string)($oferta['descripcion'] ?? ''));
//$productos ;          ARRAY DE PRODUCTOS QUE INCLUYEN ESTA OFERTA??
$cantidades = ((int)($oferta['cantidades'] ?? 0));
$comienzo = ((date)($oferta['comienzo'] ?? null));
$fin = ((date)($oferta['fin'] ?? null));
$descuento = ((int)($oferta['cantidades'] ?? 0));


$tituloPagina = 'Contenido oferta';

$urlVolver = RUTA_APP.'includes/vistas/ofertas/listarOfertas.php';
if ($esGerente) {
    $urlEditar = RUTA_APP.'includes/vistas/ofertas/actualizarOfertas.php?id='.$id;
    $urlBorrar = RUTA_APP.'includes/vistas/ofertas/borrarOfertas.php?id='.$id;
    $accionesGerente = <<<EOS
    <p>
        <a href="$urlEditar" class='button-general'>Actualizar</a>
        <a href="$urlBorrar" class='button-general'>Retirar</a>
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






/* VISUALIZAR PRODUCTOS


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
        <a href="$urlEditar"><button>Actualizar</button></a>
        <a href="$urlBorrar"><button>Retirar</button></a>
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
*/