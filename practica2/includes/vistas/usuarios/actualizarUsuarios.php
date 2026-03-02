<?php

require_once __DIR__.'/../../config.php';

// 1. Recogida de datos de la sesión
$user = $_SESSION['user'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
$apellidos = $_SESSION['apellidos'] ?? '';
$email = $_SESSION['email'] ?? '';
$rol = $_SESSION['rol'] ?? 'Cliente';
$imagen = $_SESSION['imagen'] ?? null;
$isAdmin = $rol === 'Gerente';

$tituloPagina = 'Actualizar usuario';

$selectRol = '';

if (!$isAdmin) {
    $selectRol = "<label>Rol asignado: </label>";
    $selectRol .= "<span>$rol</span>";
    $selectRol .= "<input type='hidden' name='rol' value='$rol' />";
} else {
    $roles = ['Cliente', 'Cocinero', 'Camarero', 'Gerente'];

    foreach ($roles as $r) {
        $checked = ($r === $rol) ? 'checked' : '';
        $selectRol .= "<label><input type='radio' name='rol' value='$r' $checked /> $r</label> ";
    }
}

$editandoPass = isset($_GET['editarPass']) && $_GET['editarPass'] == 1;

$estadoInput = $editandoPass ? '' : 'disabled';
$estadoBoton = $editandoPass ? 'disabled' : '';
$enlaceBoton = $editandoPass ? '' : 'actualizarUsuarios.php?editarPass=1';

$contenidoPrincipal = <<<EOS
<h1>Acceso al sistema</h1>
<form action="procesarActualizacion.php" method="POST" enctype="multipart/form-data">
    <fieldset>
    <legend><strong>Edita tu perfil: $user</strong></legend>
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
        <label for="password">Password (dejar en blanco para no cambiar):</label>
        <input id="password" type="password" name="password" $estadoInput />
    </div>
    <div>
        <label for="password_confirm">Confirme contraseña:</label>
        <input id="password_confirm" type="password" name="password_confirm" $estadoInput />
    </div>
    <a href="$enlaceBoton" type="button" $estadoBoton>Editar contraseña</a>
    <div>
        $selectRol
    </div>
    <div>
        <label for="imagen">Imagen:</label>
        <select name="imagen" id="imagen">
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
        <button type="reset">Reset</button>
        <button type="submit" name="update">Actualizar</button>
    </div>
    </fieldset>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';