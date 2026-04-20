<?php
use es\ucm\fdi\aw\usuarios\Auth;
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\usuarios\FormularioActualizacion;
Auth::verificarAcceso('Cliente');

$tituloPagina = 'Actualizar usuario';
$formulario = new FormularioActualizacion();
$htmlFormulario = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h2>Actualizar usuario</h2>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php';
