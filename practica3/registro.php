<?php
use es\ucm\fdi\aw\usuarios\FormularioRegistro;
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Registro';
$formulario = new FormularioRegistro();
$htmlFormularioRegistro = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h2>Registro a Bistro FDI</h2>
$htmlFormularioRegistro
<p>¿Ya tiene una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';

