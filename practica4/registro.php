<?php
use es\ucm\fdi\aw\usuarios\FormularioRegistro; //Usa la clase FormularioRegistro
require_once __DIR__.'/includes/config.php'; //Carga config.php (1 sola vez)

$tituloPagina = 'Registro';
$formulario = new FormularioRegistro(); //Crea un objeto de la clase FormularioRegistro
$htmlFormularioRegistro = $formulario->gestiona(); //Llama al metodo gestiona del formulario

//La variable $htmlFormularioRegistro contiene el HTML que se va a mostrar
$contenidoPrincipal = <<<EOS
$htmlFormularioRegistro
<div class="enlace-registro">
    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php'; //Carga la plantilla comun

