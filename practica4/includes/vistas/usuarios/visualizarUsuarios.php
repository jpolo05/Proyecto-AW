<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Usuario; //Usa la clase Usuario
require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente

//Convierte datos de sesion antes de meterlos en HTML (seguridad)
$user = htmlspecialchars((string)($_SESSION['user'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$nombre = htmlspecialchars((string)($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
$apellidos = htmlspecialchars((string)($_SESSION['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string)($_SESSION['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$rol = htmlspecialchars((string)($_SESSION['rol'] ?? 'Cliente'), ENT_QUOTES, 'UTF-8');
$imagenSesion = $_SESSION['imagen'] ?? 'img/uploads/usuarios/default.jpg'; //Recoge imagen de sesion o usa la de defecto
$bistroCoins = (int)($_SESSION['bistroCoins'] ?? 0); //Recoge BistroCoins de sesion

$usuarioActual = Usuario::buscaUsuario((string)($_SESSION['user'] ?? '')); //Busca usuario actual en la BD
if ($usuarioActual) { //Si lo encuentra, actualiza BistroCoins desde BD
    $bistroCoins = (int)$usuarioActual->getBistroCoins(); //Obtiene BistroCoins reales
    $_SESSION['bistroCoins'] = $bistroCoins; //Guarda BistroCoins actualizados en sesion
}

if (preg_match('/^https?:\\/\\//', $imagenSesion) === 1 || str_starts_with($imagenSesion, RUTA_APP)) { //Si la imagen ya es URL completa
    $imagen = $imagenSesion; //Usa la imagen tal cual
} else {
    $imagen = RUTA_APP.ltrim($imagenSesion, '/'); //Construye la ruta de la imagen
}

$tituloPagina = 'Perfil';
$rutaPedidos = RUTA_APP.'includes/vistas/pedidos/listarPedidos.php'; //URL para ver pedidos
$rutaCarrito = RUTA_APP.'includes/vistas/pedidos/carrito.php'; //URL para ver carrito

//HTML contenido principal (que vera el usuario)
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
                <p><strong>BistroCoins:</strong> $bistroCoins BC</p>
            </div>
        </div>
    </div>
</div>

<div class="buttons-estandar">
    <a href="{$rutaCarrito}" class="button-estandar">Mi Carrito</a>
    <a href="{$rutaPedidos}" class="button-estandar">Mis Pedidos</a>
    <a href="actualizarUsuarios.php" class="button-estandar">Editar datos</a>
    <a href="borrarUsuarios.php" class="button-delete">Borrar cuenta</a>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun





