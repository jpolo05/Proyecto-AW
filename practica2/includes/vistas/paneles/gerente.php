<?php
use es\ucm\fdi\aw\Auth;
require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$tituloPagina = 'Administracion - Bistro FDI';

$rutaUsuarios = RUTA_APP.'includes/vistas/usuarios/listarUsuarios.php';
$rutaCategorias = RUTA_APP.'includes/vistas/categorias/listarCategorias.php';
$rutaPedidos = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php';
$rutaProductos = RUTA_APP.'includes/vistas/productos/listarProductos.php';
$rutaInicio = RUTA_APP.'index.php';

$contenidoPrincipal = <<<EOS
<div>
    <h2 class="titulo">Panel de Administracion - Bistro FDI</h2>
    <hr>
    <p class="desc">Seleccione una categoria para gestionar los recursos del sistema:</p>
    <br>

    <table class="control-panel">
        <tr>
            <td>
                <a href="$rutaUsuarios">
                    <button>Usuarios</button>
                </a>
            </td>
            <td>
                <a href="$rutaCategorias">
                    <button>Categorias</button>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                <a href="$rutaPedidos">
                    <button>Pedidos</button>
                </a>
            </td>
            <td>
                <a href="$rutaProductos">
                    <button>Productos</button>
                </a>
            </td>
        </tr>
    </table>

    <br><br>
    <a href="$rutaInicio"><button>Volver al Inicio</button></a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';


