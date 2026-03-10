<?php
use es\ucm\fdi\aw\Auth;
use es\ucm\fdi\aw\Pedido;

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
    $tablaPedidos = '
        <table border="1" cellpadding="8">
            <tr>
                <th>Numero pedido</th>
                <th>Estado</th>
                <th>Tipo</th>
                <th>Cocinero</th>
                <th>Foto</th>
                <th>Total</th>
                <th>Accion</th>
            </tr>';

    foreach ($pedidos as $p) {
        $numeroPedido = (int)($p['numeroPedido'] ?? 0);
        $estado = h((string)($p['estado'] ?? ''));
        $tipo = h((string)($p['tipo'] ?? ''));
        $cocinero = h((string)($p['cocinero'] ?? ''));
        $imagenCocinero = (string)($p['imagenCocinero'] ?? '');
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
            <td>{$total}</td>
            <td><a href='{$urlVer}'><button>Ver pedido</button></a></td>
        </tr>";
    }
    $tablaPedidos .= '</table>';
} else {
    $usuario = $_SESSION['user'] ?? '';
    $pedidos = Pedido::listar_cliente($usuario);
    $urlCrearPedido = RUTA_APP.'includes/vistas/pedidos/crearPedido.php';
    $encabezadoExtra = '<p><a href="'.$urlCrearPedido.'"><button>Crear pedido</button></a></p>';

    $tablaPedidos = '
        <table border="1" cellpadding="8">
            <tr>
                <th>Numero pedido</th>
                <th>Estado</th>
                <th>Tipo</th>
                <th>Total</th>
                <th>Accion</th>
            </tr>';

    foreach ($pedidos as $p) {
        $numeroPedido = (int)($p['numeroPedido'] ?? 0);
        $estado = h((string)($p['estado'] ?? ''));
        $tipo = h((string)($p['tipo'] ?? ''));
        $total = number_format((float)($p['total'] ?? 0), 2, '.', '');
        $urlVer = 'visualizarPedido.php?numeroPedido='.$numeroPedido;
        $urlBorrar = 'borrarPedido.php?numeroPedido='.$numeroPedido;

        $tablaPedidos .= "
        <tr>
            <td>{$numeroPedido}</td>
            <td>{$estado}</td>
            <td>{$tipo}</td>
            <td>{$total}</td>
            <td>
                <a href='{$urlVer}'><button>Ver pedido</button></a>
                <br>
                <a href='{$urlBorrar}'><button>Cancelar/Borrar pedido</button></a>
            </td>
        </tr>";
    }
    $tablaPedidos .= '</table>';
}

$contenidoPrincipal = <<<EOS
    <h1>Pedidos</h1>
    $encabezadoExtra
    $tablaPedidos
EOS;

require __DIR__.'/../plantillas/plantilla.php';
