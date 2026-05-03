<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Pedido; //Usa la clase Pedido
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto
use es\ucm\fdi\aw\usuarios\Recompensa; //Usa la clase Recompensa
use es\ucm\fdi\aw\usuarios\Usuario; //Usa la clase Usuario

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente
$ofertasActivas = \es\ucm\fdi\aw\usuarios\Oferta::obtenerOfertasActivas(); //Obtiene ofertas activas

//Funcion para limpiar el texto (seguridad)
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) { //Si todavia no existe el carrito, lo crea
    $_SESSION['carrito'] = [
        'tipo' => 'Local',
        'items' => [],
        'ofertas' => [],
        'recompensas' => [],
    ];
}

if (!isset($_SESSION['carrito']['ofertas']) || !is_array($_SESSION['carrito']['ofertas'])) { //Evita errores si no hay ofertas en sesion
    $_SESSION['carrito']['ofertas'] = [];
}
if (!isset($_SESSION['carrito']['recompensas']) || !is_array($_SESSION['carrito']['recompensas'])) { //Evita errores si no hay recompensas en sesion
    $_SESSION['carrito']['recompensas'] = [];
}

$usuarioSesion = Usuario::buscaUsuario((string)($_SESSION['user'] ?? '')); //Busca usuario en la BD
$bistroCoinsCliente = $usuarioSesion ? (int)$usuarioSesion->getBistroCoins() : 0; //Obtiene bistroCoins del usuario (si no usa 0)
$_SESSION['bistroCoins'] = $bistroCoinsCliente; //Guarda bistroCoins en sesion

$recompensasDisponibles = Recompensa::listarConProducto(true); //Lista recompensas disponibles
$mapaRecompensas = [];
foreach ($recompensasDisponibles as $rec) { //Crea un array donde la clave es el id de la recompensa
    $mapaRecompensas[(int)($rec['id'] ?? 0)] = $rec; //Guarda la recompensa para buscarla rapido
}

