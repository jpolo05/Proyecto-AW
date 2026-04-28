<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Pedido;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$rol = $_SESSION['rol'] ?? 'Cliente';
$tituloPagina = 'Pedidos';
$encabezadoExtra = '';

if ($rol === 'Gerente') {
    $pedidos = Pedido::listar();
    $rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php';
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

    foreach ($pedidos as $p) {
        $numeroPedido = (int)($p['numeroPedido'] ?? 0);
        $estado = h((string)($p['estado'] ?? ''));
        $tipo = h((string)($p['tipo'] ?? ''));
        $cocinero = h((string)($p['cocinero'] ?? ''));
        $imagenCocinero = (string)($p['imagenCocinero'] ?? '');
        $coinsPedido = (int)($p['bistroCoinsGastados'] ?? 0);
        $total = number_format((float)($p['total'] ?? 0), 2, '.', '');

        $foto = '-';
        if ($imagenCocinero !== '') {
            $src = preg_match('/^https?:\/\//', $imagenCocinero)
                ? h($imagenCocinero)
                : RUTA_APP.ltrim($imagenCocinero, '/');
            $foto = "<img src='{$src}' width='50' height='50' alt='Cocinero'>";
        }

        $urlVer = 'visualizarPedido.php?numeroPedido='.$numeroPedido;
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
    $tablaPedidos .= '</table>';
    $encabezadoExtra = '<div class="buttons-estandar"><a href="'.$rutaPanelGerente.'" class="button-estandar">Volver al Panel</a></div>';
} else {
    $usuario = $_SESSION['user'] ?? '';
    $pedidos = Pedido::listar_cliente($usuario);
    $urlCrearPedido = RUTA_APP.'includes/vistas/pedidos/crearPedido.php';
    $urlCarrito = RUTA_APP.'includes/vistas/pedidos/carrito.php';
    $encabezadoExtra = '<div class="buttons-estandar pedidos-acciones-finales"><a href="'.$urlCrearPedido.'" class="button-estandar">Añadir productos</a><a href="'.$urlCarrito.'" class="button-estandar">Ver carrito</a></div>';

    $pedidosEnCurso = [];
    $pedidosCompletados = [];
    foreach ($pedidos as $p) {
        $estadoPedido = (string)($p['estado'] ?? '');
        if ($estadoPedido === Pedido::ESTADO_ENTREGADO) {
            $pedidosCompletados[] = $p;
        } else {
            $pedidosEnCurso[] = $p;
        }
    }

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

    foreach ($pedidosEnCurso as $p) {
        $numeroPedido = (int)($p['numeroPedido'] ?? 0);
        $estadoPedido = (string)($p['estado'] ?? '');
        $estado = h($estadoPedido);
        $tipo = h((string)($p['tipo'] ?? ''));
        $coinsPedido = (int)($p['bistroCoinsGastados'] ?? 0);
        $total = number_format((float)($p['total'] ?? 0), 2, '.', '');
        $urlVer = 'visualizarPedido.php?numeroPedido='.$numeroPedido;
        $urlBorrar = 'borrarPedido.php?numeroPedido='.$numeroPedido;
        $accionCancelar = '';

        if (Pedido::clientePuedeCancelarEstado($estadoPedido)) {
            $accionCancelar = "<br><a href='{$urlBorrar}' class='button-estandar'>Cancelar/Borrar pedido</a>";
        }

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
    $tablaPedidosEnCurso .= '</table>';

    if (empty($pedidosEnCurso)) {
        $tablaPedidosEnCurso = '<p>No tienes pedidos en curso.</p>';
    } else {
        $tablaPedidosEnCurso = '<h2 class="pedidos-subtitulo">Pedidos en curso</h2>'.$tablaPedidosEnCurso;
    }

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

    foreach ($pedidosCompletados as $p) {
        $numeroPedido = (int)($p['numeroPedido'] ?? 0);
        $estado = h((string)($p['estado'] ?? ''));
        $tipo = h((string)($p['tipo'] ?? ''));
        $coinsPedido = (int)($p['bistroCoinsGastados'] ?? 0);
        $total = number_format((float)($p['total'] ?? 0), 2, '.', '');
        $urlVer = 'visualizarPedido.php?numeroPedido='.$numeroPedido;

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
    $tablaPedidosCompletados .= '</table>';

    if (empty($pedidosCompletados)) {
        $tablaPedidosCompletados = '<p>No tienes pedidos completados.</p>';
    } else {
        $tablaPedidosCompletados = '<h2 class="pedidos-subtitulo">Pedidos completados</h2>'.$tablaPedidosCompletados;
    }

    $tablaPedidos = $tablaPedidosEnCurso.'<br>'.$tablaPedidosCompletados;
}

$contenidoPrincipal = <<<EOS
    <div class="pedidos-centrado">
    <div class="seccion-titulo">
        <h1>Pedidos</h1>
    </div>
    $tablaPedidos
    $encabezadoExtra
    </div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
