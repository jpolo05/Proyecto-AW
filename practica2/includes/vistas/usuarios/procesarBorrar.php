<?php
require_once __DIR__.'/../../auth.php';
verificarAcceso('Cliente');

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/usuario_mysql.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'] ?? null;

$exito = usuarios_borrar($user);

if ($exito) {    
    header('Location: registro.php?');
    session_destroy();
} else {
    header('Location: '.RUTA_APP.'error.php?error=BorrarUsuario-Error%20sql');
}
exit;