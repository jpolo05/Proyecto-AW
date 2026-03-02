<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/usuario_mysql.php'; // IMPORTANTE: Importar la función

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'] ?? null;
if (!$user){
    header('Location: login.php');
    exit;
}

$user = trim($_POST['user'] ?? '');

$exito = usuarios_borrar($user);

if ($exito) {    
    header('Location: registro.php');
} else {
    header('Location: borrarUsuarios.php?error=update&err=Error%20en%20la%20base%20de%20datos');
}
exit;