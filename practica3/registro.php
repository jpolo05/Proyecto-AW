<?php
use es\ucm\fdi\aw\usuarios\FormularioRegistro;
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Registro';
$formulario = new FormularioRegistro();
$htmlFormularioRegistro = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
$htmlFormularioRegistro
<div class="enlace-registro">
    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';

