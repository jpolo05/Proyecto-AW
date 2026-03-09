<?php
use es\ucm\fdi\aw\Auth;
require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Cliente');

$user = $_SESSION['user'] ?? 'Usuario';
$nombre = $_SESSION['nombre'] ?? '';
$apellidos = $_SESSION['apellidos'] ?? '';
$email = $_SESSION['email'] ?? '';
$rol = $_SESSION['rol'] ?? 'Cliente';
$imagenSesion = $_SESSION['imagen'] ?? 'img/uploads/usuarios/default.jpg';

if (preg_match('/^https?:\\/\\//', $imagenSesion) === 1 || str_starts_with($imagenSesion, RUTA_APP)) {
    $imagen = $imagenSesion;
} else {
    $imagen = RUTA_APP.ltrim($imagenSesion, '/');
}

$tituloPagina = 'Perfil';
$rutaPedidos = RUTA_APP.'includes/vistas/pedidos/pedidosUsuario.php';

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





