<?php
use es\ucm\fdi\aw\Auth;
require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Camarero');

require_once __DIR__.'/../../config.php';

$tituloPagina = 'Administración - Bistro FDI';
$rutaInicio = RUTA_APP.'index.php';

$contenidoPrincipal = <<<EOS
<div>
    <h2 class="titulo">Panel de Camarero - Bistro FDI</h2>
    <hr>
    <p class="desc">Acceso habilitado. Este panel está preparado para incorporar las acciones operativas del camarero.</p>
    <br><br>
    <a href="$rutaInicio"><button class="button-estandar">Volver al Inicio</button></a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';





