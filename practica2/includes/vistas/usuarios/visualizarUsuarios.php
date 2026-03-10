<?php
use es\ucm\fdi\aw\Auth;
require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

$user = htmlspecialchars((string)($_SESSION['user'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$nombre = htmlspecialchars((string)($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
$apellidos = htmlspecialchars((string)($_SESSION['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string)($_SESSION['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$rol = htmlspecialchars((string)($_SESSION['rol'] ?? 'Cliente'), ENT_QUOTES, 'UTF-8');
$imagenSesion = $_SESSION['imagen'] ?? 'img/uploads/usuarios/default.jpg';

if (preg_match('/^https?:\\/\\//', $imagenSesion) === 1 || str_starts_with($imagenSesion, RUTA_APP)) {
    $imagen = $imagenSesion;
} else {
    $imagen = RUTA_APP.ltrim($imagenSesion, '/');
}

$tituloPagina = 'Perfil';
$rutaPedidos = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php';

$contenidoPrincipal = <<<EOS
    <h1>MI PERFIL</h1>
    
    <div>
        <p><strong> $user </strong></p> <br>
    </div>
    <div>
        <img src="$imagen" class="img-perfil" alt="Foto de perfil de $user"> <br>
    </div>
    <div>
        <p><strong> $nombre $apellidos </strong></p> <br>
        <p> Email: $email  <br>
        Rol: $rol </p> <br>
    </div>
    <div>
        <a href="{$rutaPedidos}"><button class="button-estandar">Mis Pedidos</button></a>
        <a href="actualizarUsuarios.php"><button class="button-estandar">Editar mis datos</button></a>
        <a href="borrarUsuarios.php"><button class="button-estandar">Borrar mi cuenta</button></a>
    </div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';





