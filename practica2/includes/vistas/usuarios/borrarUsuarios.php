<?php
require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Cliente');

require_once __DIR__.'/../../config.php';

$tituloPagina = 'Eliminar mi usuario';
$formulario = new \es\ucm\fdi\aw\FormularioBorrar();
$htmlFormulario = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h1>EliminaciÃ³n de cuenta</h1>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php';



