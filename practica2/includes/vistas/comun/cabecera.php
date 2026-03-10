<?php
function mostrarSaludo() {
	$rutaUsuarios = rtrim(RUTA_APP, '/');
	$html='';
	if (isset($_SESSION["login"]) && ($_SESSION["login"]===true)) {
		$nombre = htmlspecialchars((string)($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
		return "Bienvenido, {$nombre} <br><a href='{$rutaUsuarios}/logout.php'>(salir)</a>";
	} else {
		return "Usuario desconocido. <br><a href='{$rutaUsuarios}/login.php'>Login</a> <a href='{$rutaUsuarios}/registro.php'>Registro</a>";
	}
	return $html;
}

$aux = '';
$ruta = RUTA_APP.'index.php';
	switch ($_SESSION['rol'] ?? '') {
		case 'Gerente':
			$ruta = RUTA_APP.'includes/vistas/paneles/gerente.php';
			break;
	case 'Cocinero':
		$ruta = RUTA_APP.'includes/vistas/paneles/cocinero.php';
		break;
	case 'Camarero':
		$ruta = RUTA_APP.'includes/vistas/paneles/camarero.php';
		break;
	default:
		$aux = 'class = "none"';
}

?>
<header>
	<h1>
		<a href="<?= RUTA_APP.'index.php' ?>"><img src="<?= RUTA_IMGS.'ui/bistroFDILogo.png' ?>" alt="Logo Bistro FDI" width="100" height="100"></a>
	</h1>
	<h2>
		<a href="<?= $ruta ?>" <?= $aux ?>>
			Panel de Control
		</a>
	</h2>
	<div class="saludo">
		<?= mostrarSaludo() ?>
	</div>
</header>

