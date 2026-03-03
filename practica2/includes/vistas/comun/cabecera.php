<?php
function mostrarSaludo() {
	$rutaUsuarios = RUTA_APP.'includes/vistas/usuarios';
	$html='';
	if (isset($_SESSION["login"]) && ($_SESSION["login"]===true)) {
		return "Bienvenido, {$_SESSION['nombre']} <a href='{$rutaUsuarios}/logout.php'>(salir)</a>";
	} else {
		return "Usuario desconocido. <a href='{$rutaUsuarios}/login.php'>Login</a> <a href='{$rutaUsuarios}/registro.php'>Registro</a>";
	}
	return $html;
}

switch ($_SESSION['rol'] ?? '') {
	case 'Gerente':
		$ruta = RUTA_APP.'admin.php';
		break;
	case 'Cocinero':
		$ruta = RUTA_APP.'cocinero.php';
		break;
	case 'Camarero':
		$ruta = RUTA_APP.'camarero.php';
		break;
	default:
		$ruta = RUTA_APP.'index.php';
}

?>
<header style="justify-content: space-between; display: flex; align-items: center;">
	<h1>
		<a href="<?= $ruta ?>" style="color: black; text-decoration: none;">Bistro FDI</a>
	</h1>
	<div class="saludo">
	<?= mostrarSaludo() ?>
	</div>
</header>

