<?php

require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\usuarios\FormularioLogin;

$tituloPagina = 'Login';
$formulario = new FormularioLogin();
$htmlFormularioLogin = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
$htmlFormularioLogin
<div class="enlace-registro">
    <p>¿No tienes una cuenta? <a href="registro.php">Regí­strate aquí­</a></p>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';

