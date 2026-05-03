<?php
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)

//Funcion para limpiar el texto (seguridad)
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$id = (int)($_GET['id'] ?? 0); //Recoge el id desde la URL
if ($id <= 0) { //Si el id no es valido
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg='.rawurlencode('Producto inválido'));
    exit;
}

$producto = Producto::buscaPorId($id); //Busca el producto en la base de datos
if (!$producto) { //Si no encuentra producto
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+no+encontrado');
    exit;
}

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente'); //Comprueba si es Gerente
$idCategoriaContexto = (int)($_GET['id_categoria'] ?? ($producto['id_categoria'] ?? 0)); //Recoge categoria de contexto

//Convierte datos antes de meterlos en HTML (seguridad)
$nombre = h((string)($producto['nombre'] ?? ''));
$descripcion = h((string)($producto['descripcion'] ?? ''));
$categoria = h((string)($producto['categoria'] ?? 'Sin categoría'));
$precioBase = (float)($producto['precio_base'] ?? 0);
$iva = (int)($producto['iva'] ?? 0);
$precioFinal = $precioBase + ($precioBase * $iva / 100); //Calcula precio con IVA
$precioBaseFmt = number_format($precioBase, 2, '.', ''); //Formatea precio base con 2 decimales
$precioFinalFmt = number_format($precioFinal, 2, '.', ''); //Formatea precio final con 2 decimales
$disponible = ((int)($producto['disponible'] ?? 0) === 1) ? 'Si' : 'No'; //Convierte disponible a texto
$ofertado = ((int)($producto['ofertado'] ?? 0) === 1) ? 'Si' : 'No'; //Convierte ofertado a texto
$imagenRaw = (string)($producto['imagen'] ?? ''); //Ruta original de la imagen

$imgHtml = '<p>Sin imagen</p>'; //Por defecto si no hay imagen mostrara "Sin imagen"
if ($imagenRaw !== '') { //Si la imagen no esta vacia
    $src = preg_match('/^https?:\/\//', $imagenRaw)
        ? h($imagenRaw) //Si es URL externa la limpia
        : RUTA_APP.ltrim($imagenRaw, '/'); //Construye la ruta de la imagen
    $imgHtml = "<img src='{$src}' alt='Imagen de {$nombre}' width='220'>"; //Crea el HTML de la imagen
}

$urlVolver = RUTA_APP.'includes/vistas/productos/listarProductos.php'; //URL base para volver
if (!$esGerente && $idCategoriaContexto > 0) { //Si es cliente vuelve a la categoria
    $urlVolver .= '?id_categoria='.$idCategoriaContexto; //Mantiene la categoria al volver
}
$accionesGerente = ''; //Prepara acciones de gerente
$bloquePrecioGerente = ''; //Prepara bloque de precio para gerente
$bloquePrecioPublico = ''; //Prepara bloque de precio para cliente
$accionCliente = ''; //Prepara accion de cliente
if ($esGerente) { //Si es gerente muestra datos de gestion
    $urlEditar = RUTA_APP.'includes/vistas/productos/actualizarProductos.php?id='.$id; //URL para actualizar producto
    $urlBorrar = RUTA_APP.'includes/vistas/productos/borrarProductos.php?id='.$id; //URL para retirar producto
    $accionesGerente = <<<EOS
        <a href="$urlEditar" class="button-estandar">Actualizar</a>
        <a href="$urlBorrar" class="button-estandar">Retirar</a>
    EOS;
    $bloquePrecioGerente = <<<EOS
    <p><strong>Precio base:</strong> $precioBaseFmt EUR</p>
    <p><strong>IVA:</strong> $iva%</p>
    <p><strong>Precio final:</strong> $precioFinalFmt EUR</p>
    <p><strong>Ofertado:</strong> $ofertado</p>
    EOS;
} else {
    $bloquePrecioPublico = "<p><strong>Precio:</strong> $precioFinalFmt EUR</p>"; //Precio que ve el cliente
    $urlCrearPedido = RUTA_APP.'includes/vistas/pedidos/crearPedido.php'; //URL para crear pedido
    $accionCliente = "<p><a href=\"{$urlCrearPedido}\" class='button-estandar'>Crear pedido</a></p>"; //Boton para clientes
}

$tituloPagina = 'Visualizar producto';
//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<div class="contenedor-producto">
    <div class="fila-superior">
        <div class="info-producto">
            <h1>Producto #$id</h1>
            <p><strong>Nombre:</strong> $nombre</p>
            <p><strong>Descripción:</strong> $descripcion</p>
            <p><strong>Categoría:</strong> $categoria</p>
            $bloquePrecioGerente
            $bloquePrecioPublico
            <p><strong>Disponible:</strong> $disponible</p>
        </div>

        <div class="imagen-producto">
            $imgHtml
        </div>
    </div>
</div>
<div class="buttons-estandar">
    <a href="$urlVolver" class="button-estandar">Volver</a>
        $accionesGerente
        $accionCliente
    </div>

EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
