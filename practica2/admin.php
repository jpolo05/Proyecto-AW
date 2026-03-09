<?php
require_once __DIR__.'/includes/config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Gerente');

$tituloPagina = 'Administración - Bistro FDI';

$contenidoPrincipal = <<<EOS
<div>
    <h2 class="titulo">Panel de Administración - Bistro FDI</h2>
    <hr>
    <p class="desc">Seleccione una categoría para gestionar los recursos del sistema:</p>
    <br>

    <table class="control-panel">
        <tr>
            <td>
                <a href="includes/vistas/usuarios/listarUsuarios.php">
                    <button>Usuarios</button>
                </a>
            </td>
            <td>
                <a href="includes/vistas/categorias/listar.php">
                    <button>Categorías</button>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                <a href="includes/vistas/pedidos/listarPedidos.php">
                    <button>Pedidos</button>
                </a>
            </td>
            <td>
                <a href="includes/vistas/productos/listarProductos.php">
                    <button>Productos</button>
                </a>
            </td>
        </tr>
    </table>

    <br><br>
    <a href="index.php"><button>Volver al Inicio</button></a>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';


