<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Producto;
use es\ucm\fdi\aw\usuarios\Recompensa;
use es\ucm\fdi\aw\usuarios\Usuario;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');
$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$tituloPagina = 'Recompensas';
$rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php';
$urlVolverCarta = RUTA_APP.'includes/vistas/productos/listarProductos.php';

if ($esGerente) {
    $recompensas = Recompensa::listar();

    $tablaRecompensas = '
        <table>
            <tr>
                <th>Producto</th>
                <th>BistroCoins</th>
                <th>Acciones</th>
            </tr>';

    foreach ($recompensas as $r) {
        $id = (int)($r['id'] ?? 0);
        $idProducto = (int)($r['id_producto'] ?? 0);
        $bistroCoins = h((string)($r['bistroCoins'] ?? '0'));
        $nombreProducto = h(Producto::nombre($idProducto));

        $urlVer = 'visualizarRecompensa.php?id='.urlencode((string)$id);
        $urlEditar = 'actualizarRecompensa.php?id='.urlencode((string)$id);
        $urlBorrar = 'borrarRecompensa.php?id='.urlencode((string)$id);

        $producto = "<a href='{$urlVer}' class='link-usuario'>{$nombreProducto}</a>";
        $acciones = "
            <a href='{$urlVer}' class='link-usuario'>Ver</a>
            |
            <a href='{$urlEditar}' class='link-usuario'>Editar</a>
            |
            <a href='{$urlBorrar}' class='link-usuario'>Borrar</a>
        ";

        $tablaRecompensas .= "
            <tr>
                <td>{$producto}</td>
                <td>{$bistroCoins} BC</td>
                <td>{$acciones}</td>
            </tr>
        ";
    }
    $tablaRecompensas .= '</table>';

    $contenidoPrincipal = <<<EOS
<div class="contenedor-gestion">
    <div class="header-admin">
        <h2 class="seccion-titulo">Gestión de Recompensas</h2>
    </div>

    $tablaRecompensas
    <div class="buttons-estandar">
        <a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a>
        <a href="crearRecompensa.php" class="button-estandar">Crear recompensa</a>
    </div>
</div>
EOS;
} else {
    $recompensasDisponibles = Recompensa::listarConProducto(true);
    $usuarioSesion = Usuario::buscaUsuario((string)($_SESSION['user'] ?? ''));
    $bistroCoinsCliente = $usuarioSesion ? (int)$usuarioSesion->getBistroCoins() : 0;
    $tablaRecompensas = '<p class="ofertas-texto-centrado">No hay recompensas disponibles actualmente.</p>';

    if (!empty($recompensasDisponibles)) {
        $filas = '';
        foreach ($recompensasDisponibles as $r) {
            $nombre = h((string)($r['nombre_producto'] ?? ''));
            $descripcion = h((string)($r['descripcion_producto'] ?? ''));
            $coins = (int)($r['bistroCoins'] ?? 0);
            $estado = $bistroCoinsCliente >= $coins ? 'Aplicable' : 'No aplicable';

            $filas .= "
            <tr>
                <td>{$nombre}</td>
                <td>{$descripcion}</td>
                <td>{$coins} BC</td>
                <td>{$estado}</td>
            </tr>";
        }

        $tablaRecompensas = "
        <table class='tabla-carta-centro'>
            <tr>
                <th>Producto</th>
                <th>Descripción</th>
                <th>Coste</th>
                <th>Estado</th>
            </tr>
            {$filas}
        </table>";
    }

    $contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Recompensas disponibles</h1>
</div>
$tablaRecompensas
<div class="buttons-estandar">
    <a href="$urlVolverCarta" class="button-estandar">Volver a la carta</a>
</div>
EOS;
}

require __DIR__.'/../plantillas/plantilla.php';
