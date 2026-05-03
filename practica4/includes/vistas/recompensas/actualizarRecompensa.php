<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto
use es\ucm\fdi\aw\usuarios\Recompensa; //Usa la clase Recompensa

require_once __DIR__.'/../../config.php'; //Carga configuracion
Auth::verificarAcceso('Gerente'); //Comprueba acceso de gerente

$id = (int)($_GET['id'] ?? 0); //Recoge id de la recompensa
$recompensa = $id > 0 ? Recompensa::buscaPorId($id) : null; //Busca recompensa

if (!$recompensa) { //Si no existe
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+no+encontrada'); //Redirige al listado
    exit;
}

$error = ''; //Mensaje de error
$csrfToken = Auth::getCsrfToken(); //Obtiene token CSRF

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Si se envia formulario
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Valida token CSRF
        $error = 'Token CSRF inválido.';
    }

    $idProducto = (int)($_POST['id_producto'] ?? 0); //Producto seleccionado
    $bistroCoins = (int)($_POST['bistroCoins'] ?? 0); //Coste seleccionado

    if ($error === '' && ($idProducto <= 0 || $bistroCoins <= 0)) { //Valida datos
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') { //Si no hay errores
        $ok = Recompensa::actualizar($id, $idProducto, $bistroCoins); //Actualiza recompensa
        if ($ok) { //Si se actualiza bien
            header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+actualizada'); //Redirige al listado
            exit;
        }

        $error = 'No se pudo actualizar la recompensa.'; //Mensaje si falla
    }
}

$productos = Producto::listarNombres(); //Lista productos
$tituloPagina = 'Actualizar recompensa'; //Titulo de la pagina
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Error seguro
$action = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/actualizarRecompensa.php?id='.urlencode((string)$id), ENT_QUOTES, 'UTF-8'); //URL del formulario
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php', ENT_QUOTES, 'UTF-8'); //URL para cancelar

$idProductoActual = (int)($recompensa['id_producto'] ?? 0); //Producto actual
$bistroCoinsActual = (int)($recompensa['bistroCoins'] ?? 0); //Coins actuales

$opcionesProductos = '<option value="">Selecciona un producto</option>'; //Opcion inicial
foreach ($productos as $producto) { //Recorre productos
    $idProd = (int)($producto['id'] ?? 0); //Id del producto
    $nombre = htmlspecialchars((string)($producto['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); //Nombre seguro
    $selected = $idProd === $idProductoActual ? 'selected' : ''; //Marca producto actual
    $opcionesProductos .= "<option value=\"{$idProd}\" {$selected}>{$nombre}</option>"; //Añade opcion
}

$bistroCoinsSafe = htmlspecialchars((string)$bistroCoinsActual, ENT_QUOTES, 'UTF-8'); //Coins seguros

$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Actualizar recompensa #{$id}</h1>
</div>

<form method="POST" action="$action" class="form-estandar">
    <div class="info-categoria">
        $errorHtml

        <input type="hidden" name="csrfToken" value="$csrfToken">

        <div class="campo-form">
            <label for="id_producto"><strong>Producto:</strong></label>
            <select id="id_producto" name="id_producto" required>
                $opcionesProductos
            </select>
        </div>

        <div class="campo-form">
            <label for="bistroCoins"><strong>BistroCoins:</strong></label>
            <input type="number" id="bistroCoins" name="bistroCoins" min="1" step="1" value="$bistroCoinsSafe" required>
        </div>
    </div>

    <div class="buttons-estandar">
        <button type="submit" class="button-estandar">Guardar cambios</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS; //HTML principal

require __DIR__.'/../plantillas/plantilla.php'; //Carga plantilla
