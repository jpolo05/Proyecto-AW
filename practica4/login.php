<?php
require_once __DIR__.'/includes/config.php'; //Carga config.php (1 sola vez)
use es\ucm\fdi\aw\usuarios\FormularioLogin; //Usa la clase FormularioLogin

$tituloPagina = 'Login';
$formulario = new FormularioLogin(); //Crea un objeto de la clase FormularioLogin
$htmlFormularioLogin = $formulario->gestiona(); //Llama al metodo gestiona del formulario

//La variable $htmlFormularioLogin contiene el HTML que se va a mostrar
$contenidoPrincipal = <<<EOS
$htmlFormularioLogin
<div class="enlace-registro">
    <p>¿No tienes una cuenta? <a href="registro.php">Regí­strate aquí­</a></p>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php'; //Carga la plantilla comun

