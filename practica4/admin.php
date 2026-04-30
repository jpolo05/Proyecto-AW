<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
require_once __DIR__.'/includes/config.php'; //Carga config.php (1 sola vez)

//Comprueba si el usuario actual tiene permiso de gerente
Auth::verificarAcceso('Gerente'); //Para llamar a verificarAcceso no hacemos new Auth() ya que es una llamada estatica

//Si el usuario tiene permiso, lo redirige al panel del gerente
header('Location: '.RUTA_APP.'includes/vistas/paneles/gerente.php'); //header envia una cabecera HTTP al navegador para dirigir a otra pagina
exit;
