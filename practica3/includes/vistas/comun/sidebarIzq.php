<?php require_once __DIR__.'/../../config.php'; ?>

<nav>
	<h2>Navegacion</h2>
	<ul>
		<a href="<?= RUTA_APP ?>index.php">Inicio</a>
		<?php if (($_SESSION['rol'] ?? '') === 'Gerente') : ?>
			<li><a href="<?= RUTA_APP ?>includes/vistas/paneles/gerente.php">Panel gerente</a></li>
		<?php endif; ?>
		<?php if (($_SESSION['rol'] ?? '') === 'Cocinero') : ?>
			<li><a href="<?= RUTA_APP ?>includes/vistas/paneles/cocinero.php">Panel cocinero</a></li>
		<?php endif; ?>
		<?php if (($_SESSION['rol'] ?? '') === 'Camarero') : ?>
			<li><a href="<?= RUTA_APP ?>includes/vistas/paneles/camarero.php">Panel camarero</a></li>
		<?php endif; ?>
		<a href="<?= RUTA_APP ?>includes/vistas/productos/listarProductos.php">Carta</a>
		<a href="<?= RUTA_APP ?>includes/vistas/usuarios/visualizarUsuarios.php">Mi perfil</a>
	</ul>
</nav>

