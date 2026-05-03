<?php
//Funcion que devuelve el texto de la zona de usuario
function mostrarSaludo() {
	$rutaUsuarios = rtrim(RUTA_APP, '/'); //Quita la / final de RUTA_APP si la tiene (evita dobles /)
	
	if (isset($_SESSION["login"]) && ($_SESSION["login"] === true)) { //Comprueba que hay sesion y esta logueado
        $nombre = htmlspecialchars((string)($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); //Guarda nombre y quita caracteres especiales (seguridad)
        return "Bienvenido, <strong>{$nombre}</strong> <br>
                <a href='{$rutaUsuarios}/logout.php' class='link-usuario'>(salir)</a>";
    } else { //Si no ha iniciado sesion
        return "Usuario desconocido <br>
                <a href='{$rutaUsuarios}/login.php' class='link-usuario'>Login</a>
                <span class='separador'> / </span>
                <a href='{$rutaUsuarios}/registro.php' class='link-usuario'>Registro</a>";
    }
} 

//Calculo de la ruta segun el rol
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
//HTML de la cabecera
?>
<header>
    <div class="logo-seccion">
        <a href="<?= RUTA_APP.'index.php' ?>" class="logo-enlace">
            <img src="<?= RUTA_IMGS ?>ui/bistroFDILogo.png?v=1" 
                 alt="Logo Bistro" class="img-logo">
            
            <img src="<?= RUTA_IMGS ?>ui/letrasBistroFDI.png" 
                 alt="Bistro FDI" class="img-letras">
        </a>
    </div>

    <div class="saludo">
        <?= mostrarSaludo() ?>
    </div>
</header>
