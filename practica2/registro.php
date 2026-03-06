<?php

require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Registro';
$formulario = new \es\ucm\fdi\aw\FormularioRegistro();
$htmlFormularioRegistro = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h1>Registro a Bistro FDI</h1>
$htmlFormularioRegistro
<p>¿Ya tiene una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';