$csrfToken = Auth::getCsrfToken(); //Obtiene el token de seguridad
$error = ''; //Prepara mensaje error
$mensaje = $_GET['msg'] ?? ''; //Recoge mensaje de la URL

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la pagina se esta cargando por un envio de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Valida token
        $error = 'Token CSRF inválido.';
    } else {
        //Recoge datos enviados
        $accion = $_POST['accion'] ?? '';
        $tipo = $_POST['tipo'] ?? ($_SESSION['carrito']['tipo'] ?? 'Local');
        if (!in_array($tipo, ['Local', 'Llevar'], true)) { //Si el tipo no es valido usa Local
            $tipo = 'Local';
        }

        //Recoge cantidades de productos normales
        $cantidades = $_POST['cantidad'] ?? [];
        if (!is_array($cantidades)) { //Evita errores si no llega un array
            $cantidades = [];
        }
        //Recoge cantidades de recompensas
        $cantidadesRecompensa = $_POST['recompensa_cantidad'] ?? [];
        if (!is_array($cantidadesRecompensa)) { //Evita errores si no llega un array
            $cantidadesRecompensa = [];
        }

        $ofertaSeleccionada = (int)($_POST['oferta'] ?? 0); //Recoge oferta seleccionada
        $ofertasNormalizadas = $ofertaSeleccionada > 0 ? [$ofertaSeleccionada] : []; //Guarda la oferta en formato array

        $itemsNormalizados = [];
        foreach ($cantidades as $idProducto => $cantidad) { //Recorre cantidades de productos
            $id = (int)$idProducto;
            $cant = (int)$cantidad;
            if ($id > 0 && $cant > 0) { //Solo guarda cantidades validas
                $itemsNormalizados[$id] = $cant;
            }
        }

        $recompensasNormalizadas = [];
        foreach ($cantidadesRecompensa as $idRecompensa => $cantidad) { //Recorre cantidades de recompensas
            $id = (int)$idRecompensa;
            $cant = (int)$cantidad;
            if ($id > 0 && $cant > 0 && isset($mapaRecompensas[$id])) { //Solo guarda recompensas validas
                $recompensasNormalizadas[$id] = $cant;
            }
        }

        $coinsNecesarios = 0;
        foreach ($recompensasNormalizadas as $idRecompensa => $cantidadRec) { //Calcula los BistroCoins necesarios
            $coinsNecesarios += ((int)($mapaRecompensas[$idRecompensa]['bistroCoins'] ?? 0)) * $cantidadRec;
        }

        $_SESSION['carrito']['tipo'] = $tipo; //Guarda el tipo de pedido en sesion

        if ($accion === 'vaciar') { //Si pulsa vaciar, limpia el carrito
            $_SESSION['carrito']['items'] = [];
            $_SESSION['carrito']['ofertas'] = [];
            $_SESSION['carrito']['recompensas'] = [];
            header('Location: '.RUTA_APP.'includes/vistas/pedidos/carrito.php?msg=Carrito+vaciado'); //Redirige
            exit;
        }

        //Guarda el carrito actualizado en sesion
        $_SESSION['carrito']['items'] = $itemsNormalizados;
        $_SESSION['carrito']['ofertas'] = $ofertasNormalizadas;
        $_SESSION['carrito']['recompensas'] = $recompensasNormalizadas;

        if ($accion === 'actualizar') { //Si pulsa actualizar vuelve a crear pedido
            header('Location: '.RUTA_APP.'includes/vistas/pedidos/crearPedido.php?msg=Carrito+actualizado'); //Redirige
            exit;
        }

        if ($accion === 'finalizar') { //Si pulsa finalizar intenta crear el pedido
            if (empty($itemsNormalizados) && empty($recompensasNormalizadas)) { //Comprueba que el carrito no este vacio
                $error = 'El carrito está vacío.';
            } elseif ($coinsNecesarios > $bistroCoinsCliente) { //Comprueba que tenga BistroCoins suficientes
                $error = 'No tienes BistroCoins suficientes para las recompensas seleccionadas.';
            } else {
                $lineas = [];
                foreach ($itemsNormalizados as $idProducto => $cantidad) { //Prepara lineas de productos normales
                    $lineas[] = [
                        'idProducto' => (int)$idProducto,
                        'cantidad' => (int)$cantidad,
                    ];
                }

                $lineasRecompensa = [];
                foreach ($recompensasNormalizadas as $idRecompensa => $cantidad) { //Prepara lineas de recompensas
                    $lineasRecompensa[] = [
                        'idRecompensa' => (int)$idRecompensa,
                        'cantidad' => (int)$cantidad,
                    ];
                }

                $cliente = $_SESSION['user'] ?? ''; //Recoge el cliente de la sesion
                $numeroPedido = Pedido::crear($cliente, $tipo, $lineas, $ofertasNormalizadas, $lineasRecompensa); //Crea el pedido
                if ($numeroPedido !== null) { //Si se ha creado bien
                    $_SESSION['carrito'] = [
                        'tipo' => 'Local',
                        'items' => [],
                        'ofertas' => [],
                        'recompensas' => [],
                    ];
                    header('Location: '.RUTA_APP.'includes/vistas/pedidos/visualizarPedido.php?numeroPedido='.$numeroPedido); //Redirige al detalle
                    exit;
                }

                $error = 'No se pudo finalizar el pedido.'; //Mensaje error
            }
        }
    }
}

//Recoge datos guardados en el carrito
$tipo = $_SESSION['carrito']['tipo'] ?? 'Local';
$itemsCarrito = is_array($_SESSION['carrito']['items'] ?? null) ? $_SESSION['carrito']['items'] : [];
$ofertasGuardadas = is_array($_SESSION['carrito']['ofertas'] ?? null) ? $_SESSION['carrito']['ofertas'] : [];
$recompensasGuardadas = is_array($_SESSION['carrito']['recompensas'] ?? null) ? $_SESSION['carrito']['recompensas'] : [];

$tituloPagina = 'Mi carrito';
$errorHtml = $error !== '' ? '<p class="carrito-texto-centrado"><strong>'.h($error).'</strong></p>' : ''; //Prepara mensaje error
$mensajeHtml = $mensaje !== '' ? '<p class="carrito-texto-centrado"><strong>'.h($mensaje).'</strong></p>' : ''; //Prepara mensaje de informacion
$selLocal = ($tipo === 'Local') ? 'selected' : '';
$selLlevar = ($tipo === 'Llevar') ? 'selected' : '';

