<?php
require_once __DIR__.'/includes/config.php';

$_SESSION = [];
session_destroy();

$tituloPagina = 'Logout';

$contenidoPrincipal = <<<EOS
<h2>Hasta pronto!</h2>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';