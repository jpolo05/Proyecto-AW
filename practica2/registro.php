<?php
use es\ucm\fdi\aw\FormularioRegistro;
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Registro';
$formulario = new FormularioRegistro();
$htmlFormularioRegistro = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h1>Registro a Bistro FDI</h1>
$htmlFormularioRegistro
<p>Â¿Ya tiene una cuenta? <a href="login.php">Inicia sesiÃ³n aquÃ­</a></p>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';