//Bloque de productos normales
$filas = '';
$total = 0.0;
foreach ($itemsCarrito as $idProducto => $cantidad) { //Recorre productos del carrito
    $producto = Producto::buscaPorId((int)$idProducto); //Busca producto en la BD
    if (!$producto) { //Si no existe lo salta
        continue;
    }

    //Recoge datos
    $nombre = h((string)($producto['nombre'] ?? ''));
    $descripcion = h((string)($producto['descripcion'] ?? ''));
    $precioBase = (float)($producto['precio_base'] ?? 0);
    $iva = (int)($producto['iva'] ?? 0);
    $precioFinal = $precioBase + ($precioBase * $iva / 100); //Calcula precio con IVA
    $subtotal = $precioFinal * (int)$cantidad;
    $total += $subtotal; //Suma al total
    $precioTexto = number_format($precioFinal, 2, '.', '');
    $subtotalTexto = number_format($subtotal, 2, '.', '');

    //Anade 1 fila a la tabla
    $filas .= '
    <tr>
        <td>'.$nombre.'</td>
        <td>'.$descripcion.'</td>
        <td>'.$precioTexto.' EUR</td>
        <td><input type="number" min="0" step="1" name="cantidad['.(int)$idProducto.']" value="'.(int)$cantidad.'" class="cantidad-carrito" data-precio="'.$precioTexto.'"></td>
        <td class="subtotal-linea">'.$subtotalTexto.' EUR</td>
    </tr>';
}

if ($filas === '') { //Si no hay productos normales
    $bloqueTabla = '<p>No hay productos normales en el carrito.</p>';
} else { //Si hay productos, crea la tabla
    $bloqueTabla = '
    <table>
        <tr>
            <th>Producto</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        '.$filas.'
    </table>';
}

$bloqueRecompensasDisponibles = '<p>No hay recompensas disponibles actualmente.</p>'; //Por defecto dice que no hay recompensas
$coinsNecesariosSeleccion = 0;
if (!empty($recompensasDisponibles)) { //Si hay recompensas
    $filasRecompensas = '';
    foreach ($recompensasDisponibles as $recompensa) { //Recorre todas las recompensas
        //Recoge datos
        $idRecompensa = (int)($recompensa['id'] ?? 0);
        $nombreProducto = h((string)($recompensa['nombre_producto'] ?? ''));
        $descripcionProducto = h((string)($recompensa['descripcion_producto'] ?? ''));
        $coins = (int)($recompensa['bistroCoins'] ?? 0);
        $cantidadSel = (int)($recompensasGuardadas[$idRecompensa] ?? 0);
        $aplicable = $coins > 0 && $bistroCoinsCliente >= $coins; //Comprueba si el cliente tiene suficientes BistroCoins
        $estadoAplicable = $aplicable ? 'Aplicable' : 'No aplicable';
        $coinsNecesariosSeleccion += $coins * max(0, $cantidadSel); //Suma los BistroCoins seleccionados

        //Anade 1 fila a la tabla
        $filasRecompensas .= '
        <tr>
            <td>'.$nombreProducto.'</td>
            <td>'.$descripcionProducto.'</td>
            <td>'.$coins.' BC</td>
            <td>'.$estadoAplicable.'</td>
            <td><input type="number" min="0" step="1" name="recompensa_cantidad['.$idRecompensa.']" value="'.$cantidadSel.'" class="cantidad-recompensa" data-coins="'.$coins.'"></td>
        </tr>';
    }

    //Formato de la tabla
    $bloqueRecompensasDisponibles = '
    <table>
        <tr>
            <th>Producto recompensa</th>
            <th>Descripción</th>
            <th>Coste</th>
            <th>Estado</th>
            <th>Cantidad</th>
        </tr>
        '.$filasRecompensas.'
    </table>';
}

$bloqueOfertasDisponibles = '<p>No hay ofertas activas disponibles actualmente.</p>'; //Por defecto dice que no hay ofertas
if (!empty($ofertasActivas)) { //Si hay ofertas activas
    $partesOfertas = [];
    foreach ($ofertasActivas as $oferta) { //Recorre todas las ofertas
        $esAplicable = true;
        foreach (($oferta['lineas'] ?? []) as $lineaOferta) { //Recorre requisitos de la oferta
            $idProd = (int)($lineaOferta['idProd'] ?? 0);
            $cantidadRequerida = (int)($lineaOferta['cantidad'] ?? 0);
            $cantidadCarrito = (int)($itemsCarrito[$idProd] ?? 0);
            if ($idProd <= 0 || $cantidadRequerida <= 0 || $cantidadCarrito < $cantidadRequerida) { //Si no cumple requisitos
                $esAplicable = false;
                break;
            }
        }

        if (!$esAplicable) { //Si no aplica, no la muestra
            continue;
        }

        //Recoge datos
        $idOferta = (int)($oferta['id'] ?? 0);
        $nombreOferta = h((string)($oferta['nombre'] ?? ''));
        $descripcionOferta = h((string)($oferta['descripcion'] ?? ''));
        $descuentoOferta = number_format((float)($oferta['descuento'] ?? 0), 2, '.', '');
        $checked = in_array($idOferta, $ofertasGuardadas, true) ? 'checked' : ''; //Marca si ya estaba seleccionada

        //Anade la oferta al bloque
        $partesOfertas[] = "
        <p>
            <label>
                <input type='radio' name='oferta' value='{$idOferta}' class='oferta-disponible' {$checked}>
                <strong>{$nombreOferta}</strong> - {$descripcionOferta} ({$descuentoOferta}%)
            </label>
        </p>";
    }
    if (!empty($partesOfertas)) { //Si hay ofertas aplicables
        $bloqueOfertasDisponibles = implode('', $partesOfertas);
    } else { //Si no hay ofertas aplicables
        $bloqueOfertasDisponibles = '<p>No hay ofertas aplicables a los productos seleccionados.</p>';
    }
}
$bloqueOfertasDisponibles = '<div class="carrito-ofertas-centro">'.$bloqueOfertasDisponibles.'</div>'; //Centra el bloque de ofertas

