<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Oferta; //Usa la clase Oferta

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente

//Funcion para limpiar el texto (seguridad)
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$csrfToken = Auth::getCsrfToken(); //Obtiene un token CSRF (seguridad)
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0); //Intenta obtener el id de la oferta (de GET o de POST)
$oferta = $id > 0 ? Oferta::buscaPorId($id) : null; //Si el id es mayor que 0, busca la oferta en la base de datos, si no lo deja null

if (!$oferta) { //Si no encuentra oferta
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+no+encontrada'); //Redirige a la lista de ofertas (con un mensaje)
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la pagina se esta cargando por un envio de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Comprueba que el token sea correcto
        $msg = rawurlencode('Token CSRF inválido');
    } else {
        $ok = Oferta::borrar($id); //Llama a borrar
        $msg = $ok ? 'Oferta+borrada' : 'No+se+pudo+borrar+la+oferta'; //Mensaje segun resultado
    }

    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg='.$msg); //Redirige
    exit;
}

$tituloPagina = 'Borrar oferta';

//Convierte datos antes de meterlos en HTML (seguridad)
$idMostrado = (int) ($oferta['id'] ?? 0);
$nombre = h((string) ($oferta['nombre'] ?? ''));
$descripcion = h((string) ($oferta['descripcion'] ?? ''));
$comienzo = h((string) ($oferta['comienzo'] ?? '')); //Fecha de comienzo de la oferta
$fin = h((string) ($oferta['fin'] ?? '')); //Fecha de fin de la oferta
$descuento = number_format((float) ($oferta['descuento'] ?? 0), 2, ',', '.'); //Formatea descuento con 2 decimales
$lineas = $oferta['lineas'] ?? []; //Recoge productos asociados a la oferta
//Prepara URLs
$action = h(RUTA_APP.'includes/vistas/ofertas/borrarOfertas.php');
$urlCancelar = h(RUTA_APP.'includes/vistas/ofertas/visualizarOferta.php?id='.$idMostrado);

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
    <h1>Borrar oferta</h1>
    <p>Esta acción eliminará la oferta de la base de datos y sus líneas asociadas.</p>
    <ul>
        <li><strong>ID:</strong> {$idMostrado}</li>
        <li><strong>Nombre:</strong> {$nombre}</li>
        <li><strong>Descripción:</strong> {$descripcion}</li>
        <li><strong>Comienzo:</strong> {$comienzo}</li>
        <li><strong>Fin:</strong> {$fin}</li>
        <li><strong>Descuento:</strong> {$descuento}%</li>
    </ul>
    <h2>Productos incluidos</h2>
    <ul>
        {$productosHtml}
    </ul>
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$idMostrado}">
        <button type="submit" class="button-estandar">Confirmar</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </form>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
