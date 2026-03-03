<?php
session_start();
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Error';
$error = $_GET['error'] ?? 'Ocurrió un error desconocido.';

$contenidoPrincipal = <<<EOS
<h1>Error de sesión</h1>
<h3 style="color: red;">$error</h3>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';