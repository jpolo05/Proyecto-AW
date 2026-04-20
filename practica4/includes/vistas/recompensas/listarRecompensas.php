<?php
use es\ucm\fdi\aw\usuarios\Auth;
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\usuarios\Recompensa;
use es\ucm\fdi\aw\usuarios\Producto;

$recompensas = Recompensa::listar();

function h(string $text): string {
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
    $id = h((string)$r['id'], ENT_QUOTES, 'UTF-8');
    $idProducto = h((string)$r['id_producto'], ENT_QUOTES, 'UTF-8');
    $bistroCoins = h((string)$r['bistroCoins'], ENT_QUOTES, 'UTF-8');
    $producto = Producto::nombre($idProducto);

    $urlEditar = 'actualizarRecompensa.php?id=' . urlencode((string)$id);
    $urlBorrar = 'borrarRecompensa.php?id=' . urlencode((string)$id);
    $acciones = "
        <a href='{$urlEditar}' class='link-usuario'>Editar</a>
        |
        <a href='{$urlBorrar}' class='link-usuario'>Borrar</a>
    ";

    $tablaRecompensas .= "
        <tr>
            <td>$producto</td>
            <td>$bistroCoins b€</td>
            <td>$acciones</td>
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
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
