<?php
require_once __DIR__.'/includes/config.php'; //Carga config.php (1 sola vez)

$_SESSION = []; //Vacia la variable de sesion (Borra los datos de la sesion)
session_destroy(); //Destruye la sesion con el servidor

$tituloPagina = 'Logout'; 

$contenidoPrincipal = <<<EOS
<h2>Hasta pronto!</h2>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php'; //Carga la plantilla comun