$totalTexto = number_format($total, 2, '.', ''); //Formatea el total
$ofertasJSONRaw = json_encode($ofertasActivas, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); //Convierte ofertas a JSON para JavaScript
if ($ofertasJSONRaw === false) { //Si falla el JSON usa array vacio
    $ofertasJSONRaw = '[]';
}
$ofertasJSON = htmlspecialchars($ofertasJSONRaw, ENT_QUOTES, 'UTF-8'); //Prepara JSON para meterlo en HTML

$rutaJsCarrito = dirname(__DIR__, 3).'/js/carrito.js'; //Ruta al archivo JS carrito.js
$versionJsCarrito = @filemtime($rutaJsCarrito); //Obtiene la fecha de ultima modificacion del archivo
$urlJsCarrito = RUTA_JS.'carrito.js'; //URL del JavaScript
if ($versionJsCarrito !== false) { //Comprueba si filemtime funciono
    $urlJsCarrito .= '?v='.$versionJsCarrito; //Anade la version a la URL del JS
}
$funcionesJS = "<script src='".h($urlJsCarrito)."'></script>"; //Crea la etiqueta HTML que carga el JavaScript

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Mi carrito</h1>
</div>

<div class="info-categoria carrito-centrado"> $errorHtml
    $mensajeHtml

    <input type="hidden" id="config-ofertas-json" value="$ofertasJSON">
    
    <form method="POST" class="form-estandar">
        <input type="hidden" name="csrfToken" value="$csrfToken">
        
        <div class="campo-form">
            <label for="tipo"><strong>Tipo de pedido:</strong></label>
            <select name="tipo" id="tipo">
                <option value="Local" $selLocal>Para tomar aquí</option>
                <option value="Llevar" $selLlevar>Para llevar</option>
            </select>
        </div>

        <div class="campo-form">
            <p class="coins-linea"><strong>BistroCoins disponibles:</strong> {$bistroCoinsCliente} BC</p>
            <p class="coins-linea"><strong>BistroCoins seleccionados en recompensas:</strong> <span id="coinsSeleccionados">{$coinsNecesariosSeleccion}</span> BC</p>
        </div>

        <div class="tabla-carrito-contenedor">
            <h2>Productos normales</h2>
            $bloqueTabla
        </div>

        <div class="tabla-carrito-contenedor">
            <h3>Recompensas disponibles</h3>
            $bloqueRecompensasDisponibles
        </div>

        <div class="seccion-ofertas-carrito">
            <h3><i class="fas fa-tag"></i> Ofertas aplicables</h3>
            <div class="bloque-gris">
                $bloqueOfertasDisponibles
            </div>
        </div>

        <div id="contenedorOfertas" class="seccion-ofertas-carrito">
            <h3><i class="fas fa-check-circle"></i> Ofertas seleccionadas</h3>
            <ul id="listaOfertasAplicadas" class="lista-limpia"></ul>
        </div>

        <div class="resumen-totales">
            <p>Subtotal: <span id="totalCarrito">$totalTexto</span> EUR</p>
            <p class="total-destacado">Total con descuento: <span id="totalCarritoDescuento">0.00</span> EUR</p>
        </div>

        <div class="buttons-estandar">
            <button type="submit" name="accion" value="actualizar" class="button-estandar">Actualizar carrito</button>
            <button type="submit" name="accion" value="finalizar" class="button-estandar">Finalizar pedido</button>
            <button type="submit" name="accion" value="vaciar" class="button-estandar btn-peligro">Vaciar carrito</button>
        </div>
    </form>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
