<?php
require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Camarero');

require_once __DIR__.'/../../config.php';

$tituloPagina = 'AdministraciÃ³n - Bistro FDI';
$rutaInicio = RUTA_APP.'index.php';

$contenidoPrincipal = <<<EOS
<div>
    <h2 class="titulo">Panel de Camarero - Bistro FDI</h2>
    <hr>
    <p class="desc">Acceso habilitado. Este panel estÃ¡ preparado para incorporar las acciones operativas del camarero.</p>
    <br><br>
    <a href="$rutaInicio"><button class="button-estandar">Volver al Inicio</button></a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';



