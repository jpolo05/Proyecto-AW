<?php
function mostrarSaludo() {
    $rutaUsuarios = rtrim(RUTA_APP, '/');
    
    if (isset($_SESSION["login"]) && ($_SESSION["login"] === true)) {
        $nombre = htmlspecialchars((string)($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        // Usamos el mismo div para que el diseño sea idéntico
        return "<div class='enlace-registro'>
                    <p>Bienvenido, <strong>{$nombre}</strong> <br>
                    <a href='{$rutaUsuarios}/logout.php' class='link-usuario'>(salir)</a></p>
                </div>";
    } else {
        return "<div class='enlace-registro'>
                    <p>Usuario desconocido <br>
                    <a href='{$rutaUsuarios}/login.php' class='link-usuario'>Login</a>
                    <span class='separador'> / </span>
                    <a href='{$rutaUsuarios}/registro.php' class='link-usuario'>Registro</a></p>
                </div>";
    }
}
//REVISAR TAMAÑOS DE CABECERA

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
