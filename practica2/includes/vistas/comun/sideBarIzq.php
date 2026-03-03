<?php require_once __DIR__.'/../../config.php';
?>

<nav id="sidebarIzq" style="display: flex;flex-direction: column;align-items: start;width: 200px; padding: 10px;">
	<h3>Navegación</h3>
	<ul>
		<li><a href="<?= RUTA_APP ?>index.php">Inicio</a></li>
		<li><a href="<?= RUTA_APP ?>includes/vistas/productos/carta.php">Carta</a></li>
		<li><a href="<?= RUTA_APP ?>includes/vistas/usuarios/perfil.php">Mi Perfil</a></li>
	</ul>
</nav>
