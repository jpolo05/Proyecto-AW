<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/conexion.php';
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

$nombre    = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email     = trim($_POST['email'] ?? '');
$pass1     = $_POST['password'] ?? '';
$pass2     = $_POST['password_confirm'] ?? '';
$imagen    = $_POST['imagen'] ?? 'default.jpg';
$rol       = $_POST['rol'] ?? 'Cliente';

if ($pass1 !== $pass2) {
    header('Location: actualizarUsuarios.php?error=register&err=Contrasenas%20distintas');
    exit;
}

$hash = password_hash($pass1, PASSWORD_DEFAULT);

$exito = usuarios_crear_editar($user, $email, $nombre, $apellidos, $hash, $rol, $imagen);

if ($exito) {
    
    $_SESSION['nombre']    = $nombre;
    $_SESSION['apellidos'] = $apellidos;
    $_SESSION['rol']       = $rol;  
    $_SESSION['imagen']    = $imagen;
    $_SESSION['isAdmin']   = ($rol === 'Gerente');
    
    header('Location: perfil.php');
} else {
    header('Location: actualizarUsuarios.php?error=update&err=Error%20en%20la%20base%20de%20datos');
}
exit;