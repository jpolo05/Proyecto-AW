<?php

require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioLogin;

$tituloPagina = 'Login';
$formulario = new FormularioLogin();
$htmlFormularioLogin = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h2>Acceso al sistema</h2>
$htmlFormularioLogin
<p>¿No tiene una cuenta? <a href="registro.php">Regí­strate aquí­</a></p>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';

