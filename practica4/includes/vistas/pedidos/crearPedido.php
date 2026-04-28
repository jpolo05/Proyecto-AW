<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Oferta;
use es\ucm\fdi\aw\usuarios\Producto;
use es\ucm\fdi\aw\usuarios\Recompensa;
use es\ucm\fdi\aw\usuarios\Usuario;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [
        'tipo' => 'Local',
        'items' => [],
        'ofertas' => [],
        'recompensas' => [],
    ];
}
if (!isset($_SESSION['carrito']['ofertas']) || !is_array($_SESSION['carrito']['ofertas'])) {
    $_SESSION['carrito']['ofertas'] = [];
}
if (!isset($_SESSION['carrito']['recompensas']) || !is_array($_SESSION['carrito']['recompensas'])) {
    $_SESSION['carrito']['recompensas'] = [];
}

$error = '';
$mensaje = '';
$tipo = $_POST['tipo'] ?? ($_SESSION['carrito']['tipo'] ?? 'Local');
$productos = Producto::listar(true);
$ofertasActivas = Oferta::obtenerOfertasActivas();
$recompensasDisponibles = Recompensa::listarConProducto(true);
$mapaRecompensas = [];
foreach ($recompensasDisponibles as $rec) {
    $mapaRecompensas[(int)($rec['id'] ?? 0)] = $rec;
}
$usuarioSesion = Usuario::buscaUsuario((string)($_SESSION['user'] ?? ''));
$bistroCoinsCliente = $usuarioSesion ? (int)$usuarioSesion->getBistroCoins() : 0;
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF inválido.';
    }

    $cantidades = $_POST['cantidad'] ?? [];
    if (!is_array($cantidades)) {
        $cantidades = [];
    }
    $cantidadesRecompensa = $_POST['recompensa_cantidad'] ?? [];
    if (!is_array($cantidadesRecompensa)) {
        $cantidadesRecompensa = [];
    }

    $itemsActuales = is_array($_SESSION['carrito']['items'] ?? null) ? $_SESSION['carrito']['items'] : [];
    $itemsAnadidos = 0;

    foreach ($cantidades as $idProducto => $cantidad) {
        $id = (int)$idProducto;
        $cant = (int)$cantidad;
        if ($id > 0 && $cant > 0) {
            $itemsActuales[$id] = ((int)($itemsActuales[$id] ?? 0)) + $cant;
            $itemsAnadidos += $cant;
        }
    }

    $recompensasActuales = is_array($_SESSION['carrito']['recompensas'] ?? null) ? $_SESSION['carrito']['recompensas'] : [];
    $recompensasAnadidas = 0;
    foreach ($cantidadesRecompensa as $idRecompensa => $cantidad) {
        $id = (int)$idRecompensa;
        $cant = (int)$cantidad;
        if ($id > 0 && $cant > 0 && isset($mapaRecompensas[$id])) {
            $recompensasActuales[$id] = ((int)($recompensasActuales[$id] ?? 0)) + $cant;
            $recompensasAnadidas += $cant;
        }
    }

    if ($error === '' && $itemsAnadidos === 0 && $recompensasAnadidas === 0) {
        $error = 'Debes seleccionar al menos un producto o recompensa con cantidad mayor que cero.';
    } elseif ($error === '' && !in_array($tipo, ['Local', 'Llevar'], true)) {
        $error = 'Tipo de pedido no válido.';
    } else {
        $_SESSION['carrito']['tipo'] = $tipo;
        $_SESSION['carrito']['items'] = $itemsActuales;
        $_SESSION['carrito']['recompensas'] = $recompensasActuales;
        header('Location: '.RUTA_APP.'includes/vistas/pedidos/carrito.php?msg='.rawurlencode('Productos añadidos al carrito'));
        exit;
    }
}

$itemsCarrito = is_array($_SESSION['carrito']['items'] ?? null) ? $_SESSION['carrito']['items'] : [];
$unidadesCarrito = array_sum(array_map('intval', $itemsCarrito));

