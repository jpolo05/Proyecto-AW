<?php
use es\ucm\fdi\aw\usuarios\Auth;
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
<div class="contenedor-perfil">
    <div class="seccion-titulo">
        <h2>Mi Perfil</h2>
    </div>

    <div class="tarjeta-usuario">
        <div class="foto-perfil">
            <img src="$imagen" class="img-perfil-grande" alt="Foto de $user">
        </div>

        <div class="info-personal">
            <p class="nickname">@$user</p>
            <h3 class="nombre-completo">$nombre $apellidos</h3>
            
            <div class="datos-contacto">
                <p><strong>Email:</strong> $email</p>
                <p><strong>Rol:</strong> <span class="badge-rol">$rol</span></p>
            </div>
        </div>
    </div>
</div>

<div class="buttons-estandar">
    <a href="{$rutaPedidos}" class="button-estandar">Mis Pedidos</a>
    <a href="actualizarUsuarios.php" class="button-estandar">Editar datos</a>
    <a href="borrarUsuarios.php" class="button-delete">Borrar cuenta</a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';





