<?php

require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Administración - Bistro FDI';

$contenidoPrincipal = <<<EOS
<div align="center">
    <h2>Panel de Administración - Bistro FDI</h2>
    <hr style="width: 75%;">
    <p>Seleccione una categoría para gestionar los recursos del sistema:</p>
    <br>

    <table cellpadding="15">
        <tr>
            <td align="center">
                <a href="includes/vistas/usuarios/listarUsuarios.php">
                    <strong>Usuarios</strong>
                </a>
            </td>
            <td align="center">
                <a href="includes/vistas/categorias/listar.php">
                    <strong>Categorías</strong>
                </a>
            </td>
        </tr>
        <tr>
            <td align="center">
                <a href="includes/vistas/pedidos/listar.php">
                    <strong>Pedidos</strong>
                </a>
            </td>
            <td align="center">
                <a href="includes/vistas/productos/listar.php">
                    <strong>Productos</strong>
                </a>
            </td>
        </tr>
    </table>

    <br><br>
    <a href="index.php"><button>Volver al Inicio</button></a>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';