$tituloPagina = 'Crear pedido';
$errorHtml = $error !== '' ? '<p class="crear-pedido-centrado"><strong>'.h($error).'</strong></p>' : '';
$mensaje = $_GET['msg'] ?? '';
$mensajeHtml = $mensaje !== '' ? '<p class="crear-pedido-centrado"><strong>'.h($mensaje).'</strong></p>' : '';
$urlVolver = RUTA_APP.'includes/vistas/pedidos/carrito.php';
$action = h(RUTA_APP.'includes/vistas/pedidos/crearPedido.php');
$selLocal = ($tipo === 'Local') ? 'selected' : '';
$selLlevar = ($tipo === 'Llevar') ? 'selected' : '';
$urlCarrito = RUTA_APP.'includes/vistas/pedidos/carrito.php';

$bloqueOfertas = '<p class="crear-pedido-centrado">No hay ofertas activas disponibles actualmente.</p>';
if (!empty($ofertasActivas)) {
    $htmlOfertas = '';
    foreach ($ofertasActivas as $oferta) {
        $idOferta = (int)($oferta['id'] ?? 0);
        $nombreOferta = h((string)($oferta['nombre'] ?? ''));
        $descripcionOferta = h((string)($oferta['descripcion'] ?? ''));
        $finOferta = h((string)($oferta['fin'] ?? ''));
        $descuentoOferta = number_format((float)($oferta['descuento'] ?? 0), 2, '.', '');
        $urlVerOferta = RUTA_APP.'includes/vistas/ofertas/visualizarOferta.php?id='.$idOferta.'&origen=pedido';

        $htmlOfertas .= "
        <tr>
            <td>{$nombreOferta}</td>
            <td>{$descripcionOferta}</td>
            <td>{$finOferta}</td>
            <td>{$descuentoOferta}%</td>
            <td><a href='{$urlVerOferta}' class='button-estandar'>Ver</a></td>
        </tr>";
    }

    $bloqueOfertas = "
    <table class='tabla-ofertas-pedido'>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Fin</th>
            <th>Descuento</th>
            <th>Acción</th>
        </tr>
        {$htmlOfertas}
    </table>";
}

$filasProductos = '';
$totalInicial = 0.0;
foreach ($productos as $p) {
    $id = (int)($p['id'] ?? 0);
    $nombre = h((string)($p['nombre'] ?? ''));
    $precioBase = (float)($p['precio_base'] ?? 0);
    $iva = (int)($p['iva'] ?? 0);
    $precioFinal = $precioBase + ($precioBase * $iva / 100);
    $cantidadDefecto = (int)($_POST['cantidad'][$id] ?? 0);
    $totalInicial += ($precioFinal * $cantidadDefecto);
    $precioFinalTexto = number_format($precioFinal, 2, '.', '');

    $filasProductos .= '
    <tr>
        <td>'.$nombre.'</td>
        <td>'.$precioFinalTexto.'</td>
        <td><input type="number" min="0" step="1" name="cantidad['.$id.']" value="'.$cantidadDefecto.'" class="cantidad-producto" data-precio="'.$precioFinalTexto.'"></td>
    </tr>';
}

$bloqueProductos = '';
if ($filasProductos === '') {
    $bloqueProductos = '<p>No hay productos disponibles para pedir.</p>';
} else {
    $bloqueProductos = '
    <table class="tabla-productos-pedido">
        <tr>
            <th>Producto</th>
            <th>Precio (IVA incl.)</th>
            <th>Cantidad</th>
        </tr>
        '.$filasProductos.'
    </table>';
}

$totalInicialTexto = number_format($totalInicial, 2, '.', '');
$bloqueTotal = '<p><strong>Total del pedido: <span id="totalPedido">'.$totalInicialTexto.'</span> EUR</strong></p>';

