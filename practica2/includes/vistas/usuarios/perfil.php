<?php
session_start();

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!$idUsuario){
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
$apellidos = $_SESSION['apellidos'] ?? '';
$email = $_SESSION['email'] ?? '';
$rol = $_SESSION['rol'] ?? 'Cliente';
$imagen = $_SESSION['imagen'] ?? null; //null o un enlace a una foto a un perfil vacio

$tituloPagina = 'Perfil';

$contenidoPrincipal = <<<EOS
    <h1>MI PERFIL</h1>
    
    <div>
        <p><strong> $user </strong></p> <br>
    </div>
    <div>
        <img src="$imagen" style="width: 100px;" alt="Foto de perfil de $user"> <br>
    </div>
    <div>
        <p><strong> $nombre $apellidos </strong></p> <br>
        <p> Email: $email  <br>
        Rol: $rol </p> <br>
    </div>
    <div>
        <a href="actualizarUsuarios.php">Editar mis datos</a>
    </div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';