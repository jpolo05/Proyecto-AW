<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Pedido; //Usa la clase Pedido

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente

//Funcion para limpiar el texto (seguridad)
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$rol = $_SESSION['rol'] ?? 'Cliente'; //Recoge el rol de la sesion
$tituloPagina = 'Pedidos';
$encabezadoExtra = ''; //Prepara botones extra

if ($rol === 'Gerente') { //Si es gerente lista todos los pedidos
    $pedidos = Pedido::listar(); //Llama a listar (devuelve un array)
    $rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php'; //Ruta para volver al panel

    //Empieza a crear la tabla HTML
    $tablaPedidos = '
        <table class="tabla-carta-centro">
            <tr>
                <th>Número pedido</th>
                <th>Estado</th>
                <th>Tipo</th>
                <th>Cocinero</th>
                <th>Foto</th>
                <th>BistroCoins</th>
                <th>Total</th>
                <th>Acción</th>
            </tr>';

    foreach ($pedidos as $p) { //Recorre cada pedido obtenido de la BD (cada $p representa un pedido)

        //Recoge datos
        $numeroPedido = (int)($p['numeroPedido'] ?? 0);
        $estado = h((string)($p['estado'] ?? ''));
        $tipo = h((string)($p['tipo'] ?? ''));
        $cocinero = h((string)($p['cocinero'] ?? ''));
        $imagenCocinero = (string)($p['imagenCocinero'] ?? '');
        $coinsPedido = (int)($p['bistroCoinsGastados'] ?? 0);
        $total = number_format((float)($p['total'] ?? 0), 2, '.', '');

        $foto = '-'; //Por defecto no muestra foto
        if ($imagenCocinero !== '') { //Si hay imagen de cocinero
            $src = preg_match('/^https?:\/\//', $imagenCocinero)
                ? h($imagenCocinero)
                : RUTA_APP.ltrim($imagenCocinero, '/'); //Construye la ruta de la imagen
            $foto = "<img src='{$src}' width='50' height='50' alt='Cocinero'>"; //Crea el HTML de la imagen
        }

        $urlVer = 'visualizarPedido.php?numeroPedido='.$numeroPedido; //Crea enlace para ver el detalle del pedido

        //Anade 1 fila a la tabla
        $tablaPedidos .= "
        <tr>
            <td>{$numeroPedido}</td>
            <td>{$estado}</td>
            <td>{$tipo}</td>
            <td>{$cocinero}</td>
            <td>{$foto}</td>
            <td>{$coinsPedido} BC</td>
            <td>{$total}</td>
            <td><a href='{$urlVer}' class='button-estandar'>Ver pedido</a></td>
        </tr>";
    }
    $tablaPedidos .= '</table>'; //Cierra la tabla HTML
    $encabezadoExtra = '<div class="buttons-estandar"><a href="'.$rutaPanelGerente.'" class="button-estandar">Volver al Panel</a></div>'; //Crea boton para volver al panel
} else { //Si no es gerente lista solo los pedidos del cliente
    $usuario = $_SESSION['user'] ?? ''; //Recoge el usuario de la sesion
    $pedidos = Pedido::listar_cliente($usuario); //Lista los pedidos del cliente
    $urlCrearPedido = RUTA_APP.'includes/vistas/pedidos/crearPedido.php'; //URL para crear pedido
    $urlCarrito = RUTA_APP.'includes/vistas/pedidos/carrito.php'; //URL para ver carrito
    //Crea botones de acciones
    $encabezadoExtra = '<div class="buttons-estandar pedidos-acciones-finales"><a href="'.$urlCrearPedido.'" class="button-estandar">Añadir productos</a><a href="'.$urlCarrito.'" class="button-estandar">Ver carrito</a></div>';

    //Prepara arrays para separar pedidos
    $pedidosEnCurso = [];
    $pedidosCompletados = [];
    foreach ($pedidos as $p) { //Recorre todos los pedidos del cliente
        $estadoPedido = (string)($p['estado'] ?? '');
        if ($estadoPedido === Pedido::ESTADO_ENTREGADO) { //Si esta entregado va a completados
            $pedidosCompletados[] = $p;
        } else { //Si no esta entregado va a en curso
            $pedidosEnCurso[] = $p;
        }
    }

    //Empieza a crear la tabla de pedidos en curso
    $tablaPedidosEnCurso = '
        <table>
            <tr>
                <th>Número pedido</th>
                <th>Estado</th>
                <th>Tipo</th>
                <th>BistroCoins</th>
                <th>Total</th>
                <th>Acción</th>
            </tr>';

    foreach ($pedidosEnCurso as $p) { //Recorre pedidos en curso

        //Recoge datos
        $numeroPedido = (int)($p['numeroPedido'] ?? 0);
        $estadoPedido = (string)($p['estado'] ?? '');
        $estado = h($estadoPedido);
        $tipo = h((string)($p['tipo'] ?? ''));
        $coinsPedido = (int)($p['bistroCoinsGastados'] ?? 0);
        $total = number_format((float)($p['total'] ?? 0), 2, '.', '');
        $urlVer = 'visualizarPedido.php?numeroPedido='.$numeroPedido;
        $urlBorrar = 'borrarPedido.php?numeroPedido='.$numeroPedido;
        $accionCancelar = '';

        if (Pedido::clientePuedeCancelarEstado($estadoPedido)) { //Si el estado permite cancelar
            $accionCancelar = "<br><a href='{$urlBorrar}' class='button-estandar'>Cancelar/Borrar pedido</a>";
        }

        //Anade 1 fila a la tabla
        $tablaPedidosEnCurso .= "
        <tr>
            <td>{$numeroPedido}</td>
            <td>{$estado}</td>
            <td>{$tipo}</td>
            <td>{$coinsPedido} BC</td>
            <td>{$total}</td>
            <td>
                <a href='{$urlVer}' class='button-estandar'>Ver pedido</a>
                {$accionCancelar}
            </td>
        </tr>";
    }
    $tablaPedidosEnCurso .= '</table>'; //Cierra la tabla HTML

    if (empty($pedidosEnCurso)) { //Si no hay pedidos en curso
        $tablaPedidosEnCurso = '<p>No tienes pedidos en curso.</p>';
    } else { //Si hay pedidos, anade titulo
        $tablaPedidosEnCurso = '<h2 class="pedidos-subtitulo">Pedidos en curso</h2>'.$tablaPedidosEnCurso;
    }

    //Empieza a crear la tabla de pedidos completados
    $tablaPedidosCompletados = '
        <table>
            <tr>
                <th>Número pedido</th>
                <th>Estado</th>
                <th>Tipo</th>
                <th>BistroCoins</th>
                <th>Total</th>
                <th>Acción</th>
            </tr>';

    foreach ($pedidosCompletados as $p) { //Recorre pedidos completados

        //Recoge datos
        $numeroPedido = (int)($p['numeroPedido'] ?? 0);
        $estado = h((string)($p['estado'] ?? ''));
        $tipo = h((string)($p['tipo'] ?? ''));
        $coinsPedido = (int)($p['bistroCoinsGastados'] ?? 0);
        $total = number_format((float)($p['total'] ?? 0), 2, '.', '');
        $urlVer = 'visualizarPedido.php?numeroPedido='.$numeroPedido; //Crea enlace para ver el detalle del pedido

        //Anade 1 fila a la tabla
        $tablaPedidosCompletados .= "
        <tr>
            <td>{$numeroPedido}</td>
            <td>{$estado}</td>
            <td>{$tipo}</td>
            <td>{$coinsPedido} BC</td>
            <td>{$total}</td>
            <td><a href='{$urlVer}' class='button-estandar'>Ver pedido</a></td>
        </tr>";
    }
    $tablaPedidosCompletados .= '</table>'; //Cierra la tabla HTML

    if (empty($pedidosCompletados)) { //Si no hay pedidos completados
        $tablaPedidosCompletados = '<p>No tienes pedidos completados.</p>';
    } else { //Si hay pedidos, anade titulo
        $tablaPedidosCompletados = '<h2 class="pedidos-subtitulo">Pedidos completados</h2>'.$tablaPedidosCompletados;
    }

    $tablaPedidos = $tablaPedidosEnCurso.'<br>'.$tablaPedidosCompletados; //Une las tablas
}

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
    <div class="pedidos-centrado">
    <div class="seccion-titulo">
        <h1>Pedidos</h1>
    </div>
    $tablaPedidos
    $encabezadoExtra
    </div>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
