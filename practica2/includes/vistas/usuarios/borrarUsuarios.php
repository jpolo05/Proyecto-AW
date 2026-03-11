<?php
use es\ucm\fdi\aw\Auth;
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\FormularioBorrar;
Auth::verificarAcceso('Cliente');

require_once __DIR__.'/../../config.php';

$tituloPagina = 'Eliminar mi usuario';
$formulario = new FormularioBorrar();
$htmlFormulario = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h2>Eliminación de cuenta</h2>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php';





