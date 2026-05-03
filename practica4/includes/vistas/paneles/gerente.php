<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente

$tituloPagina = 'Administración - Bistro FDI';

$rutaUsuarios = RUTA_APP.'includes/vistas/usuarios/listarUsuarios.php'; //URL para gestionar usuarios
$rutaCategorias = RUTA_APP.'includes/vistas/categorias/listarCategorias.php'; //URL para gestionar categorias
$rutaPedidos = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php'; //URL para gestionar pedidos
$rutaProductos = RUTA_APP.'includes/vistas/productos/listarProductos.php'; //URL para gestionar productos
$rutaOfertas = RUTA_APP.'includes/vistas/ofertas/listarOfertas.php'; //URL para gestionar ofertas
$rutaRecompensas = RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php'; //URL para gestionar recompensas
$rutaInicio = RUTA_APP.'index.php'; //URL para volver al inicio

//HTML contenido principal (que vera el usuario)
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
            <a href="$rutaRecompensas" class="boton-panel">Recompensas</a>
        </div> 
    </div>
    <div class="buttons-estandar">
            <a href="$rutaInicio" class="button-estandar">Volver al Inicio</a>
    </div>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
