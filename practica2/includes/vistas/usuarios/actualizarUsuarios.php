<?php
require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Cliente');

require_once __DIR__.'/../../config.php';

$tituloPagina = 'Actualizar usuario';
$formulario = new \es\ucm\fdi\aw\FormularioActualizacion();
$htmlFormulario = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h1>Acceso al sistema</h1>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php';



