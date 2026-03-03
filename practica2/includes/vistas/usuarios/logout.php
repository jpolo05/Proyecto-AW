<?php
session_start();
require_once __DIR__.'/../../config.php';

$_SESSION = array();

session_destroy();

$tituloPagina = 'Logout';

$contenidoPrincipal = <<<EOS
<h1>Hasta pronto!</h1>
EOS;

require __DIR__.'/../plantillas/plantilla.php';