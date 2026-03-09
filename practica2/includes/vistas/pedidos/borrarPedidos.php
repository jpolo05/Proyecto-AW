<?php
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\FormularioBorrarPedido;

require_once __DIR__.'/../../config.php';

$tituloPagina = 'Eliminar pedido';
$formulario = new FormularioBorrarPedido();
$htmlFormulario = $formulario->gestiona();

$contenidoPrincipal = <<<EOS
<h1>EliminaciÃ³n de pedido</h1>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php';