$coinsNecesariosSeleccion = 0;
$bloqueRecompensas = '<p class="crear-pedido-centrado">No hay recompensas disponibles actualmente.</p>';
if (!empty($recompensasDisponibles)) {
    $filasRecompensas = '';
    foreach ($recompensasDisponibles as $recompensa) {
        $idRecompensa = (int)($recompensa['id'] ?? 0);
        $nombreProducto = h((string)($recompensa['nombre_producto'] ?? ''));
        $descripcionProducto = h((string)($recompensa['descripcion_producto'] ?? ''));
        $coins = (int)($recompensa['bistroCoins'] ?? 0);
        $cantidadDefecto = (int)($_POST['recompensa_cantidad'][$idRecompensa] ?? 0);
        $coinsNecesariosSeleccion += $coins * max(0, $cantidadDefecto);
        $estadoAplicable = ($coins > 0 && $bistroCoinsCliente >= $coins) ? 'Aplicable' : 'No aplicable';

        $filasRecompensas .= "
        <tr>
            <td>{$nombreProducto}</td>
            <td>{$descripcionProducto}</td>
            <td>{$coins} BC</td>
            <td>{$estadoAplicable}</td>
            <td><input type=\"number\" min=\"0\" step=\"1\" name=\"recompensa_cantidad[{$idRecompensa}]\" value=\"{$cantidadDefecto}\" class=\"cantidad-recompensa\" data-coins=\"{$coins}\"></td>
        </tr>";
    }

    $bloqueRecompensas = "
    <table class=\"tabla-recompensas-pedido\">
        <tr>
            <th>Producto recompensa</th>
            <th>Descripción</th>
            <th>Coste</th>
            <th>Estado</th>
            <th>Cantidad</th>
        </tr>
        {$filasRecompensas}
    </table>";
}

$contenidoPrincipal = <<<EOS
<div class="crear-pedido-contenido">
    <h1 class="crear-pedido-centrado">Crear pedido</h1>
    $errorHtml
    $mensajeHtml

    <div class="crear-pedido-info crear-pedido-centrado">
        <p><strong>Carrito actual:</strong> {$unidadesCarrito} producto(s). <a href="$urlCarrito" class="button-estandar">Ver carrito</a></p>
        <p><strong>BistroCoins disponibles:</strong> {$bistroCoinsCliente} BC</p>
        <p><strong>BistroCoins seleccionados en recompensas:</strong> <span id="coinsSeleccionadosPedido">{$coinsNecesariosSeleccion}</span> BC</p>
    </div>

    <form method="POST" action="$action" class="crear-pedido-formulario">
        <input type="hidden" name="csrfToken" value="$csrfToken">

        <div class="crear-pedido-seccion">
            <h2 class="crear-pedido-centrado">Recompensas disponibles</h2>
            $bloqueRecompensas
        </div>

        <div class="crear-pedido-seccion">
            <h2 class="crear-pedido-centrado">Ofertas disponibles</h2>
            $bloqueOfertas
        </div>

        <div class="crear-pedido-seccion">
            <h2 class="crear-pedido-centrado">Productos</h2>
            <p class="selector-tipo-pedido">
                <label>Tipo:
                    <select name="tipo">
                        <option value="Local" $selLocal>Local</option>
                        <option value="Llevar" $selLlevar>Llevar</option>
                    </select>
                </label>
            </p>
            $bloqueProductos
        </div>

        <div class="crear-pedido-seccion crear-pedido-centrado">
            $bloqueTotal
        </div>

        <p class="crear-pedido-acciones">
            <button type="submit" class='button-estandar'>Añadir al carrito</button>
            <a href="$urlVolver" class='button-estandar'>Ir al carrito</a>
        </p>
    </form>
</div>
EOS;

$rutaJsCrearPedido = dirname(__DIR__, 3).'/js/crearPedido.js';
$versionJsCrearPedido = @filemtime($rutaJsCrearPedido);
$urlJsCrearPedido = RUTA_JS.'crearPedido.js';
if ($versionJsCrearPedido !== false) {
    $urlJsCrearPedido .= '?v='.$versionJsCrearPedido;
}
$funcionesJS = "<script src='".h($urlJsCrearPedido)."'></script>";

require __DIR__.'/../plantillas/plantilla.php';
