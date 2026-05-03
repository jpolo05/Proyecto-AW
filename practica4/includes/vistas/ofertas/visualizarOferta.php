<?php
use es\ucm\fdi\aw\usuarios\Oferta; //Usa la clase Oferta

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)

//Funcion para limpiar el texto (seguridad)
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$id = (int) ($_GET['id'] ?? 0); //Recoge el id desde la URL
if ($id <= 0) { //Si el id no es valido
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg='.rawurlencode('Oferta inválida'));
    exit;
}

$oferta = Oferta::buscaPorId($id); //Busca la oferta en la base de datos
if (!$oferta) { //Si no encuentra oferta
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+no+encontrada');
    exit;
}

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente'); //Comprueba si es Gerente
$tituloPagina = 'Visualizar oferta';

//Convierte datos antes de meterlos en HTML (seguridad)
$nombre = h((string) ($oferta['nombre'] ?? ''));
$descripcion = h((string) ($oferta['descripcion'] ?? ''));
$comienzo = h((string) ($oferta['comienzo'] ?? '')); //Fecha de comienzo de la oferta
$fin = h((string) ($oferta['fin'] ?? '')); //Fecha de fin de la oferta
$descuento = number_format((float) ($oferta['descuento'] ?? 0), 2, ',', '.'); //Formatea descuento con 2 decimales
$lineas = $oferta['lineas'] ?? []; //Recoge productos asociados a la oferta
$origen = (string) ($_GET['origen'] ?? ''); //Recoge desde donde viene el usuario
$urlVolver = h(RUTA_APP.'includes/vistas/ofertas/listarOfertas.php');
$textoVolver = 'Volver';

if ($origen === 'pedido') { //Si viene de crear pedido
    $urlVolver = h(RUTA_APP.'includes/vistas/pedidos/crearPedido.php');
    $textoVolver = 'Volver al pedido';
} elseif ($origen === 'carta') { //Si viene de la carta
    $urlVolver = h(RUTA_APP.'includes/vistas/productos/listarProductos.php');
    $textoVolver = 'Volver a la carta';
}

$accionesGerente = ''; //Prepara acciones de gerente

if ($esGerente) { //Si es gerente puede editar o borrar
    $urlEditar = h(RUTA_APP.'includes/vistas/ofertas/actualizarOfertas.php?id='.$id); //URL para editar oferta
    $urlBorrar = h(RUTA_APP.'includes/vistas/ofertas/borrarOfertas.php?id='.$id); //URL para borrar oferta
    $accionesGerente = <<<EOS
    <p>
        <a href="$urlEditar" class="button-estandar">Editar oferta</a>
        <a href="$urlBorrar" class="button-estandar">Borrar oferta</a>
    </p>
EOS;
}

$productosHtml = '<li>Sin productos asociados</li>'; //Por defecto si no hay productos
if (!empty($lineas)) { //Si hay productos asociados
    $items = [];
    foreach ($lineas as $linea) { //Recorre productos de la oferta
        $producto = h((string) ($linea['producto'] ?? ''));
        $cantidadLinea = (int) ($linea['cantidad'] ?? 1); //Cantidad asociada a ese producto
        $items[] = "<li>{$producto} ({$cantidadLinea})</li>"; //Añade producto a la lista
    }
    $productosHtml = implode('', $items); //Une los productos en HTML
}

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
    <h1>Oferta #{$id}</h1>
    <p><strong>Nombre:</strong> {$nombre}</p>
    <p><strong>Descripción:</strong> {$descripcion}</p>
    <p><strong>Comienzo:</strong> {$comienzo}</p>
    <p><strong>Fin:</strong> {$fin}</p>
    <p><strong>Descuento:</strong> {$descuento}%</p>
    <h2>Productos incluidos</h2>
    <ul>
        {$productosHtml}
    </ul>
    {$accionesGerente}
    <p><a href="$urlVolver" class="button-estandar">{$textoVolver}</a></p>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
