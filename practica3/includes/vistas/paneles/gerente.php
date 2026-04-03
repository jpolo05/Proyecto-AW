<?php
use es\ucm\fdi\aw\usuarios\Auth;
require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$tituloPagina = 'Administracion - Bistro FDI';

$rutaUsuarios = RUTA_APP.'includes/vistas/usuarios/listarUsuarios.php';
$rutaCategorias = RUTA_APP.'includes/vistas/categorias/listarCategorias.php';
$rutaPedidos = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php';
$rutaProductos = RUTA_APP.'includes/vistas/productos/listarProductos.php';
$rutaOfertas = RUTA_APP.'includes/vistas/ofertas/listarOfertas.php';
$rutaInicio = RUTA_APP.'index.php';

$contenidoPrincipal = <<<EOS
<div>
    <div class="seccion-titulo">
        <h1>Panel de Administración</h1>
    </div>
    <hr>
    <p class="subtitulo-admin">Seleccione una categoría para gestionar los recursos del sistema:</p>
    <br>

    <div class="contenedor-admin">
        <div class="flex-panel">
            <a href="$rutaUsuarios" class="boton-panel">Usuarios</a>
            <a href="$rutaCategorias" class="boton-panel">Categorías</a>
            <a href="$rutaPedidos" class="boton-panel">Pedidos</a>
            <a href="$rutaProductos" class="boton-panel">Productos</a>
            <a href="$rutaOfertas" class="boton-panel">Ofertas</a>
        </div>

        <div class="botones-ordenar">
            <a href="$rutaInicio" class="button-estandar">Volver al Inicio</a>
        </div>
    </div>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';


