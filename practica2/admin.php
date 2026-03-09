<?php
use es\ucm\fdi\aw\Auth;
require_once __DIR__.'/includes/config.php';
Auth::verificarAcceso('Gerente');

header('Location: '.RUTA_APP.'includes/vistas/paneles/gerente.php');
exit;


