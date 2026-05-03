<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Oferta; //Usa la clase Oferta
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0); //Intenta obtener el id de la oferta (de GET o de POST)
$oferta = $id > 0 ? Oferta::buscaPorId($id) : null; //Si el id es mayor que 0, busca la oferta en la base de datos, si no lo deja null

if (!$oferta) { //Si no encuentra oferta
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+no+encontrada'); //Redirige a la lista de ofertas (con un mensaje)
    exit;
}

$productos = Producto::listarNombres(); //Lista productos para poder seleccionarlos
$error = ''; //Prepara mensaje error
$csrfToken = Auth::getCsrfToken(); //Obtiene un token CSRF (seguridad)

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la pagina se esta cargando por un envio de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Comprueba que el token sea correcto
        $error = 'Token CSRF inválido.';
    }

    //Recoge datos enviados
    $nombrePost = trim($_POST['nombre'] ?? '');
    $descripcionPost = trim($_POST['descripcion'] ?? '');
    $comienzoPost = trim($_POST['comienzo'] ?? ''); //Recoge fecha de comienzo
    $finPost = trim($_POST['fin'] ?? ''); //Recoge fecha de fin
    $productosElegidos = $_POST['productos'] ?? []; //Recoge productos elegidos
    $cantidadesElegidas = $_POST['cantidades'] ?? []; //Recoge cantidades de cada producto
    $descuentoPost = (float)($_POST['descuento'] ?? 0.00); //Recoge descuento calculado por JavaScript

    if ($error === '' && ($nombrePost === '' || $descripcionPost === '')) { //Comprueba errores
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Oferta::actualizar( //Si no hay errores llama a actualizar
            $id,
            $nombrePost,
            $descripcionPost,
            $comienzoPost !== '' ? $comienzoPost : null, //Si no hay comienzo guarda null
            $finPost !== '' ? $finPost : null, //Si no hay fin guarda null
            $descuentoPost,
            $productosElegidos,
            $cantidadesElegidas
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+actualizada'); //Redirige si todo sale bien
            exit;
        }

        $error = 'No se pudo actualizar la oferta.';
    }
}

$tituloPagina = 'Actualizar oferta';
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Prepara mensaje con error

//Prepara URLs
$action = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/actualizarOfertas.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/listarOfertas.php', ENT_QUOTES, 'UTF-8');
$rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8');

//Convierte datos antes de meterlos en HTML (seguridad)
$nombre = htmlspecialchars($oferta['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($oferta['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$comienzo = htmlspecialchars(substr((string)($oferta['comienzo'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8'); //Recorta fecha para input date
$fin = htmlspecialchars(substr((string)($oferta['fin'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8'); //Recorta fecha para input date
$descuentoActual = number_format((float)($oferta['descuento'] ?? 0), 2, '.', ''); //Formatea descuento con 2 decimales
$lineasActuales = $oferta['lineas'] ?? []; //Recoge productos actuales de la oferta

$lineasHtml = ''; //Prepara lineas actuales de la oferta
foreach ($lineasActuales as $linea) { //Recorre los productos incluidos en la oferta
    $idProd = (int)($linea['idProd'] ?? 0); //Producto guardado en esta linea
    $cantidad = (int)($linea['cantidad'] ?? 1); //Cantidad guardada en esta linea

    //Crea select de productos marcando el producto actual
    $selectHtml = '<select name="productos[]" required>';
    $selectHtml .= '<option value="">Selecciona un producto...</option>';
    foreach ($productos as $p) { //Recorre productos disponibles
        $nombreP = htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8');
        $idP = (int)$p['id'];
        $sel = $idP === $idProd ? 'selected' : ''; //Marca selected si es el producto actual
        $selectHtml .= "<option value='$idP' $sel>$nombreP</option>"; //Añade opcion al select
    }
    $selectHtml .= '</select>';

    //Añade una linea al formulario
    $lineasHtml .= '<div>';
    $lineasHtml .= $selectHtml;
    $lineasHtml .= "<input type='number' name='cantidades[]' min='1' value='$cantidad'>";
    $lineasHtml .= '<button type="button" class="js-eliminar-linea">Eliminar</button>';
    $lineasHtml .= '</div>';
}

$productosJsonHtml = htmlspecialchars(json_encode($productos), ENT_QUOTES, 'UTF-8'); //Prepara productos para JavaScript en data-productos

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
    <h1>Actualizar oferta #{$id}</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        <input type="hidden" name="id" value="{$id}">

        <h2>Datos de la oferta</h2>
        <p><label>Nombre: <input type="text" name="nombre" value="$nombre" required></label></p>
        <p><label>Descripción: <textarea name="descripcion" required>$descripcion</textarea></label></p>
        <p><label>Comienzo: <input type="date" name="comienzo" value="$comienzo"></label></p>
        <p><label>Fin: <input type="date" name="fin" value="$fin"></label></p>
        <p>Descuento aplicado: <span id="porcentajeMostrado">$descuentoActual</span>%</p>
        <input type="hidden" name="descuento" id="inputDescuento" value="$descuentoActual">

        <h2>Productos incluidos</h2>
        <div id="contenedor-lineas">$lineasHtml</div>
        <p><button type="button" class="js-agregar-linea" data-productos="$productosJsonHtml">+ Añadir producto</button></p>

        <h2>Resumen</h2>
        <p>
            Precio previo total: <span id="precioTotal">0</span> €
            Precio con descuento: <input type="number" id="precioDescuento" step="0.01" min="0">
        </p>

        <p>
            <button type="submit">Guardar cambios</button>
            <button type="button" class="js-cancelar-oferta" data-url="$urlCancelar">Cancelar</button>
            <button type="reset" name="limpiar">Reset</button>
        </p>
    </form>
    <p><a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a></p>
EOS;

$rutaJs = RUTA_JS . 'crearOfertas.js'; //URL del JavaScript
$funcionesJS = "<script src='$rutaJs'></script>"; //Carga el archivo JS crearOfertas.js

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
