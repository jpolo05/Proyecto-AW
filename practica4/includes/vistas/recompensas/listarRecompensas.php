<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Producto;
use es\ucm\fdi\aw\usuarios\Recompensa;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$recompensas = Recompensa::listar();

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$tituloPagina = 'Listado Recompensas';

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

$rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php';

$contenidoPrincipal = <<<EOS
<div class="contenedor-gestion">
    <div class="header-admin">
        <h2 class="seccion-titulo">Gestion de Recompensas</h2>
    </div>
    
    $tablaRecompensas
    <div class="buttons-estandar">
        <a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a>
        <a href="crearRecompensa.php" class="button-estandar">Crear recompensa</a>
    </div>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
