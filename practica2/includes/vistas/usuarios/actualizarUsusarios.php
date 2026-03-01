<?php
session_start();

require_once __DIR__.'/../../config.php';

$user = $_SESSION['user'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
$apellidos = $_SESSION['apellidos'] ?? '';
$email = $_SESSION['email'] ?? '';
$rol = $_SESSION['rol'] ?? 'Cliente';
$imagen = $_SESSION['imagen'] ?? null; //null o un enlace a una foto a un perfil vacio

$tituloPagina = 'Actualizar usuario';

$selectRol = '';
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']):
    $selectRol = "$rol";
    $selectRol .= "<input type='hidden' name='rol' value='$rol' />";
else:
    $roles = ['Cliente', 'Cocinero', 'Camarero', 'Gerente'];

    foreach ($roles as $r){
        $checked = ($r === $rolActual) ? 'checked' : '';
        $selectRol .= "<input type='radio' name='rol' value='$r' $checked /> $r ";
    }

    // La idea principal
    // <input type="radio" name="rol" value="Cliente" checked/> Cliente
    // <input type="radio" name="rol" value="Cocinero" /> Cocinero
    // <input type="radio" name="rol" value="Camarero" /> Camarero
    // <input type="radio" name="rol" value="Gerente" /> Gerente
endif;

$contenidoPrincipal = <<<EOS
<h1>Acceso al sistema</h1>
<form action="procesarActualizacion.php" method="POST" enctype="multipart/form-data>
	<fieldset>
	<legend>Edita tu perfil</legend>
	<div>
		<label for="username">Nombre de usuario:</label>
		<input id="username" type="text" name="username" value="$user" required />
	</div>
    <div>
        <label for="nombre">Nombre:</label>
        <input id="nombre" type="text" name="nombre" value="$nombre" required />
    </div>
    <div>
        <label for="apellidos">Apellidos:</label>
        <input id="apellidos" type="text" name="apellidos" value="$apellidos" required>
    </div>
    <div>
        <label for="email">Email:</label>
        <input id="email" type="email" name="email" value="$email" required />
    </div>
	<div>
		<label for="password">Password:</label>
		<input id="password" type="password" name="password" />
	</div>
    <div>
        <label for="password_confirm">Introduzca la contraseña de nuevo:</label>
        <input id="password_confirm" type="password" name="password_confirm" required />
    </div>
    <div>
        <label for="rol">Rol: </label>
        $selectRol
    </div>
    <div>
        <label for="imagen">Imagen:</label>
        <select name="imagen" id="imagen" >
            <option value="default.jpg">Imagen por defecto</option>
            <option value="avatar1.jpg">Avatar 1</option>
            <option value="avatar2.jpg">Avatar 2</option>
            <option value="avatar3.jpg">Avatar 3</option>
            <option value="propia">Imagen Propia</option>
        </select>
    </div>
    <div id="subir_archivo">
        <label for="imgUser">Sube tu foto:</label>
        <input type="file" name="imgUser" id="imgUser" accept="image/*">
    </div>
	<div>
        <button type="reset" name="limpiar">Reset</button>
		<button type="submit" name="update">Actualizar</button>
	</div>
	</fieldset>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
