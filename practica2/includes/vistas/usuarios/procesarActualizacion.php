<?php
require_once __DIR__.'/../../auth.php';
verificarAcceso('Cliente');

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/usuario_mysql.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$usuarioEditar = $_POST['user'] ?? null;

if($usuarioEditar) {
    usuarios_actualiza_rol($usuarioEditar, $_POST['nuevoRol'] ?? 'Cliente');
    header('Location: listarUsuarios.php');
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
$imagen    = $_POST['imagen'] ?? $_SESSION['imagen'];
$rol       = $_POST['rol'] ?? $_SESSION['rol'];

if($pass1 === '' && $pass2 === '') {
    $pass1 = $pass2 = $hash = null;
}
else{
    $hash = password_hash($pass1, PASSWORD_DEFAULT);
}

if ($pass1 !== $pass2) {
    header('Location: '.RUTA_APP.'error.php?error=ActualizacionUsuario-Contrasenas%20distintas');
    exit;
}

$exito = usuarios_crear_editar($user, $email, $nombre, $apellidos, $hash, $rol, $imagen);

if ($exito) {
    
    $_SESSION['nombre']    = $nombre;
    $_SESSION['apellidos'] = $apellidos;
    $_SESSION['email']     = $email;
    $_SESSION['rol']       = $rol;  
    $_SESSION['imagen']    = $imagen;
    $_SESSION['isAdmin']   = ($rol === 'Gerente');
    
    header('Location: perfil.php');
} else {
    header('Location: '.RUTA_APP.'error.php?error=ActualizacionUsuario-Error%20sql');
}
exit;