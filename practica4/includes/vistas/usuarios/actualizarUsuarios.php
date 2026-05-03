<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
use es\ucm\fdi\aw\usuarios\FormularioActualizacion; //Usa la clase FormularioActualizacion
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente

$tituloPagina = 'Actualizar usuario';
$formulario = new FormularioActualizacion(); //Crea el formulario de actualizacion del usuario
$htmlFormulario = $formulario->gestiona(); //Llamada a gestiona()

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<h2>Actualizar usuario</h2>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
