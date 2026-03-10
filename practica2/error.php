<?php
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Error';
$error = htmlspecialchars((string)($_GET['error'] ?? 'Ocurrio un error desconocido.'), ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
<h1>Error de sesion</h1>
<h3 class="error">$error</h3>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';
