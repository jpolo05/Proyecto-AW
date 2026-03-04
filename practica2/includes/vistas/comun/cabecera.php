<?php
function mostrarSaludo() {
	$rutaUsuarios = RUTA_APP.'includes/vistas/usuarios';
	$html='';
	if (isset($_SESSION["login"]) && ($_SESSION["login"]===true)) {
		return "Bienvenido, {$_SESSION['nombre']} <br><a href='{$rutaUsuarios}/logout.php'>(salir)</a>";
	} else {
		return "Usuario desconocido. <br><a href='{$rutaUsuarios}/login.php'>Login</a> <a href='{$rutaUsuarios}/registro.php'>Registro</a>";
	}
	return $html;
}

$aux = '';
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
		$aux = 'class = "none"';
}

?>
<header>
	<h1>
		<a href="<?= RUTA_APP.'index.php' ?>"><img src="<?= RUTA_IMGS.'bistroFDILogo.png' ?>" alt="Logo Bistro FDI" width="100" height="100"></a>
	</h1>
	<h2>
		<a href="<?= $ruta ?>"<?= $aux ?>>
			Panel de Control
		</a>
	</h2>
	<div class="saludo">
		<?= mostrarSaludo() ?>
	</div>
</header>

