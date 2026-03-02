<?php
function mostrarSaludo() {
	$rutaUsuarios = RUTA_APP.'/includes/vistas/usuarios';
	$html='';
	if (isset($_SESSION["login"]) && ($_SESSION["login"]===true)) {
		return "Bienvenido, {$_SESSION['nombre']} <a href='{$rutaUsuarios}/logout.php'>(salir)</a>";
	} else {
		return "Usuario desconocido. <a href='{$rutaUsuarios}/login.php'>Login</a> <a href='{$rutaUsuarios}/registro.php'>Registro</a>";
	}
	return $html;
}
?>
<header style="justify-content: space-between; display: flex; align-items: center;">
	<h1>Bistro FDI</h1>
	<div class="saludo">
	<?= mostrarSaludo() ?>
	</div>
</header>

