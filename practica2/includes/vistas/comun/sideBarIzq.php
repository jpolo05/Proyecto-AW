<?php require_once __DIR__.'/../../config.php'; ?>

<nav>
	<h3>Navegacion</h3>
	<ul>
		<li><a href="<?= RUTA_APP ?>index.php">Inicio</a></li>
		<?php if (($_SESSION['rol'] ?? '') === 'Gerente') : ?>
			<li><a href="<?= RUTA_APP ?>includes/vistas/paneles/gerente.php">Panel gerente</a></li>
		<?php endif; ?>
		<li><a href="<?= RUTA_APP ?>includes/vistas/productos/listarProductos.php">Carta</a></li>
		<li><a href="<?= RUTA_APP ?>includes/vistas/usuarios/perfil.php">Mi perfil</a></li>
	</ul>
</nav>
