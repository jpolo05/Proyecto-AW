<?php
require_once __DIR__.'/includes/config.php'; //Carga config.php (1 sola vez)

$tituloPagina = 'Error';
$error = htmlspecialchars((string)($_GET['error'] ?? 'Ocurrió un error desconocido.'), ENT_QUOTES, 'UTF-8'); //$_GET['error'] recoge un mensaje de error enviado por la URL

//Muestra el mensaje guardado en $error
$contenidoPrincipal = <<<EOS
<h1>Error de sesión</h1>
<h3 class="error">$error</h3>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php'; //Carga la plantilla comun
