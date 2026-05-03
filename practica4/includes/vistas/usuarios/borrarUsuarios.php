<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
use es\ucm\fdi\aw\usuarios\FormularioBorrar; //Usa la clase FormularioBorrar
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente

$tituloPagina = 'Eliminar mi usuario';
$formulario = new FormularioBorrar(); //Crea el formulario para borrar la cuenta
$htmlFormulario = $formulario->gestiona(); //Llamada a gestiona()

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<h2>Eliminación de cuenta</h2>
$htmlFormulario
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun





