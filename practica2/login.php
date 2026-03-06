<?php

require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Login';
$formulario = new \es\ucm\fdi\aw\FormularioLogin();
$htmlFormularioLogin = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h1>Acceso al sistema</h1>
$htmlFormularioLogin
<p>¿No tiene una cuenta? <a href="registro.php">Regístrate aquí</a></p>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';