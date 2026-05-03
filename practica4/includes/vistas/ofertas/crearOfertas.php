<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Oferta; //Usa la clase Oferta
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente
$productos = Producto::listarNombres(); //Lista productos para poder seleccionarlos

$error = ''; //Prepara mensaje error
$csrfToken = Auth::getCsrfToken(); //Obtiene un token CSRF (seguridad)

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la pagina se esta cargando por un envio de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Comprueba que el token sea correcto
        $error = 'Token CSRF inválido.';
    }

    //Recoge datos enviados
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $comienzo = trim($_POST['comienzo'] ?? ''); //Recoge fecha de comienzo
    $fin = trim($_POST['fin'] ?? ''); //Recoge fecha de fin
    $productosElegidos = $_POST['productos'] ?? []; //Recoge productos elegidos
    $cantidadesElegidas = $_POST['cantidades'] ?? []; //Recoge cantidades de cada producto
    $descuento = (float)($_POST['descuento'] ?? 0.00); //Recoge descuento calculado por JavaScript

    if ($error === '' && ($nombre === '' || $descripcion === '')) { //Comprueba errores
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Oferta::crear( //Si no hay errores llama a crear
            $nombre,
            $descripcion,
            $comienzo !== '' ? $comienzo : null, //Si no hay comienzo guarda null
            $fin !== '' ? $fin : null, //Si no hay fin guarda null
            $descuento,
            $productosElegidos,
            $cantidadesElegidas
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+creada'); //Redirige si todo sale bien
            exit;
        }

        $error = 'No se pudo crear la oferta.';
    }
}

$tituloPagina = 'Crear oferta';
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Prepara mensaje con error
//Prepara URLs y datos para HTML
$action = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/crearOfertas.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/ofertas/listarOfertas.php', ENT_QUOTES, 'UTF-8');
$rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8');
$productosJsonHtml = htmlspecialchars(json_encode($productos), ENT_QUOTES, 'UTF-8'); //Prepara productos para JavaScript en data-productos

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
    <h1>Crear nueva oferta</h1>
    $errorHtml
    <form method="POST" action="$action">
        <input type="hidden" name="csrfToken" value="$csrfToken">

        <h2>Datos de la oferta</h2>
        <p><label>Nombre: <input type="text" name="nombre" required></label></p>
        <p><label>Descripción: <textarea name="descripcion" required></textarea></label></p>
        <p><label>Comienzo: <input type="date" name="comienzo"></label></p>
        <p><label>Fin: <input type="date" name="fin"></label></p>
        <p>Descuento aplicado: <span id="porcentajeMostrado">0</span>%</p>
        <input type="hidden" name="descuento" id="inputDescuento" value="0.00">

        <h2>Productos incluidos</h2>
        <div id="contenedor-lineas"></div>
        <p><button type="button" class="js-agregar-linea" data-productos="$productosJsonHtml">+ Añadir producto</button></p>

        <h2>Resumen</h2>
        <p>
            Precio previo total: <span id="precioTotal">0</span> €
            Precio con descuento: <input type="number" id="precioDescuento" step="0.01" min="0"> €
        </p>

        <p>
            <button type="submit">Guardar oferta</button>
            <button type="button" class="js-cancelar-oferta" data-url="$urlCancelar">Cancelar</button>
            <button type="reset" name="limpiar">Reset</button>
        </p>
    </form>
    <p><a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a></p>
EOS;

$rutaJs = RUTA_JS . 'crearOfertas.js'; //URL del JavaScript
$funcionesJS = "<script src='$rutaJs'></script>"; //Carga el archivo JS crearOfertas.js

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
